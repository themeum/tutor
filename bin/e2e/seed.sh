#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
SLUG="$(basename "$ROOT")"
cd "$ROOT"

pnpm exec wp-env run cli -- wp rewrite structure '/%postname%/' --hard
pnpm exec wp-env run cli -- wp eval-file "wp-content/plugins/${SLUG}/bin/e2e/seed.php"

if [[ -f "bin/e2e/seed-admin.php" ]]; then
  pnpm exec wp-env run cli -- wp eval-file "wp-content/plugins/${SLUG}/bin/e2e/seed-admin.php"
fi

if [[ -f "../tutor-pro/bin/e2e/seed-pro.php" ]] && pnpm exec wp-env run cli -- test -f "wp-content/plugins/tutor-pro/bin/e2e/seed-pro.php" &>/dev/null; then
  if pnpm exec wp-env run cli -- wp plugin is-active tutor-pro &>/dev/null; then
    pnpm exec wp-env run cli -- wp eval-file "wp-content/plugins/tutor-pro/bin/e2e/seed-pro.php"
  fi
fi
