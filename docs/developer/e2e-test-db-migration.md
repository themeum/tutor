# E2E migration: isolated test database

Status: implemented
Branch: `research/e2e-isolated-environment`
Source architecture: PR #3118, merged at `0f4af643d`

Implementation note: the baseline grew while this plan was in flight. Discovery now
lists 45 specs / 155 tests (34 core, 121 Pro on `feat/e2e-coverage`); the earlier
98-test figure was 34 core + 64 Pro.

## Goal

One Playwright config, one test database per runner, one env file, and CI that uses the same path as local. Keep all 45 specs / 155 tests, five roles, zero retries.

## Invariants

1. A test command never reads or writes the live database.
2. Playwright always sends `X-WP-Test-Env: playwright`.
3. PHPUnit never shares a database with Playwright.
4. No spec moves and no retries.
5. Pro code, factories and seeders stay in `tutor-pro`; free only carries shared helpers and the plugin-slug list.

## Target flow

```
local   Playwright --header--> Local site --test-config--> local_test
        wp eval-file (WP_TEST_ENV=1) ----------------------> local_test
        phpunit (WP_RUNNING_TESTS) ------------------------> local_phpunit

CI      wp server (WP_TEST_ENV=1) -------------------------> wordpress_test
        wp eval-file (WP_TEST_ENV=1) ----------------------> wordpress_test
```

## Decisions

| # | Decision |
|---|---|
| Q1 | One config at `tests/e2e/playwright.config.ts`, merged from the root config; root file deleted, along with `specs/smoke.spec.ts` |
| Q2 | CI uses the test database too; wp-env is removed after CI is migrated |
| Q3 | `WP_TEST_ACTIVE_PLUGINS` controls activation; default includes `tutor`, `tutor-pro`, `woocommerce` when present |
| Q4 | One shared seeder, five roles, `E2E_PASSWORD` (default `password`) |
| Q5 | Explicit DB name, hard guards, dry-run installer, uninstall, preflight |
| Q6 | PHPUnit gets its own database (`WP_PHPUNIT_DB_NAME`) |
| Q7 | One `.env` at plugin root; `.env.e2e` is retired |

---

## Phase 0 — Back up and baseline

1. Start Local's MySQL and confirm the site URL.
2. Copy `wp-config.php` and `wp-config.php.bak` aside.
3. Dump the live DB (`local`).
4. Record the current test count: `pnpm exec playwright test --list` must show 98 tests.
5. Confirm `.env` has `SITE_URL`, `WP_TEST_DB_*`, `ADMIN_*`, and the five role credentials. It currently only has the four `WP_TEST_DB_*` keys.

Exit: a restorable backup exists and the current suite baseline is recorded.

## Phase 1 — One config inside the suite, header everywhere

Merge the root `playwright.config.ts` into `tests/e2e/playwright.config.ts`, then delete the root file. The merged config keeps the root's projects, global setup, timeouts and reporters, and adds the PR's env loading and header.

- `tests/e2e/playwright.config.ts`
  - `dotenv.config({ path: path.resolve(__dirname, '../../.env') })`;
  - `baseURL = process.env.E2E_BASE_URL ?? process.env.SITE_URL ?? 'http://localhost:8888'`;
  - `testDir: '.'`;
  - `globalSetup: './global-setup.ts'`;
  - `outputDir` and the HTML report folder stay at the plugin root (`path.resolve(__dirname, '../../test-results')`, `../../playwright-report`);
  - projects: `core` (`testIgnore: ['**/external/**']`), `external` (`./external`), `pro` (`path.resolve(__dirname, '../../../tutor-pro/tests/e2e')`);
  - add `extraHTTPHeaders: { 'X-WP-Test-Env': 'playwright' }` to `use`;
  - keep `retries: 0`, `workers: 1`, `timeout: 60_000`, `expect.timeout: 10_000`.
- `playwright.config.ts` (root) — delete.
- `tests/e2e/global-setup.ts` — add the same header to `browser.newContext()`.
- `tests/e2e/support/test.ts` — add the same header to every role context.
- `tests/e2e/support/auth.ts` — delete the commented `loginAsAdmin` block; keep the active multi-role `login` / `storageState` API.
- `tests/e2e/specs/smoke.spec.ts` — delete (imports a function that no longer exists).
- `package.json`
  - `test:e2e` -> `php tests/support/preflight.php && playwright test -c tests/e2e/playwright.config.ts`;
  - add `test:e2e:smoke` -> `playwright test -c tests/e2e/playwright.config.ts --grep @smoke`;
  - add `test:e2e:journey` -> `playwright test -c tests/e2e/playwright.config.ts --grep @journey`;
  - keep `test:e2e:ui` and `test:e2e:debug` with the same `-c` flag;
  - keep the wp-env scripts until Phase 7.
- `tests/e2e/README.md` — commands and layout rewritten for one config.

Exit: `pnpm exec playwright test -c tests/e2e/playwright.config.ts --list` lists 98 tests and no import errors.

## Phase 2 — Database safety and test-config

- `tests/support/test-config.php`
  - `db()` reads `WP_TEST_DB_*` only; remove the `<live>_test` derivation;
  - fail closed when `WP_TEST_DB_NAME` is missing or empty;
  - add a host allowlist (`WP_TEST_HOSTS`, default `localhost,127.0.0.1,*.local`) so a public host cannot opt into the test DB with a cookie;
  - define `UPLOADS` as `wp-content/uploads-e2e` when in test mode, so uploads do not pollute the live tree.
- `tests/support/install.php`
  - `--dry-run` is the default; `--yes` writes;
  - `--uninstall` restores `wp-config.php.bak`, removes mu-plugin links and the generated test DBs;
  - only create the backup once;
  - create both `WP_TEST_DB_NAME` and `WP_PHPUNIT_DB_NAME`;
  - write a marker option in the test DB (`tutor_e2e_test_db = 1`) for the preflight.
- `tests/support/reset-test-db.php`
  - read the database name explicitly; remove the hardcoded fallback;
  - refuse if the target equals the live DB, does not end in `_test`, or is empty;
  - keep the drop/install logic unchanged.
- New `tests/support/preflight.php`
  - load `.env`;
  - assert live DB name, test DB name and PHPUnit DB name are all different;
  - assert the test names end in `_test`;
  - exit non-zero with a one-line message.
- `.env.example` — `SITE_URL`, `WP_TEST_DB_*`, `WP_PHPUNIT_DB_NAME`, `WP_TEST_ACTIVE_PLUGINS`, `WP_TEST_HOSTS`, `ADMIN_*`, `E2E_*`.

Exit: `composer test:install` prints what it would do; `composer test:install:apply` writes idempotently; `preflight` fails if any DB name is wrong.

## Phase 3 — One seeder

- New `tests/support/seed.php` (pure WP, no `WP_CLI`):
  - five roles: `admin`, `instructor`, `student`, `student2`, `manager`;
  - password from `E2E_PASSWORD`, default `password`;
  - pages, settings, manual payment, baseline course/topic/lesson from `bin/e2e/seed.php`;
  - invoke `../tutor-pro/bin/e2e/seed-pro.php` when present.
- `tests/support/reset-test-db.php` — replace the inline users/course with a `require` of the seeder.
- `bin/e2e/seed.php` — becomes a thin wrapper or is deleted after wp-env removal.
- `bin/e2e/seed-admin.php` — fold into the shared seeder.
- `tests/e2e/support/env.ts` — defaults stay `password`; the seeder now matches.

Exit: reset produces five users with `password`, and the current suite logs in without credential overrides.

## Phase 4 — Factory transport

- `tests/e2e/support/factories.ts`
  - `WP_ROOT` env, default `path.resolve(process.cwd(), '../../..')`;
  - `WP_CLI_BIN` env, default `wp`;
  - `E2E_FACTORY_RUNNER` — `wp-cli` default, `wp-env` kept temporarily for the old CI;
  - run `wp --path <WP_ROOT> eval-file <plugin>/bin/e2e/factories.php <payload>` with `WP_TEST_ENV=1`;
  - results directory `<WP_ROOT>/wp-content/e2e`, not `process.cwd()/.e2e`.
- `bin/e2e/factories.php` — accept an explicit result directory from the payload; fall back to `WP_CONTENT_DIR/e2e`.
- `tests/e2e/support/factories.ts` error message — point at the new directory.

Exit: all 27 factory cases return a result locally; rerunning a spec is idempotent.

## Phase 5 — Pro, Woo and mu-plugins

- `tests/support/reset-test-db.php`
  - `WP_TEST_ACTIVE_PLUGINS` (comma-separated) overrides;
  - otherwise build the list from files that exist: `tutor`, `tutor-pro`, `woocommerce`, `h5p`;
  - set `active_plugins` before running activation hooks;
  - run `activate_plugin()` for each, so Pro/Woo tables and roles are created in the test DB;
  - load the Pro seeder when present.
- `bin/e2e/mu-plugins/e2e-http-stub.php` and `e2e-mail-log.php`
  - return early unless `TutorTestConfig::is_test_environment()`;
  - installer links them into `wp-content/mu-plugins/tutor-e2e-*.php`;
  - `--uninstall` removes the links.
- No Pro specs move; the `pro` project still points at `../tutor-pro/tests/e2e`.

Exit: `E2E_PRO=1 pnpm test:e2e` runs 29 Pro specs / 121 tests against `local_test`.

## Phase 6 — PHPUnit database

- `tests/support/test-config.php` — when `WP_RUNNING_TESTS` is set as a constant or environment variable, use `WP_PHPUNIT_DB_NAME`; otherwise the E2E test DB.
- `tests/support/install.php` — create the PHPUnit DB alongside the E2E DB.
- `tests/bootstrap.php` — exports `WP_RUNNING_TESTS=1` so the wp-phpunit installer child process also selects `local_phpunit`.
- `composer.json` — `test:unit` runs preflight first.

Exit: `composer test:unit` passes without touching `local_test` (176 tests, 1 skipped).

## Phase 7 — CI on the test database

Rewrite `.github/workflows/e2e.yml`:

1. MySQL 8 service with a health check.
2. `actions/checkout` with `path: wordpress/wp-content/plugins/tutor`.
3. `wp core download --path=wordpress` (pin the version currently in `.wp-env.json`).
4. `wp config create --path=wordpress --dbname=wordpress --dbuser=root --dbpass=root --dbhost=127.0.0.1 --skip-check`.
5. Install deps and build inside the plugin directory.
6. `SITE_URL=http://127.0.0.1:8888 php tests/support/install.php --yes`.
7. `WP_TEST_ENV=1 PHP_CLI_SERVER_WORKERS=4 wp server --host=127.0.0.1 --port=8888 --path=wordpress` in the background; wait for HTTP 200.
8. `E2E_BASE_URL=http://127.0.0.1:8888 pnpm test:e2e`.
9. Upload `playwright-report/` and `test-results/`.

Because the server runs with `WP_TEST_ENV=1`, every request — cron loopback included — uses `wordpress_test`. The header stays for local parity.

Keep the old wp-env workflow green until this lands. The Pro project is skipped in CI because `tutor-pro` is not checked out; add a separate job only if a Pro token exists.

Exit: a PR run is green with zero retries.

## Phase 8 — Remove wp-env

- Delete `.wp-env.json`, generated `.wp-env.override.json`, `bin/e2e/run.sh`, `bin/e2e/ensure-docker.sh`, `bin/e2e/seed.sh`, `bin/e2e/seed.php`, `bin/e2e/seed-admin.php`.
- Remove `@wordpress/env` from `devDependencies` and the wp-env scripts (`test`, `e2e`, `e2e:full`, `e2e:journey`, `e2e:open`, `e2e:env`, `e2e:reset`, `e2e:down`); point `test` at `pnpm test:e2e`.
- Remove the `wp-env` branch from `factories.ts`.
- `.gitignore`: drop `.wp-env.override.json`; keep `.env`, `.e2e/`, `test-results/`, `playwright-report/`.
- `tests/e2e/README.md`: final commands.

Exit: `rg 'wp-env'` returns only historical docs.

## Phase 9 — Verification

Local:

```bash
composer test:install            # dry run
composer test:install:apply      # writes wp-config, creates local_test + local_phpunit
composer test:db:reset
pnpm test:e2e                    # 34 core tests, retries 0
E2E_PRO=1 pnpm test:e2e          # + 121 Pro tests
composer test:unit               # 15 PHPUnit files, 176 tests, on local_phpunit
```

Data safety:

- set a marker option in the live DB and assert it is unchanged after the run;
- checksum `wp-content/uploads` and `wp-content/plugins` before/after;
- tail the MySQL general log for live-DB queries during the E2E run.

## Rollback

`php tests/support/install.php --uninstall` restores `wp-config.php.bak`, removes mu-plugin links, and drops the generated test DBs. The migration itself is a branch; no live data is rewritten.

## Residual risks

- Local testing still shares the plugin filesystem. Activation does not write files, but a plugin update run from the test DB would change code the live site serves.
- Local cron and inbound webhooks do not send the header. Outbound calls are stubbed by the test mu-plugin; cron is the remaining leak. For full isolation, use a test vhost or set `DISABLE_WP_CRON` on the dev site.
- The `wp_test_env` cookie is a manual convenience. The host allowlist in Phase 2 is what keeps it off public hosts.
- `wp server` is single-threaded; `PHP_CLI_SERVER_WORKERS` is required in CI.

## Out of scope

- Moving specs.
- Adding retries.
- Copying Pro tests or factories into free.
- Seeding the test DB from a live dump.
- Committing `.env`.
