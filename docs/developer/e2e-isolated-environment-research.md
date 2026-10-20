# Isolated E2E environment for Tutor LMS

Status: research / proposal. Date: 2026-10-20. Branch: `research/e2e-isolated-environment`.

## TL;DR

**Keep Cypress.** Isolation is an environment problem, not a runner problem. Replacing Cypress would rewrite the suite and still leave the same fresh-site work.

- Use **`@wordpress/env` (wp-env)** with Docker, MariaDB, the plugin mounted from `.`, and a fresh environment per CI job/shard.
- Seed the site with **WP-CLI** on `afterStart` / `afterReset`; reset with `wp-env reset` between local runs.
- Do **not** use WordPress Playground/SQLite as the primary target: Tutor runs MySQL-specific SQL (`SQL_CALC_FOUND_ROWS`, custom `CREATE TABLE`, `mysqli_get_server_info`), and SQLite is explicitly not a complete MySQL substitute.
- Do **not** adopt wp-now (deprecated), a hand-rolled Compose stack (duplicates wp-env), wp-browser (different framework), or the 10up package (beta wrapper around the same tools).
- Keep the existing manual-site path working. The wp-env setup is additive: `.env` and `CYPRESS_*` stay the contract.
- PayPal, Zoom, and Google Meet suites stay out of the default CI run. No environment tool makes external services hermetic.

## Current state

- Cypress `^14.4.0`; `cypress.config.ts` loads `.env` via `dotenv/config`, uses `baseUrl: process.env.CYPRESS_base_url`, and sets `experimentalRunAllSpecs: true` (`cypress.config.ts:4,21,22`).
- Auth uses `cy.session`; only admin is cached across specs (`cacheAcrossSpecs: true`, `cypress/support/auth.ts:4,24`). Instructor/student sessions are per spec.
- `cypress/support/commands.ts` is ~1,200 lines. Many specs mutate the same site: course builder, settings, purchase journeys, dashboards. The suite assumes a manually prepared site, not a factory.
- Seed contract in `.env.example`: admin/instructor/student logins, `CYPRESS_single_course_slug`, WooCommerce cart/checkout URLs, PayPal, Zoom, Google Meet.
- External dependencies: `cypress/e2e/purchase-journey/**` (WooCommerce + PayPal), `cypress/e2e/frontend/dashboard/instructor-zoom.cy.ts`, `instructor-meet.cy.ts`.
- No e2e CI today. `.github/workflows/` contains PHPStan, WPCS, PHP compatibility, and staging build only. No `.wp-env.json`, Dockerfile, or Compose file exists.
- The plugin requires `vendor/autoload.php` and built `assets/`; both are gitignored (`includes/bootstrap.php:39`). Any isolated run or CI job must run Composer and the asset build before Cypress.
- Database fidelity matters: `models/BaseModel.php:258`, `models/OrderModel.php:1080`, `helpers/QueryHelper.php:832,1161,1297`, and `classes/WooCommerce.php:1173,1193` use `SQL_CALC_FOUND_ROWS`; `classes/Tutor.php:791-930` creates custom tables with raw MySQL DDL; `classes/Utils.php:4586` calls `mysqli_get_server_info`.
- `phpunit.xml.dist` uses the WordPress test library, but that is PHP-only and does not boot a browser.

## Options evaluated

### 1. wp-env (`@wordpress/env`)

Official WordPress development environment. Requires Docker, maps the plugin into a WordPress container, and provides a MariaDB container, WP-CLI, and lifecycle commands. `.wp-env.json` supports `core`, `phpVersion`, `mariadbVersion`, `plugins`, `themes`, `port`, `config`, `mappings`, `lifecycleScripts`, and `testsEnvironment`. `wp-env start`, `stop`, `reset`, `run`, `cleanup`, `destroy`, and `logs` are documented commands; `reset` resets the database and reinstalls WordPress. Multiple config files (`--config`) can run parallel isolated environments with their own ports and data. ([wp-env README](https://github.com/WordPress/gutenberg/tree/trunk/packages/env), [config schema](https://schemas.wp.org/trunk/wp-env.json))

This is the smallest option that gives a real MySQL/MariaDB database, a real WordPress install, WP-CLI, and a built-in reset. Gutenberg itself uses wp-env for Playwright e2e, with a separate test config and one fresh CI job per shard. ([Gutenberg `.wp-env.json`](https://github.com/WordPress/gutenberg/blob/trunk/.wp-env.json), [Gutenberg e2e workflow](https://github.com/WordPress/gutenberg/blob/trunk/.github/workflows/end2end-test.yml))

Caveats: Docker is required; ports are fixed by default (8888 development, 8889 tests) unless `port` or `--auto-port` is set, and automatic port selection is ignored when `CI` is set; the default MariaDB is `mariadb:lts`; and `wp-env reset` reinstalls WordPress, so it is a per-run operation, not a per-test one. The experimental Playground runtime (`--runtime=playground`) avoids Docker but uses SQLite and does not support `wp-env run`, so it cannot run the WP-CLI seed described below. ([wp-env README](https://github.com/WordPress/gutenberg/tree/trunk/packages/env))

### 2. WordPress Playground CLI (SQLite)

`@wp-playground/cli` is the recommended local runner and replaces wp-now. It needs no Docker, runs WordPress on PHP-WASM, persists sites under `~/.wordpress-playground/sites/`, and supports `--mount`, `--blueprint`, `--reset`, `--phpmyadmin`, and `--workers` for concurrent requests. ([Playground CLI README](https://github.com/WordPress/wordpress-playground/tree/trunk/packages/playground/cli))

Default database is SQLite through the official SQLite Database Integration plugin, which translates MySQL queries. The plugin's own documentation says SQLite is not a complete substitute for MySQL and that MySQL-specific SQL should be verified against a real MySQL server. ([SQLite Database Integration](https://wordpress.org/plugins/sqlite-database-integration/)) Playground can use an external MySQL with a mounted `wp-config.php` and `--skip-sqlite-setup`, but that reintroduces a database server and adds configuration the default path does not exercise. ([Playground CLI README](https://github.com/WordPress/wordpress-playground/tree/trunk/packages/playground/cli))

Verdict: excellent for quick manual checks and docs; too risky as the only e2e database for a plugin with raw MySQL DDL and `SQL_CALC_FOUND_ROWS`. Whether every Tutor query works under the SQLite translator is **UNVERIFIED**, and that uncertainty is the point.

### 3. wp-now

Officially deprecated. Playground CLI documents `wp-now start` as an alias of `playground cli start`. Do not build new infrastructure on it. ([Playground CLI README](https://github.com/WordPress/wordpress-playground/tree/trunk/packages/playground/cli))

### 4. Plain Docker Compose

A `wordpress` + `mariadb` Compose stack with a bind mount for the plugin and the `wordpress:cli` image for seeding. The official image supports `WORDPRESS_DB_*`, `WORDPRESS_TABLE_PREFIX`, `WORDPRESS_DEBUG`, and `WORDPRESS_CONFIG_EXTRA`; the `cli` variant provides WP-CLI. ([Docker Official Image: wordpress](https://hub.docker.com/_/wordpress))

This works, but you own container healthchecks, WP install, plugin activation, WP-CLI wiring, reset, and CI cleanup. wp-env already packages those decisions, so Compose is more code for the same result. Choose it only if wp-env blocks a hard requirement (for example, a custom PHP extension or a nonstandard web server).

### 5. `10up/cypress-wp-setup`

A beta scaffolding package that generates Cypress + wp-env boilerplate and a GitHub Actions workflow, including a permalink fix for CI. It also points at `@10up/cypress-wp-utils` for `cy.login`, `cy.activatePlugin`, and `cy.createPost`. ([10up/cypress-wp-setup](https://github.com/10up/cypress-wp-setup), [Cypress WP Utils](https://10up.github.io/cypress-wp-utils/))

Useful as a reference for the workflow and the permalink step. Not worth adopting wholesale: Tutor already has Cypress, commands, and environment variables; the package would add a second convention layer and is explicitly beta. The wp-env workflow pattern can be copied in a few lines.

### 6. wp-browser (Codeception)

Mature WordPress testing library, but it is Codeception, not Cypress. Its v4 quick install defaults to SQLite plus the SQLite Database Integration plugin and a PHP built-in server. Adopting it means rewriting every spec and every custom command. ([wp-browser](https://github.com/lucatume/wp-browser))

Not a candidate for this suite. Its WP-CLI and fixture ideas are useful prior art only.

### 7. WordPress core / Gutenberg Playwright pattern

Core e2e uses Playwright and resets state through REST calls in global setup (`deleteAllPosts`, `deleteAllBlocks`, `resetPreferences`) rather than restoring a database dump. Gutenberg shards Playwright across CI jobs and runs `workers: 1` against a dedicated wp-env config. ([core global setup](https://github.com/WordPress/wordpress-develop/blob/trunk/tests/e2e/config/global-setup.js), [Gutenberg e2e workflow](https://github.com/WordPress/gutenberg/blob/trunk/.github/workflows/end2end-test.yml))

Steal the pattern, not the framework: fresh environment per CI shard, serial specs, artifacts on failure. Do not migrate to Playwright for this task.

## Comparison

Ten criteria, scored for the primary use case: running the existing Cypress suite against an isolated Tutor LMS site.

| #   | Criterion                        | wp-env                       | Playground CLI            | Compose           | 10up setup   | wp-browser               | Core Playwright |
| --- | -------------------------------- | ---------------------------- | ------------------------- | ----------------- | ------------ | ------------------------ | --------------- |
| 1   | Keeps existing Cypress specs     | Yes                          | Yes                       | Yes               | Yes          | No                       | No              |
| 2   | Pins WordPress/PHP versions      | Yes                          | Yes                       | Yes               | Yes (wp-env) | Partial (WP only)        | Yes             |
| 3   | Real MySQL/MariaDB               | Yes                          | Partial (SQLite default)  | Yes               | Yes          | Partial (SQLite default) | Yes             |
| 4   | Built-in clean reset             | Yes (DB reset + reinstall)   | Partial (`--reset`)       | No                | Yes (wp-env) | Partial                  | No              |
| 5   | Seeding via WP-CLI               | Yes (`run cli`)              | Partial (limited)         | Yes (`cli` image) | Yes          | Yes                      | Yes             |
| 6   | Parallel runs without paid cloud | Yes (separate configs/ports) | Partial (request workers) | Manual            | Yes (wp-env) | Unknown                  | Yes (shards)    |
| 7   | Proven GitHub Actions pattern    | Yes (Gutenberg, 10up)        | Yes (CLI designed for CI) | Yes (manual)      | Yes          | Yes                      | Yes             |
| 8   | New infrastructure to maintain   | Low                          | Low                       | High              | Medium       | High                     | High            |
| 9   | Hermetic external services       | No                           | No                        | No                | No           | No                       | No              |
| 10  | Adoption effort for this repo    | Low                          | Low                       | Medium            | Medium       | Very high                | Very high       |

wp-now is omitted from the table because it is deprecated and functionally a Playground alias. Criterion 9 is a wash: none of these tools isolate PayPal, Zoom, or Google Meet; that requires excluding or mocking those suites.

## Recommended approach

**Keep Cypress.** Add wp-env as an opt-in, reproducible target. The current `.env`-driven manual-site workflow remains valid, which keeps day-to-day developer flow and all existing CI-free usage intact.

Why this is the lazy correct choice:

- One config file replaces a Dockerfile, Compose file, install script, and reset script.
- MariaDB keeps the SQL surface honest, including `SQL_CALC_FOUND_ROWS` and Tutor's custom tables.
- `wp-env run cli` gives seeding and assertions without a second tool.
- `wp-env reset` gives a clean database and a fresh WordPress install in one command.
- Gutenberg and 10up already validate the "fresh wp-env per CI job" pattern.

### Proposed `.wp-env.json`

```json
{
  "phpVersion": "8.2",
  "plugins": [".", "https://downloads.wordpress.org/plugin/woocommerce.latest-stable.zip"],
  "config": {
    "WP_DEBUG": true
  },
  "lifecycleScripts": {
    "afterStart": "tests/e2e/seed.sh",
    "afterReset": "tests/e2e/seed.sh"
  },
  "testsEnvironment": false
}
```

Notes:

- The plugin at `.` is mapped and activated by wp-env; the WooCommerce zip is installed and activated the same way. ([wp-env README](https://github.com/WordPress/gutenberg/tree/trunk/packages/env))
- `testsEnvironment: false` avoids a second WordPress + database pair. It is documented as deprecated and defaults to false; setting it explicitly keeps the intent. ([wp-env README](https://github.com/WordPress/gutenberg/tree/trunk/packages/env))
- Pin `core` and the WooCommerce zip when reproducibility matters more than "latest". Do not pin `phpVersion` to a version whose WordPress image is not published; verify the tag before adding a matrix.
- Ports are fixed at 8888 (development) and 8889 (tests) by default. Add `port` or use `--auto-port` when running a second local environment; CI runners are fresh, so the default is fine. ([wp-env README](https://github.com/WordPress/gutenberg/tree/trunk/packages/env))

### Seed and reset

`tests/e2e/seed.sh` runs on every start/reset and must be idempotent:

1. `wp rewrite structure '/%postname%/'` so pretty permalinks match the URL contract (the 10up workflow exists largely because of this CI failure mode). ([WP-CLI `rewrite structure`](https://developer.wordpress.org/cli/commands/rewrite/structure/), [10up/cypress-wp-setup](https://github.com/10up/cypress-wp-setup))
2. Set known passwords for admin, instructor, and student; create the instructor/student if missing.
3. Create one published course with the slug in `CYPRESS_single_course_slug`, owned by the instructor, with whatever Tutor meta the student journey reads.
4. Enable the Tutor pages and WooCommerce basics that the non-payment specs assume.
5. Assert the result with `wp user get`, `wp post list`, and a check that Tutor's custom tables exist. Fail loudly; a silent seed produces confusing spec failures.

Do not reset between specs. Reset between runs: `npx wp-env reset` reinstalls WordPress, then `afterReset` re-seeds. Use `npx wp-env destroy` only when containers or volumes are broken. ([wp-env README](https://github.com/WordPress/gutenberg/tree/trunk/packages/env))

### Lighter reset options, and why they are not the default

- **Dump/restore** (`wp db export` / `wp db import` through `wp-env run cli`) is faster than reinstalling WordPress and is the first optimization if reset time hurts. It is still a per-run/per-group operation, and restoring a dump taken at seed time invalidates any login sessions created after it. ([WP-CLI db export](https://developer.wordpress.org/cli/commands/db/export/), [db import](https://developer.wordpress.org/cli/commands/db/import/))
- **Per-test transactions** do not work for browser e2e: each HTTP request is handled by a separate PHP process with its own database connection, so a transaction opened by the test runner cannot roll back the site's writes.
- **REST cleanup** (core's `deleteAllPosts` / `deleteAllBlocks` / `resetPreferences`) is cheap but leaves Tutor options, custom tables, orders, and settings behind. It complements a reset; it does not replace one. ([core global setup](https://github.com/WordPress/wordpress-develop/blob/trunk/tests/e2e/config/global-setup.js))

### Local runbook

```bash
pnpm add -D @wordpress/env
pnpm run build           # Composer install + rspack/gulp assets (gitignored)
npx wp-env start
DOTENV_CONFIG_PATH=.env.e2e npx cypress run
npx wp-env reset          # clean DB + reinstall + seed
npx wp-env stop
```

`dotenv/config` honors `DOTENV_CONFIG_PATH`, so `cypress.config.ts` needs no change. Add `.env.e2e.example` with `CYPRESS_base_url=http://localhost:8888` and the seeded credentials/slug; keep `.env` for the manual site. ([dotenv](https://github.com/motdotla/dotenv#dotenv_config_path))

Implementation note: for CI, do not use `pnpm run build` — it also builds the
distributable zip and clones droip/kirki. Use `composer install --no-dev --no-scripts`
plus `pnpm exec rspack --mode=production` (see the plan).

### CI shape

One workflow, one job per spec group, fresh runner per job:

- `actions/checkout`, Node 22, `pnpm install --frozen-lockfile`, `pnpm run build` (Composer + assets), `npx wp-env start` (which runs the seed), `npx cypress run --spec <group>`, upload screenshots on failure.
- Do not use `cypress run --parallel`: Cypress parallelization requires a Cypress Cloud account and `--record`. Splitting by folder in a matrix gets isolation for free. ([cypress-io/github-action](https://github.com/cypress-io/github-action#parallel))
- If folder groups become too coarse, `cypress-split` can split specs without Cypress Cloud. It is third-party; start with folders. ([cypress-split](https://github.com/bahmutov/cypress-split))
- Pin Node 22: wp-env 11 requires Node >=18.12; Cypress 14 supports Node 18/20/22; current Cypress 16 documents Node 22.x/24.x/26.x. The repo's `engines.node >=14.17.5` is stale and not a CI target. ([wp-env package](https://www.npmjs.com/package/@wordpress/env), [Cypress 14.4.0](https://www.npmjs.com/package/cypress/v/14.4.0), [Cypress install docs](https://docs.cypress.io/app/get-started/install-cypress))

### What not to do

- Do not run the whole suite in parallel against one database. The specs share settings, courses, and orders.
- Do not move to Playwright as part of this work.
- Do not add MySQL-specific test assertions that only pass on SQLite, or vice versa.
- Do not put PayPal/Zoom/Meet credentials in CI just to keep those specs green.

## Implementation plan

| Step | Work                                                     | Files                                     | Rough effort |
| ---- | -------------------------------------------------------- | ----------------------------------------- | ------------ |
| 1    | Add wp-env config and package scripts                    | `.wp-env.json`, `package.json`            | 0.5 day      |
| 2    | Inventory what specs read, then write the seed           | `tests/e2e/seed.sh`, `tests/e2e/seed.php` | 1-2 days     |
| 3    | Add isolated env contract                                | `.env.e2e.example`, `.gitignore` check    | 0.5 day      |
| 4    | Add CI workflow with 2-3 spec groups                     | `.github/workflows/e2e.yml`               | 0.5 day      |
| 5    | Quarantine external suites                               | Cypress config/scripts                    | 0.5 day      |
| 6    | Prove it: run twice from clean, then reset and run again | docs                                      | 1 day        |

Verification is part of the work: the same commit must produce the same result on two consecutive clean environments. If it does not, the seed or the specs are still stateful; wp-env is not the problem.

The run script must include `composer install` and the asset build. Without `vendor/` and built assets the plugin boots or renders incorrectly, which looks like an environment failure but is a missing build.

## Risks and open questions

- **Seed fidelity is the real project.** Isolation succeeds technically even if the seed is wrong; specs then fail for missing data. Inventory every slug, option, user, and post the suite reads before writing the seed. This is the main unknown.
- **Specs are order-dependent.** `experimentalRunAllSpecs: true` plus a shared database preserves leakage within a run. A fresh environment per run does not make specs independent. Split CI by folder first; refactor specs to create their own data only if flakiness persists.
- **Sessions vs resets.** `cacheAcrossSpecs: true` keeps the admin session across specs. A mid-run database reset would invalidate it. Either reset only between runs (recommended) or drop cross-spec caching before adding per-spec resets.
- **External services are not hermetic.** PayPal IPN, Zoom, and Google Meet cannot be made reliable by a local WordPress environment. Keep those specs manual/nightly, or test the integration boundary with unit/integration tests instead of browser e2e.
- **WooCommerce drift.** `latest-stable` changes without notice. Pin a versioned zip once the suite is green. The exact versioned download URL is **UNVERIFIED**; confirm it from the plugin's release page.
- **wp-env reset cost.** Each reset reinstalls WordPress, which costs minutes per run/shard. Exact timing on this repo is **UNVERIFIED**. Acceptable in CI; local developers may prefer one reset per session.
- **Platform quirks.** wp-env's default `mariadb:lts` is the safe choice; some older MariaDB tags (e.g. 5.5) are not published for ARM and require emulation on Apple Silicon. ([wp-env README](https://github.com/WordPress/gutenberg/tree/trunk/packages/env))
- **MariaDB is not MySQL.** wp-env gives MariaDB, which is close but not identical. If production supports MySQL 8.x, add one Compose-based MySQL job (or verify the raw SQL separately) to catch dialect and deprecation differences; wp-env alone will not.
- **Open:** which suites gate PRs, whether WooCommerce is in the default group, and whether a WP/PHP matrix is required. Recommendation: start with one PHP/WP version and the core frontend/backend specs; add a matrix only after the suite is stable.

## Sources

1. wp-env README — https://github.com/WordPress/gutenberg/tree/trunk/packages/env
2. wp-env config schema — https://schemas.wp.org/trunk/wp-env.json
3. Gutenberg `.wp-env.json` — https://github.com/WordPress/gutenberg/blob/trunk/.wp-env.json
4. Gutenberg e2e workflow — https://github.com/WordPress/gutenberg/blob/trunk/.github/workflows/end2end-test.yml
5. WordPress core e2e global setup — https://github.com/WordPress/wordpress-develop/blob/trunk/tests/e2e/config/global-setup.js
6. Playground CLI README — https://github.com/WordPress/wordpress-playground/tree/trunk/packages/playground/cli
7. Playground CLI docs — https://developer.wordpress.org/playground/developers/local-development/wp-playground-cli/
8. SQLite Database Integration — https://wordpress.org/plugins/sqlite-database-integration/
9. Docker Official Image: wordpress — https://hub.docker.com/_/wordpress
10. 10up/cypress-wp-setup — https://github.com/10up/cypress-wp-setup
11. Cypress WP Utils — https://10up.github.io/cypress-wp-utils/
12. wp-browser — https://github.com/lucatume/wp-browser
13. cypress-io/github-action — https://github.com/cypress-io/github-action
14. cypress-split — https://github.com/bahmutov/cypress-split
15. Cypress installation/system requirements — https://docs.cypress.io/app/get-started/install-cypress
16. Cypress 14.4.0 package metadata — https://www.npmjs.com/package/cypress/v/14.4.0
17. @wordpress/env package metadata — https://www.npmjs.com/package/@wordpress/env
18. dotenv `DOTENV_CONFIG_PATH` — https://github.com/motdotla/dotenv#dotenv_config_path
