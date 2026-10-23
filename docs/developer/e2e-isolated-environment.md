# E2E environment with an isolated site and database

Status: plan. Branch: `research/e2e-isolated-environment`.
Detailed comparison and sources: [e2e-isolated-environment-research.md](./e2e-isolated-environment-research.md).

## Goal

Run the Cypress suite against a disposable WordPress site created from this working
copy, with its own database, so that:

- tests never touch a developer's Local/manual site;
- a fresh clone can run the suite with one command;
- CI needs no pre-provisioned site;
- two consecutive clean runs produce the same result.

Non-goals: replacing Cypress, rewriting existing specs, testing Tutor Pro or
third-party SaaS (PayPal, Zoom, Google Meet/Classroom) by default.

## Current state (facts from this repo)

- `cypress.config.ts` reads everything from `.env` (`CYPRESS_base_url`, credentials,
  slugs); there is no fallback. The site must already exist and be seeded by hand.
- 66 specs: 20 dashboard, 17 backend, 7 course builder, 4 content bank, 4 frontend,
  12 purchase journeys, 2 settings.
- Specs share mutable state:
  - `cypress/fixtures/course.json` and `collection.json` are written by one spec and
    read by later ones (`purchase-journey/*/02-*` → `03-*`). Both are gitignored.
  - Settings specs change global options (`monetization`, checkout, …).
  - Only the **admin** session is cached across specs (`cacheAcrossSpecs: true` in
    `cypress/support/auth.ts`); instructor/student sessions are per spec.
- Up to 11 specs touch PayPal, Zoom or Google (`zoom`, `google-*`, `*paypal*`,
  `instructor-zoom`, `instructor-meet`); they cannot be hermetic.
- No e2e CI. `.github/workflows/` has PHPStan, WPCS, PHP compatibility, staging only.
- `vendor/` and built `assets/` are gitignored, but the plugin requires
  `vendor/autoload.php` (`includes/bootstrap.php`) and built CSS/JS. Any clean run
  must run Composer and the rspack build first.
- Tutor uses MySQL-specific SQL (`SQL_CALC_FOUND_ROWS`, `SHOW TABLES`, `GROUP_CONCAT`,
  raw DDL, `mysqli_get_server_info`) — see `helpers/QueryHelper.php`, `core/DB.php`,
  `models/QuizModel.php`, `classes/WooCommerce.php`, `classes/Tutor.php`.

## Decision

Use **`@wordpress/env` (wp-env, Docker runtime)** and **keep Cypress**. One instance
per run/shard; reset it to a freshly installed site, then seed deterministically.

Why:

- Real MariaDB plus WP-CLI, a built-in reset, plugin mounting and lifecycle hooks in
  one committed config. No Dockerfile/Compose/install scripts to own.
- Gutenberg and 10up already use the "fresh wp-env per CI job" pattern; wp-env
  supports parallel isolated environments via separate `--config` files and ports.
- Cypress 14 and 66 specs already exist; a framework switch is the largest possible
  diff and does not improve isolation.

Rejected:

| Option                                         | Why not                                                                                                                                                                                                                          |
| ---------------------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| WordPress Playground / wp-now (SQLite default) | Tutor's raw MySQL DDL and `SQL_CALC_FOUND_ROWS` are unverified under the SQLite translator. Playground can use external MySQL, but then it is a more complex Docker setup. wp-now is deprecated in favour of the Playground CLI. |
| Hand-written docker-compose                    | Duplicates wp-env's install, activation, WP-CLI and reset wiring. Fallback only if wp-env blocks a hard requirement.                                                                                                             |
| wp-browser / Codeception, Playwright rewrite   | Rewrites the suite for no isolation gain.                                                                                                                                                                                        |
| Snapshotting a Local site DB                   | Not reproducible or CI-friendly, and risks copying developer data.                                                                                                                                                               |
| Per-test transactions                          | Browser requests use separate PHP processes; a runner transaction cannot roll them back.                                                                                                                                         |
| 10up cypress-wp-setup                          | Beta wrapper around the same tools; use its permalink/CI ideas, not the dependency.                                                                                                                                              |

## Run flow

```
pnpm e2e
  └─ bin/e2e/run.sh
       ├─ wp-env start            # Docker: wordpress + mariadb + cli; afterStart seeds
       ├─ wp-env reset all        # drop + reinstall
       ├─ wp-env start            # ensure running; afterStart reseeds
       └─ DOTENV_CONFIG_PATH=.env.e2e cypress run
```

Isolation boundary is the **run**, not the test. The purchase journeys are numbered
specs sharing `course.json`; per-test resets would break them. If stronger isolation
is needed later, reset per spec **group** (top-level folder), never per test, and drop
`cacheAcrossSpecs` first — a DB restore invalidates the cached admin session.

`afterStart` is the only lifecycle hook used: `e2e:reset` runs `wp-env reset all`
followed by `wp-env start`, so seeding always happens on start. wp-env's reset already
drops the database and runs `wp core install`; `afterReset` is available if reset is
ever run standalone. wp-env also enables pretty permalinks and writes `.htaccess` on
install/reset, so the seed does not need a rewrite step.

## Implementation plan

### Phase 1 — Environment and minimal seed

New files:

- `.wp-env.json`
  ```json
  {
    "$schema": "https://schemas.wp.org/trunk/wp-env.json",
    "core": null,
    "phpVersion": "8.1",
    "plugins": [".", "https://downloads.wordpress.org/plugin/woocommerce.latest-stable.zip"],
    "port": 8888,
    "mappings": {
      "wp-content/e2e": "./.e2e"
    },
    "config": {
      "WP_DEBUG": true,
      "WP_DEBUG_LOG": true,
      "WP_DEBUG_DISPLAY": false
    },
    "lifecycleScripts": {
      "afterStart": "bash bin/e2e/seed.sh"
    }
  }
  ```
  `core: null` = latest stable; pin it after the first green run. `phpVersion: 8.1`
  matches existing CI and stays above the plugin minimum (7.4). WooCommerce is needed
  by the Woo purchase specs; the `latest-stable` zip must be pinned once green. The
  mapping exposes seeded IDs (and later DB dumps) to both host and container.
- `package.json`: add `@wordpress/env` to `devDependencies` (only new dependency) and
  scripts:
  `e2e` (reset + run), `e2e:open`, `e2e:env` (start), `e2e:reset`
  (`wp-env reset all && wp-env start`), `e2e:down`, `e2e:nuke`.
- `bin/e2e/seed.sh`: idempotent by `tutor_e2e_seed_version` option. Runs
  `wp-env run cli wp eval-file wp-content/plugins/<repo-dir>/bin/e2e/seed.php`
  (slug derived from `basename "$PWD"`), then writes `cypress/fixtures/course.json`
  and `collection.json` from `.e2e/ids.json`.
- `bin/e2e/seed.php`: known admin/instructor/student credentials (create
  instructor/student if missing), one published course whose slug matches
  `CYPRESS_single_course_slug`, categories, and a paid course for native checkout.
  Asserts: users exist, Tutor custom tables exist, course is published. Any missing
  dependency should fail the seed loudly.
- `bin/e2e/run.sh`: `set -euo pipefail`; provision (`wp-env start`), reset, start
  (install + seed), Cypress.
- `.env.e2e.example`: `CYPRESS_base_url=http://localhost:8888`, seeded
  credentials/slugs, no real secrets. Copy to `.env.e2e` (gitignored).
- `.gitignore`: `.e2e/`, `.env.e2e`, `cypress/videos/`.

No `cypress.config.ts` change: `dotenv/config` already honours
`DOTENV_CONFIG_PATH=.env.e2e`, and `CYPRESS_base_url`/`CYPRESS_*` remain the contract,
so the manual-site workflow keeps working.

Acceptance:

- With Docker, `pnpm install`, `composer install --no-scripts`,
  `pnpm exec rspack --mode=production`, then `cp .env.e2e.example .env.e2e` and
  `pnpm e2e`: the site boots at `localhost:8888`, seeds, and the login smoke spec is
  green.
- The developer's `.env` and Local site are untouched.

### Phase 2 — Seed fidelity and a meaningful default suite

This is the real work; environment tooling is the easy part.

- Inventory what each spec group reads: users, course/lesson/quiz/assignment data,
  options, pages, WooCommerce state. Seed only that, minimally.
- Curate the default set: exclude PayPal/Zoom/Google and Pro-only pages via
  `excludeSpecPattern` from an env var, or an explicit `--spec` list in `run.sh`.
- Replace hidden cross-spec dependencies with per-run seeded data where cheap; keep
  journey sequences intact.
- Keep an opt-in profile for external services with credentials supplied as CI
  secrets; never put them in the default run.

Acceptance: a named core subset (auth, course builder, content bank, backend CRUD,
non-PayPal purchase steps) is stable across two consecutive runs from a clean DB.

### Phase 3 — CI

`.github/workflows/e2e.yml`:

- `ubuntu-latest`, Node 22 (wp-env requires >=18.12; the repo's `engines.node` is
  stale), pnpm cache, Composer cache.
- Steps: checkout → Cypress GitHub Action with `runTests: false` (installs Cypress and
  its Linux libraries via pnpm) → `composer install --no-dev --no-scripts` →
  `pnpm exec rspack --mode=production` → `pnpm e2e`.
- `CYPRESS_base_url=http://localhost:8888` and seeded test credentials only; no
  production secrets.
- Upload `cypress/screenshots` and `cypress/videos` on failure; `timeout-minutes: 45`;
  cancel superseded runs.
- Start with one job. If runtime hurts, split by spec folder into a matrix (fresh
  runner + fresh wp-env per job) — that gives isolation without Cypress Cloud.
  `cypress run --parallel` is not an option: it requires Cloud recording.

Acceptance: green on the curated suite; failures upload artifacts.

### Phase 4 (only if needed) — faster or stronger isolation

- Snapshot after seeding: `wp-env run cli wp db export wp-content/e2e/snapshot.sql`
  and `wp db import` before a spec group. This is faster than reinstall, but restore
  invalidates admin sessions created after the snapshot.
- Split CI by folder first; add `cypress-split` only if folder granularity is too
  coarse.
- Consider a separate `--config` environment per worker only after measuring the cost
  (each is a full WordPress + DB container set).

## Risks and open questions

- **Seed fidelity is the main unknown.** Isolation can be technically perfect and the
  suite still red because a slug, option or meta is missing. Budget 1–2 days for the
  seed and require two clean consecutive runs before trusting the suite.
- **Specs are order-dependent.** A fresh DB per run does not make them independent;
  folder-level CI shards and seed-created data are the mitigation.
- **Not all 66 specs are hermetic.** External services and Tutor Pro/addon pages will
  still fail; Phase 2 must define the default set explicitly.
- **Pin versions after green.** `core: null` and WooCommerce `latest-stable` drift.
  Pin both (and `mariadbVersion` if needed) once stable.
- **MariaDB is not MySQL.** wp-env provides MariaDB LTS; raw SQL dialect differences
  vs MySQL 8 are not covered. Add a Compose-based MySQL job only if production
  differences matter.
- **Platform quirks.** Default `mariadb:lts` is the safe ARM choice; old MariaDB tags
  may need emulation on Apple Silicon. First `wp-env start` builds images (minutes);
  later starts are fast.
- **Reset cost.** `wp-env reset` + `wp-env start` reinstalls WordPress; exact timing is
  unverified and may be minutes per run. Acceptable in CI; locally developers may
  reuse an instance.
- **Port conflicts.** 8888/8889 clash with a running wp-env or Local; use
  `WP_ENV_PORT` or `--auto-port` locally (auto-port is ignored when `CI` is set).
- **Data safety.** Never import a production/developer dump; seed from code only.
- **Build prerequisites.** Without `vendor/` and built assets the plugin boots or
  renders incorrectly; the runbook must include Composer + rspack, not just
  `pnpm install`.

## Sources

See the companion research document. Key primary sources:

- wp-env docs and config schema:
  https://developer.wordpress.org/block-editor/reference-guides/packages/packages-env/ ,
  https://schemas.wp.org/trunk/wp-env.json
- Gutenberg e2e workflow and `.wp-env.json`:
  https://github.com/WordPress/gutenberg/blob/trunk/.github/workflows/end2end-test.yml
- WordPress core Playwright global setup (REST cleanup, not DB dump):
  https://github.com/WordPress/wordpress-develop/blob/trunk/tests/e2e/config/global-setup.js
- Cypress `before:spec`:
  https://docs.cypress.io/api/node-events/before-spec-api
- Cypress GitHub Action:
  https://docs.cypress.io/app/continuous-integration/github-actions
- dotenv `DOTENV_CONFIG_PATH`:
  https://github.com/motdotla/dotenv#dotenv_config_path
- WP-CLI `wp db`, `wp eval-file`, `wp rewrite structure`:
  https://developer.wordpress.org/cli/commands/db/ ,
  https://developer.wordpress.org/cli/commands/eval-file/ ,
  https://developer.wordpress.org/cli/commands/rewrite/structure/
