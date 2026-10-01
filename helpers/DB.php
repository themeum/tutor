<?php
/**
 * Fluent Query Builder for safe, chainable database operations.
 *
 * Provides a Laravel-inspired fluent interface for building WordPress
 * database queries while ensuring SQL injection prevention through
 * consistent use of $wpdb->prepare().
 *
 * @package Tutor\Helpers
 * @since 4.1.2
 */

namespace Tutor\Helpers;

/**
 * Class DB
 *
 * Fluent, chainable query builder for WordPress database operations.
 * All user-supplied values are escaped via $wpdb->prepare(), and
 * identifiers (columns, tables) are validated against a safe pattern.
 *
 * Usage:
 *   DB::table('tutor_orders')
 *       ->where('status', 'completed')
 *       ->order_by('id', 'DESC')
 *       ->limit(10)
 *       ->get();
 *
 * @since 4.1.2
 */
class DB {

	/**
	 * Table name (with prefix).
	 *
	 * @since 4.1.2
	 *
	 * @var string
	 */
	private $table = '';

	/**
	 * Table alias.
	 *
	 * @since 4.1.2
	 *
	 * @var string
	 */
	private $alias = '';

	/**
	 * Columns to select.
	 *
	 * @since 4.1.2
	 *
	 * @var array
	 */
	private $columns = array();

	/**
	 * Raw select expressions.
	 *
	 * @since 4.1.2
	 *
	 * @var array
	 */
	private $raw_columns = array();

	/**
	 * Whether to apply DISTINCT.
	 *
	 * @since 4.1.2
	 *
	 * @var bool
	 */
	private $is_distinct = false;

	/**
	 * JOIN clauses.
	 *
	 * @since 4.1.2
	 *
	 * @var array
	 */
	private $joins = array();

	/**
	 * WHERE clause conditions.
	 *
	 * Each entry: [ 'sql' => string, 'boolean' => 'AND'|'OR' ].
	 *
	 * @since 4.1.2
	 *
	 * @var array
	 */
	private $wheres = array();

	/**
	 * GROUP BY columns.
	 *
	 * @since 4.1.2
	 *
	 * @var array
	 */
	private $group_by_columns = array();

	/**
	 * HAVING clause conditions.
	 *
	 * @since 4.1.2
	 *
	 * @var array
	 */
	private $havings = array();

	/**
	 * ORDER BY clauses.
	 *
	 * @since 4.1.2
	 *
	 * @var array
	 */
	private $order_by_clauses = array();

	/**
	 * LIMIT value.
	 *
	 * @since 4.1.2
	 *
	 * @var int|null
	 */
	private $limit_value = null;

	/**
	 * OFFSET value.
	 *
	 * @since 4.1.2
	 *
	 * @var int
	 */
	private $offset_value = 0;

	/**
	 * Supported comparison operators.
	 *
	 * @since 4.1.2
	 *
	 * @var string[]
	 */
	private static $valid_operators = array(
		'=',
		'!=',
		'<>',
		'<',
		'>',
		'<=',
		'>=',
		'LIKE',
		'NOT LIKE',
		'IN',
		'NOT IN',
		'IS',
		'IS NOT',
		'BETWEEN',
		'NOT BETWEEN',
	);

	// ─────────────────────────────────────────────
	// Factory
	// ─────────────────────────────────────────────

	/**
	 * Create a new DB query instance for the given table.
	 *
	 * The table name will be automatically prefixed with the WordPress
	 * table prefix if not already present.
	 *
	 * @since 4.1.2
	 *
	 * @param string $table Table name (without prefix is fine).
	 *
	 * @return self
	 */
	public static function table( string $table ): self {
		$instance        = new self();
		$instance->table = self::prepare_table_name( $table );

		return $instance;
	}

	// ─────────────────────────────────────────────
	// SELECT
	// ─────────────────────────────────────────────

	/**
	 * Set the columns to select.
	 *
	 * Accepts one or more column names. Multiple calls are additive.
	 *
	 * @since 4.1.2
	 *
	 * @param string ...$columns Column names to select.
	 *
	 * @return self
	 */
	public function select( string ...$columns ): self {
		foreach ( $columns as $column ) {
			if ( self::is_valid_column( $column ) ) {
				$this->columns[] = $column;
			}
		}

		return $this;
	}

	/**
	 * Add a raw SELECT expression.
	 *
	 * Use this for aggregate functions, computed columns, or any SQL
	 * expression that cannot be represented as a simple column name.
	 *
	 * @since 4.1.2
	 *
	 * @param string $expression Raw SQL expression, e.g., 'COUNT(*) AS total'.
	 * @param array  $bindings   Optional values to bind to placeholders.
	 *
	 * @return self
	 */
	public function select_raw( string $expression, array $bindings = array() ): self {
		if ( ! empty( $bindings ) ) {
			global $wpdb;
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- User-provided expression with bindings.
			$expression = $wpdb->prepare( $expression, $bindings );
		}

		$this->raw_columns[] = $expression;

		return $this;
	}

	/**
	 * Apply DISTINCT to the query.
	 *
	 * @since 4.1.2
	 *
	 * @return self
	 */
	public function distinct(): self {
		$this->is_distinct = true;

		return $this;
	}

	/**
	 * Set a table alias.
	 *
	 * @since 4.1.2
	 *
	 * @param string $alias Alias for the primary table.
	 *
	 * @return self
	 */
	public function as( string $alias ): self {
		if ( self::is_valid_identifier( $alias ) ) {
			$this->alias = $alias;
		}

		return $this;
	}

	// ─────────────────────────────────────────────
	// JOINs
	// ─────────────────────────────────────────────

	/**
	 * Add an INNER JOIN clause.
	 *
	 * @since 4.1.2
	 *
	 * @param string $table    Table to join (may include alias, e.g., 'users AS u').
	 * @param string $first    First column in the ON condition.
	 * @param string $operator Comparison operator.
	 * @param string $second   Second column in the ON condition.
	 *
	 * @return self
	 */
	public function join( string $table, string $first, string $operator, string $second ): self {
		return $this->add_join( 'INNER', $table, $first, $operator, $second );
	}

	/**
	 * Add a LEFT JOIN clause.
	 *
	 * @since 4.1.2
	 *
	 * @param string $table    Table to join.
	 * @param string $first    First column.
	 * @param string $operator Comparison operator.
	 * @param string $second   Second column.
	 *
	 * @return self
	 */
	public function left_join( string $table, string $first, string $operator, string $second ): self {
		return $this->add_join( 'LEFT', $table, $first, $operator, $second );
	}

	/**
	 * Add a RIGHT JOIN clause.
	 *
	 * @since 4.1.2
	 *
	 * @param string $table    Table to join.
	 * @param string $first    First column.
	 * @param string $operator Comparison operator.
	 * @param string $second   Second column.
	 *
	 * @return self
	 */
	public function right_join( string $table, string $first, string $operator, string $second ): self {
		return $this->add_join( 'RIGHT', $table, $first, $operator, $second );
	}

	/**
	 * Internal method to add a JOIN clause.
	 *
	 * Validates join type, table alias, columns, and operator before
	 * adding to the query.
	 *
	 * @since 4.1.2
	 *
	 * @param string $type     Join type (INNER, LEFT, RIGHT, CROSS).
	 * @param string $table    Table name, may include alias e.g., 'users AS u'.
	 * @param string $first    First column in the ON condition.
	 * @param string $operator Comparison operator.
	 * @param string $second   Second column in the ON condition.
	 *
	 * @return self
	 */
	private function add_join( string $type, string $table, string $first, string $operator, string $second ): self {
		$type = strtoupper( $type );

		$allowed_types = array( 'INNER', 'LEFT', 'RIGHT', 'CROSS' );
		if ( ! in_array( $type, $allowed_types, true ) ) {
			return $this;
		}

		// Parse "table AS alias" or "table alias" format.
		$parts       = preg_split( '/\s+(?:AS\s+)?/i', $table, 2 );
		$table_name  = self::prepare_table_name( trim( $parts[0] ) );
		$table_alias = isset( $parts[1] ) ? trim( $parts[1] ) : '';

		$table_expr = $table_name;
		if ( ! empty( $table_alias ) && self::is_valid_identifier( $table_alias ) ) {
			$table_expr .= " AS {$table_alias}";
		}

		if ( self::is_valid_column( $first )
			&& self::is_valid_operator( $operator )
			&& self::is_valid_column( $second )
		) {
			$operator      = strtoupper( $operator );
			$this->joins[] = "{$type} JOIN {$table_expr} ON {$first} {$operator} {$second}";
		}

		return $this;
	}

	// ─────────────────────────────────────────────
	// WHERE
	// ─────────────────────────────────────────────

	/**
	 * Add a WHERE condition.
	 *
	 * Two-argument form uses '=' operator: where('col', 'value').
	 * Three-argument form uses custom operator: where('col', '>=', 5).
	 * Passing null as value auto-converts to IS NULL / IS NOT NULL.
	 *
	 * @since 4.1.2
	 *
	 * @param string $column           Column name.
	 * @param mixed  $operator_or_value Operator (if 3 args) or value (if 2 args).
	 * @param mixed  $value            Value when operator is specified.
	 *
	 * @return self
	 */
	public function where( string $column, $operator_or_value, $value = null ): self {
		return $this->add_where( $column, $operator_or_value, $value, 'AND', func_num_args() < 3 );
	}

	/**
	 * Add an OR WHERE condition.
	 *
	 * @since 4.1.2
	 *
	 * @param string $column           Column name.
	 * @param mixed  $operator_or_value Operator or value.
	 * @param mixed  $value            Value when operator is specified.
	 *
	 * @return self
	 */
	public function or_where( string $column, $operator_or_value, $value = null ): self {
		return $this->add_where( $column, $operator_or_value, $value, 'OR', func_num_args() < 3 );
	}

	/**
	 * Add a WHERE IN condition.
	 *
	 * @since 4.1.2
	 *
	 * @param string $column Column name.
	 * @param array  $values Array of values.
	 *
	 * @return self
	 */
	public function where_in( string $column, array $values ): self {
		return $this->add_where_in( $column, $values, 'IN', 'AND' );
	}

	/**
	 * Add a WHERE NOT IN condition.
	 *
	 * @since 4.1.2
	 *
	 * @param string $column Column name.
	 * @param array  $values Array of values.
	 *
	 * @return self
	 */
	public function where_not_in( string $column, array $values ): self {
		return $this->add_where_in( $column, $values, 'NOT IN', 'AND' );
	}

	/**
	 * Add an OR WHERE IN condition.
	 *
	 * @since 4.1.2
	 *
	 * @param string $column Column name.
	 * @param array  $values Array of values.
	 *
	 * @return self
	 */
	public function or_where_in( string $column, array $values ): self {
		return $this->add_where_in( $column, $values, 'IN', 'OR' );
	}

	/**
	 * Add an OR WHERE NOT IN condition.
	 *
	 * @since 4.1.2
	 *
	 * @param string $column Column name.
	 * @param array  $values Array of values.
	 *
	 * @return self
	 */
	public function or_where_not_in( string $column, array $values ): self {
		return $this->add_where_in( $column, $values, 'NOT IN', 'OR' );
	}

	/**
	 * Add a WHERE IS NULL condition.
	 *
	 * @since 4.1.2
	 *
	 * @param string $column Column name.
	 *
	 * @return self
	 */
	public function where_null( string $column ): self {
		if ( self::is_valid_column( $column ) ) {
			$this->wheres[] = array(
				'sql'     => "{$column} IS NULL",
				'boolean' => 'AND',
			);
		}

		return $this;
	}

	/**
	 * Add a WHERE IS NOT NULL condition.
	 *
	 * @since 4.1.2
	 *
	 * @param string $column Column name.
	 *
	 * @return self
	 */
	public function where_not_null( string $column ): self {
		if ( self::is_valid_column( $column ) ) {
			$this->wheres[] = array(
				'sql'     => "{$column} IS NOT NULL",
				'boolean' => 'AND',
			);
		}

		return $this;
	}

	/**
	 * Add an OR WHERE IS NULL condition.
	 *
	 * @since 4.1.2
	 *
	 * @param string $column Column name.
	 *
	 * @return self
	 */
	public function or_where_null( string $column ): self {
		if ( self::is_valid_column( $column ) ) {
			$this->wheres[] = array(
				'sql'     => "{$column} IS NULL",
				'boolean' => 'OR',
			);
		}

		return $this;
	}

	/**
	 * Add an OR WHERE IS NOT NULL condition.
	 *
	 * @since 4.1.2
	 *
	 * @param string $column Column name.
	 *
	 * @return self
	 */
	public function or_where_not_null( string $column ): self {
		if ( self::is_valid_column( $column ) ) {
			$this->wheres[] = array(
				'sql'     => "{$column} IS NOT NULL",
				'boolean' => 'OR',
			);
		}

		return $this;
	}

	/**
	 * Add a WHERE BETWEEN condition.
	 *
	 * @since 4.1.2
	 *
	 * @param string $column Column name.
	 * @param mixed  $min    Minimum value.
	 * @param mixed  $max    Maximum value.
	 *
	 * @return self
	 */
	public function where_between( string $column, $min, $max ): self {
		return $this->add_where_between( $column, $min, $max, 'BETWEEN', 'AND' );
	}

	/**
	 * Add a WHERE NOT BETWEEN condition.
	 *
	 * @since 4.1.2
	 *
	 * @param string $column Column name.
	 * @param mixed  $min    Minimum value.
	 * @param mixed  $max    Maximum value.
	 *
	 * @return self
	 */
	public function where_not_between( string $column, $min, $max ): self {
		return $this->add_where_between( $column, $min, $max, 'NOT BETWEEN', 'AND' );
	}

	/**
	 * Add an OR WHERE BETWEEN condition.
	 *
	 * @since 4.1.2
	 *
	 * @param string $column Column name.
	 * @param mixed  $min    Minimum value.
	 * @param mixed  $max    Maximum value.
	 *
	 * @return self
	 */
	public function or_where_between( string $column, $min, $max ): self {
		return $this->add_where_between( $column, $min, $max, 'BETWEEN', 'OR' );
	}

	/**
	 * Add an OR WHERE NOT BETWEEN condition.
	 *
	 * @since 4.1.2
	 *
	 * @param string $column Column name.
	 * @param mixed  $min    Minimum value.
	 * @param mixed  $max    Maximum value.
	 *
	 * @return self
	 */
	public function or_where_not_between( string $column, $min, $max ): self {
		return $this->add_where_between( $column, $min, $max, 'NOT BETWEEN', 'OR' );
	}

	/**
	 * Add a WHERE LIKE condition.
	 *
	 * The value is automatically wrapped with '%' wildcards and escaped
	 * using $wpdb->esc_like() to prevent LIKE injection.
	 *
	 * @since 4.1.2
	 *
	 * @param string $column Column name.
	 * @param string $value  Search term (auto-wrapped with % wildcards).
	 *
	 * @return self
	 */
	public function where_like( string $column, string $value ): self {
		return $this->add_where_like( $column, $value, 'LIKE', 'AND' );
	}

	/**
	 * Add a WHERE NOT LIKE condition.
	 *
	 * @since 4.1.2
	 *
	 * @param string $column Column name.
	 * @param string $value  Search term.
	 *
	 * @return self
	 */
	public function where_not_like( string $column, string $value ): self {
		return $this->add_where_like( $column, $value, 'NOT LIKE', 'AND' );
	}

	/**
	 * Add an OR WHERE LIKE condition.
	 *
	 * @since 4.1.2
	 *
	 * @param string $column Column name.
	 * @param string $value  Search term.
	 *
	 * @return self
	 */
	public function or_where_like( string $column, string $value ): self {
		return $this->add_where_like( $column, $value, 'LIKE', 'OR' );
	}

	/**
	 * Add an OR WHERE NOT LIKE condition.
	 *
	 * @since 4.1.2
	 *
	 * @param string $column Column name.
	 * @param string $value  Search term.
	 *
	 * @return self
	 */
	public function or_where_not_like( string $column, string $value ): self {
		return $this->add_where_like( $column, $value, 'NOT LIKE', 'OR' );
	}

	/**
	 * Add a raw WHERE expression.
	 *
	 * Use $wpdb->prepare() format specifiers (%s, %d, %f) in the expression
	 * and pass corresponding values in the bindings array.
	 *
	 * @since 4.1.2
	 *
	 * @param string $expression Raw SQL expression, e.g., 'DATE(col) = %s'.
	 * @param array  $bindings   Optional values to bind.
	 *
	 * @return self
	 */
	public function where_raw( string $expression, array $bindings = array() ): self {
		return $this->add_where_raw( $expression, $bindings, 'AND' );
	}

	/**
	 * Add a raw OR WHERE expression.
	 *
	 * @since 4.1.2
	 *
	 * @param string $expression Raw SQL expression.
	 * @param array  $bindings   Optional values to bind.
	 *
	 * @return self
	 */
	public function or_where_raw( string $expression, array $bindings = array() ): self {
		return $this->add_where_raw( $expression, $bindings, 'OR' );
	}

	// ─────────────────────────────────────────────
	// GROUP BY
	// ─────────────────────────────────────────────

	/**
	 * Add GROUP BY columns.
	 *
	 * @since 4.1.2
	 *
	 * @param string ...$columns Column names to group by.
	 *
	 * @return self
	 */
	public function group_by( string ...$columns ): self {
		foreach ( $columns as $column ) {
			if ( self::is_valid_column( $column ) ) {
				$this->group_by_columns[] = $column;
			}
		}

		return $this;
	}

	// ─────────────────────────────────────────────
	// HAVING
	// ─────────────────────────────────────────────

	/**
	 * Add a raw HAVING expression.
	 *
	 * @since 4.1.2
	 *
	 * @param string $expression Raw SQL expression, e.g., 'COUNT(*) > %d'.
	 * @param array  $bindings   Optional values to bind.
	 *
	 * @return self
	 */
	public function having_raw( string $expression, array $bindings = array() ): self {
		if ( ! empty( $bindings ) ) {
			global $wpdb;
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Raw expression with user-controlled bindings.
			$expression = $wpdb->prepare( $expression, $bindings );
		}

		$this->havings[] = $expression;

		return $this;
	}

	// ─────────────────────────────────────────────
	// ORDER BY
	// ─────────────────────────────────────────────

	/**
	 * Add an ORDER BY clause.
	 *
	 * Multiple calls add multiple sort columns.
	 *
	 * @since 4.1.2
	 *
	 * @param string $column    Column name.
	 * @param string $direction ASC or DESC, default ASC.
	 *
	 * @return self
	 */
	public function order_by( string $column, string $direction = 'ASC' ): self {
		if ( ! self::is_valid_column( $column ) ) {
			return $this;
		}

		$direction                = 'DESC' === strtoupper( $direction ) ? 'DESC' : 'ASC';
		$this->order_by_clauses[] = "{$column} {$direction}";

		return $this;
	}

	/**
	 * Add a raw ORDER BY expression.
	 *
	 * @since 4.1.2
	 *
	 * @param string $expression Raw SQL expression.
	 * @param array  $bindings   Optional values to bind.
	 *
	 * @return self
	 */
	public function order_by_raw( string $expression, array $bindings = array() ): self {
		if ( ! empty( $bindings ) ) {
			global $wpdb;
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$expression = $wpdb->prepare( $expression, $bindings );
		}

		$this->order_by_clauses[] = $expression;

		return $this;
	}

	/**
	 * Order results by the given column descending (default 'id').
	 *
	 * @since 4.1.2
	 *
	 * @param string $column Column name, default 'id'.
	 *
	 * @return self
	 */
	public function latest( string $column = 'id' ): self {
		return $this->order_by( $column, 'DESC' );
	}

	/**
	 * Order results by the given column ascending (default 'id').
	 *
	 * @since 4.1.2
	 *
	 * @param string $column Column name, default 'id'.
	 *
	 * @return self
	 */
	public function oldest( string $column = 'id' ): self {
		return $this->order_by( $column, 'ASC' );
	}

	// ─────────────────────────────────────────────
	// LIMIT / OFFSET
	// ─────────────────────────────────────────────

	/**
	 * Set the query LIMIT.
	 *
	 * @since 4.1.2
	 *
	 * @param int $limit Number of rows to return.
	 *
	 * @return self
	 */
	public function limit( int $limit ): self {
		$this->limit_value = max( 0, $limit );

		return $this;
	}

	/**
	 * Set the query OFFSET.
	 *
	 * @since 4.1.2
	 *
	 * @param int $offset Number of rows to skip.
	 *
	 * @return self
	 */
	public function offset( int $offset ): self {
		$this->offset_value = max( 0, $offset );

		return $this;
	}

	// ─────────────────────────────────────────────
	// Terminal Methods (execute & return results)
	// ─────────────────────────────────────────────

	/**
	 * Execute the query and return all matching rows.
	 *
	 * @since 4.1.2
	 *
	 * @param string $output Output type: OBJECT, ARRAY_A, ARRAY_N.
	 *
	 * @return array Array of result rows.
	 */
	public function get( string $output = 'OBJECT' ): array {
		global $wpdb;

		$sql = $this->build_select_query();

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- SQL built with prepare_value/prepare calls.
		$results = $wpdb->get_results( $sql, $output );

		return is_array( $results ) ? $results : array();
	}

	/**
	 * Execute the query and return the first matching row.
	 *
	 * @since 4.1.2
	 *
	 * @param string $output Output type: OBJECT, ARRAY_A, ARRAY_N.
	 *
	 * @return object|array|null Single row or null if not found.
	 */
	public function first( string $output = 'OBJECT' ) {
		global $wpdb;

		// Temporarily set limit to 1.
		$saved_limit       = $this->limit_value;
		$this->limit_value = 1;

		$sql = $this->build_select_query();

		$this->limit_value = $saved_limit;

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return $wpdb->get_row( $sql, $output );
	}

	/**
	 * Get the count of rows matching the query.
	 *
	 * @since 4.1.2
	 *
	 * @param string $column Column to count, default '*'.
	 *
	 * @return int Total matching row count.
	 */
	public function count( string $column = '*' ): int {
		global $wpdb;

		if ( '*' !== $column && ! self::is_valid_column( $column ) ) {
			$column = '*';
		}

		// Save state that count() modifies.
		$saved_columns  = $this->columns;
		$saved_raw      = $this->raw_columns;
		$saved_order    = $this->order_by_clauses;
		$saved_limit    = $this->limit_value;
		$saved_offset   = $this->offset_value;
		$saved_distinct = $this->is_distinct;

		// Override for count.
		$this->columns          = array();
		$this->raw_columns      = array( "COUNT({$column}) AS aggregate" );
		$this->order_by_clauses = array();
		$this->limit_value      = null;
		$this->offset_value     = 0;
		$this->is_distinct      = false;

		$sql = $this->build_select_query();

		// Restore state.
		$this->columns          = $saved_columns;
		$this->raw_columns      = $saved_raw;
		$this->order_by_clauses = $saved_order;
		$this->limit_value      = $saved_limit;
		$this->offset_value     = $saved_offset;
		$this->is_distinct      = $saved_distinct;

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return (int) $wpdb->get_var( $sql );
	}

	/**
	 * Execute the query with pagination.
	 *
	 * @since 4.1.2
	 *
	 * @param int    $per_page     Results per page.
	 * @param int    $current_page Current page number (1-indexed).
	 * @param string $output       Output type: OBJECT, ARRAY_A.
	 *
	 * @return array {
	 *     @type int   $total_count  Total matching records.
	 *     @type int   $per_page     Results per page.
	 *     @type int   $current_page Current page number.
	 *     @type int   $total_pages  Total number of pages.
	 *     @type array $results      Array of result rows.
	 * }
	 */
	public function paginate( int $per_page, int $current_page = 1, string $output = 'OBJECT' ): array {
		global $wpdb;

		$current_page = max( 1, $current_page );
		$per_page     = max( 1, $per_page );

		$this->limit_value  = $per_page;
		$this->offset_value = ( $current_page - 1 ) * $per_page;

		$sql = $this->build_select_query( true );

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$results = $wpdb->get_results( $sql, $output );

		$has_records = is_array( $results ) && count( $results );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$total_count = $has_records ? (int) $wpdb->get_var( 'SELECT FOUND_ROWS()' ) : 0;
		$total_pages = (int) ceil( $total_count / $per_page );

		return array(
			'total_count'  => $total_count,
			'per_page'     => $per_page,
			'current_page' => $current_page,
			'total_pages'  => $total_pages,
			'results'      => is_array( $results ) ? $results : array(),
		);
	}

	/**
	 * Get a single column value from the first matching row.
	 *
	 * @since 4.1.2
	 *
	 * @param string $column Column name to retrieve.
	 *
	 * @return mixed|null The column value or null if not found.
	 */
	public function value( string $column ) {
		global $wpdb;

		if ( ! self::is_valid_column( $column ) ) {
			return null;
		}

		// Save state.
		$saved_columns = $this->columns;
		$saved_raw     = $this->raw_columns;
		$saved_limit   = $this->limit_value;

		// Override for single value.
		$this->columns     = array( $column );
		$this->raw_columns = array();
		$this->limit_value = 1;

		$sql = $this->build_select_query();

		// Restore state.
		$this->columns     = $saved_columns;
		$this->raw_columns = $saved_raw;
		$this->limit_value = $saved_limit;

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return $wpdb->get_var( $sql );
	}

	/**
	 * Retrieve a list of values of a single column.
	 *
	 * If a key column is provided, the resulting array will use that column's
	 * values as array keys.
	 *
	 * @since 4.1.2
	 *
	 * @param string      $column Column value to retrieve.
	 * @param string|null $key    Optional column to use as array key.
	 *
	 * @return array Array of column values.
	 */
	public function pluck( string $column, ?string $key = null ): array {
		if ( ! self::is_valid_column( $column ) ) {
			return array();
		}

		$cols = array( $column );
		if ( null !== $key && self::is_valid_column( $key ) ) {
			$cols[] = $key;
		}

		$saved_columns = $this->columns;
		$saved_raw     = $this->raw_columns;

		$this->columns     = $cols;
		$this->raw_columns = array();

		$results = $this->get( 'ARRAY_A' );

		$this->columns     = $saved_columns;
		$this->raw_columns = $saved_raw;

		if ( empty( $results ) ) {
			return array();
		}

		$col_clean = false !== strpos( $column, '.' ) ? substr( $column, strrpos( $column, '.' ) + 1 ) : $column;
		$key_clean = ( null !== $key && false !== strpos( $key, '.' ) ) ? substr( $key, strrpos( $key, '.' ) + 1 ) : $key;

		if ( null !== $key_clean ) {
			return array_column( $results, $col_clean, $key_clean );
		}

		return array_column( $results, $col_clean );
	}

	/**
	 * Determine if any matching records exist for the current query.
	 *
	 * @since 4.1.2
	 *
	 * @return bool True if at least one matching record exists.
	 */
	public function exists(): bool {
		return null !== $this->first();
	}

	/**
	 * Find a single record by its primary key.
	 *
	 * @since 4.1.2
	 *
	 * @param int|string $id          Primary key value.
	 * @param string     $primary_key Primary key column name, default 'id'.
	 * @param string     $output      Output type: OBJECT, ARRAY_A, ARRAY_N.
	 *
	 * @return object|array|null
	 */
	public function find( $id, string $primary_key = 'id', string $output = 'OBJECT' ) {
		return $this->where( $primary_key, $id )->first( $output );
	}

	/**
	 * Apply the given callback if the condition is truthy.
	 *
	 * @since 4.1.2
	 *
	 * @param mixed         $condition Truthy/falsy condition.
	 * @param callable      $callback         Callback receiving ($this, $condition).
	 * @param callable|null $default_callback Optional callback if condition is falsy.
	 *
	 * @return self
	 */
	public function when( $condition, callable $callback, ?callable $default_callback = null ): self {
		if ( $condition ) {
			$callback( $this, $condition );
		} elseif ( null !== $default_callback ) {
			$default_callback( $this, $condition );
		}

		return $this;
	}

	/**
	 * Insert a single row and return the insert ID.
	 *
	 * Uses $wpdb->insert() which automatically handles escaping.
	 *
	 * @since 4.1.2
	 *
	 * @param array $data   Assoc array of column => value pairs.
	 * @param array $format Optional format specifiers (%s, %d, %f) for each value.
	 *
	 * @throws \Exception If a database error occurs.
	 *
	 * @return int Inserted row ID.
	 */
	public function insert( array $data, array $format = array() ): int {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$result = $wpdb->insert( $this->table, $data, ! empty( $format ) ? $format : null );

		if ( $wpdb->last_error ) {
			throw new \Exception( esc_html( $wpdb->last_error ) );
		}

		return $result ? $wpdb->insert_id : 0;
	}

	/**
	 * Insert multiple rows in a single query.
	 *
	 * All rows must have the same columns. Values are individually escaped
	 * using $wpdb->prepare(). NULL values are inserted as SQL NULL.
	 *
	 * @since 4.1.2
	 *
	 * @param array $rows Array of assoc arrays, each representing a row.
	 *
	 * @throws \Exception If a database error occurs.
	 *
	 * @return bool True on success.
	 */
	public function insert_multiple( array $rows ): bool {
		global $wpdb;

		if ( empty( $rows ) ) {
			return false;
		}

		$columns     = array_keys( $rows[0] );
		$column_list = implode( ', ', array_map( 'sanitize_key', $columns ) );

		$value_groups = array();
		foreach ( $rows as $row ) {
			$row_parts = array();
			foreach ( $columns as $col ) {
				$val = $row[ $col ] ?? null;
				if ( is_null( $val ) ) {
					$row_parts[] = 'NULL';
				} else {
					$row_parts[] = self::prepare_value( $val );
				}
			}
			$value_groups[] = '(' . implode( ', ', $row_parts ) . ')';
		}

		$sql = "INSERT INTO {$this->table} ({$column_list}) VALUES " . implode( ', ', $value_groups );

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Values prepared inline via prepare_value().
		$wpdb->query( $sql );

		if ( $wpdb->last_error ) {
			throw new \Exception( esc_html( $wpdb->last_error ) );
		}

		return true;
	}

	/**
	 * Update rows matching the accumulated WHERE conditions.
	 *
	 * All SET values are individually escaped using $wpdb->prepare().
	 * NULL values produce `SET column = NULL`.
	 *
	 * @since 4.1.2
	 *
	 * @param array $data Assoc array of column => value pairs to update.
	 *
	 * @throws \Exception If a database error occurs.
	 *
	 * @return bool True on success.
	 */
	public function update( array $data ): bool {
		global $wpdb;

		if ( empty( $data ) ) {
			return false;
		}

		$set_parts = array();
		foreach ( $data as $column => $value ) {
			$safe_column = sanitize_key( $column );
			if ( is_null( $value ) ) {
				$set_parts[] = "{$safe_column} = NULL";
			} else {
				$prepared    = self::prepare_value( $value );
				$set_parts[] = "{$safe_column} = {$prepared}";
			}
		}

		$set_clause   = implode( ', ', $set_parts );
		$where_clause = $this->build_where_sql();

		$table_expr = ! empty( $this->alias ) ? "{$this->table} AS {$this->alias}" : $this->table;
		$sql        = "UPDATE {$table_expr} SET {$set_clause}";
		if ( ! empty( $where_clause ) ) {
			$sql .= " WHERE {$where_clause}";
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Values prepared inline.
		$wpdb->query( $sql );

		if ( $wpdb->last_error ) {
			throw new \Exception( esc_html( $wpdb->last_error ) );
		}

		return true;
	}

	/**
	 * Delete rows matching the accumulated WHERE conditions.
	 *
	 * WARNING: Calling delete() without any where() conditions will
	 * delete ALL rows from the table.
	 *
	 * @since 4.1.2
	 *
	 * @throws \Exception If a database error occurs.
	 *
	 * @return int Number of affected rows.
	 */
	public function delete(): int {
		global $wpdb;

		$where_clause = $this->build_where_sql();

		if ( ! empty( $this->alias ) ) {
			$sql = "DELETE {$this->alias} FROM {$this->table} AS {$this->alias}";
		} else {
			$sql = "DELETE FROM {$this->table}";
		}

		if ( ! empty( $where_clause ) ) {
			$sql .= " WHERE {$where_clause}";
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- WHERE values prepared inline.
		$result = $wpdb->query( $sql );

		if ( $wpdb->last_error ) {
			throw new \Exception( esc_html( $wpdb->last_error ) );
		}

		return (int) $result;
	}

	// ─────────────────────────────────────────────
	// Debug
	// ─────────────────────────────────────────────

	/**
	 * Get the generated SQL string without executing.
	 *
	 * Useful for debugging and logging. Returns the SELECT query as it
	 * would be sent to the database.
	 *
	 * @since 4.1.2
	 *
	 * @return string The generated SQL string.
	 */
	public function to_sql(): string {
		global $wpdb;

		$sql = $this->build_select_query();

		if ( isset( $wpdb ) && method_exists( $wpdb, 'remove_placeholder_escape' ) ) {
			return $wpdb->remove_placeholder_escape( $sql );
		}

		return $sql;
	}

	// ─────────────────────────────────────────────
	// Internal WHERE helpers
	// ─────────────────────────────────────────────

	/**
	 * Internal method to add a basic WHERE condition.
	 *
	 * Handles two-argument (default '=') and three-argument (custom operator)
	 * forms. Null values are auto-converted to IS NULL / IS NOT NULL.
	 *
	 * @since 4.1.2
	 *
	 * @param string $column           Column name.
	 * @param mixed  $operator_or_value Operator or value.
	 * @param mixed  $value            Value (null when in two-arg form).
	 * @param string $boolean          AND or OR.
	 * @param bool   $is_two_arg       Whether this is a two-argument call.
	 *
	 * @return self
	 */
	private function add_where( string $column, $operator_or_value, $value, string $boolean, bool $is_two_arg ): self {
		global $wpdb;

		if ( ! self::is_valid_column( $column ) ) {
			return $this;
		}

		// Determine operator and value.
		if ( $is_two_arg ) {
			$value    = $operator_or_value;
			$operator = '=';
		} else {
			$operator = strtoupper( (string) $operator_or_value );
		}

		// Handle null values → IS NULL / IS NOT NULL.
		if ( is_null( $value ) ) {
			$null_op        = in_array( $operator, array( '!=', '<>' ), true ) ? 'IS NOT' : 'IS';
			$this->wheres[] = array(
				'sql'     => "{$column} {$null_op} NULL",
				'boolean' => $boolean,
			);

			return $this;
		}

		if ( ! self::is_valid_operator( $operator ) ) {
			return $this;
		}

		$prepared_value = self::prepare_value( $value );

		$this->wheres[] = array(
			'sql'     => "{$column} {$operator} {$prepared_value}",
			'boolean' => $boolean,
		);

		return $this;
	}

	/**
	 * Internal method to add WHERE IN / NOT IN conditions.
	 *
	 * @since 4.1.2
	 *
	 * @param string $column  Column name.
	 * @param array  $values  Array of values.
	 * @param string $type    IN or NOT IN.
	 * @param string $boolean AND or OR.
	 *
	 * @return self
	 */
	private function add_where_in( string $column, array $values, string $type, string $boolean ): self {
		if ( ! self::is_valid_column( $column ) || empty( $values ) ) {
			return $this;
		}

		$escaped_values = self::prepare_in_clause( $values );

		$this->wheres[] = array(
			'sql'     => "{$column} {$type} ({$escaped_values})",
			'boolean' => $boolean,
		);

		return $this;
	}

	/**
	 * Internal method for BETWEEN / NOT BETWEEN conditions.
	 *
	 * @since 4.1.2
	 *
	 * @param string $column  Column name.
	 * @param mixed  $min     Minimum value.
	 * @param mixed  $max     Maximum value.
	 * @param string $type    BETWEEN or NOT BETWEEN.
	 * @param string $boolean AND or OR.
	 *
	 * @return self
	 */
	private function add_where_between( string $column, $min, $max, string $type, string $boolean ): self {
		if ( ! self::is_valid_column( $column ) ) {
			return $this;
		}

		$min_prepared = self::prepare_value( $min );
		$max_prepared = self::prepare_value( $max );

		$this->wheres[] = array(
			'sql'     => "{$column} {$type} {$min_prepared} AND {$max_prepared}",
			'boolean' => $boolean,
		);

		return $this;
	}

	/**
	 * Internal method for LIKE / NOT LIKE conditions.
	 *
	 * Escapes the value with $wpdb->esc_like() and wraps with '%' wildcards.
	 *
	 * @since 4.1.2
	 *
	 * @param string $column  Column name.
	 * @param string $value   Search term.
	 * @param string $type    LIKE or NOT LIKE.
	 * @param string $boolean AND or OR.
	 *
	 * @return self
	 */
	private function add_where_like( string $column, string $value, string $type, string $boolean ): self {
		global $wpdb;

		if ( ! self::is_valid_column( $column ) ) {
			return $this;
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Column validated, type is hardcoded.
		$sql = $wpdb->prepare( "{$column} {$type} %s", '%' . $wpdb->esc_like( $value ) . '%' );

		$this->wheres[] = array(
			'sql'     => $sql,
			'boolean' => $boolean,
		);

		return $this;
	}

	/**
	 * Internal method for raw WHERE expressions.
	 *
	 * @since 4.1.2
	 *
	 * @param string $expression Raw SQL expression.
	 * @param array  $bindings   Values to bind.
	 * @param string $boolean    AND or OR.
	 *
	 * @return self
	 */
	private function add_where_raw( string $expression, array $bindings, string $boolean ): self {
		if ( ! empty( $bindings ) ) {
			global $wpdb;
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Raw expression with user-controlled bindings.
			$expression = $wpdb->prepare( $expression, $bindings );
		}

		$this->wheres[] = array(
			'sql'     => $expression,
			'boolean' => $boolean,
		);

		return $this;
	}

	// ─────────────────────────────────────────────
	// SQL Building (private)
	// ─────────────────────────────────────────────

	/**
	 * Build the complete SELECT query string.
	 *
	 * @since 4.1.2
	 *
	 * @param bool $calc_found_rows Whether to include SQL_CALC_FOUND_ROWS.
	 *
	 * @return string The complete SQL query.
	 */
	private function build_select_query( bool $calc_found_rows = false ): string {
		$select = $this->build_select_sql();
		$from   = $this->build_from_sql();
		$join   = $this->build_join_sql();
		$where  = $this->build_where_sql();
		$group  = $this->build_group_by_sql();
		$having = $this->build_having_sql();
		$order  = $this->build_order_by_sql();
		$limit  = $this->build_limit_sql();

		$parts = array( 'SELECT' );

		if ( $calc_found_rows ) {
			$parts[] = 'SQL_CALC_FOUND_ROWS';
		}

		if ( $this->is_distinct ) {
			$parts[] = 'DISTINCT';
		}

		$parts[] = $select;
		$parts[] = "FROM {$from}";

		if ( ! empty( $join ) ) {
			$parts[] = $join;
		}

		if ( ! empty( $where ) ) {
			$parts[] = "WHERE {$where}";
		}

		if ( ! empty( $group ) ) {
			$parts[] = "GROUP BY {$group}";
		}

		if ( ! empty( $having ) ) {
			$parts[] = "HAVING {$having}";
		}

		if ( ! empty( $order ) ) {
			$parts[] = "ORDER BY {$order}";
		}

		if ( ! empty( $limit ) ) {
			$parts[] = $limit;
		}

		return implode( ' ', $parts );
	}

	/**
	 * Build SELECT column list.
	 *
	 * @since 4.1.2
	 *
	 * @return string
	 */
	private function build_select_sql(): string {
		$all_columns = array_merge( $this->columns, $this->raw_columns );

		return empty( $all_columns ) ? '*' : implode( ', ', $all_columns );
	}

	/**
	 * Build FROM clause with optional alias.
	 *
	 * @since 4.1.2
	 *
	 * @return string
	 */
	private function build_from_sql(): string {
		if ( ! empty( $this->alias ) ) {
			return "{$this->table} AS {$this->alias}";
		}

		return $this->table;
	}

	/**
	 * Build JOIN clauses.
	 *
	 * @since 4.1.2
	 *
	 * @return string
	 */
	private function build_join_sql(): string {
		return implode( ' ', $this->joins );
	}

	/**
	 * Build WHERE clause (without the WHERE keyword).
	 *
	 * Joins conditions with their respective boolean operators (AND/OR).
	 * The first condition's boolean is always omitted.
	 *
	 * @since 4.1.2
	 *
	 * @return string
	 */
	private function build_where_sql(): string {
		if ( empty( $this->wheres ) ) {
			return '';
		}

		$sql = '';
		foreach ( $this->wheres as $index => $where ) {
			if ( 0 === $index ) {
				$sql .= $where['sql'];
			} else {
				$sql .= " {$where['boolean']} {$where['sql']}";
			}
		}

		return $sql;
	}

	/**
	 * Build GROUP BY clause (without the GROUP BY keywords).
	 *
	 * @since 4.1.2
	 *
	 * @return string
	 */
	private function build_group_by_sql(): string {
		return implode( ', ', $this->group_by_columns );
	}

	/**
	 * Build HAVING clause (without the HAVING keyword).
	 *
	 * @since 4.1.2
	 *
	 * @return string
	 */
	private function build_having_sql(): string {
		return implode( ' AND ', $this->havings );
	}

	/**
	 * Build ORDER BY clause (without the ORDER BY keywords).
	 *
	 * @since 4.1.2
	 *
	 * @return string
	 */
	private function build_order_by_sql(): string {
		return implode( ', ', $this->order_by_clauses );
	}

	/**
	 * Build LIMIT/OFFSET clause.
	 *
	 * @since 4.1.2
	 *
	 * @return string
	 */
	private function build_limit_sql(): string {
		if ( is_null( $this->limit_value ) || $this->limit_value < 0 ) {
			return '';
		}

		return sprintf( 'LIMIT %d OFFSET %d', $this->limit_value, $this->offset_value );
	}

	// ─────────────────────────────────────────────
	// Validation Helpers
	// ─────────────────────────────────────────────

	/**
	 * Validate a SQL column identifier.
	 *
	 * Allows: column, table.column, t.*, *, _col, col123, etc.
	 *
	 * @since 4.1.2
	 *
	 * @param string $column Column identifier.
	 *
	 * @return bool True if valid.
	 */
	private static function is_valid_column( string $column ): bool {
		return (bool) preg_match( '/^[A-Za-z_*][A-Za-z0-9_.*]*$/', $column );
	}

	/**
	 * Validate a SQL identifier (table name, alias).
	 *
	 * @since 4.1.2
	 *
	 * @param string $identifier Identifier to validate.
	 *
	 * @return bool True if valid.
	 */
	private static function is_valid_identifier( string $identifier ): bool {
		return (bool) preg_match( '/^[A-Za-z_][A-Za-z0-9_]*$/', $identifier );
	}

	/**
	 * Check whether a comparison operator is supported.
	 *
	 * @since 4.1.2
	 *
	 * @param string $operator SQL operator.
	 *
	 * @return bool True if the operator is valid.
	 */
	private static function is_valid_operator( string $operator ): bool {
		return in_array( strtoupper( $operator ), self::$valid_operators, true );
	}

	/**
	 * Get the wpdb format placeholder for a value based on its PHP type.
	 *
	 * @since 4.1.2
	 *
	 * @param mixed $value Value to determine placeholder for.
	 *
	 * @return string %d for int, %f for float, %s for everything else.
	 */
	private static function get_placeholder( $value ): string {
		if ( is_int( $value ) ) {
			return '%d';
		}

		if ( is_float( $value ) ) {
			return '%f';
		}

		return '%s';
	}

	// ─────────────────────────────────────────────
	// Value Preparation & DB Utilities (Self-Contained)
	// ─────────────────────────────────────────────

	/**
	 * Prepare a single value for safe SQL inclusion using $wpdb->prepare().
	 *
	 * Numbers and booleans are formatted accordingly; strings are escaped and quoted.
	 * NULL is returned as the unquoted literal string 'NULL'.
	 *
	 * @since 4.1.2
	 *
	 * @param mixed $value Value to prepare.
	 *
	 * @return string Escaped SQL value literal.
	 */
	public static function prepare_value( $value ): string {
		global $wpdb;

		if ( is_null( $value ) ) {
			return 'NULL';
		}

		if ( is_bool( $value ) ) {
			return $wpdb->prepare( '%d', $value ? 1 : 0 );
		}

		if ( is_int( $value ) ) {
			return $wpdb->prepare( '%d', $value );
		}

		if ( is_float( $value ) ) {
			$str_val = (string) $value;
			$dot_pos = strpos( $str_val, '.' );
			if ( false !== $dot_pos ) {
				$decimals = strlen( substr( $str_val, $dot_pos + 1 ) );
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
				return $wpdb->prepare( '%.' . $decimals . 'f', $value );
			}
			return $wpdb->prepare( '%f', $value );
		}

		return $wpdb->prepare( '%s', $value );
	}

	/**
	 * Make sanitized SQL IN clause values from an array.
	 *
	 * @since 4.1.2
	 *
	 * @param array $values Array of scalar values.
	 *
	 * @return string Comma-separated list of prepared values.
	 */
	public static function prepare_in_clause( array $values ): string {
		$escaped = array_map(
			function ( $value ) {
				return self::prepare_value( $value );
			},
			$values
		);

		return implode( ', ', $escaped );
	}

	/**
	 * Prepare table name with WordPress prefix if not already prefixed.
	 *
	 * @since 4.1.2
	 *
	 * @param string $table Table name.
	 *
	 * @return string Prefixed table name.
	 */
	public static function prepare_table_name( string $table ): string {
		global $wpdb;

		$prefix = isset( $wpdb->prefix ) ? $wpdb->prefix : '';
		if ( ! empty( $prefix ) && 0 !== strpos( $table, $prefix ) ) {
			return $prefix . $table;
		}

		return $table;
	}

	/**
	 * Get the WordPress database table prefix.
	 *
	 * @since 4.1.2
	 *
	 * @return string Table prefix.
	 */
	public static function get_table_prefix(): string {
		global $wpdb;

		return isset( $wpdb->prefix ) ? $wpdb->prefix : '';
	}

	/**
	 * Get the last executed query from $wpdb.
	 *
	 * @since 4.1.2
	 *
	 * @return string Last executed SQL query.
	 */
	public static function get_last_query(): string {
		global $wpdb;

		return isset( $wpdb->last_query ) ? $wpdb->last_query : '';
	}
}
