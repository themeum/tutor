# DB Helper (`Tutor\Helpers\DB`)

- **Class:** `Tutor\Helpers\DB`
- **File:** `wp-content/plugins/tutor/helpers/DB.php`
- **Namespace:** `Tutor\Helpers`
- **Since:** `4.2.0`
- **Package:** `Tutor\Helpers`

---

## Overview

The `DB` class provides a fluent, chainable query builder interface for WordPress database operations. Inspired by Laravel's Query Builder, it simplifies complex query construction while guaranteeing strict SQL injection prevention through automatic parameter binding via `$wpdb->prepare()` and strict regex validation on SQL identifiers.

### Key Features

- **Fluent API:** Clean, readable, chainable query building.
- **SQL Injection Prevention:** All values are bound through `$wpdb->prepare()`. Table names and column identifiers are validated against strict regex patterns.
- **Automatic Table Prefixing:** Auto-detects and prepends `$wpdb->prefix` when omitted.
- **Self-Contained & Independent:** Zero external dependencies beyond WordPress `$wpdb`.
- **Full Query Lifecycle:** Supports `SELECT`, `INSERT`, `UPDATE`, `DELETE`, aggregates, joins, and built-in pagination.

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
12. [Debugging & Raw Queries](#debugging--raw-queries)
13. [Security Architecture](#security-architecture)

---

## Quick Start

```php
use Tutor\Helpers\DB;

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

    // Joins
    public function join( string $table, string $first, string $operator, string $second ): self;
    public function left_join( string $table, string $first, string $operator, string $second ): self;
    public function right_join( string $table, string $first, string $operator, string $second ): self;

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

    // Debug & Inspection
    public function to_sql(): string;

    // Utility Helpers
    public static function prepare_value( $value ): string;
    public static function prepare_in_clause( array $values ): string;
    public static function prepare_table_name( string $table ): string;
    public static function get_table_prefix(): string;
    public static function get_last_query(): string;
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

Joins accept:
1. Target table (optionally with alias: `'users AS u'`)
2. Left column
3. Operator (`=`, `>`, etc.)
4. Right column

### Supported Join Methods:
- `join( $table, $first, $operator, $second )` — `INNER JOIN`
- `left_join( $table, $first, $operator, $second )` — `LEFT JOIN`
- `right_join( $table, $first, $operator, $second )` — `RIGHT JOIN`

```php
DB::table('tutor_earnings')
    ->as('e')
    ->select('e.*', 'u.user_login')
    ->left_join("{$wpdb->users} AS u", 'e.user_id', '=', 'u.ID')
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
2. **Identifier Validation:** Column identifiers and table names are strictly validated against `/^[A-Za-z_*][A-Za-z0-9_.*]*$/`. Characters that could trigger SQL injection (spaces, semicolons, comments, quotes, brackets) are rejected.
3. **LIKE-Injection Protection:** `where_like()` automatically processes input values via `$wpdb->esc_like()`, preventing wildcard abuse (`%` or `_`).
4. **WPCS Compliance:** Follows WordPress Coding Standards (WPCS) with full escaping and cache awareness annotations.
