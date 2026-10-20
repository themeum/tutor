#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$ROOT"

WP_VERSION=""
PHP_VERSION=""
PLAYWRIGHT_ARGS=()
WP_ENV_ARGS=()

for arg in "$@"; do
  case "$arg" in
    --wp=*) WP_VERSION="${arg#*=}" ;;
    --php=*) PHP_VERSION="${arg#*=}" ;;
    *) PLAYWRIGHT_ARGS+=("$arg") ;;
  esac
done

if [[ "${E2E_PRO:-0}" == "1" ]]; then
  if [[ ! -d ../tutor-pro ]]; then
    echo "E2E: E2E_PRO=1 but ../tutor-pro does not exist." >&2
    exit 1
  fi

  node -e '
    const fs = require("fs");
    const config = JSON.parse(fs.readFileSync(".wp-env.json", "utf8"));
    const plugins = config.plugins ?? ["."];
    config.plugins = [plugins[0], "../tutor-pro", ...plugins.slice(1)];
    fs.writeFileSync(".wp-env.pro.json", `${JSON.stringify(config, null, 2)}\n`);
  '

  export E2E_WP_ENV_CONFIG=".wp-env.pro.json"
  WP_ENV_ARGS+=(--config "$E2E_WP_ENV_CONFIG")
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
  pnpm exec wp-env ${WP_ENV_ARGS[@]+"${WP_ENV_ARGS[@]}"} destroy || true
fi
printf '%s\n' "$CURRENT_VERSIONS" > "$VERSION_FILE"

echo "E2E: WordPress ${RESOLVED_WP}, PHP ${RESOLVED_PHP}"

pnpm exec wp-env ${WP_ENV_ARGS[@]+"${WP_ENV_ARGS[@]}"} start
pnpm exec wp-env ${WP_ENV_ARGS[@]+"${WP_ENV_ARGS[@]}"} reset all
pnpm exec wp-env ${WP_ENV_ARGS[@]+"${WP_ENV_ARGS[@]}"} start
pnpm exec playwright install chromium
pnpm exec playwright test ${PLAYWRIGHT_ARGS[@]+"${PLAYWRIGHT_ARGS[@]}"}
