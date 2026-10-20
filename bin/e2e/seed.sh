#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
SLUG="$(basename "$ROOT")"
cd "$ROOT"

WP_ENV_ARGS=()
if [[ -n "${E2E_WP_ENV_CONFIG:-}" ]]; then
  WP_ENV_ARGS+=(--config "$E2E_WP_ENV_CONFIG")
fi

pnpm exec wp-env ${WP_ENV_ARGS[@]+"${WP_ENV_ARGS[@]}"} run cli -- wp rewrite structure '/%postname%/' --hard
pnpm exec wp-env ${WP_ENV_ARGS[@]+"${WP_ENV_ARGS[@]}"} run cli -- wp eval-file "wp-content/plugins/${SLUG}/bin/e2e/seed.php"
