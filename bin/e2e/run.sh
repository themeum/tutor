#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$ROOT"

bash bin/e2e/ensure-docker.sh

WP_VERSION=""
PHP_VERSION=""
PLAYWRIGHT_ARGS=()

for arg in "$@"; do
  case "$arg" in
    --wp=*) WP_VERSION="${arg#*=}" ;;
    --php=*) PHP_VERSION="${arg#*=}" ;;
    *) PLAYWRIGHT_ARGS+=("$arg") ;;
  esac
done

# If tutor-pro exists locally, ensure it is mapped in .wp-env.override.json
if [[ -d "../tutor-pro" ]]; then
  if [[ ! -f ".wp-env.override.json" ]]; then
    printf '{\n  "mappings": {\n    "wp-content/plugins/tutor-pro": "../tutor-pro"\n  }\n}\n' > .wp-env.override.json
  fi
fi

RESOLVED_WP="${WP_VERSION:-${WP_ENV_CORE:-7.1.3}}"
RESOLVED_PHP="${PHP_VERSION:-${WP_ENV_PHP_VERSION:-8.3}}"

if [[ -n "$WP_VERSION" ]]; then
  export WP_ENV_CORE="WordPress/WordPress#${WP_VERSION}"
fi
if [[ -n "$PHP_VERSION" ]]; then
  export WP_ENV_PHP_VERSION="$PHP_VERSION"
fi

mkdir -p .e2e
VERSION_FILE=".e2e/versions.txt"
CURRENT_VERSIONS="${RESOLVED_WP}
${RESOLVED_PHP}"

if [[ ! -f vendor/autoload.php || ! -f assets/js/tutor.js ]]; then
  echo "E2E: installing PHP dependencies and building plugin assets."
  pnpm build-dev
fi

if [[ -f "$VERSION_FILE" && "$(cat "$VERSION_FILE")" != "$CURRENT_VERSIONS" ]]; then
  echo "E2E: WordPress/PHP changed; recreating wp-env."
  pnpm exec wp-env destroy || true
fi
printf '%s\n' "$CURRENT_VERSIONS" > "$VERSION_FILE"

echo "E2E: WordPress ${RESOLVED_WP}, PHP ${RESOLVED_PHP}"

pnpm exec wp-env start

# Auto-activate Tutor Pro if available in the unified environment
if [[ -d "../tutor-pro" && "${E2E_PRO:-1}" != "0" ]]; then
  pnpm exec wp-env run cli -- wp plugin activate tutor-pro >/dev/null 2>&1 || true
  if [[ -f "../tutor-pro/bin/e2e/seed-pro.php" ]]; then
    pnpm exec wp-env run cli -- wp eval-file "wp-content/plugins/tutor-pro/bin/e2e/seed-pro.php" >/dev/null 2>&1 || true
  fi
else
  # Ensure Pro is deactivated for clean Core/Free testing
  pnpm exec wp-env run cli -- wp plugin deactivate tutor-pro >/dev/null 2>&1 || true
fi

if [[ -f "bin/e2e/seed-admin.php" ]]; then
  pnpm exec wp-env run cli -- wp eval-file "wp-content/plugins/tutor/bin/e2e/seed-admin.php" >/dev/null 2>&1 || true
fi

pnpm exec playwright install chromium
pnpm exec playwright test ${PLAYWRIGHT_ARGS[@]+"${PLAYWRIGHT_ARGS[@]}"}
