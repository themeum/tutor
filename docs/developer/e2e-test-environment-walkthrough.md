# Test environment walkthrough

This is the end-to-end explanation of how Tutor LMS runs PHPUnit and Playwright
against isolated databases: what each file does, how a request chooses a
database, how the installer and seeder work, and the exact steps to set up,
run, reset, and troubleshoot the environment.

For the short command reference, see [Testing Tutor LMS](./testing.md). For the
historical migration plan, see
[E2E migration: isolated test database](./e2e-test-db-migration.md).

## Contents

- [Quick start](#quick-start)
- [1. The mental model](#1-the-mental-model)
- [2. File map](#2-file-map)
- [3. How a request chooses a database](#3-how-a-request-chooses-a-database)
- [4. The installer](#4-the-installer)
- [5. Reset and seed pipeline](#5-reset-and-seed-pipeline)
- [6. Playwright lifecycle](#6-playwright-lifecycle)
- [7. Factories](#7-factories)
- [8. Test-only mu-plugins](#8-test-only-mu-plugins)
- [9. PHPUnit](#9-phpunit)
- [10. Safety model](#10-safety-model)
- [11. Environment variables](#11-environment-variables)
- [12. Step-by-step: first-time local setup](#12-step-by-step-first-time-local-setup)
- [13. Step-by-step: daily workflow](#13-step-by-step-daily-workflow)
- [14. Step-by-step: reset](#14-step-by-step-reset)
- [15. Step-by-step: CI](#15-step-by-step-ci)
- [16. Troubleshooting](#16-troubleshooting)
- [17. Extending the environment](#17-extending-the-environment)
- [18. Command reference](#18-command-reference)
- [19. Known limitations](#19-known-limitations)

## Quick start

From `wp-content/plugins/tutor` in a working WordPress install:

```bash
pnpm install
composer install
pnpm test:setup          # installer prompt, then Chromium

# In another terminal, start the test server (see section 12, step 4)
WP_TEST_ENV=1 PHP_CLI_SERVER_WORKERS=4 \
  wp server --host=127.0.0.1 --port=8888 --path="<wordpress-root>"

pnpm test:e2e:smoke      # 5 smoke tests
composer test:unit       # PHPUnit on its own database
pnpm test                # PHPUnit, then the full E2E suite
```

`pnpm test:setup` must run in an interactive terminal: it prints the installer
plan and asks before changing `wp-config.php`. In CI or any non-TTY shell, use
the explicit form instead:

```bash
composer test:install -- --yes
pnpm exec playwright install chromium
```

## 1. The mental model

There is one WordPress codebase, one web server, and one set of plugin files.
Only the database (and the uploads directory) changes per test runner:

```text
Playwright browser
  X-WP-Test-Env: playwright
        |
        v
wp-config.php ──> tests/support/test-config.php ──> local_test
        ^
        |
WP-CLI, reset, factories
  WP_TEST_ENV=1
        |
PHPUnit
  WP_RUNNING_TESTS
        |
        v
tests/support/test-config.php ──> local_phpunit

Normal browser / admin
  no signal
        |
        v
wp-config.php ──> local (live)
```

| Database         | Used by                          | Disposable? | Default name       |
| ---------------- | -------------------------------- | ----------- | ------------------ |
| `local`          | The real site                    | No          | From `wp-config.php` |
| `local_test`     | Playwright E2E, WP-CLI, factories | Yes         | `<live>_test`      |
| `local_phpunit`  | PHPUnit / wp-phpunit             | Yes         | `<live>_phpunit`   |

The E2E and PHPUnit databases are separate because wp-phpunit drops the
WordPress core tables and reinstalls WordPress in its database on every run. If
PHPUnit shared `local_test`, it would erase the E2E seed before the browser
suite ran.

The live database is never copied into the test database. The test database is
built from scratch by `tests/support/reset-test-db.php` and seeded by
`tests/support/seed.php`.

## 2. File map

| Path                                     | Responsibility                                                                 |
| ---------------------------------------- | ------------------------------------------------------------------------------ |
| `tests/support/test-config.php`          | Request-scoped database selector, name guards, test constants, uploads, debug  |
| `tests/support/install.php`              | Interactive installer, `.env` sync, DB creation, `wp-config.php` routing, links |
| `tests/support/preflight.php`            | Static fail-fast checks before PHPUnit or Playwright starts                    |
| `tests/support/reset-test-db.php`        | Drop, install WordPress, activate plugins, seed fixtures                       |
| `tests/support/seed.php`                 | Five roles, Tutor settings, pages, manual payment, baseline course             |
| `tests/support/seed-admin.php`           | Admin dashboard fixtures: courses, orders, Q&A, quiz attempts, withdrawals     |
| `tests/support/page-urls.php`            | Optional CLI helper that prints dynamic page URLs as JSON                      |
| `tests/bootstrap.php`                    | PHPUnit bootstrap; selects the PHPUnit database and loads wp-phpunit           |
| `phpunit.xml.dist`                       | PHPUnit test suites                                                            |
| `tests/e2e/playwright.config.ts`         | The only Playwright config: projects, timeouts, header, artifacts              |
| `tests/e2e/global-setup.ts`              | Logs in all five roles and writes one storage state per role                   |
| `tests/e2e/support/auth.ts`              | Login flow and storage-state paths                                             |
| `tests/e2e/support/env.ts`               | Fixed usernames, password, base URL, role list                                 |
| `tests/e2e/support/test.ts`              | Role page fixtures (`adminPage`, `studentPage`, ...)                           |
| `tests/e2e/support/factories.ts`         | TypeScript bridge to the WP-CLI factories                                      |
| `bin/e2e/factories.php`                  | Per-spec fixture creation inside the test database                             |
| `bin/e2e/mu-plugins/e2e-http-stub.php`   | Deterministic external HTTP responses                                          |
| `bin/e2e/mu-plugins/e2e-mail-log.php`    | Captures `wp_mail()` instead of sending mail                                   |
| `.github/workflows/e2e.yml`              | CI: MySQL, installer, `wp server`, Playwright, artifacts                       |
| `.env.example`                           | The documented environment variables                                           |

Pro-owned code stays in `tutor-pro`:

- `tutor-pro/tests/e2e/` — Pro specs, Pro support, Pro factories.
- `tutor-pro/bin/e2e/seed-pro.php` — Pro add-on and dashboard fixtures.
- `tutor-pro/bin/e2e/factories.php` — Pro-only factories such as course bundles.

The free plugin auto-detects those paths. It does not copy Pro code into the
free repo.

## 3. How a request chooses a database

### 3.1 The `wp-config.php` routing block

The installer replaces the normal `DB_NAME` … `DB_HOST` definitions with:

```php
$tutor_test_config = __DIR__ . '/wp-content/plugins/tutor/tests/support/test-config.php';

if ( file_exists( $tutor_test_config ) ) {
	require_once $tutor_test_config;
	define( 'DB_NAME', TutorTestConfig::db( 'NAME', 'local' ) );
	define( 'DB_USER', TutorTestConfig::db( 'USER', 'root' ) );
	define( 'DB_PASSWORD', TutorTestConfig::db( 'PASS', 'root' ) );
	define( 'DB_HOST', TutorTestConfig::db( 'HOST', 'localhost' ) );
	TutorTestConfig::define_test_constants();
} else {
	define( 'DB_NAME', 'local' );
	define( 'DB_USER', 'root' );
	define( 'DB_PASSWORD', 'root' );
	define( 'DB_HOST', 'localhost' );
}
```

The `else` branch preserves the original values. If `test-config.php` is
missing, the site falls back to the live database instead of breaking. The
second argument to `TutorTestConfig::db()` is always the live value, so the
selector knows what it must never use for tests.

### 3.2 Test signals

`TutorTestConfig::is_test_environment()` returns true when any of these is true:

| Signal                                    | Where it comes from                         | Which database      |
| ----------------------------------------- | ------------------------------------------- | ------------------- |
| `WP_RUNNING_TESTS` constant or env `=1`   | `tests/bootstrap.php`, wp-phpunit child     | `WP_PHPUNIT_DB_NAME` |
| `WP_INSTALLING` constant                  | Reset script, installer, wp-phpunit         | E2E database        |
| `WP_TEST_ENV=1` environment variable      | WP-CLI, reset script, CI server, factories  | E2E database        |
| `X-WP-Test-Env: playwright` header        | Playwright config, global setup, fixtures   | E2E database        |
| `wp_test_env=test` cookie                 | Manual browser escape hatch                 | E2E database        |

The header and cookie are only honored on a host in `WP_TEST_HOSTS`
(default `localhost,127.0.0.1,::1,*.local`). A public host cannot opt into the
test database by sending a header or setting a cookie.

`WP_RUNNING_TESTS` wins over the other signals for the database name. That is
what makes the PHPUnit child installer use `WP_PHPUNIT_DB_NAME` even though it
was spawned by a process that may also have `WP_TEST_ENV=1`.

### 3.3 Name resolution and guards

For the E2E database, `test-config.php` reads `WP_TEST_DB_NAME`; for PHPUnit it
reads `WP_PHPUNIT_DB_NAME`. Values are resolved in this order:

1. Process environment.
2. `.env` at the plugin root.
3. No fallback for the database name — the request fails loudly.

The user, password, and host default to the live values. Only the database name
is normally different. `WP_TEST_DB_HOST` may be `host`, `host:port`, or
`host:/path/to/mysql.sock`.

Before returning a test database name, the selector refuses:

- a missing or empty name;
- a name that does not match `_test` or `phpunit`;
- a name equal to the live database.

The installer, preflight, and reset script are stricter:

- E2E name must end in `_test`.
- PHPUnit name must end in `_test` or contain `phpunit`.
- The live, E2E, and PHPUnit names must all differ.

These checks are intentionally duplicated. A single missed check in one entry
point must not be enough to point a destructive command at the live database.

### 3.4 Constants and side effects

`TutorTestConfig::define_test_constants()` only runs in test mode. It:

- defines `WP_TESTS_DOMAIN`, `WP_TESTS_EMAIL`, `WP_TESTS_TITLE`, and
  `WP_PHP_BINARY` so wp-phpunit's installer does not warn about undefined
  constants;
- pins `WP_HOME` and `WP_SITEURL` to `SITE_URL`, then `E2E_BASE_URL`, then
  `http://localhost:8888`, so login forms and redirects never point at a host
  the browser cannot resolve;
- defines `UPLOADS` as `wp-content/uploads-e2e`, keeping test media out of the
  live uploads directory;
- sets `WP_DEBUG` false, `WP_DEBUG_LOG` true, and `WP_DEBUG_DISPLAY` false, so
  notices go to `wp-content/debug.log` instead of polluting the HTML that
  Playwright asserts on.

### 3.5 The `wp-settings.php` guard

The installer also wraps the normal bootstrap line in `wp-config.php`:

```php
if ( ! defined( 'WP_RUNNING_TESTS' ) && ! defined( 'WP_INSTALLING' ) ) {
	require_once ABSPATH . 'wp-settings.php';
}
```

Normal web requests still boot WordPress. PHPUnit and the reset script skip
that line because they need to install or bootstrap WordPress themselves. The
reset script explicitly requires `wp-settings.php` after it has connected to
the test database and is ready.

## 4. The installer

`composer test:install` runs `tests/support/install.php`. It is idempotent and
safe to run more than once; the final reset step rebuilds the E2E database.

### 4.1 What it changes

1. `.env` — creates it from `.env.example` if missing, then adds missing
   `WP_TEST_DB_*` and `WP_PHPUNIT_DB_NAME` keys.
2. MySQL — creates both test databases with `CREATE DATABASE IF NOT EXISTS`.
3. `wp-config.php` — backs it up to `wp-config.php.bak`, routes the DB
   definitions through `test-config.php`, and adds the `wp-settings.php` guard.
4. `wp-content/mu-plugins` — links the test mu-plugins as
   `tutor-e2e-http-stub.php` and `tutor-e2e-mail-log.php`.
5. The E2E database — runs `composer test:db:reset`, which drops, installs,
   activates, and seeds.

### 4.2 Step by step

When you run `composer test:install` in a terminal, it:

1. Locates `wp-config.php` five levels above `tests/support`.
2. Reads the live DB values from the fallback arguments of
   `TutorTestConfig::db(...)` or from `define(...)` in `wp-config.php`.
3. Prepares `.env` (preview only at this point).
4. Resolves the test DB settings: process env, then `.env`, then
   `<live>_test` and `<live>_phpunit`.
5. Syncs missing keys into `.env`. An active key is only overwritten when the
   same key is set in the process environment; otherwise the existing value is
   preserved.
6. Prints the plan.
7. Asks: `Apply these changes? This modifies wp-config.php, creates the test
   databases, and seeds fixtures. (y/N)`.
8. On `y`, runs the steps again with writes enabled.
9. Prints a green check per step and stops at the first failure.
10. Offers to run `composer test:unit:filter DBTest` as a verification.

The first pass is a real preview: every write is guarded by `$this->apply`, so
it does not create `.env`, databases, backups, or links.

### 4.3 Flags

| Command                                       | Behavior                                      |
| --------------------------------------------- | --------------------------------------------- |
| `composer test:install`                       | Preview, ask, then apply                      |
| `composer test:install -- --yes`              | Apply without asking                          |
| `composer test:install -- --dry-run`          | Preview only, never write                     |
| `composer test:uninstall`                     | Restore config, remove links, drop test DBs   |
| `php tests/support/install.php --uninstall`   | Preview the uninstall                         |
| `php tests/support/install.php --uninstall --yes` | Apply the uninstall                       |

`--yes` and `--dry-run` are mutually exclusive; `--dry-run` wins.

Non-interactive stdin is treated as “no”. A CI job that runs
`composer test:install` without `--yes` will print the plan and exit 0 without
installing anything. Always use `--yes` in automation.

### 4.4 Idempotency and the backup

- `.env` keys are added only when missing; an active key is preserved unless a
  process-level override is present.
- The DB creation uses `IF NOT EXISTS`.
- The `wp-config.php` routing step skips if `test-config.php` already appears.
- The `wp-settings.php` guard skips if `WP_RUNNING_TESTS` already appears.
- Mu-plugin links are skipped when the target already points at the source or
  has the same SHA-1.
- The reset step always rebuilds the E2E database, so running the installer
  again is destructive to test data by design.

The first write creates `wp-config.php.bak`. Later writes reuse it, so the
backup always represents the original live configuration. Uninstall copies it
back and deletes it. If you routed `wp-config.php` by hand and there is no
backup, uninstall stops and tells you to restore manually.

### 4.5 Uninstall safety

`composer test:uninstall`:

1. Resolves the test DB settings with the same guards as install.
2. Restores `wp-config.php` from `wp-config.php.bak`.
3. Removes only mu-plugin links that point at this plugin's source, or copies
   whose SHA-1 still matches.
4. Drops only names that pass the safety check: not empty, not the live name,
   and matching `_test` or `phpunit`.

## 5. Reset and seed pipeline

`composer test:db:reset`, `pnpm test:e2e:reset`, and
`php tests/support/reset-test-db.php` all run the same script.

### 5.1 Pre-boot guards

Before WordPress boots, the script:

1. Sets `WP_TEST_ENV=1` and defines `WP_INSTALLING`.
2. Reads `WP_TEST_DB_NAME` from the process environment, then `.env`.
3. Refuses an empty name or a name that does not end in `_test`.
4. Refuses to continue if `wp-config.php` does not contain `test-config.php`.

This matters because booting an unrouted `wp-config.php` would connect to the
live database before the post-boot guard could run.

### 5.2 Boot and post-boot guards

The script requires `wp-load.php`. If WordPress did not initialize `$wpdb`, it
requires `wp-settings.php`. Then it checks:

- `$wpdb->dbname` equals the expected `WP_TEST_DB_NAME`;
- the live name from `TutorTestConfig::live_db_name()` is non-empty;
- the expected name is not the live name.

Any mismatch aborts with a fatal error.

### 5.3 Drop and install

1. Reads `SHOW TABLES`, disables foreign-key checks, drops every table, and
   re-enables foreign-key checks.
2. Calls `wp_install()` with the site title, the `admin` user, and
   `E2E_PASSWORD` (default `password`).
3. Sets `siteurl` and `home` to `SITE_URL` (process environment or `.env`),
   then the process-level `E2E_BASE_URL`, then `http://localhost:8888`.
4. Sets a pretty permalink structure: `/%postname%/`.

### 5.4 Plugin activation

`WP_TEST_ACTIVE_PLUGINS` can be a comma-separated list of plugin files or
slugs. If unset, the script activates, when present:

- `tutor/tutor.php`
- `woocommerce/woocommerce.php`
- `tutor-pro/tutor-pro.php`
- `h5p/h5p.php`

A slug is normalized to `<slug>/<slug>.php`. A missing plugin file is a fatal
error. The script marks the Tutor wizard complete before activation, because
Tutor's activation hook otherwise redirects to the setup wizard and exits.

After activation, it sorts `active_plugins` alphabetically. This keeps
`tutor-pro` before `tutor`, which lets Pro hook `tutor_loaded`. It then fires
`plugins_loaded` and `init` manually, because the plugins were activated after
`wp-settings.php` had already run.

### 5.5 Tutor bootstrap

The script calls `TUTOR\Tutor::create_database()` and
`manage_tutor_roles_and_permissions()`. It installs default options only when
`tutor_option` is missing. It deliberately does not call `save_data()`, because
that would create duplicate dashboard pages and repoint the option at the copy.
It also sets the wizard flags, feature version, and first-activation time.

### 5.6 Seeding

`tests/support/seed.php` runs next. It:

- Requires Tutor to be active.
- Reads `E2E_PASSWORD` from the environment, then `.env`, then `password`.
- Enables registration so admin notices stay quiet.
- Rebuilds the instructor role and grants `manage_tutor_instructor` and
  `publish_tutor_courses`; grants `manage_tutor_instructor` to administrators.
- Creates or updates the five fixed accounts:

  | Login        | Role               | Purpose                          |
  | ------------ | ------------------ | -------------------------------- |
  | `admin`      | `administrator`    | Dashboard and settings           |
  | `instructor` | `tutor_instructor` | Course creation and instructor UI |
  | `student`    | `subscriber`       | Enrolled student flows           |
  | `student2`   | `subscriber`       | Access-control and second student |
  | `manager`    | `shop_manager`     | WooCommerce manager screens      |

  All five use the same `E2E_PASSWORD`. The seeder updates the password, email,
  display name, and role on every run, so changing `E2E_PASSWORD` requires a
  reset or re-seed.
- Skips the first-visit dashboard tour for every account.
- Marks the instructor approved.
- Writes Tutor settings: native monetization, buy-now on, guest checkout off,
  native login on, instructor publishing on, admin bar shown, coupons and tax
  off, USD, 2FA/spam/email verification off, marketplace and Q&A on.
- Updates or creates the Tutor pages: dashboard, instructor registration,
  student registration, cart, and checkout.
- Stores a manual bank-transfer payment method with instructions.
- Asserts that monetization, the dashboard page, the checkout page, and the
  payment settings are all present.
- Creates the baseline `Sample Test Course` with `Introduction Topic` and
  `Lesson 1: Getting Started` if that course does not already exist.
- Requires `tests/support/seed-admin.php`.
- Requires `tutor-pro/bin/e2e/seed-pro.php` when that file exists.

`seed-admin.php` adds the admin dashboard baseline: categories and tags,
published/draft/pending courses, orders and order items, announcements, Q&A
threads, quiz attempts, marketplace settings, a pending instructor, and
withdrawal requests.

The Pro seeder enables the Pro add-ons, creates a subscription plan and a
student subscription, creates gradebook tables and scales, and adds assignment
submissions. It is the only Pro-specific seed code; the free repo just detects
and runs it.

### 5.7 Finish

The reset flushes rewrite rules, writes the marker option
`tutor_e2e_test_db = 1`, and prints the elapsed time and the five accounts. A
core reset takes roughly 3–4 minutes; a full Pro reset can take much longer
because the Pro seeder creates a lot of data.

### 5.8 What reset does not do

- It does not touch the live database or the live uploads directory.
- It does not build JavaScript/CSS assets. Run `pnpm build-dev` or `pnpm build`
  when assets are stale.
- It does not start a web server.
- It does not install Chromium.

## 6. Playwright lifecycle

### 6.1 Config

`tests/e2e/playwright.config.ts` is the only Playwright config.

- Loads `.env` from the plugin root, or `E2E_ENV_FILE` if set.
- Resolves `baseURL` as `E2E_BASE_URL`, then `SITE_URL`, then
  `http://localhost:8888`.
- Uses Chromium.
- Runs one worker, not fully parallel, with zero retries.
- Sets a 60-second test timeout and a 10-second expect timeout.
- Uses `data-testid` as the test-id attribute.
- Keeps traces, screenshots, and video only on failure.
- Sends `X-WP-Test-Env: playwright` on the default context.
- Writes `test-results/` and `playwright-report/` at the plugin root.

### 6.2 Projects

| Project    | Included when                                      | Test directory                     |
| ---------- | -------------------------------------------------- | ---------------------------------- |
| `core`     | Always                                             | `tests/e2e` (ignores `external`)   |
| `external` | `tests/e2e/external` exists and `E2E_EXTERNAL≠0`   | `tests/e2e/external`               |
| `pro`      | `../tutor-pro/tests/e2e` exists and `E2E_PRO≠0`    | `tutor-pro/tests/e2e`              |

`E2E_PRO=0` is the switch for a core-only run. `E2E_EXTERNAL=0` does the same
for external specs. The default run includes Pro when the sibling checkout is
present.

### 6.3 Global setup and auth

`global-setup.ts` runs once before the suite:

1. Creates `tests/e2e/.auth/`.
2. Launches Chromium.
3. For each role (`admin`, `instructor`, `student`, `student2`, `manager`),
   creates a fresh context with the base URL and test header, logs in through
   `/wp-login.php`, and saves the cookies to `.auth/<role>.json`.
4. Closes every context and the browser.

The auth files are gitignored. They are snapshots: if you reset the database
after global setup, the cookies may no longer be valid. Do not reset mid-run.

### 6.4 Role fixtures

`tests/e2e/support/test.ts` exports a Playwright `test` with these fixtures:

- `adminPage`
- `instructorPage`
- `studentPage`
- `student2Page`
- `managerPage`
- `guestPage`

Each fixture creates a new browser context, loads the role's storage state
(except `guestPage`), adds the test header explicitly, yields the page, and
closes the context afterward.

The explicit header is required. `extraHTTPHeaders` in the config does not
propagate to contexts created with `browser.newContext()`. If you add a new
fixture, add the header or reuse `rolePage`.

### 6.5 Isolation and flake policy

- One worker and no parallel tests avoid database races.
- Retries are disabled. A failure is a real failure.
- A flaky test is quarantined with `test.fixme` and `@quarantine`, with an
  owner and a failing-run link in a comment. It must be fixed or deleted within
  two weeks. Do not add retries.
- Specs share the seeded database. Use factories with unique titles for data
  that must not collide, and clean up options or capabilities you change.

### 6.6 Artifacts

On failure, Playwright writes traces, screenshots, and video to
`test-results/`. The HTML report is `playwright-report/`. Both are gitignored
and uploaded by CI.

## 7. Factories

The seeder provides a baseline. Factories create per-spec data without making
every spec depend on the same course or enrollment.

### 7.1 The bridge

`tests/e2e/support/factories.ts`:

1. Resolves the WordPress root from `WP_ROOT`, or from the plugin path
   (`../../..`).
2. Creates `wp-content/e2e` for results.
3. Generates a UUID result filename.
4. Base64-encodes a JSON payload containing the factory name and input.
5. Runs:

   ```bash
   wp --path=<wp-root> eval-file <factory-script> <base64-payload>
   ```

   with `WP_TEST_ENV=1` in the environment.
6. Reads the JSON result file, deletes it, and returns the parsed object.

The `WP_TEST_ENV=1` is the safety line: the entire WP-CLI process selects the
test database. `WP_CLI_BIN` overrides the `wp` binary; `WP_ROOT` overrides the
WordPress root.

### 7.2 Free factories

`bin/e2e/factories.php` supports:

| Factory | Creates or returns |
| --- | --- |
| `course` | Course, topic, lesson, optional quiz/assignment/plans |
| `get_course` | Course fields and builder state |
| `lesson` | Lesson under an existing course/topic |
| `enrollment` | Native Tutor enrollment |
| `course_complete` | Marks a course complete for a user |
| `notification` | Notifications row |
| `post_meta`, `user_meta` | Meta reads/writes |
| `user` | User, or the existing user with that login |
| `wc_product` | WooCommerce simple product |
| `tutor_order_complete` | Marks a Tutor order paid |
| `wc_order_complete` | Completes a WooCommerce order |
| `wc_clear_cart` | Clears carts and sessions |
| `set_option` | Whole option or one `tutor_option` key |
| `enable_addon`, `get_addon_states`, `set_addon_state`, `set_addon_baseline` | Pro add-on state |
| `snapshot` | Post/table/comment/option counts |
| `get_debug_log`, `clear_debug_log` | `wp-content/debug.log` |
| `mail_log` | Read or clear captured mail |
| `set_http_stub` | Configure the HTTP stub |
| `get_plugins` | Active and installed plugins |
| `add_capability`, `remove_capability` | Per-user capabilities |
| `get_cron` | Cron hooks |

Pro factories are in `tutor-pro/bin/e2e/factories.php` and currently cover
course bundles (`bundle`, `get_bundle`). Pro specs import the free bridge and
pass the Pro script path.

### 7.3 Factory safety

- The result filename must match a UUID pattern.
- The factory script requires `ABSPATH` and `WP_CLI`.
- Failures throw with stdout, stderr, and the WP-CLI error.
- The result directory is under the WordPress root, not the plugin.
- Factories run with a 120-second timeout.

Use a factory for data a spec creates and can leave behind. Use the seeder for
data every spec expects to exist. Do not use factories to mutate the live
database; they always run with `WP_TEST_ENV=1`.

## 8. Test-only mu-plugins

The installer links `bin/e2e/mu-plugins/*.php` into
`wp-content/mu-plugins/tutor-*.php`. Both plugins bail out unless
`TutorTestConfig` exists and `is_test_environment()` is true, so they are inert
on the live site.

### 8.1 HTTP stub

`e2e-http-stub.php` hooks `pre_http_request`. It reads the `e2e_http_stub`
option, a map of URL substring to a response:

```php
array(
	'api.example.com' => array(
		'status'  => 200,
		'headers' => array( 'content-type' => 'application/json' ),
		'body'    => '{"ok":true}',
	),
);
```

If a URL contains a key, the stub returns that response. When a stub map is
configured, it also records the last 100 intercepted requests in
`e2e_http_requests`. The `set_http_stub` factory writes the option. Use it for
external providers instead of calling the real service.

### 8.2 Mail log

`e2e-mail-log.php` hooks `pre_wp_mail`. It appends the message to the
`e2e_mail_log` option (last 200 messages) and returns `true`, which short-circuits
`wp_mail()` so no real email is sent. The `mail_log` factory reads or clears it.

## 9. PHPUnit

### 9.1 Command

```bash
composer test:unit
composer test:unit:filter DBTest
vendor/bin/phpunit --filter=CourseTest
vendor/bin/phpunit --testsuite=query-helper-test
```

`composer test:unit` runs `preflight` first, then `vendor/bin/phpunit` with
`phpunit.xml.dist`.

### 9.2 Bootstrap

`tests/bootstrap.php`:

1. Starts a session.
2. Defines `WP_RUNNING_TESTS` and puts it in the environment, so the wp-phpunit
   child installer also selects the PHPUnit database.
3. Points `WP_TESTS_CONFIG_FILE_PATH` at the real `wp-config.php`, which routes
   through `test-config.php`.
4. Locates `vendor/wp-phpunit/wp-phpunit`.
5. Defines the polyfills path.
6. Loads wp-phpunit's functions and registers a `muplugins_loaded` filter that
   requires `tutor.php`.
7. Loads wp-phpunit's bootstrap.
8. Initializes Tutor roles.

Because `WP_RUNNING_TESTS` is defined before `wp-config.php` is loaded, the
`wp-settings.php` guard skips the normal bootstrap. wp-phpunit owns the install
and test lifecycle. The `WP_RUNNING_TESTS=1` environment variable is what makes
the child installer process choose `WP_PHPUNIT_DB_NAME` instead of
`WP_TEST_DB_NAME`.

### 9.3 What PHPUnit does to the database

wp-phpunit drops the WordPress core tables and installs a fresh WordPress into
`WP_PHPUNIT_DB_NAME` for the test run. It does not use the E2E seed. PHPUnit
tests must create the data they need in `setUp()` or the test body. The PHPUnit
database is disposable; do not store fixtures there.

### 9.4 Test suites

`phpunit.xml.dist` defines the `all-test` suite plus focused suites such as
`utils-test`, `gradebook-test`, `query-helper-test`, `flash-message-test`,
`course-model-test`, `lesson-test`, and `coupon-model-test`.

Pro PHPUnit tests live in the Pro repo and have their own bootstrap. The free
`composer test:unit` command runs the free suite; run Pro's PHPUnit from the Pro
checkout when needed.

## 10. Safety model

The isolation is layered so one mistake does not become live data loss.

| Layer | What it prevents |
| --- | --- |
| `preflight.php` | Missing routing, colliding DB names, unsafe suffixes, disallowed site host |
| `test-config.php` | Live DB selection, missing/unsafe names, header opt-in from public hosts |
| `reset-test-db.php` | Booting the live DB before guards, resetting the live DB, routing mismatch |
| `install.php` | Creating/dropping the live DB, unsafe uninstall, overwriting the backup |
| `uninstall` | Removing unrelated mu-plugins, dropping non-test databases |
| `UPLOADS` | Writing test media into the live uploads tree |
| `WP_TEST_ENV=1` on WP-CLI/server | Factories, cron loopbacks, and WP-CLI writing to the live DB |
| Playwright header + fixtures | Browser requests selecting the test DB |
| `retries: 0` | Hiding flakiness instead of fixing it |

What the layers do not protect against:

- Running a destructive SQL command manually against the live database.
- Starting the web server without `WP_TEST_ENV=1` and relying only on the
  browser header. Browser requests are covered, but WP-Cron loopbacks and other
  server-side requests are not. Use `WP_TEST_ENV=1` for the server.
- Running the installer non-interactively without `--yes` and assuming it
  applied. It previews and exits 0.
- Resetting the E2E database while a test run is in progress.

## 11. Environment variables

All values can be set in the process environment or in `.env`. The process
environment wins. The installer adds missing keys to `.env`.

| Variable | Default | Purpose |
| --- | --- | --- |
| `SITE_URL` | `http://localhost:8888` | Canonical site URL used by the reset and test constants |
| `E2E_BASE_URL` | `SITE_URL`, then `http://localhost:8888` | Playwright base URL; wins over `SITE_URL` |
| `E2E_ENV_FILE` | plugin-root `.env` | Alternate env file for Playwright |
| `E2E_PASSWORD` | `password` | Password for all five seeded accounts |
| `E2E_PRO` | unset; project enabled when Pro exists | Set `0` to skip the Pro project |
| `E2E_EXTERNAL` | unset; project enabled when external specs exist | Set `0` to skip the external project |
| `WP_TEST_DB_NAME` | `<live>_test` | E2E database; must end in `_test` |
| `WP_TEST_DB_USER` | live user | Test DB user |
| `WP_TEST_DB_PASS` | live password | Test DB password |
| `WP_TEST_DB_HOST` | live host | Test DB host, `host:port`, or `host:/socket` |
| `WP_PHPUNIT_DB_NAME` | `<live>_phpunit` | PHPUnit database; must end in `_test` or contain `phpunit` |
| `WP_TEST_ACTIVE_PLUGINS` | auto-detect | Comma-separated plugin files or slugs to activate |
| `WP_TEST_HOSTS` | `localhost,127.0.0.1,::1,*.local` | Hosts allowed to opt in with header/cookie |
| `WP_TEST_ENV` | unset | `1` selects the E2E database for a process |
| `WP_RUNNING_TESTS` | unset | `1` selects the PHPUnit database |
| `WP_ROOT` | derived from plugin path | WordPress root for factories |
| `WP_CLI_BIN` | `wp` | WP-CLI binary for factories |

The fixed usernames are not configurable: `admin`, `instructor`, `student`,
`student2`, `manager`. Only the shared password changes.

## 12. Step-by-step: first-time local setup

Prerequisites: a working WordPress install with Tutor in
`wp-content/plugins/tutor`, PHP with `mysqli` or PDO MySQL, Composer, Node.js,
pnpm, MySQL, and optionally WP-CLI.

1. Install dependencies:

   ```bash
   cd wp-content/plugins/tutor
   pnpm install
   composer install
   ```

2. Build assets if the checkout does not already have them:

   ```bash
   pnpm build-dev
   ```

3. Run the one-step setup in an interactive terminal:

   ```bash
   pnpm test:setup
   ```

   Read the plan, answer `y`, and let the installer create both databases,
   route `wp-config.php`, link the mu-plugins, and seed the E2E database.
   Chromium installs after the installer succeeds.

   If you are in a non-TTY shell:

   ```bash
   composer test:install -- --yes
   pnpm exec playwright install chromium
   ```

   Do not use `pnpm test:setup -- --yes` for this: pnpm appends the flag to the
   Chromium command, not to the installer.

4. Start the test web server in a separate terminal:

   ```bash
   WP_TEST_ENV=1 PHP_CLI_SERVER_WORKERS=4 \
     wp server --host=127.0.0.1 --port=8888 --path="<wordpress-root>"
   ```

   `WP_TEST_ENV=1` makes every request in that server process use the test
   database, including WP-Cron loopbacks. `PHP_CLI_SERVER_WORKERS=4` prevents
   the single-threaded PHP server from deadlocking on loopback requests.

   If WP-CLI is unavailable, use PHP's built-in server with a router that
   serves existing files and falls through to `index.php`:

   ```php
   <?php
   $root = '/absolute/path/to/wordpress';
   $path = parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH );
   $file = realpath( $root . $path );

   if ( '/' !== $path && $file && 0 === strpos( $file, $root ) && file_exists( $file ) ) {
       return false;
   }

   require $root . '/index.php';
   ```

   Save it outside the web root, then:

   ```bash
   WP_TEST_ENV=1 PHP_CLI_SERVER_WORKERS=4 \
     php -S 127.0.0.1:8888 -t "<wordpress-root>" /path/to/router.php
   ```

5. Confirm the server is up:

   ```bash
   curl -I http://localhost:8888/
   ```

6. Run the smoke suite:

   ```bash
   pnpm test:e2e:smoke
   ```

7. Run PHPUnit:

   ```bash
   composer test:unit
   ```

8. Run everything:

   ```bash
   pnpm test
   ```

   This runs PHPUnit first, then the full E2E suite. With a Pro checkout
   present, the E2E half includes Pro. Use `E2E_PRO=0 pnpm test` for core only.

## 13. Step-by-step: daily workflow

1. Pull the branch and update dependencies if the lockfiles changed:

   ```bash
   pnpm install
   composer install
   ```

2. Make sure the test server is running with `WP_TEST_ENV=1`.

3. Run the fast check:

   ```bash
   pnpm test:e2e:smoke
   ```

4. Run the spec you are changing:

   ```bash
   pnpm test:e2e tests/e2e/admin/01-admin-courses.spec.ts
   ```

   `pnpm test:e2e` runs preflight first, then passes the path to Playwright.
   Do not add a `--` separator: pnpm forwards it to Playwright, where it ends
   option parsing.

5. Run by tag or project:

   ```bash
   pnpm test:e2e --grep "@feature"
   pnpm test:e2e --project=core
   E2E_PRO=0 pnpm test:e2e
   ```

6. Debug interactively:

   ```bash
   pnpm test:e2e:ui
   pnpm test:e2e:debug
   ```

7. Before pushing, run the full suite:

   ```bash
   pnpm test
   ```

8. If a test leaves the database in a bad state, reset it and re-run:

   ```bash
   pnpm test:e2e:reset
   ```

## 14. Step-by-step: reset

Reset when settings, users, or fixtures have drifted, or after a spec changes
global options without restoring them.

1. Stop any running test suite.
2. Run:

   ```bash
   pnpm test:e2e:reset
   ```

3. Confirm the output contains `[SUCCESS]` and lists the five accounts.
4. Re-run the smoke suite.

The reset drops every table in `local_test`, reinstalls WordPress, activates
the plugins, and re-seeds. It does not affect `local_phpunit` or the live
database. A core reset takes about 3–4 minutes; a full Pro reset takes about
20 minutes.

## 15. Step-by-step: CI

`.github/workflows/e2e.yml` runs on pull requests.

1. Starts a MySQL 8.0 service with database `wordpress` and root password
   `root`.
2. Checks out the plugin into `wordpress/wp-content/plugins/tutor`.
3. Installs PHP 8.3, Composer, WP-CLI, pnpm 9, and Node.js 20.
4. Downloads WordPress `7.1.3` and creates a `wp-config.php` pointing at
   `wordpress`.
5. Installs dependencies and builds assets with `pnpm build-dev`.
6. Installs Chromium with system dependencies.
7. Runs `php tests/support/install.php --yes` with:

   ```text
   SITE_URL=http://127.0.0.1:8888
   WP_TEST_ACTIVE_PLUGINS=tutor
   WP_TEST_DB_NAME=wordpress_test
   WP_PHPUNIT_DB_NAME=wordpress_phpunit
   ```

   then runs preflight.
8. Starts `WP_TEST_ENV=1 PHP_CLI_SERVER_WORKERS=4 wp server` on port 8888 and
   waits for an HTTP 200.
9. Runs `pnpm test:e2e`.
10. Uploads `playwright-report/` always, and `test-results/` plus the WordPress
    server log on failure.

CI currently runs E2E only; it does not run the PHPUnit suite. CI also does not
check out Pro, so the Pro project is absent and only the core project runs.
`WP_TEST_ACTIVE_PLUGINS=tutor` keeps WooCommerce, Pro, and H5P inactive even if
they happen to be present.

To reproduce CI locally, use the same database names and URL, run the installer
with `--yes`, start the server with `WP_TEST_ENV=1`, and run `pnpm test:e2e`.

## 16. Troubleshooting

| Symptom | Cause | Fix |
| --- | --- | --- |
| `wp-config.php is missing` | Installer run outside a WordPress tree | Run from `wp-content/plugins/tutor` in the WordPress install |
| `WP_TEST_DB_NAME is missing` | `.env` not created or key removed | `composer test:install -- --yes` |
| `Refusing unsafe test database name` | Name does not match `_test` / `phpunit` | Fix `WP_TEST_DB_NAME` or `WP_PHPUNIT_DB_NAME` in `.env` |
| `Refusing to use the live database` | Test name equals the live name | Choose a different name ending in `_test` |
| `wp-config.php does not route through ...` | Routing block missing or reverted | Re-run the installer, or add the block manually |
| `Connected to X but WP_TEST_DB_NAME is Y` | Routing mismatch | Check `.env`, process env, and the `wp-config.php` block |
| Preflight host error | `SITE_URL` / `E2E_BASE_URL` host not in `WP_TEST_HOSTS` | Add the host or use `localhost` / a `.local` host |
| Login fails for a role | Password mismatch, user missing, or reset mid-run | Re-run `pnpm test:e2e:reset`; check `E2E_PASSWORD` |
| Pro license screen blocks the suite | Site URL is `127.0.0.1`, not `localhost` or `.local` | Use `http://localhost:8888` or a `.local` host for Pro |
| `wp-cli factory ... failed` | `wp` not in PATH, wrong `WP_ROOT`, or plugin inactive | Set `WP_CLI_BIN` / `WP_ROOT`; check the plugin |
| Missing Chromium | `test:setup` not run after a fresh clone | `pnpm exec playwright install chromium` |
| Port 8888 already in use | Another server is running | Stop it or set `E2E_BASE_URL` and the server port to another value |
| Tests hit the live database | Server started without `WP_TEST_ENV=1`; only browser requests had the header | Restart the server with `WP_TEST_ENV=1` |
| PHPUnit undefined constant warnings | `wp-config.php` guard or test constants missing | Re-run the installer; check the guard block |
| PHPUnit cannot connect | MySQL down or wrong `WP_TEST_DB_*` | Start MySQL; check `.env` |
| Reset is very slow | Pro seeder is creating add-on data | Expected; core is faster, full Pro can take much longer |
| CI server log needed | Request failed before Playwright reported | Download the `wp-server-log` artifact |

Useful diagnostics:

```bash
php tests/support/preflight.php
php tests/support/page-urls.php
wp --path="<wordpress-root>" option get siteurl
WP_TEST_ENV=1 wp --path="<wordpress-root>" option get tutor_e2e_test_db
```

The last command should return `1` only when it is run with `WP_TEST_ENV=1`
against the test database.

## 17. Extending the environment

### Add a core spec

1. Put it under `tests/e2e/` in the appropriate folder (`smoke`, `feature`,
   `journey`, or `admin`).
2. Import `test` and `expect` from `../support/test` (adjust the relative
   path).
3. Use a role fixture such as `adminPage` or `studentPage`; do not create a
   bare context without the test header.
4. Tag it with `@smoke`, `@feature`, `@journey`, or `@external` as
   appropriate.
5. Use factories for data that must be unique.
6. Run it with `pnpm test:e2e <path>`.

### Add a Pro spec

Keep the spec, support code, and Pro-only factories in `tutor-pro`. Import the
shared fixtures from `@e2e/support/test` and the shared factory bridge from
`@e2e/support/factories`. The free Playwright config auto-includes the Pro
project when the sibling checkout exists.

### Add a factory

1. Add the PHP function and a `case` in `bin/e2e/factories.php`.
2. Add a typed wrapper in `tests/e2e/support/factories.ts`.
3. Return JSON-serializable data.
4. Keep the factory idempotent or make its unique inputs explicit.
5. Add a small assertion in the spec that uses it.

Pro-only factories go in `tutor-pro/bin/e2e/factories.php` and
`tutor-pro/tests/e2e/support/factories.ts`.

### Add a seeded fixture

Add it to `tests/support/seed.php` if every spec needs it, or to
`tests/support/seed-admin.php` if it is admin-dashboard baseline data. Keep it
idempotent: check for an existing entity before creating one. Run
`pnpm test:e2e:reset` to verify.

### Add an environment variable

1. Add it to `.env.example` with a comment.
2. Read it in the PHP or TypeScript layer that needs it, with a safe default.
3. If it affects database selection, add a guard in `test-config.php` and a
   check in `preflight.php`.
4. Document it in the table above.

### Add a mu-plugin

1. Put it in `bin/e2e/mu-plugins/`.
2. Bail out unless `TutorTestConfig` exists and `is_test_environment()` is
   true.
3. Re-run `composer test:install -- --yes` to link it.
4. Add a factory or spec helper to configure it.

## 18. Command reference

| Command | What it does |
| --- | --- |
| `pnpm test:setup` | Installer prompt, then Chromium |
| `pnpm test` | PHPUnit, then full E2E |
| `pnpm test:unit` | PHPUnit |
| `pnpm test:e2e` | Full E2E (core + Pro when present) |
| `pnpm test:e2e:smoke` | `@smoke` only |
| `pnpm test:e2e:journey` | `@journey` only |
| `pnpm test:e2e:ui` | Playwright UI |
| `pnpm test:e2e:debug` | Playwright debug mode |
| `pnpm test:e2e:reset` | Drop and reseed the E2E database |
| `composer test:install` | Installer preview, then confirm |
| `composer test:install -- --yes` | Installer without prompting |
| `composer test:install -- --dry-run` | Installer preview only |
| `composer test:uninstall` | Restore config, remove links, drop test DBs |
| `composer test:db:reset` | Same as `pnpm test:e2e:reset` |
| `composer test:unit:filter DBTest` | One PHPUnit test class |
| `php tests/support/preflight.php` | Static safety checks |
| `php tests/support/page-urls.php` | Print dynamic page URLs as JSON |

## 19. Known limitations

- **Non-TTY installer.** `composer test:install` previews and exits 0 when
  stdin is not a terminal. Use `--yes` in automation.
- **Pro seeder password.** `tutor-pro/bin/e2e/seed-pro.php` currently resets
  the instructor password to the literal `password`. If you use a non-default
  `E2E_PASSWORD`, the instructor login can fail after a Pro seed. Check that
  file in the Pro repo when changing the password.
- **Pro seeder add-on list.** The Pro seed enables a fixed list of add-ons.
  Adding a new Pro add-on to the suite may require updating that list.
- **CI runs E2E only.** PHPUnit is not part of the current CI workflow.
- **CI is core-only.** Pro is not checked out, so Pro specs do not run there.
- **Serial E2E.** One worker keeps the database deterministic but makes the
  full suite slower.
- **No per-test database reset.** Specs share `local_test`; unique factory data
  and cleanup are the spec author's responsibility.
- **Chromium is installed only by `test:setup`.** Running an E2E script on a
  fresh clone before setup fails with Playwright's missing-browser error.
- **`pnpm test` stops on the first failure.** If PHPUnit fails, E2E does not
  run.
- **`127.0.0.1` and the Pro license.** The Pro license bypass checks for
  `localhost` or `.local`; use one of those hosts for Pro runs.
- **`wp server` needs workers.** Without `PHP_CLI_SERVER_WORKERS`, loopback
  requests can deadlock the single-threaded server.
