# DB Helper (`Tutor\Core\DB`)

- **Class:** `Tutor\Core\DB`
- **File:** `wp-content/plugins/tutor/core/DB.php`
- **Namespace:** `Tutor\Core`
- **Since:** `4.2.0`
- **Package:** `Tutor\Core`

---

## Overview

The `DB` class provides a fluent, chainable query builder interface for WordPress database operations. Inspired by Laravel's Query Builder, it simplifies complex query construction while guaranteeing strict SQL injection prevention through automatic parameter binding via `$wpdb->prepare()` and strict regex validation on SQL identifiers.

### Key Features

- **Fluent API:** Clean, readable, chainable query building.
- **SQL Injection Prevention:** All values are bound through `$wpdb->prepare()`. Table names and column identifiers are validated against strict regex patterns.
- **Automatic Table Prefixing:** Auto-detects and prepends `$wpdb->prefix` when omitted.
- **Self-Contained & Independent:** Zero external dependencies beyond WordPress `$wpdb`.
- **Full Query Lifecycle:** Supports `SELECT`, `INSERT`, `UPDATE`, `DELETE`, aggregates, joins, and built-in pagination.
- **Transaction Management:** Manual and automatic scoped transactions with full support for nested transactions via savepoints.
- **Row-Level Locking:** Pessimistic locking support (`FOR UPDATE`) to prevent race conditions during concurrent modifications.
- **Schema Validation:** Built-in table and column existence checks (`has_table`, `has_column`, `has_columns`) with strict identifier validation.

---

## Table of Contents

1. [Quick Start](#quick-start)
2. [API Synopsis](#api-synopsis)
3. [Query Initialization](#query-initialization)
4. [Select Clauses](#select-clauses)
5. [Joins](#joins)
6. [Where Clauses](#where-clauses)
7. [Ordering & Grouping](#ordering--grouping)
8. [Conditional Queries (`when`)](#conditional-queries-when)
9. [Retrieving Data](#retrieving-data)
10. [Pagination](#pagination)
11. [Data Mutations (CRUD)](#data-mutations-crud)
12. [Row Locking](#row-locking)
13. [Transactions](#transactions)
14. [Schema Validation](#schema-validation)
15. [Debugging & Raw Queries](#debugging--raw-queries)
16. [Security Architecture](#security-architecture)

---

## Quick Start

```php
use Tutor\Core\DB;

// Fetch active courses with instructor names
$courses = DB::table('posts')
    ->as('p')
    ->select('p.ID', 'p.post_title', 'u.display_name AS instructor')
    ->join("{$wpdb->users} AS u", 'p.post_author', '=', 'u.ID')
    ->where('p.post_type', 'courses')
    ->where('p.post_status', 'publish')
    ->order_by('p.ID', 'DESC')
    ->limit(10)
    ->get();
```

---

## API Synopsis

```php
class DB {
    // Factory
    public static function table( string $table ): self;

    // Selection & Projection
    public function select( string ...$columns ): self;
    public function select_raw( string $expression, array $bindings = [] ): self;
    public function distinct(): self;
    public function as( string $alias ): self;

    // Joins (supports 4-argument syntax or closure for multiple ON conditions)
    public function join( string $table, $first, string $operator = '', string $second = '' ): self;
    public function left_join( string $table, $first, string $operator = '', string $second = '' ): self;
    public function right_join( string $table, $first, string $operator = '', string $second = '' ): self;

    // Where Clauses
    public function where( string $column, $operator_or_val, $val = null ): self;
    public function or_where( string $column, $operator_or_val, $val = null ): self;
    public function where_in( string $column, array $values ): self;
    public function or_where_in( string $column, array $values ): self;
    public function where_not_in( string $column, array $values ): self;
    public function or_where_not_in( string $column, array $values ): self;
    public function where_null( string $column ): self;
    public function or_where_null( string $column ): self;
    public function where_not_null( string $column ): self;
    public function or_where_not_null( string $column ): self;
    public function where_between( string $column, $min, $max ): self;
    public function or_where_between( string $column, $min, $max ): self;
    public function where_not_between( string $column, $min, $max ): self;
    public function or_where_not_between( string $column, $min, $max ): self;
    public function where_like( string $column, string $value ): self;
    public function or_where_like( string $column, string $value ): self;
    public function where_not_like( string $column, string $value ): self;
    public function or_where_not_like( string $column, string $value ): self;
    public function where_raw( string $expression, array $bindings = [] ): self;
    public function or_where_raw( string $expression, array $bindings = [] ): self;

    // Grouping & Ordering
    public function group_by( string ...$columns ): self;
    public function having_raw( string $expression, array $bindings = [] ): self;
    public function order_by( string $column, string $direction = 'ASC' ): self;
    public function order_by_raw( string $expression, array $bindings = [] ): self;
    public function latest( string $column = 'id' ): self;
    public function oldest( string $column = 'id' ): self;

    // Pagination & Limits
    public function limit( int $limit ): self;
    public function offset( int $offset ): self;
    public function paginate( int $per_page, int $current_page = 1, string $output = 'OBJECT' ): array;

    // Conditionals
    public function when( $condition, callable $callback, ?callable $default = null ): self;

    // Retrieval
    public function get( string $output = 'OBJECT' ): array;
    public function first( string $output = 'OBJECT' );
    public function find( $id, string $primary_key = 'id', string $output = 'OBJECT' );
    public function value( string $column );
    public function pluck( string $column, ?string $key = null ): array;
    public function count( string $column = '*' ): int;
    public function exists(): bool;

    // Mutations
    public function insert( array $data, array $format = [] ): int;
    public function insert_multiple( array $rows ): bool;
    public function update( array $data ): bool;
    public function delete(): int;

    // Row Locking
    public function lock_for_update(): self;
    public function unlock(): self;
    public function is_locked(): bool;

    // Transaction Management
    public static function begin_transaction(): bool;
    public static function commit(): bool;
    public static function rollback(): bool;
    public static function transaction( callable $callback );
    public static function in_transaction(): bool;
    public static function transaction_level(): int;
    public static function reset_transaction_state(): void;

    // Schema Validation
    public static function has_table( $table ): bool;
    public static function has_column( string $table, string $column ): bool;
    public static function has_columns( string $table, array $columns ): bool;

    // Debug & Inspection
    public function to_sql(): string;

    // Utility & Validation Helpers
    public static function set_db( ?\wpdb $db = null ): void;
    public static function prepare_value( $value ): string;
    public static function prepare_in_clause( array $values ): string;
    public static function prepare_table_name( string $table ): string;
    public static function get_table_prefix(): string;
    public static function get_last_query(): string;
    public static function parse_column_alias( string $expression ): ?string;
    public static function is_valid_column( string $column ): bool;
    public static function is_valid_identifier( string $identifier ): bool;
    public static function is_valid_operator( string $operator ): bool;
}
```

---

## Query Initialization

### `table( string $table ): self`
Initializes a new query builder instance. The table name is automatically prefixed with `$wpdb->prefix` if not already present.

```php
// Both yield: wp_tutor_orders (assuming prefix is 'wp_')
DB::table('tutor_orders');
DB::table('wp_tutor_orders');
```

### `as( string $alias ): self`
Assigns an alias to the main query table.

```php
DB::table('tutor_orders')->as('o');
// FROM wp_tutor_orders AS o
```

---

## Select Clauses

### `select( string ...$columns ): self`
Specifies which columns to retrieve. Multiple calls append columns.

```php
DB::table('users')->select('ID', 'user_email');
```

#### Column Aliases
Column aliases are fully supported using uppercase `AS`, lowercase `as`, or implicit space separation:

```php
DB::table('orders')
    ->select('id AS order_id', 'parent_id as parent_order_id', 'name person_name');

// With table prefixes:
DB::table('users')
    ->as('u')
    ->select('u.id AS user_id', 'u.user_login as username');
```

> [!NOTE]
> All column names and alias identifiers are individually validated against strict regex patterns (`is_valid_column` and `is_valid_identifier`) to prevent SQL injection.

### `select_raw( string $expression, array $bindings = [] ): self`
Injects raw SQL expressions into the `SELECT` clause (e.g., aggregate functions).

```php
DB::table('tutor_orders')
    ->select_raw('COUNT(*) as total_orders, AVG(total_price) as avg_price')
    ->get();
```

### `distinct(): self`
Forces the query to return distinct results.

```php
DB::table('posts')->distinct()->select('post_type')->get();
```

---

## Joins

Joins support two distinct styles:
1. **Standard 4-argument syntax:** Table, first column, operator, second column.
2. **Closure syntax:** For advanced joins with multiple `ON` conditions chained via `on()` or `or_on()`.

### Supported Join Methods:
- `join( string $table, $first, string $operator = '', string $second = '' )` — `INNER JOIN`
- `left_join( string $table, $first, string $operator = '', string $second = '' )` — `LEFT JOIN`
- `right_join( string $table, $first, string $operator = '', string $second = '' )` — `RIGHT JOIN`

### 1. Standard 4-Argument Syntax
```php
DB::table('tutor_earnings')
    ->as('e')
    ->select('e.*', 'u.user_login')
    ->left_join("{$wpdb->users} AS u", 'e.user_id', '=', 'u.ID')
    ->get();
```

### 2. Closure Syntax for Multiple ON Conditions
Pass a closure accepting the `$join` collector as the second argument. The closure supports chaining multiple `on()` and `or_on()` conditions:

```php
$users = DB::table('users')
    ->join('contacts', function ($join) {
        $join->on('users.id', '=', 'contacts.user_id')
             ->on('users.status', '=', 'contacts.status');
    })
    ->select('users.*', 'contacts.phone')
    ->get();
```

#### Closure Methods:
- `$join->on( $first, $operator, $second )` — Combines condition with `AND` (default).
- `$join->on( $first, $second )` — 2-argument shortcut defaulting the operator to `=`.
- `$join->or_on( $first, $operator, $second )` — Combines condition with `OR`.
- `$join->or_on( $first, $second )` — 2-argument shortcut with `OR`.

```php
// Chaining with OR conditions and table aliases:
DB::table('users')
    ->left_join('contacts AS c', function ($join) {
        $join->on('users.id', '=', 'c.user_id')
             ->or_on('users.email', '=', 'c.email');
    })
    ->get();
```

---

## Where Clauses

### 1. Basic Where
Supports both 2-argument (assumes `=`) and 3-argument syntax.

```php
DB::table('users')->where('user_status', 0);           // user_status = 0
DB::table('tutor_orders')->where('total_price', '>=', 100); // total_price >= 100
DB::table('users')->or_where('user_login', 'admin');   // OR user_login = 'admin'
```

### 2. IN / NOT IN
```php
DB::table('posts')->where_in('ID', [10, 20, 30]);
DB::table('posts')->where_not_in('post_status', ['trash', 'auto-draft']);
DB::table('posts')->or_where_in('ID', [40, 50]);
DB::table('posts')->or_where_not_in('post_type', ['attachment']);
```

### 3. NULL / NOT NULL
```php
DB::table('posts')->where_null('post_parent');
DB::table('posts')->where_not_null('post_password');
DB::table('posts')->or_where_null('post_excerpt');
DB::table('posts')->or_where_not_null('to_ping');
```

### 4. BETWEEN
```php
DB::table('tutor_orders')->where_between('created_at', '2024-01-01', '2024-12-31');
DB::table('tutor_orders')->where_not_between('total_price', 10, 50);
```

### 5. LIKE
Automatically wraps the search term with `%` wildcards and escapes internal characters using `$wpdb->esc_like()` to prevent LIKE-injection vulnerabilities.

```php
DB::table('posts')->where_like('post_title', 'WordPress');
// Generates: post_title LIKE '%WordPress%'

DB::table('posts')->where_not_like('post_content', 'deprecated');
```

### 6. Raw WHERE
For advanced or database-native functions. Use `$wpdb->prepare()` placeholders (`%s`, `%d`, `%f`) in expressions.

```php
DB::table('tutor_orders')
    ->where_raw('DATE(created_at) = CURDATE()')
    ->or_where_raw('TIMESTAMPDIFF(DAY, created_at, NOW()) <= %d', [7]);
```

---

## Ordering & Grouping

```php
// Standard Ordering
DB::table('posts')->order_by('post_date', 'DESC');

// Shortcuts
DB::table('posts')->latest();             // ORDER BY id DESC
DB::table('posts')->oldest('post_date');  // ORDER BY post_date ASC

// Raw Order By (e.g. MySQL FIELD)
DB::table('posts')->order_by_raw('FIELD(post_status, %s, %s, %s)', ['publish', 'draft', 'pending']);

// Group By & Having
DB::table('tutor_quiz_attempts')
    ->select('quiz_id')
    ->select_raw('COUNT(*) AS total_attempts')
    ->group_by('quiz_id')
    ->having_raw('COUNT(*) >= %d', [10])
    ->get();
```

---

## Conditional Queries (`when`)

Applies constraints conditionally without breaking fluent chaining:

```php
$search    = sanitize_text_field( $_GET['search'] ?? '' );
$order_by  = sanitize_key( $_GET['order_by'] ?? '' );

$results = DB::table('posts')
    ->where('post_type', 'courses')
    ->when($search, function ($query, $term) {
        $query->where_like('post_title', $term);
    })
    ->when($order_by, function ($query, $column) {
        $query->order_by($column, 'ASC');
    }, function ($query) {
        $query->latest(); // Default fallback when false
    })
    ->get();
```

---

## Retrieving Data

| Method | Return Type | Description |
| :--- | :--- | :--- |
| `get( $output )` | `array` | Returns all matching records. Supports `'OBJECT'`, `'ARRAY_A'`, `'ARRAY_N'`. |
| `first( $output )` | `object\|array\|null` | Returns the first matching record (automatically applies `LIMIT 1`). |
| `find( $id, $pk, $output )` | `object\|array\|null` | Finds a record by primary key (default `$pk = 'id'`). |
| `value( $column )` | `mixed\|null` | Retrieves a single field value from the first matching row. |
| `pluck( $column, $key )` | `array` | Extracts a flat list of column values, optionally indexed by `$key`. |
| `count( $column )` | `int` | Returns total row count matching the query. |
| `exists()` | `bool` | Returns `true` if at least one matching record exists. |

```php
// Fetch as associative array
$user = DB::table('users')->where('ID', 1)->first('ARRAY_A');

// Key-value pair array: [ 10 => 'Course Alpha', 12 => 'Course Beta' ]
$course_map = DB::table('posts')
    ->where('post_type', 'courses')
    ->pluck('post_title', 'ID');

// Check existence
if ( DB::table('tutor_enrolments')->where('user_id', 5)->where('course_id', 12)->exists() ) {
    // User already enrolled
}
```

---

## Pagination

The `paginate()` method handles record fetching, `SQL_CALC_FOUND_ROWS`, and page calculation in a single call:

```php
$pagination = DB::table('tutor_orders')
    ->where('order_status', 'completed')
    ->order_by('id', 'DESC')
    ->paginate($per_page = 15, $current_page = 1);
```

### Response Structure:
```php
[
    'total_count'  => 142,   // (int) Total records matching criteria
    'per_page'     => 15,    // (int) Records per page
    'current_page' => 1,     // (int) Active page number
    'total_pages'  => 10,    // (int) Total calculated pages
    'results'      => [ ... ]// (array) Array of result objects
]
```

---

## Data Mutations (CRUD)

### 1. Insert
Inserts a single row and returns the new auto-increment ID:

```php
$insert_id = DB::table('options')->insert([
    'option_name'  => 'tutor_custom_setting',
    'option_value' => 'active',
    'autoload'     => 'no',
]);
```

### 2. Batch Insert
Bulk inserts multiple rows within a single SQL statement:

```php
DB::table('tutor_quiz_attempts')->insert_multiple([
    ['user_id' => 10, 'quiz_id' => 45, 'attempt_status' => 'pass'],
    ['user_id' => 11, 'quiz_id' => 45, 'attempt_status' => 'fail'],
    ['user_id' => 12, 'quiz_id' => 45, 'attempt_status' => 'pass'],
]);
```

### 3. Update
Updates rows matching the accumulated `where()` conditions. Returns `true` on success:

```php
DB::table('users')
    ->where('ID', 10)
    ->update([
        'display_name' => 'Jane Doe',
        'user_email'   => 'jane@example.com',
    ]);
```

### 4. Delete
Deletes rows matching the accumulated `where()` conditions. Returns the number of affected rows:

```php
$deleted_rows = DB::table('options')
    ->where('option_name', 'tutor_custom_setting')
    ->delete();
```

> [!CAUTION]
> Calling `delete()` without any `where()` conditions will delete **all rows** in the target table.

---

## Row Locking

The query builder supports pessimistic row locking using the MySQL `FOR UPDATE` clause. Row locking prevents concurrent transactions from modifying or locking the selected rows until the current transaction completes (via commit or rollback).

### Applying Locks

Call `lock_for_update()` before executing a terminal retrieval method such as `get()` or `first()`:

```php
use Tutor\Core\DB;

// Lock order record during checkout processing
$order = DB::table('tutor_orders')
    ->where('id', 101)
    ->lock_for_update()
    ->first();
```

The compiled SQL appends `FOR UPDATE`:
```sql
SELECT * FROM wp_tutor_orders WHERE id = 101 LIMIT 1 FOR UPDATE
```

### Removing & Inspecting Locks

- `unlock()`: Removes any previously applied row lock on the query builder instance.
- `is_locked()`: Checks whether row locking is currently applied.

```php
$query = DB::table('tutor_earnings')->where('user_id', 5);

$query->lock_for_update();
$query->is_locked(); // true

$query->unlock();
$query->is_locked(); // false
```

> [!NOTE]
> - Row locking is only active within a database transaction. Executing `lock_for_update()` outside of a transaction has no locking effect once the query finishes.
> - `count()` queries intentionally omit the `FOR UPDATE` clause to adhere to SQL syntax standards while preserving the builder's lock state for subsequent queries.
> - For storage engines that do not support row-level locking (such as MyISAM), MySQL silently falls back to standard table or shared locks.

---

## Transactions

The `DB` class provides comprehensive database transaction management with full support for nested transactions using MySQL savepoints.

### 1. Automatic Scoped Transactions (`DB::transaction`)

The recommended way to handle transactions is `DB::transaction(callable $callback)`. It wraps operations in `begin_transaction()` and `commit()`. If an exception or fatal error occurs, it automatically rolls back and re-throws the error:

```php
use Tutor\Core\DB;

$order_id = DB::transaction(function() {
    $order_id = DB::table('tutor_orders')->insert([
        'user_id'      => 42,
        'order_status' => 'completed',
        'total_amount' => 99.00,
    ]);

    DB::table('tutor_enrolments')->insert([
        'user_id'   => 42,
        'course_id' => 15,
        'status'    => 'completed',
    ]);

    return $order_id;
});
```

Key behaviors of `DB::transaction()`:
- **Return Value:** Returns whatever the callback returns.
- **Automatic Rollback:** Catches `\Throwable` (both `\Exception` and `\Error`), executes `DB::rollback()`, and re-throws the original error.
- **Rollback Fault Tolerance:** Secondary exceptions during rollback are suppressed so the primary, root cause exception is never masked.

### 2. Manual Transactions

For fine-grained lifecycle management, use `begin_transaction()`, `commit()`, and `rollback()`:

```php
use Tutor\Core\DB;
use Tutor\Exceptions\DatabaseException;

try {
    DB::begin_transaction();

    $updated = DB::table('users')
        ->where('ID', 10)
        ->update(['display_name' => 'Alice']);

    if ( ! $updated ) {
        throw new \Exception('Failed to update user profile.');
    }

    DB::commit();
} catch ( \Throwable $e ) {
    DB::rollback();
    // Handle error or log
    error_log( $e->getMessage() );
}
```

#### Manual Transaction API:

- **`DB::begin_transaction(): bool`**
  - Starts a new transaction (`START TRANSACTION`) when outside a transaction.
  - When nested within an existing transaction, automatically creates a savepoint (`SAVEPOINT trans_N`).
  - Throws `DatabaseException` if the query fails.

- **`DB::commit(): bool`**
  - Commits the outermost transaction (`COMMIT`) when at level 1.
  - Releases the corresponding savepoint (`RELEASE SAVEPOINT trans_N`) when nested.
  - The internal transaction level is only decremented **after** a successful query, ensuring a failed commit can still be rolled back.
  - Returns `false` if no transaction is active.
  - Throws `DatabaseException` if the query fails.

- **`DB::rollback(): bool`**
  - Rolls back the entire transaction (`ROLLBACK`) when at level 1.
  - Rolls back to the last savepoint (`ROLLBACK TO SAVEPOINT trans_N`) when nested.
  - In a top-level transaction, if `ROLLBACK` fails (e.g., lost connection), the level is still decremented so the PHP state doesn't stay permanently locked.
  - Returns `false` if no transaction is active.
  - Throws `DatabaseException` if the query fails.

### 3. Inspection & Testing Helpers

- **`DB::in_transaction(): bool`**
  Returns `true` if a transaction is currently active (level > 0).

- **`DB::transaction_level(): int`**
  Returns the current nesting level (`0` when outside, `1` for top-level, `2+` for nested savepoints).

- **`DB::reset_transaction_state(): void`**
  Resets the PHP-side nesting counter to `0`. Useful for teardown in automated test suites (e.g. PHPUnit) to ensure isolated test runs.

---

## Schema Validation

The `DB` class includes static schema introspection methods to check for table and column existence safely without risking SQL injection or identifier leaks.

### 1. Table Existence (`has_table`)

Checks whether a table exists in the database. Supports both raw WordPress table names and unprefixed convenience names (auto-prefixing with `$wpdb->prefix`):

```php
use Tutor\Core\DB;

// Checks with automatic prefixing (checks for 'wp_tutor_courses')
if ( DB::has_table('tutor_courses') ) {
    // Table exists
}

// Checks with explicit prefix
if ( DB::has_table('wp_users') ) {
    // Table exists
}

// Returns false for nonexistent or invalid identifiers
DB::has_table('invalid_table_name'); // false
DB::has_table('table; DROP TABLE users;'); // false (rejected by identifier validation)
```

### 2. Single Column Existence (`has_column`)

Checks whether a specific column exists in a database table:

```php
use Tutor\Core\DB;

if ( DB::has_column('tutor_earnings', 'instructor_rate') ) {
    // Column exists in wp_tutor_earnings
}
```

### 3. Multiple Column Existence (`has_columns`)

Checks whether **all** specified columns exist in a database table. Returns `true` only if every column is found:

```php
use Tutor\Core\DB;

$columns_required = ['course_id', 'user_id', 'completed_at'];

if ( DB::has_columns('tutor_course_progress', $columns_required) ) {
    // All columns exist
}
```

> [!NOTE]
> - All column comparisons in `has_column` and `has_columns` are case-insensitive.
> - Passing an empty array to `has_columns()` returns `false`.
> - If any provided column name is not a valid SQL identifier, `has_columns()` immediately returns `false` without executing an unsafe query.

---

## Debugging & Raw Queries

### Inspect Compiled SQL (`to_sql`)
Returns the compiled SQL query string with all placeholders resolved, without executing it:

```php
$sql = DB::table('posts')
    ->where('post_type', 'courses')
    ->where_in('post_status', ['publish', 'draft'])
    ->latest()
    ->limit(10)
    ->to_sql();

// Output:
// SELECT * FROM wp_posts WHERE post_type = 'courses' AND post_status IN ('publish', 'draft') ORDER BY id DESC LIMIT 10 OFFSET 0
```

### Static Utility Methods

```php
// Table name prefixing
DB::prepare_table_name('tutor_orders'); // "wp_tutor_orders"

// Escaping single values (numbers, booleans, strings, null)
DB::prepare_value('hello');             // "'hello'"
DB::prepare_value(123);                 // "123"
DB::prepare_value(null);                // "NULL"

// Escaping list for IN clauses
DB::prepare_in_clause([1, 2, 'text']);  // "1, 2, 'text'"

// Accessors
DB::get_table_prefix();                 // "wp_"
DB::get_last_query();                   // Returns $wpdb->last_query
```

---

## Security Architecture

1. **Prepared Statements:** All literal values passed into `where()`, `where_in()`, `where_between()`, `insert()`, `update()`, and raw bindings are passed through `$wpdb->prepare()`.
2. **Identifier Validation:** Column identifiers and table names are strictly validated against `/^[A-Za-z_*][A-Za-z0-9_.*]*$/`. Column aliases in `select()` are parsed and verified against strict column and alias patterns (`is_valid_column` and `is_valid_identifier`). Join conditions (including closure-based `on()` and `or_on()` conditions) validate both columns and operators against allowlists before inclusion. Characters that could trigger SQL injection (semicolons, comments, quotes, brackets) are rejected.
3. **LIKE-Injection Protection:** `where_like()` automatically processes input values via `$wpdb->esc_like()`, preventing wildcard abuse (`%` or `_`).
4. **Schema Inspection Safety:** `has_table()`, `has_column()`, and `has_columns()` validate all identifiers before running schema queries. Table lookups employ `$wpdb->esc_like()` with `SHOW TABLES LIKE`, and column queries quote validated table names within backticks.
5. **Robust Exception & Rollback Handling:** `DB::transaction()` catches all `\Throwable` instances (both exceptions and fatal PHP errors), ensuring transactions roll back reliably. Secondary rollback failures are trapped so the original root cause exception propagates cleanly.
6. **WPCS Compliance:** Follows WordPress Coding Standards (WPCS) with full escaping and cache awareness annotations.
