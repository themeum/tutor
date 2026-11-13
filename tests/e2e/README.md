# Tutor LMS E2E (Playwright)

The browser suite runs against a real WordPress site whose test requests are routed to a dedicated database. Core tests are always run, and Pro tests (`../tutor-pro/tests/e2e`) or External tests (`./external`) are automatically included when present.

## Setup

```bash
cp .env.example .env      # fill in the database credentials
composer test:install     # dry run: prints what would change
composer test:install:apply
pnpm test:e2e:install     # install Chromium
```

`test:install:apply` creates `WP_TEST_DB_NAME` and `WP_PHPUNIT_DB_NAME`, routes `wp-config.php` through `tests/support/test-config.php`, links the test mu-plugins, and seeds fixtures. `composer test:uninstall` restores `wp-config.php.bak`, removes the mu-plugin links, and drops both test databases.

## Commands

```bash
pnpm test:e2e           # full suite (core + pro when present)
pnpm test:e2e:smoke     # @smoke
pnpm test:e2e:journey   # @journey
pnpm test:e2e:ui        # Playwright interactive UI
pnpm test:e2e:reset     # drop and reseed the test database
composer test:unit      # PHPUnit on its own database
```

Every test command runs `tests/support/preflight.php` first. It fails when the live, E2E, and PHPUnit database names collide, when the E2E name does not end in `_test`, or when the configured site host is not in `WP_TEST_HOSTS`.

## How isolation works

- The Playwright config, global setup, and every role fixture send `X-WP-Test-Env: playwright`.
- `tests/support/test-config.php` swaps `DB_NAME` for `WP_TEST_DB_NAME` and defines `UPLOADS` as `wp-content/uploads-e2e`.
- `WP_TEST_ENV=1` does the same for WP-CLI, the reset command, and the CI web server.
- `WP_RUNNING_TESTS` (constant or environment) selects `WP_PHPUNIT_DB_NAME` for PHPUnit, including the child installer process.
- The header and cookie only count on hosts in `WP_TEST_HOSTS` (default `localhost,127.0.0.1,::1,*.local`), so a public host cannot opt into the test database.

Current coverage is 45 spec files / 155 tests: 16 files / 34 core tests and 29 files / 121 Pro tests.

## Layout

```text
tests/e2e/
  playwright.config.ts # the only Playwright config
  global-setup.ts      # UI login, one storage state per seeded role
  support/             # env, auth, role fixtures, factories, selectors
  smoke/               # @smoke
  feature/             # @feature (new areas go here)
  journey/             # @journey (one serial spec per journey)
  admin/               # dashboard coverage
  external/            # @external, auto-included when present
tests/support/
  test-config.php      # request-scoped database selector
  install.php          # dry-run installer and uninstaller
  preflight.php        # fail-fast database safety checks
  seed.php             # shared five-role seeder
  reset-test-db.php    # drop, install, seed
bin/e2e/
  factories.php        # per-spec courses/lessons/enrollments
  mu-plugins/          # mail log and HTTP stub (test requests only)
```

## Selectors

Use lowercase `data-testid` (Playwright's default `testIdAttribute`). WordPress core screens have no test ids; use labels, roles, or their stable IDs there. `getByTestId()` is the default for plugin UI.

## Flake policy

- Retries are disabled. A failure is a failure, not a prompt to retry.
- Quarantine a flaky test with `test.fixme` and the `@quarantine` tag, keep the owner and the failing run link in a comment, and fix or delete it within two weeks. Do not add `retries` or `test.describe.configure({ retries })`.
- Suite owner: Tutor LMS platform team (Themeum). Area owners are recorded in the pull request that ports each feature area.

## Notes

- The suite reads the single plugin-root `.env` (override with `E2E_ENV_FILE`). `E2E_BASE_URL` wins over `SITE_URL`; the fallback is `http://localhost:8888`.
- Config-level `extraHTTPHeaders` does not reach contexts created by the role fixtures; those add the header explicitly.
- The Pro license gate is bypassed on localhost/.local.
- Build Pro's Composer/npm assets in `../tutor-pro` first; the core suite does not build them.
- CI uses MySQL, the installer, and `wp server` with `WP_TEST_ENV=1`; cron loopbacks therefore use the test database too.
