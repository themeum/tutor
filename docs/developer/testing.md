# Testing Tutor LMS

Automated tests for Tutor LMS live in the `tests/` directory:

| Path             | Purpose                                              |
| ---------------- | ---------------------------------------------------- |
| `tests/phpunit/` | PHPUnit unit and integration tests                   |
| `tests/e2e/`     | End-to-end tests powered by Playwright               |
| `tests/support/` | Test installer, database reset and fixture utilities |

For a detailed explanation of how the isolated databases, installer, seeder,
Playwright lifecycle, factories, and safety guards work, see
[Test environment walkthrough](./e2e-test-environment-walkthrough.md).

## Table of contents

- [Setup](#setup)
- [Resetting the test database](#resetting-the-test-database)
- [Running PHPUnit tests](#running-phpunit-tests)
- [Running Playwright E2E tests](#running-playwright-e2e-tests)
- [Troubleshooting](#troubleshooting)

## Setup

Run the installer once:

```bash
pnpm test:setup           # installer prompt, then Chromium
```

`test:setup` runs the installer and installs Chromium. The installer prints what it will do and asks before modifying `wp-config.php`. Pass `--yes` to the installer (`composer test:install -- --yes`) to apply without prompting, or `--dry-run` to preview only. `pnpm test:uninstall` (or `composer test:uninstall`) restores `wp-config.php.bak`, removes the mu-plugin links, and drops both test databases.

To run only the installer:

```bash
composer test:install
```

The installer will:

1. Read your database settings from `wp-config.php`.
2. Create `.env` (from `.env.example` when available) and add any missing `WP_TEST_DB_*` variables.
3. Create two separate databases: an E2E database (your live database name with a `_test` suffix, for example `local_test`) and a PHPUnit database (for example `local_phpunit`).
4. Route `wp-config.php` to the test databases and skip `wp-settings.php` while tests run. A backup is saved as `wp-config.php.bak`.
5. Reset the E2E database and seed fixtures.
6. Install Chromium (`pnpm test:setup` does this after the installer).

Each step prints a green ✔ on success or a red ✘ on failure. The installer stops at the first failure and shows the error.

### Environment variables

The installer adds these to `.env` if they are missing:

```dotenv
WP_TEST_DB_NAME=local_test
WP_TEST_DB_USER=root
WP_TEST_DB_PASS=root
WP_TEST_DB_HOST=127.0.0.1:10003
```

Values already set in your shell environment take priority over `.env`.

### Manual configuration

If your `wp-config.php` is heavily customized and the installer cannot update it, make sure it contains the following blocks.

**Database routing**, in place of your existing `DB_*` definitions:

```php
/** Test database routing for PHPUnit, Playwright E2E and CLI testing */
$tutor_test_config = __DIR__ . '/wp-content/plugins/tutor/tests/support/test-config.php';

if ( file_exists( $tutor_test_config ) ) {
	require_once $tutor_test_config;
	define( 'DB_NAME', TutorTestConfig::db( 'NAME', 'local' ) );
	define( 'DB_USER', TutorTestConfig::db( 'USER', 'root' ) );
	define( 'DB_PASSWORD', TutorTestConfig::db( 'PASS', 'root' ) );
	define( 'DB_HOST', TutorTestConfig::db( 'HOST', '127.0.0.1:10003' ) );
	TutorTestConfig::define_test_constants();
} else {
	define( 'DB_NAME', 'local' );
	define( 'DB_USER', 'root' );
	define( 'DB_PASSWORD', 'root' );
	define( 'DB_HOST', '127.0.0.1:10003' );
}
```

**`wp-settings.php` guard**, so the test suite can handle its own bootstrap:

```php
if ( ! defined( 'WP_RUNNING_TESTS' ) && ! defined( 'WP_INSTALLING' ) ) {
	require_once ABSPATH . 'wp-settings.php';
}
```

## Resetting the test database

Reset the database and re-seed fixtures whenever tests leave it in a bad state:

```bash
# Composer
composer test:db:reset

# pnpm / npm
pnpm run test:e2e:reset

# Directly via PHP
php tests/support/reset-test-db.php
```

## Running PHPUnit tests

```bash
# All tests
composer test:unit
# or
vendor/bin/phpunit

# A single test class
composer test:unit:filter DBTest
vendor/bin/phpunit --filter=CourseTest
vendor/bin/phpunit --filter=QueryHelperTest

# A single test suite
vendor/bin/phpunit --testsuite=query-helper-test
```

> **Tip:** `composer test:unit:filter DBTest` is a quick way to confirm your setup works.

## Running Playwright E2E tests

The browser suite runs against a real WordPress site whose test requests are routed to a dedicated database (`local_test`). Core tests are always run, and Pro tests (`../tutor-pro/tests/e2e`) or External tests (`tests/e2e/external`) are automatically included when present.

Make sure your local WordPress development site is running (e.g. in Local,
Valet, Docker) and that `SITE_URL` in `.env` matches your site URL (e.g.
`http://localhost:8888` or `http://themeum-tutor.local`). Rebuild assets after pulling source changes (`pnpm exec rspack --mode=production` in this plugin, and in `../tutor-pro` when running Pro tests).

```bash
# All E2E tests (core + pro when present)
pnpm test:e2e

# Smoke / journey subsets
pnpm test:e2e:smoke     # @smoke
pnpm test:e2e:journey   # @journey

# Interactive Playwright UI / debug
pnpm test:e2e:ui
pnpm test:e2e:debug

# PHPUnit + E2E
pnpm test
```

### How isolation works

- Every test command runs `tests/support/preflight.php` first and fails fast if the live, E2E, and PHPUnit database names collide, if the E2E database does not end in `_test`, or if the configured host is not in `WP_TEST_HOSTS`.
- Playwright config, global setup, and per-role fixtures send `X-WP-Test-Env: playwright` (accepted only on hosts in `WP_TEST_HOSTS`, defaulting to `localhost,127.0.0.1,::1,*.local`).
- `tests/support/test-config.php` swaps `DB_NAME` for `WP_TEST_DB_NAME` and sets `UPLOADS` to `wp-content/uploads-e2e`.
- `WP_TEST_ENV=1` selects `WP_TEST_DB_NAME` for WP-CLI, `reset-test-db.php`, and CI `wp server`.
- `WP_RUNNING_TESTS` selects `WP_PHPUNIT_DB_NAME` for PHPUnit.

### E2E layout & conventions

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
  install.php          # interactive installer and uninstaller
  preflight.php        # fail-fast database safety checks
  seed.php             # shared five-role seeder
  reset-test-db.php    # drop, install, seed
tests/e2e/bin/
  factories.php        # per-spec courses/lessons/enrollments
  mu-plugins/          # mail log and HTTP stub (test requests only)
```

- **Selectors**: Use lowercase `data-testid` (`getByTestId()`) for plugin UI, and labels, roles, or stable IDs for WordPress core screens.
- **Flake policy**: Retries are disabled (`retries: 0`). Quarantine a flaky test with `test.fixme` and `@quarantine` with owner and failing run link in a comment, and fix or remove within two weeks.
- **Notes**: `E2E_BASE_URL` overrides `SITE_URL` (fallback `http://localhost:8888`); the Pro license gate is bypassed on `localhost`/`.local`.

## Troubleshooting

| Problem                                    | Fix                                                                                                      |
| ------------------------------------------ | -------------------------------------------------------------------------------------------------------- |
| `wp-config.php is missing`                 | Run the installer from inside the plugin directory (`wp-content/plugins/tutor`) of a WordPress install.  |
| Database connection failed                 | Make sure MySQL is running and the `WP_TEST_DB_*` values in `.env` are correct.                          |
| Tests hit the live database                | Check that the database routing block above is present in `wp-config.php`.                               |
| Need to undo `wp-config.php` changes       | Restore `wp-config.php.bak`.                                                                             |