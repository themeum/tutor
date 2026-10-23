# Testing Tutor LMS

This directory documents automated testing for Tutor LMS:
- **`tests/phpunit/`**: PHPUnit unit and integration tests.
- **`tests/e2e/`**: Modern E2E tests powered by Playwright.
- **`tests/supports/`**: Test database reset and fixture seeding utilities.

---

## Architecture Overview

1. **Reuses the Local WordPress Instance**:
   - Tests execute directly against the existing WordPress installation in Local by Flywheel (`app/public`), avoiding duplicate core downloads or out-of-sync WordPress copies.
2. **Dedicated Test Database (`local_test`)**:
   - All PHPUnit and Playwright E2E tests execute against `local_test`.
   - Your primary development database (`local`) is completely protected and untouched.
3. **Dynamic Database Routing**:
   - When Playwright runs, requests carry the header `X-WP-Test-Env: playwright`.
   - WordPress automatically routes requests to `local_test`.
   - Normal browser visits to your site continue to use `local`.
4. **Automated Safeguards**:
   - Both PHPUnit bootstrap (`tests/wp-tests-config.php`) and the database reset tool refuse to run if the targeted database is `local` or does not contain `test` or `phpunit`.

---

## Quick Start & Commands

### 1. Initialize or Reset the Test Database
Initializes a fresh test database (`local_test`) from scratch using native WordPress APIs, creating standard users (`admin`, `instructor`, `student`) and sample course fixtures:
```bash
# Via Composer
composer test:db:reset

# Or via npm/pnpm
pnpm run test:e2e:reset

# Or directly via PHP
php tests/supports/reset-test-db.php
```

### 2. Run PHPUnit Tests
```bash
# Run all PHPUnit tests
vendor/bin/phpunit
# or
composer test:unit

# Run a specific test class
vendor/bin/phpunit --filter=DBTest
vendor/bin/phpunit --filter=CourseTest
vendor/bin/phpunit --filter=QueryHelperTest

# Run a specific test suite
vendor/bin/phpunit --testsuite=query-helper-test
```

### 3. Run Playwright E2E Tests
```bash
# Run all E2E tests headless
pnpm run test:e2e

# Run with interactive Playwright UI
pnpm run test:e2e:ui

# Run in debug mode
pnpm run test:e2e:debug
```

---

## Configuration & Environment Variables

Put it into `wp-config.php`

```php
/** Test database routing for Playwright E2E and CLI testing */
$is_test_environment =
	( defined( 'WP_RUNNING_TESTS' ) && WP_RUNNING_TESTS ) ||
	( isset( $_SERVER['HTTP_X_WP_TEST_ENV'] ) && 'playwright' === $_SERVER['HTTP_X_WP_TEST_ENV'] ) ||
	( isset( $_COOKIE['wp_test_env'] ) && 'test' === $_COOKIE['wp_test_env'] ) ||
	( getenv( 'WP_TEST_ENV' ) === '1' );

define( 'DB_NAME', $is_test_environment ? 'local_test' : 'local' );
```
Create a `.env` file in the plugin root (or copy from `.env.example`). Values are automatically tailored for Local by Flywheel:

| Variable | Default Value | Description |
|---|---|---|
| `SITE_URL` | `https://tutor-dev.local` | Base site URL for tests |
| `TUTOR_CHECKOUT_URL` | `https://tutor-dev.local/checkout-21` | Checkout URL for tests |
| `ADMIN_USERNAME` | `admin` | Admin username for E2E tests |
| `ADMIN_PASSWORD` | `12345` | Admin password for E2E tests |
| `WP_TEST_DB_NAME` | `local_test` | Dedicated test database (must contain `test`) |
| `WP_TEST_DB_USER` | `root` | Database username |
| `WP_TEST_DB_PASS` | `root` | Database password |
| `WP_TEST_DB_HOST` | `127.0.0.1:10003` | Database host & port |
