# Tutor LMS Upstream Fix Notes (v4.0.4)

Handoff document for Tutor LMS maintainers.

| Item | Value |
|---|---|
| Plugin version tested | Tutor LMS **4.0.4** |
| WordPress | **7.0.2** Multisite |
| PHP | 7.4+ |
| Example subsite | Blog ID `7`, prefix `wp_7_`, URL `https://esgahead.com` |
| Main DB prefix | `wp_` (base) / site prefix `wp_N_` |

These changes are a **small upstream patch set**, not a site-specific workaround. Do **not** invent fake `wp_N_users` / `wp_N_usermeta` tables.

---

## Suggested PR order

1. **Issue 1** — `prepare_table_name()` (+ ValidationHelper / WithdrawModel) + unit tests  
2. **Issue 2** — dbDelta schema cleanup in `Tutor::create_database()` + Upgrader  
3. **Issue 3** — `BatchProcessor::can_run()` migration guards  
4. **Issue 4** — REST `the_content` skip + CartModel soft-fail / lazy create  

Issues 2 and 4 are related (FK-free schemas are required for Multisite table creation). Issues 1 and 3 are independent.

---

## Summary

| # | Status | Problem |
|---|---|---|
| 1 | Fixed | Blog prefix applied to global `users` / `usermeta` → `wp_7_usermeta`, `wp_7_wp_users` |
| 2 | Fixed | `dbDelta` schemas use unnamed `INDEX`, `--` comments, TEXT defaults, `FOREIGN KEY`s → activation SQL errors; Multisite FK **name** collisions |
| 3 | Fixed | Cron `quiz_attempt_migrator` fatals on blogs without `wp_N_tutor_quiz_attempts` |
| 4 | Fixed | Site Editor Patterns REST loads cart/checkout → queries missing `wp_N_tutor_carts` |

---

## Issue 1 — Multisite-safe table name resolution

### Root cause

`Tutor\Helpers\QueryHelper::prepare_table_name()` treated every table as blog-scoped:

```php
// BEFORE
$table_prefix = self::get_table_prefix(); // $wpdb->prefix, e.g. wp_7_
if ( strpos( $table_name, $table_prefix ) !== 0 ) {
    $table_name = $table_prefix . $table_name;
}
```

| Input | Wrong (blog 7) | Correct |
|---|---|---|
| `usermeta` | `wp_7_usermeta` | `wp_usermeta` |
| `$wpdb->users` (`wp_users`) | `wp_7_wp_users` | `wp_users` |
| `users u` / `users AS u` | `wp_7_users u` | `wp_users u` |
| `tutor_orders` / `tutor_orders o` | `wp_7_tutor_orders` | OK |
| Already `wp_7_tutor_orders` | OK | OK |

Affected call paths (fix **centrally** — do not patch each site):

- `classes/Utils.php` → `QueryHelper::get_count( 'usermeta', … )`
- `models/OrderModel.php` → join `"{$wpdb->users} u"`
- `models/WithdrawModel.php` / `UserModel.php` → users joins
- `restapi/RestAuth.php`, tools token UI → `$wpdb->usermeta`

### Fix — `helpers/QueryHelper.php`

Rewrite `prepare_table_name( string $table_name )`:

1. `trim`; split optional alias with `/^(\S+)(\s+(?:AS\s+)?\S+)$/i`; keep alias text exact.
2. Validate base as `[A-Za-z0-9_]+`; if invalid, return original string.
3. If base starts with `$wpdb->prefix` → keep.
4. If base ∈ `$wpdb->global_tables` (and Multisite `$wpdb->ms_global_tables`) → `$wpdb->{$name}` or `$wpdb->base_prefix . $name`.
5. If base equals a known global full name → keep.
6. If base starts with `$wpdb->base_prefix` → keep (no double-prefix).
7. Else prepend `$wpdb->prefix`.
8. Reattach alias.

Must use `$wpdb` properties (custom prefixes). Single-site must stay: `users` → `{prefix}users`, `tutor_orders` → `{prefix}tutor_orders`.

### Related call sites

| File | Change |
|---|---|
| `helpers/ValidationHelper.php` | `has_record()`: drop local `$wpdb->prefix` prepend; pass table to `QueryHelper::get_row()` only. |
| `models/WithdrawModel.php` | `FROM {$wpdb->prefix}users` → `FROM {$wpdb->users}`. |
| `classes/Tutor.php` | Prefer `$wpdb->users` over `$wpdb->prefix . 'users'` where users are referenced. *(FK lines later removed in Issue 2.)* |

### Unit tests (recommended)

| Path | Purpose |
|---|---|
| `tests/unit/QueryHelperPrepareTableNameTest.php` | Multisite + single-site + aliases + custom prefix |
| `tests/bootstrap.php` | Stub `$wpdb` + `is_multisite()` |
| `phpunit.xml.dist` | Suite config |
| `composer.json` | Optional `phpunit/phpunit` require-dev |

Assert never: `wp_7_users`, `wp_7_usermeta`, `wp_7_wp_users`, `wp_7_wp_usermeta`.

---

## Issue 2 — dbDelta-compatible CREATE TABLE schemas

### Root cause

`dbDelta()` mishandles patterns in `Tutor::create_database()`:

| Symptom | Cause |
|---|---|
| `ADD KEY `` (`course_id`)` | Unnamed `INDEX (course_id)` |
| `CHANGE COLUMN … -- comment` syntax error | Inline `--` comments on columns |
| TEXT/BLOB can't have default | `answer_explanation longtext DEFAULT ''` |
| `ADD COLUMN CONSTRAINT fk_…` | `CONSTRAINT … FOREIGN KEY` in CREATE TABLE |
| Multisite: `Duplicate foreign key constraint name 'fk_tutor_cart_user_id'` | InnoDB FK **names are unique per database**; main site `wp_tutor_carts` blocks `wp_7_tutor_carts` with the same constraint name → **subsite tables fail to create** (feeds Issue 4) |

### Fix — `classes/Tutor.php` (`create_database()`)

1. `INDEX (col)` → `KEY col (col)` (quiz attempts, earnings, …).
2. Remove all inline `-- …` comments from CREATE TABLE lines (orders, order items, coupons, …). Keep `COMMENT '…'` if needed.
3. `answer_explanation longtext DEFAULT ''` → `answer_explanation longtext`.
4. `method_data text DEFAULT NULL` → `method_data text`.
5. **Remove all** `CONSTRAINT … FOREIGN KEY …` from dbDelta schemas. Keep `KEY` indexes. Enforce relations in PHP if needed.

Remove FK constraints from:

| Table | Constraint name(s) |
|---|---|
| `tutor_ordermeta` | `fk_tutor_ordermeta_order_id` |
| `tutor_order_items` | `fk_tutor_order_item_order_id` |
| `tutor_coupon_applications` | `fk_tutor_coupon_application_coupon_code` |
| `tutor_coupon_usages` | `fk_tutor_coupon_usage_coupon_code`, `fk_tutor_coupon_usage_user_id` |
| `tutor_carts` | `fk_tutor_cart_user_id` |
| `tutor_cart_items` | `fk_tutor_cart_item_cart_id`, `fk_tutor_cart_item_course_id` |

### Fix — `classes/Upgrader.php` (`upgrade_to_3_8_0()`)

Remove `CONSTRAINT fk_tutor_itemmeta FOREIGN KEY …` from `tutor_order_itemmeta` CREATE TABLE.

### Recovery after deploy

```bash
wp --url=<subsite-url> eval 'TUTOR\Tutor::create_database();' --allow-root
```

---

## Issue 3 — Migration cron fatal without Tutor tables

### Root cause

Network-activated Tutor loads on every blog. `migrations/Migration.php` schedules unfinished migrators. Cron:

```text
wp-cron.php
  → quiz_attempt_migrator
  → BatchProcessor::process_batch
  → QuizAttemptMigrator::get_total_items
  → QueryHelper::get_count( 'tutor_quiz_attempts' )
```

On blogs without tables (e.g. blog 6):

```text
Table 'wordpress.wp_6_tutor_quiz_attempts' doesn't exist
Uncaught Exception in QueryHelper.php (get_count)
```

### Fix

**`migrations/BatchProcessor.php`**

```php
protected function can_run(): bool {
    return true;
}

public function schedule() {
    if ( ! $this->can_run() ) {
        return;
    }
    // existing schedule logic…
}

public function process_batch() {
    if ( ! $this->can_run() ) {
        return; // do NOT mark complete — tables may be created later
    }
    // existing process logic…
}
```

**`migrations/QuizAttemptMigrator.php`**

```php
protected function can_run(): bool {
    return QueryHelper::table_exists( 'tutor_quiz_attempts' );
}
```

Also guard `get_total_items()` / `get_items()` with `can_run()` (return `0` / `array()`).

**`migrations/ProcessByWcMigrator.php`**

```php
protected function can_run(): bool {
    return QueryHelper::table_exists( 'tutor_earnings' );
}
```

Guard `get_total_items()` / `get_items()` similarly.

### Larger follow-up (optional)

On network activation, run `create_database()` per blog via `switch_to_blog`, so sites that need Tutor are not left without schema.

---

## Issue 4 — Missing cart tables + Site Editor / Patterns REST

### Root cause

1. Tables never created on the subsite because of Issue 2 FK name collision (`fk_tutor_cart_user_id`).
2. Site Editor Patterns REST applies `the_content` → `Template::convert_static_page_to_template` → cart/checkout → `CartModel::get_cart_items` → missing `wp_N_tutor_carts`.

`wp_N_tutor_carts` is the **correct** Multisite table name; this is not a prefix bug.

### Fix — `classes/Template.php`

In `convert_static_page_to_template()`, after the existing `wp_head` guard:

```php
if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
    return $content;
}
```

### Fix — `models/CartModel.php`

In `get_cart_items()`, before querying:

```php
static $attempted_create_database = false;
if ( ! QueryHelper::table_exists( 'tutor_carts' ) ) {
    if ( ! $attempted_create_database ) {
        $attempted_create_database = true;
        \TUTOR\Tutor::create_database();
    }
    if ( ! QueryHelper::table_exists( 'tutor_carts' ) ) {
        return $is_details ? $cart_data : $cart_data['courses']['results'];
    }
}
```

Requires Issue 2 (FK-free schemas) so lazy `create_database()` can succeed on Multisite.

---

## Files checklist

| File | Issues | Change |
|---|---|---|
| `helpers/QueryHelper.php` | 1 | Multisite-safe `prepare_table_name()` |
| `helpers/ValidationHelper.php` | 1 | `has_record()` via QueryHelper only |
| `models/WithdrawModel.php` | 1 | `{$wpdb->users}` in raw SQL |
| `classes/Tutor.php` | 1–2 | dbDelta-safe schemas; no FKs / unnamed INDEX / `--` / TEXT defaults |
| `classes/Upgrader.php` | 2 | Remove FK from `order_itemmeta` |
| `migrations/BatchProcessor.php` | 3 | `can_run()` gate |
| `migrations/QuizAttemptMigrator.php` | 3 | Require `tutor_quiz_attempts` |
| `migrations/ProcessByWcMigrator.php` | 3 | Require `tutor_earnings` |
| `classes/Template.php` | 4 | Skip page→template conversion on `REST_REQUEST` |
| `models/CartModel.php` | 4 | Lazy create + empty cart if table missing |
| `tests/unit/QueryHelperPrepareTableNameTest.php` | 1 | Unit tests |
| `tests/bootstrap.php` | 1 | Test bootstrap |
| `phpunit.xml.dist` | 1 | PHPUnit config |
| `composer.json` | 1 | Optional PHPUnit require-dev |
| `tests/MULTISITE_MIGRATION_NOTE.md` | 3 | Short note / follow-up |

---

## Verification

### Issue 1

On a Multisite subsite, never query:

- `wp_N_users`, `wp_N_usermeta`, `wp_N_wp_users`, `wp_N_wp_usermeta`

Expected shapes:

```sql
FROM wp_N_tutor_orders o
INNER JOIN wp_users u ON o.user_id = u.ID;

FROM wp_usermeta …;
```

Run unit tests: `phpunit -c phpunit.xml.dist`

### Issue 2

```bash
wp eval 'TUTOR\Tutor::create_database();' --url=<subsite-url> --allow-root
```

No empty KEY names, no `--` in CHANGE COLUMN, no TEXT default errors, no `ADD COLUMN CONSTRAINT` / duplicate FK names.  
`SHOW TABLES LIKE 'wp_N_tutor_carts';` succeeds.

### Issue 3

```bash
wp cron event run quiz_attempt_migrator --url=<blog-without-tutor-tables> --allow-root
```

No fatal / no `QueryHelper` exception for missing `tutor_quiz_attempts`.  
On a blog **with** tables, migrator still schedules and runs.

### Issue 4

1. Create tables (Issue 2 recovery) on the affected blog.
2. Reload Site Editor → Patterns — no `tutor_carts` missing-table errors.
3. Frontend cart page still works when tables exist.

### Single-site regression

`users` → `{prefix}users`, Tutor tables still use `$wpdb->prefix`.

---

## Compatibility / non-goals

- Preserve single-site table-naming behavior.
- Support custom DB prefixes via `$wpdb` (no hardcoded `wp_`).
- Do **not** create fake global user tables per blog.
- Do **not** keep MySQL FKs in dbDelta schemas on Multisite (constraint name collisions).
- Optional follow-up: network-activation per-blog `create_database()` via `switch_to_blog`.
