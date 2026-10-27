# Testing Tutor LMS

This directory documents automated testing for Tutor LMS:
- **`tests/phpunit/`**: PHPUnit unit and integration tests.
- **`tests/e2e/`**: Modern E2E tests powered by Playwright.
- **`tests/support/`**: Test database reset and fixture seeding utilities.


---

## Setup

1. Create a database for test. ex: `local_test`
2. Copy `.env.example` to `.env` in the plugin root, then set your test database details.

3. Initializes a fresh test database by `composer test:db:reset`

4. Put it into `wp-config.php`

```php
/** Test database routing for PHPUnit, Playwright E2E and CLI testing */
require_once __DIR__ . '/wp-content/plugins/tutor/tests/support/test-config.php';

define( 'DB_NAME', TutorTestConfig::db( 'NAME', 'local' ) );
define( 'DB_USER', TutorTestConfig::db( 'USER', 'root' ) );
define( 'DB_PASSWORD', TutorTestConfig::db( 'PASS', 'root' ) );
define( 'DB_HOST', TutorTestConfig::db( 'HOST', '127.0.0.1:10003' ) );

TutorTestConfig::define_test_constants();

// ... table_prefix and other settings

/** Sets up WordPress vars and included files (skipped during PHPUnit tests so test suite handles bootstrap). */
if ( ! defined( 'WP_RUNNING_TESTS' ) && ! defined( 'WP_INSTALLING' ) ) {
	require_once ABSPATH . 'wp-settings.php';
}
```
5. Check that it works: `composer test:unit:filter DBTest` 

## Commands

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

