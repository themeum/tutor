# Testing Tutor LMS

Automated tests for Tutor LMS live in the `tests/` directory:

| Path             | Purpose                                              |
| ---------------- | ---------------------------------------------------- |
| `tests/phpunit/` | PHPUnit unit and integration tests                   |
| `tests/e2e/`     | End-to-end tests powered by Playwright               |
| `tests/support/` | Test installer, database reset and fixture utilities |

## Table of contents

- [Setup](#setup)
- [Resetting the test database](#resetting-the-test-database)
- [Running PHPUnit tests](#running-phpunit-tests)
- [Running Playwright E2E tests](#running-playwright-e2e-tests)
- [Troubleshooting](#troubleshooting)

## Setup

Run the installer once:

```bash
pnpm test:setup
```

`test:setup` runs the installer and installs Chromium. The installer prints what it will do and asks before modifying `wp-config.php`. Pass `--yes` to the installer (`composer test:install -- --yes`) to apply without prompting, or `--dry-run` to preview only.

To run only the installer:

```bash
composer test:install
```

The installer will:

1. Read your database settings from `wp-config.php`.
2. Create `.env` (from `.env.example` when available) and add any missing `WP_TEST_DB_*` variables.
3. Create a separate test database (your live database name with a `_test` suffix, for example `local_test`).
4. Route `wp-config.php` to the test database and skip `wp-settings.php` while tests run. A backup is saved as `wp-config.php.bak`.
5. Reset the test database and seed fixtures.

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

```bash
# All E2E tests (headless)
pnpm run test:e2e

# Interactive Playwright UI
pnpm run test:e2e:ui

# Debug mode
pnpm run test:e2e:debug

# PHPUnit + E2E
pnpm test
```

## Troubleshooting

| Problem                                    | Fix                                                                                                      |
| ------------------------------------------ | -------------------------------------------------------------------------------------------------------- |
| `wp-config.php is missing`                 | Run the installer from inside the plugin directory (`wp-content/plugins/tutor`) of a WordPress install.  |
| Database connection failed                 | Make sure MySQL is running and the `WP_TEST_DB_*` values in `.env` are correct.                          |
| Tests hit the live database                | Check that the database routing block above is present in `wp-config.php`.                               |
| Need to undo `wp-config.php` changes       | Restore `wp-config.php.bak`.                                                                             |