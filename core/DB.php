<?php
/**
 * Fluent Query Builder for safe, chainable database operations.
 *
 * Provides a Laravel-inspired fluent interface for building WordPress
 * database queries while ensuring SQL injection prevention through
 * consistent use of $wpdb->prepare().
 *
 * @package Tutor\Core
 * @since 4.2.0
 */

namespace Tutor\Core;

use Closure;
use InvalidArgumentException;
use Tutor\Exceptions\DatabaseException;

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
 * @since 4.2.0
 */
class DB {

	/**
	 * WordPress database instance.
	 *
	 * @since 4.2.0
	 *
	 * @var \wpdb|null
	 */
	protected static $db = null;

	/**
	 * Table name (with prefix).
	 *
	 * @since 4.2.0
	 *
	 * @var string
	 */
	private $table = '';

	/**
	 * Table alias.
	 *
	 * @since 4.2.0
	 *
	 * @var string
	 */
	private $alias = '';

	/**
	 * Whether the primary FROM source is a derived table subquery.
	 *
	 * @since 4.3.0
	 *
	 * @var bool
	 */
	private $is_derived = false;

	/**
	 * Columns to select.
	 *
	 * @since 4.2.0
	 *
	 * @var array
	 */
	private $columns = array();

	/**
	 * Raw select expressions.
	 *
	 * @since 4.2.0
	 *
	 * @var array
	 */
	private $raw_columns = array();

	/**
	 * Whether to apply DISTINCT.
	 *
	 * @since 4.2.0
	 *
	 * @var bool
	 */
	private $is_distinct = false;

	/**
	 * JOIN clauses.
	 *
	 * @since 4.2.0
	 *
	 * @var array
	 */
	private $joins = array();

	/**
	 * WHERE clause conditions.
	 *
	 * Each entry: [ 'sql' => string, 'boolean' => 'AND'|'OR' ].
	 *
	 * @since 4.2.0
	 *
	 * @var array
	 */
	private $wheres = array();

	/**
	 * GROUP BY columns.
	 *
	 * @since 4.2.0
	 *
	 * @var array
	 */
	private $group_by_columns = array();

	/**
	 * HAVING clause conditions.
	 *
	 * @since 4.2.0
	 *
	 * @var array
	 */
	private $havings = array();

	/**
	 * ORDER BY clauses.
	 *
	 * @since 4.2.0
	 *
	 * @var array
	 */
	private $order_by_clauses = array();

	/**
	 * LIMIT value.
	 *
	 * @since 4.2.0
	 *
	 * @var int|null
	 */
	private $limit_value = null;

	/**
	 * OFFSET value.
	 *
	 * @since 4.2.0
	 *
	 * @var int
	 */
	private $offset_value = 0;

	/**
	 * Row locking clause (e.g., 'FOR UPDATE').
	 *
	 * @since 4.2.0
	 *
	 * @var string
	 */
	private $lock = '';

	/**
	 * Transaction nesting level.
	 *
	 * @since 4.2.0
	 *
	 * @var int
	 */
	private static $transaction_level = 0;

	/**
	 * Supported comparison operators.
	 *
	 * @since 4.2.0
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

	/**
	 * Supported comparison operators for column-to-column and scalar comparisons.
	 *
	 * @since 4.3.0
	 *
	 * @var string[]
	 */
	private static $comparison_operators = array(
		'=',
		'!=',
		'<>',
		'<',
		'>',
		'<=',
		'>=',
		'LIKE',
		'NOT LIKE',
	);

	// ─────────────────────────────────────────────
	// Factory & Database Accessors
	// ─────────────────────────────────────────────

	/**
	 * Get the database instance, falling back to the global $wpdb.
	 *
	 * @since 4.2.0
	 *
	 * @return \wpdb Database instance.
	 */
	protected static function get_db(): \wpdb {
		if ( ! static::$db instanceof \wpdb ) {
			global $wpdb;
			static::$db = $wpdb;
		}

		return static::$db;
	}

	/**
	 * Override the database instance (mainly for testing).
	 * Pass null to reset to the global $wpdb.
	 *
	 * @since 4.2.0
	 *
	 * @param \wpdb|null $db Database instance.
	 *
	 * @return void
	 */
	public static function set_db( ?\wpdb $db = null ): void {
		static::$db = $db;
	}

	/**
	 * Create a new DB query instance for the given table or subquery.
	 *
	 * The table name will be automatically prefixed with the WordPress
	 * table prefix if not already present. If a Closure or DB instance is
	 * provided, it will be treated as a derived table subquery (which requires
	 * an alias before the query is built). Note: DB subquery instances are
	 * compiled eagerly when passed.
	 *
	 * @since 4.2.0
	 * @since 4.3.0 Added subquery and alias support.
	 *
	 * @param string|Closure|self $table Table name or subquery.
	 * @param string|null         $alias Optional table alias.
	 *
	 * @throws InvalidArgumentException If table name, subquery, or alias is invalid.
	 *
	 * @return self
	 */
	public static function table( $table, ?string $alias = null ): self {
		return ( new self() )->from( $table, $alias );
	}

	/**
	 * Set the primary table for the query.
	 *
	 * Alias for setting the table on an existing instance, commonly used
	 * inside subquery closures: function( $q ) { $q->from('orders'); }
	 *
	 * @since 4.3.0
	 *
	 * @param string|Closure|self $table Table name or subquery.
	 * @param string|null         $alias Optional table alias.
	 *
	 * @throws InvalidArgumentException If table name, subquery, or alias is invalid.
	 *
	 * @return self
	 */
	public function from( $table, ?string $alias = null ): self {
		if ( null !== $alias && ! self::is_valid_identifier( $alias ) ) {
			throw new InvalidArgumentException( 'Invalid table alias.' );
		}

		if ( $table instanceof Closure || $table instanceof self ) {
			$sub_sql          = self::resolve_subquery( $table );
			$this->table      = "({$sub_sql})";
			$this->is_derived = true;
			$this->alias      = null !== $alias ? $alias : '';

			return $this;
		}

		if ( ! is_string( $table ) || '' === trim( $table ) ) {
			throw new InvalidArgumentException( 'Table name must be a non-empty string.' );
		}

		$prepared_table = self::prepare_table_name( trim( $table ) );
		if ( ! self::is_valid_identifier( $prepared_table ) ) {
			throw new InvalidArgumentException( 'Invalid table name.' );
		}

		$this->table      = $prepared_table;
		$this->is_derived = false;
		if ( null !== $alias ) {
			$this->alias = $alias;
		}

		return $this;
	}

	/**
	 * Set a derived table subquery as the FROM source.
	 *
	 * @since 4.3.0
	 *
	 * @param Closure|self $subquery Subquery closure or DB instance.
	 * @param string       $alias    Alias for the derived table.
	 *
	 * @throws InvalidArgumentException If subquery or alias is invalid.
	 *
	 * @return self
	 */
	public function from_sub( $subquery, string $alias ): self {
		if ( ! ( $subquery instanceof Closure || $subquery instanceof self ) ) {
			throw new InvalidArgumentException( 'Subquery must be a Closure or DB instance.' );
		}

		if ( ! self::is_valid_identifier( $alias ) ) {
			throw new InvalidArgumentException( 'Derived table alias must be a valid identifier.' );
		}

		return $this->from( $subquery, $alias );
	}

	// ─────────────────────────────────────────────
	// SELECT
	// ─────────────────────────────────────────────

	/**
	 * Set the columns to select.
	 *
	 * Accepts column names or associative arrays mapping aliases to subqueries:
	 *   select('id', 'name')
	 *   select(['id', 'total_orders' => function($q) { ... }])
	 * Multiple calls are additive.
	 *
	 * @since 4.2.0
	 *
	 * @param string|array ...$columns Column names or alias => subquery arrays.
	 *
	 * @return self
	 */
	public function select( ...$columns ): self {
		foreach ( $columns as $column ) {
			if ( is_array( $column ) ) {
				foreach ( $column as $key => $val ) {
					if ( is_string( $key ) && ( $val instanceof Closure || $val instanceof self ) ) {
						$this->select_sub( $val, $key );
					} elseif ( is_string( $key ) && is_string( $val ) ) {
						if ( ! self::is_valid_column( $val ) || ! self::is_valid_identifier( $key ) ) {
							throw new InvalidArgumentException( 'Invalid column or alias in select().' );
						}
						$this->columns[] = "{$val} AS {$key}";
					} elseif ( is_string( $val ) ) {
						$this->add_select_column( $val );
					}
				}
				continue;
			}

			if ( is_string( $column ) ) {
				$this->add_select_column( $column );
			}
		}

		return $this;
	}

	/**
	 * Internal helper to validate and append a single SELECT column or alias.
	 *
	 * @since 4.3.0
	 *
	 * @param string $column Column name or aliased expression.
	 *
	 * @return void
	 */
	private function add_select_column( string $column ): void {
		$aliased = self::parse_column_alias( $column );
		if ( null !== $aliased ) {
			$this->columns[] = $aliased;
		} elseif ( self::is_valid_column( $column ) ) {
			$this->columns[] = $column;
		}
	}

	/**
	 * Add a raw SELECT expression.
	 *
	 * Use this for aggregate functions, computed columns, or any SQL
	 * expression that cannot be represented as a simple column name.
	 *
	 * @since 4.2.0
	 *
	 * @param string $expression Raw SQL expression, e.g., 'COUNT(*) AS total'.
	 * @param array  $bindings   Optional values to bind to placeholders.
	 *
	 * @return self
	 */
	public function select_raw( string $expression, array $bindings = array() ): self {
		if ( ! empty( $bindings ) ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- User-provided expression with bindings.
			$expression = self::get_db()->prepare( $expression, $bindings );
		}

		$this->raw_columns[] = $expression;

		return $this;
	}

	/**
	 * Add a subquery expression to the SELECT columns.
	 *
	 * @since 4.3.0
	 *
	 * @param Closure|self $subquery Subquery closure or DB instance.
	 * @param string       $alias    Column alias.
	 *
	 * @return self
	 */
	public function select_sub( $subquery, string $alias ): self {
		if ( ! self::is_valid_identifier( $alias ) ) {
			throw new InvalidArgumentException( 'Invalid column alias in select_sub().' );
		}

		$sub_sql             = self::resolve_subquery( $subquery );
		$this->raw_columns[] = "({$sub_sql}) AS {$alias}";

		return $this;
	}

	/**
	 * Apply DISTINCT to the query.
	 *
	 * @since 4.2.0
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
	 * @since 4.2.0
	 *
	 * @param string $alias Alias for the primary table.
	 *
	 * @throws InvalidArgumentException If alias is invalid.
	 *
	 * @return self
	 */
	public function as( string $alias ): self {
		if ( ! self::is_valid_identifier( $alias ) ) {
			throw new InvalidArgumentException( 'Invalid table alias.' );
		}

		$this->alias = $alias;

		return $this;
	}

	// ─────────────────────────────────────────────
	// JOINs
	// ─────────────────────────────────────────────

	/**
	 * Add an INNER JOIN clause.
	 *
	 * Supports standard 4-argument syntax or a closure for multiple ON conditions.
	 *
	 * @since 4.2.0
	 *
	 * @param string          $table    Table to join (may include alias, e.g., 'users AS u').
	 * @param string|\Closure $first    First column in the ON condition, or a closure for multiple ON conditions.
	 * @param string          $operator Comparison operator (omitted if using closure).
	 * @param string          $second   Second column in the ON condition (omitted if using closure).
	 *
	 * @return self
	 */
	public function join( string $table, $first, string $operator = '', string $second = '' ): self {
		return $this->compile_join( 'INNER', $this->parse_join_table( $table ), $first, $operator, $second );
	}

	/**
	 * Add a LEFT JOIN clause.
	 *
	 * Supports standard 4-argument syntax or a closure for multiple ON conditions.
	 *
	 * @since 4.2.0
	 *
	 * @param string         $table    Table to join.
	 * @param string|Closure $first    First column or Closure.
	 * @param string         $operator Comparison operator.
	 * @param string         $second   Second column.
	 *
	 * @return self
	 */
	public function left_join( string $table, $first, string $operator = '', string $second = '' ): self {
		return $this->compile_join( 'LEFT', $this->parse_join_table( $table ), $first, $operator, $second );
	}

	/**
	 * Add a RIGHT JOIN clause.
	 *
	 * Supports standard 4-argument syntax or a closure for multiple ON conditions.
	 *
	 * @since 4.2.0
	 *
	 * @param string         $table    Table to join.
	 * @param string|Closure $first    First column or Closure.
	 * @param string         $operator Comparison operator.
	 * @param string         $second   Second column.
	 *
	 * @return self
	 */
	public function right_join( string $table, $first, string $operator = '', string $second = '' ): self {
		return $this->compile_join( 'RIGHT', $this->parse_join_table( $table ), $first, $operator, $second );
	}

	/**
	 * Add a JOIN subquery clause.
	 *
	 * Supports standard 4-argument syntax or a closure for multiple ON conditions.
	 *
	 * @since 4.3.0
	 *
	 * @param Closure|self   $subquery Subquery closure or DB instance.
	 * @param string         $alias    Alias for the subquery table.
	 * @param string|Closure $first    First column in ON condition, or Closure.
	 * @param string         $operator Comparison operator.
	 * @param string         $second   Second column in ON condition.
	 * @param string         $type     Join type (INNER, LEFT, RIGHT, CROSS). Default 'INNER'.
	 *
	 * @throws InvalidArgumentException If join type, alias, or subquery is invalid.
	 *
	 * @return self
	 */
	public function join_sub( $subquery, string $alias, $first, string $operator = '', string $second = '', string $type = 'INNER' ): self {
		return $this->add_subquery_join( $type, $subquery, $alias, $first, $operator, $second );
	}

	/**
	 * Add a LEFT JOIN subquery clause.
	 *
	 * Supports standard 4-argument syntax or a closure for multiple ON conditions.
	 *
	 * @since 4.3.0
	 *
	 * @param Closure|self   $subquery Subquery closure or DB instance.
	 * @param string         $alias    Alias for the subquery table.
	 * @param string|Closure $first    First column or Closure.
	 * @param string         $operator Comparison operator.
	 * @param string         $second   Second column.
	 *
	 * @return self
	 */
	public function left_join_sub( $subquery, string $alias, $first, string $operator = '', string $second = '' ): self {
		return $this->add_subquery_join( 'LEFT', $subquery, $alias, $first, $operator, $second );
	}

	/**
	 * Add a RIGHT JOIN subquery clause.
	 *
	 * Supports standard 4-argument syntax or a closure for multiple ON conditions.
	 *
	 * @since 4.3.0
	 *
	 * @param Closure|self   $subquery Subquery closure or DB instance.
	 * @param string         $alias    Alias for the subquery table.
	 * @param string|Closure $first    First column or Closure.
	 * @param string         $operator Comparison operator.
	 * @param string         $second   Second column.
	 *
	 * @return self
	 */
	public function right_join_sub( $subquery, string $alias, $first, string $operator = '', string $second = '' ): self {
		return $this->add_subquery_join( 'RIGHT', $subquery, $alias, $first, $operator, $second );
	}

	/**
	 * Add a CROSS JOIN subquery clause.
	 *
	 * @since 4.3.0
	 *
	 * @param Closure|self $subquery Subquery closure or DB instance.
	 * @param string       $alias    Alias for the subquery table.
	 *
	 * @throws InvalidArgumentException If alias or subquery is invalid.
	 *
	 * @return self
	 */
	public function cross_join_sub( $subquery, string $alias ): self {
		if ( ! self::is_valid_identifier( $alias ) ) {
			throw new InvalidArgumentException( 'Invalid alias in cross_join_sub().' );
		}

		$sub_sql       = self::resolve_subquery( $subquery );
		$this->joins[] = "CROSS JOIN ({$sub_sql}) AS {$alias}";

		return $this;
	}

	/**
	 * Internal method to parse and prepare a join table with optional alias.
	 *
	 * @since 4.2.0
	 *
	 * @param string $table Table name, may include alias e.g., 'users AS u'.
	 *
	 * @throws InvalidArgumentException If table name or alias is invalid.
	 *
	 * @return string
	 */
	private function parse_join_table( string $table ): string {
		$parts       = preg_split( '/\s+(?:AS\s+)?/i', trim( $table ), 2 );
		$table_name  = self::prepare_table_name( trim( $parts[0] ) );
		$table_alias = isset( $parts[1] ) ? trim( $parts[1] ) : '';

		if ( ! self::is_valid_identifier( $table_name ) ) {
			throw new InvalidArgumentException( 'Invalid join table name.' );
		}

		$table_expr = $table_name;
		if ( '' !== $table_alias ) {
			if ( ! self::is_valid_identifier( $table_alias ) ) {
				throw new InvalidArgumentException( 'Invalid join table alias.' );
			}
			$table_expr .= " AS {$table_alias}";
		}

		return $table_expr;
	}

	/**
	 * Create an anonymous class collector for closure-based joins.
	 *
	 * @since 4.2.0
	 *
	 * @return object
	 */
	private static function create_join_collector(): object {
		return new class() {

			/**
			 * Collected ON conditions.
			 *
			 * @var array
			 */
			public $conditions = array();

			/**
			 * Add an ON condition (AND).
			 *
			 * @param string      $first              First column.
			 * @param string      $operator_or_second Operator or second column if 2 args.
			 * @param string|null $second             Second column if 3 args.
			 *
			 * @return self
			 */
			public function on( string $first, string $operator_or_second, ?string $second = null ): self {
				if ( null === $second ) {
					return $this->add_on( $first, '=', $operator_or_second, 'AND' );
				}

				return $this->add_on( $first, $operator_or_second, $second, 'AND' );
			}

			/**
			 * Add an OR ON condition.
			 *
			 * @param string      $first              First column.
			 * @param string      $operator_or_second Operator or second column if 2 args.
			 * @param string|null $second             Second column if 3 args.
			 *
			 * @return self
			 */
			public function or_on( string $first, string $operator_or_second, ?string $second = null ): self {
				if ( null === $second ) {
					return $this->add_on( $first, '=', $operator_or_second, 'OR' );
				}

				return $this->add_on( $first, $operator_or_second, $second, 'OR' );
			}

			/**
			 * Internal method to validate and store an ON condition.
			 *
			 * @param string $first    First column.
			 * @param string $operator Comparison operator.
			 * @param string $second   Second column.
			 * @param string $boolean  Boolean connector (AND/OR).
			 *
			 * @return self
			 */
			private function add_on( string $first, string $operator, string $second, string $boolean ): self {
				$operator = strtoupper( $operator );
				if ( ! DB::is_valid_comparison_operator( $operator ) ) {
					throw new InvalidArgumentException( "Invalid JOIN ON operator '{$operator}'." );
				}

				if ( DB::is_valid_column( $first ) && DB::is_valid_column( $second ) ) {
					$this->conditions[] = array(
						'sql'     => "{$first} {$operator} {$second}",
						'boolean' => $boolean,
					);
				}

				return $this;
			}
		};
	}

	/**
	 * Internal method to compile a JOIN clause for a table or subquery expression.
	 *
	 * Validates join type, columns, and operator before adding to the query.
	 *
	 * @since 4.2.0
	 *
	 * @param string         $type       Join type (INNER, LEFT, RIGHT, CROSS).
	 * @param string         $table_expr Prepared table or subquery expression with optional alias.
	 * @param string|Closure $first      First column in ON condition, or Closure.
	 * @param string         $operator   Comparison operator.
	 * @param string         $second     Second column in ON condition.
	 *
	 * @throws InvalidArgumentException If join type or operator is invalid.
	 *
	 * @return self
	 */
	private function compile_join( string $type, string $table_expr, $first, string $operator, string $second ): self {
		$type = strtoupper( $type );

		$allowed_types = array( 'INNER', 'LEFT', 'RIGHT', 'CROSS' );
		if ( ! in_array( $type, $allowed_types, true ) ) {
			throw new InvalidArgumentException( "Invalid join type '{$type}'." );
		}

		if ( $first instanceof Closure ) {
			$collector = self::create_join_collector();
			$first( $collector );

			if ( ! empty( $collector->conditions ) ) {
				$on_parts = '';
				foreach ( $collector->conditions as $index => $condition ) {
					if ( 0 === $index ) {
						$on_parts .= $condition['sql'];
					} else {
						$on_parts .= " {$condition['boolean']} {$condition['sql']}";
					}
				}
				$this->joins[] = "{$type} JOIN {$table_expr} ON {$on_parts}";
			}

			return $this;
		}

		$operator = strtoupper( $operator );
		if ( ! self::is_valid_comparison_operator( $operator ) ) {
			throw new InvalidArgumentException( "Invalid JOIN operator '{$operator}'." );
		}

		if ( is_string( $first )
			&& self::is_valid_column( $first )
			&& self::is_valid_column( $second )
		) {
			$this->joins[] = "{$type} JOIN {$table_expr} ON {$first} {$operator} {$second}";
		}

		return $this;
	}

	/**
	 * Internal method to add a JOIN subquery clause.
	 *
	 * @since 4.3.0
	 *
	 * @param string         $type     Join type (INNER, LEFT, RIGHT, CROSS).
	 * @param Closure|self   $subquery Subquery closure or DB instance.
	 * @param string         $alias    Alias for the subquery table.
	 * @param string|Closure $first    First column in ON condition, or Closure.
	 * @param string         $operator Comparison operator.
	 * @param string         $second   Second column in ON condition.
	 *
	 * @throws InvalidArgumentException If join type, alias, or subquery is invalid.
	 *
	 * @return self
	 */
	private function add_subquery_join( string $type, $subquery, string $alias, $first, string $operator, string $second ): self {
		$type          = strtoupper( $type );
		$allowed_types = array( 'INNER', 'LEFT', 'RIGHT', 'CROSS' );
		if ( ! in_array( $type, $allowed_types, true ) ) {
			throw new InvalidArgumentException( "Invalid join type '{$type}'." );
		}

		if ( ! self::is_valid_identifier( $alias ) ) {
			throw new InvalidArgumentException( 'Invalid alias in join_sub().' );
		}

		$sub_sql = self::resolve_subquery( $subquery );

		return $this->compile_join( $type, "({$sub_sql}) AS {$alias}", $first, $operator, $second );
	}

	// ─────────────────────────────────────────────
	// WHERE
	// ─────────────────────────────────────────────

	/**
	 * Add a WHERE condition or multiple conditions.
	 *
	 * Supports single-condition syntax:
	 *   Two-argument form uses '=' operator: where('col', 'value').
	 *   Three-argument form uses custom operator: where('col', '>=', 5).
	 *   Passing null as value auto-converts to IS NULL / IS NOT NULL.
	 *
	 * Supports array syntax (conditions combined with AND):
	 *   Associative array: where(['status' => 'active', 'role' => 'admin'])
	 *   Numeric array: where([['status', '=', 'active'], ['role', '!=', 'guest']])
	 *
	 * @since 4.2.0
	 *
	 * @param string|array $column            Column name, or array of conditions.
	 * @param mixed        $operator_or_value Operator (if 3 args) or value (if 2 args).
	 * @param mixed        $value             Value when operator is specified.
	 *
	 * @return self
	 */
	public function where( $column, $operator_or_value = null, $value = null ): self {
		return $this->dispatch_where( $column, $operator_or_value, $value, 'AND', func_num_args() );
	}

	/**
	 * Add an OR WHERE condition or multiple conditions.
	 *
	 * @since 4.2.0
	 *
	 * @param string|array|Closure|self $column            Column name, array of conditions, or LHS subquery.
	 * @param mixed                     $operator_or_value Operator or value.
	 * @param mixed                     $value             Value when operator is specified.
	 *
	 * @return self
	 */
	public function or_where( $column, $operator_or_value = null, $value = null ): self {
		return $this->dispatch_where( $column, $operator_or_value, $value, 'OR', func_num_args() );
	}

	/**
	 * Add a WHERE condition comparing two columns.
	 *
	 * Useful for correlated subqueries where an inner subquery column is compared
	 * to an outer table column: where_column('o.user_id', '=', 'u.id').
	 *
	 * @since 4.3.0
	 *
	 * @param string      $first              First column name.
	 * @param string      $operator_or_second Operator (if 3 args) or second column (if 2 args).
	 * @param string|null $second             Second column name when operator is specified.
	 *
	 * @return self
	 */
	public function where_column( string $first, string $operator_or_second, ?string $second = null ): self {
		return $this->add_where_column( $first, $operator_or_second, $second, 'AND' );
	}

	/**
	 * Add an OR WHERE condition comparing two columns.
	 *
	 * @since 4.3.0
	 *
	 * @param string      $first              First column name.
	 * @param string      $operator_or_second Operator (if 3 args) or second column (if 2 args).
	 * @param string|null $second             Second column name when operator is specified.
	 *
	 * @return self
	 */
	public function or_where_column( string $first, string $operator_or_second, ?string $second = null ): self {
		return $this->add_where_column( $first, $operator_or_second, $second, 'OR' );
	}

	/**
	 * Add a WHERE IN condition or subquery.
	 *
	 * @since 4.2.0
	 * @since 4.3.0 Added subquery support.
	 *
	 * @param string             $column Column name.
	 * @param array|Closure|self $values Array of values, Closure, or DB subquery.
	 *
	 * @return self
	 */
	public function where_in( string $column, $values ): self {
		return $this->add_where_in( $column, $values, 'IN', 'AND' );
	}

	/**
	 * Add a WHERE NOT IN condition or subquery.
	 *
	 * @since 4.2.0
	 *
	 * @param string             $column Column name.
	 * @param array|Closure|self $values Array of values, Closure, or DB subquery.
	 *
	 * @return self
	 */
	public function where_not_in( string $column, $values ): self {
		return $this->add_where_in( $column, $values, 'NOT IN', 'AND' );
	}

	/**
	 * Add an OR WHERE IN condition or subquery.
	 *
	 * @since 4.2.0
	 *
	 * @param string             $column Column name.
	 * @param array|Closure|self $values Array of values, Closure, or DB subquery.
	 *
	 * @return self
	 */
	public function or_where_in( string $column, $values ): self {
		return $this->add_where_in( $column, $values, 'IN', 'OR' );
	}

	/**
	 * Add an OR WHERE NOT IN condition or subquery.
	 *
	 * @since 4.2.0
	 *
	 * @param string             $column Column name.
	 * @param array|Closure|self $values Array of values, Closure, or DB subquery.
	 *
	 * @return self
	 */
	public function or_where_not_in( string $column, $values ): self {
		return $this->add_where_in( $column, $values, 'NOT IN', 'OR' );
	}

	/**
	 * Add a WHERE IS NULL condition.
	 *
	 * @since 4.2.0
	 *
	 * @param string $column Column name.
	 *
	 * @return self
	 */
	public function where_null( string $column ): self {
		return $this->add_where_null( $column, 'IS NULL', 'AND' );
	}

	/**
	 * Add a WHERE IS NOT NULL condition.
	 *
	 * @since 4.2.0
	 *
	 * @param string $column Column name.
	 *
	 * @throws InvalidArgumentException If column name is invalid.
	 *
	 * @return self
	 */
	public function where_not_null( string $column ): self {
		return $this->add_where_null( $column, 'IS NOT NULL', 'AND' );
	}

	/**
	 * Add an OR WHERE IS NULL condition.
	 *
	 * @since 4.2.0
	 *
	 * @param string $column Column name.
	 *
	 * @throws InvalidArgumentException If column name is invalid.
	 *
	 * @return self
	 */
	public function or_where_null( string $column ): self {
		return $this->add_where_null( $column, 'IS NULL', 'OR' );
	}

	/**
	 * Add an OR WHERE IS NOT NULL condition.
	 *
	 * @since 4.2.0
	 *
	 * @param string $column Column name.
	 *
	 * @throws InvalidArgumentException If column name is invalid.
	 *
	 * @return self
	 */
	public function or_where_not_null( string $column ): self {
		return $this->add_where_null( $column, 'IS NOT NULL', 'OR' );
	}

	/**
	 * Internal helper to add an IS NULL / IS NOT NULL condition.
	 *
	 * @since 4.3.0
	 *
	 * @param string $column  Column name.
	 * @param string $null_op 'IS NULL' or 'IS NOT NULL'.
	 * @param string $boolean 'AND' or 'OR'.
	 *
	 * @throws InvalidArgumentException If column name is invalid.
	 *
	 * @return self
	 */
	private function add_where_null( string $column, string $null_op, string $boolean ): self {
		if ( ! self::is_valid_column( $column ) ) {
			throw new InvalidArgumentException( 'Invalid column name in where_null().' );
		}

		$this->wheres[] = array(
			'sql'     => "{$column} {$null_op}",
			'boolean' => $boolean,
		);

		return $this;
	}

	/**
	 * Add a WHERE EXISTS subquery condition.
	 *
	 * @since 4.3.0
	 *
	 * @param Closure|self $subquery Subquery closure or DB instance.
	 *
	 * @return self
	 */
	public function where_exists( $subquery ): self {
		return $this->add_where_exists( $subquery, 'EXISTS', 'AND' );
	}

	/**
	 * Add an OR WHERE EXISTS subquery condition.
	 *
	 * @since 4.3.0
	 *
	 * @param Closure|self $subquery Subquery closure or DB instance.
	 *
	 * @return self
	 */
	public function or_where_exists( $subquery ): self {
		return $this->add_where_exists( $subquery, 'EXISTS', 'OR' );
	}

	/**
	 * Add a WHERE NOT EXISTS subquery condition.
	 *
	 * @since 4.3.0
	 *
	 * @param Closure|self $subquery Subquery closure or DB instance.
	 *
	 * @return self
	 */
	public function where_not_exists( $subquery ): self {
		return $this->add_where_exists( $subquery, 'NOT EXISTS', 'AND' );
	}

	/**
	 * Add an OR WHERE NOT EXISTS subquery condition.
	 *
	 * @since 4.3.0
	 *
	 * @param Closure|self $subquery Subquery closure or DB instance.
	 *
	 * @return self
	 */
	public function or_where_not_exists( $subquery ): self {
		return $this->add_where_exists( $subquery, 'NOT EXISTS', 'OR' );
	}

	/**
	 * Add a WHERE BETWEEN condition.
	 *
	 * @since 4.2.0
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
	 * @since 4.2.0
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
	 * @since 4.2.0
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
	 * @since 4.2.0
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
	 * @since 4.2.0
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
	 * @since 4.2.0
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
	 * @since 4.2.0
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
	 * @since 4.2.0
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
	 * @since 4.2.0
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
	 * @since 4.2.0
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
	 * @since 4.2.0
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
	 * @since 4.2.0
	 *
	 * @param string $expression Raw SQL expression, e.g., 'COUNT(*) > %d'.
	 * @param array  $bindings   Optional values to bind.
	 *
	 * @return self
	 */
	public function having_raw( string $expression, array $bindings = array() ): self {
		if ( ! empty( $bindings ) ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Raw expression with user-controlled bindings.
			$expression = self::get_db()->prepare( $expression, $bindings );
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
	 * @since 4.2.0
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
	 * @since 4.2.0
	 *
	 * @param string $expression Raw SQL expression.
	 * @param array  $bindings   Optional values to bind.
	 *
	 * @return self
	 */
	public function order_by_raw( string $expression, array $bindings = array() ): self {
		if ( ! empty( $bindings ) ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$expression = self::get_db()->prepare( $expression, $bindings );
		}

		$this->order_by_clauses[] = $expression;

		return $this;
	}

	/**
	 * Order results by the given column descending (default 'id').
	 *
	 * @since 4.2.0
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
	 * @since 4.2.0
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
	 * @since 4.2.0
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
	 * @since 4.2.0
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
	// Row Locking
	// ─────────────────────────────────────────────

	/**
	 * Lock the selected rows for update ('FOR UPDATE').
	 *
	 * Appends 'FOR UPDATE' to the query. Locking is held within an active
	 * transaction. On database engines that do not support row locking
	 * (such as MyISAM or SQLite), the clause is safely omitted during execution.
	 *
	 * @since 4.2.0
	 *
	 * @return self
	 */
	public function lock_for_update(): self {
		$this->lock = 'FOR UPDATE';

		return $this;
	}

	/**
	 * Check whether row locking is applied to the current query instance.
	 *
	 * @since 4.2.0
	 *
	 * @return bool True if lock_for_update has been applied.
	 */
	public function is_locked(): bool {
		return ! empty( $this->lock );
	}

	// ─────────────────────────────────────────────
	// Terminal Methods (execute & return results)
	// ─────────────────────────────────────────────

	/**
	 * Execute the query and return all matching rows.
	 *
	 * @since 4.2.0
	 *
	 * @param string $output Output type: OBJECT, ARRAY_A, ARRAY_N.
	 *
	 * @return array Array of result rows.
	 */
	public function get( string $output = 'OBJECT' ): array {
		$sql = $this->build_select_query();

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- SQL built with prepare_value/prepare calls.
		$results = self::get_db()->get_results( $sql, $output );

		return is_array( $results ) ? $results : array();
	}

	/**
	 * Execute the query and return the first matching row.
	 *
	 * @since 4.2.0
	 *
	 * @param string $output Output type: OBJECT, ARRAY_A, ARRAY_N.
	 *
	 * @return object|array|null Single row or null if not found.
	 */
	public function first( string $output = 'OBJECT' ) {
		// Temporarily set limit to 1.
		$saved_limit       = $this->limit_value;
		$this->limit_value = 1;

		$sql = $this->build_select_query();

		$this->limit_value = $saved_limit;

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return self::get_db()->get_row( $sql, $output );
	}

	/**
	 * Get the count of rows matching the query.
	 *
	 * @since 4.2.0
	 *
	 * @param string $column Column to count, default '*'.
	 *
	 * @return int Total matching row count.
	 */
	public function count( string $column = '*' ): int {
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
		$saved_lock     = $this->lock;

		// Override for count.
		$this->columns = array();
		$this->lock    = '';

		if ( $saved_distinct ) {
			if ( '*' !== $column ) {
				$this->raw_columns = array( "COUNT(DISTINCT {$column}) AS aggregate" );
			} elseif ( 1 === count( $saved_columns ) && self::is_valid_column( $saved_columns[0] ) && '*' !== $saved_columns[0] ) {
				$this->raw_columns = array( "COUNT(DISTINCT {$saved_columns[0]}) AS aggregate" );
			} else {
				$this->raw_columns = array( "COUNT({$column}) AS aggregate" );
			}
		} else {
			$this->raw_columns = array( "COUNT({$column}) AS aggregate" );
		}

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
		$this->lock             = $saved_lock;

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return (int) self::get_db()->get_var( $sql );
	}

	/**
	 * Execute the query with pagination.
	 *
	 * @since 4.2.0
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
		$current_page = max( 1, $current_page );
		$per_page     = max( 1, $per_page );

		$this->limit_value  = $per_page;
		$this->offset_value = ( $current_page - 1 ) * $per_page;

		$sql = $this->build_select_query( true );

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$results = self::get_db()->get_results( $sql, $output );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$total_count = (int) self::get_db()->get_var( 'SELECT FOUND_ROWS()' );
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
	 * @since 4.2.0
	 *
	 * @param string $column Column name to retrieve.
	 *
	 * @return mixed|null The column value or null if not found.
	 */
	public function value( string $column ) {
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
		return self::get_db()->get_var( $sql );
	}

	/**
	 * Retrieve a list of values of a single column.
	 *
	 * If a key column is provided, the resulting array will use that column's
	 * values as array keys.
	 *
	 * @since 4.2.0
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
	 * @since 4.2.0
	 *
	 * @return bool True if at least one matching record exists.
	 */
	public function exists(): bool {
		return null !== $this->first();
	}

	/**
	 * Find a single record by its primary key.
	 *
	 * @since 4.2.0
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
	 * @since 4.2.0
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
	 * @since 4.2.0
	 *
	 * @param array $data   Assoc array of column => value pairs.
	 * @param array $format Optional format specifiers (%s, %d, %f) for each value.
	 *
	 * @throws DatabaseException If a database error occurs.
	 *
	 * @return int Inserted row ID.
	 */
	public function insert( array $data, array $format = array() ): int {
		if ( $this->is_derived ) {
			throw new InvalidArgumentException( 'Insert operations are not allowed on a derived table.' );
		}

		$db = self::get_db();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$result = $db->insert( $this->table, $data, ! empty( $format ) ? $format : null );

		if ( false === $result ) {
			throw new DatabaseException( esc_html( $db->last_error ) );
		}

		return $db->insert_id;
	}

	/**
	 * Insert multiple rows in a single query.
	 *
	 * All rows must have the same columns. Values are individually escaped
	 * using $wpdb->prepare(). NULL values are inserted as SQL NULL.
	 *
	 * @since 4.2.0
	 *
	 * @param array $rows Array of assoc arrays, each representing a row.
	 *
	 * @throws DatabaseException If a database error occurs.
	 *
	 * @return bool True on success.
	 */
	public function insert_multiple( array $rows ): bool {
		if ( $this->is_derived ) {
			throw new InvalidArgumentException( 'Insert operations are not allowed on a derived table.' );
		}

		if ( empty( $rows ) ) {
			return false;
		}

		$first_row  = reset( $rows );
		$columns    = is_array( $first_row ) ? array_keys( $first_row ) : array();
		$valid_cols = array();
		foreach ( $columns as $col ) {
			if ( self::is_valid_identifier( $col ) ) {
				$valid_cols[] = $col;
			}
		}

		if ( empty( $valid_cols ) ) {
			return false;
		}

		$column_list = implode( ', ', $valid_cols );

		$value_groups = array();
		foreach ( $rows as $row ) {
			$row_parts = array();
			foreach ( $valid_cols as $col ) {
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

		$db = self::get_db();

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Values prepared inline via prepare_value().
		$result = $db->query( $sql );

		if ( false === $result ) {
			throw new DatabaseException( esc_html( $db->last_error ) );
		}

		return true;
	}

	/**
	 * Update rows matching the accumulated WHERE conditions.
	 *
	 * All SET values are individually escaped using $wpdb->prepare().
	 * NULL values produce `SET column = NULL`.
	 *
	 * @since 4.2.0
	 *
	 * @param array $data Assoc array of column => value pairs to update.
	 *
	 * @throws DatabaseException If a database error occurs.
	 *
	 * @return bool True on success.
	 */
	public function update( array $data ): bool {
		if ( $this->is_derived ) {
			throw new InvalidArgumentException( 'Update operations are not allowed on a derived table.' );
		}

		if ( empty( $data ) ) {
			return false;
		}

		$set_parts = array();
		foreach ( $data as $column => $value ) {
			if ( ! self::is_valid_identifier( $column ) ) {
				continue;
			}

			if ( is_null( $value ) ) {
				$set_parts[] = "{$column} = NULL";
			} else {
				$prepared    = self::prepare_value( $value );
				$set_parts[] = "{$column} = {$prepared}";
			}
		}

		if ( empty( $set_parts ) ) {
			return false;
		}

		$set_clause   = implode( ', ', $set_parts );
		$where_clause = $this->build_where_sql();

		$table_name = ! empty( $this->alias ) ? "{$this->table} AS {$this->alias}" : $this->table;
		$sql        = "UPDATE {$table_name} SET {$set_clause}";
		if ( ! empty( $where_clause ) ) {
			$sql .= " WHERE {$where_clause}";
		}

		$db = self::get_db();

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Values prepared inline.
		$result = $db->query( $sql );

		if ( false === $result ) {
			throw new DatabaseException( esc_html( $db->last_error ) );
		}

		return true;
	}

	/**
	 * Delete rows matching the accumulated WHERE conditions.
	 *
	 * WARNING: Calling delete() without any where() conditions will
	 * delete ALL rows from the table.
	 *
	 * @since 4.2.0
	 *
	 * @throws DatabaseException If a database error occurs.
	 *
	 * @return int Number of affected rows.
	 */
	public function delete(): int {
		if ( $this->is_derived ) {
			throw new InvalidArgumentException( 'Delete operations are not allowed on a derived table.' );
		}

		$where_clause = $this->build_where_sql();

		if ( ! empty( $this->alias ) ) {
			$sql = "DELETE {$this->alias} FROM {$this->table} AS {$this->alias}";
		} else {
			$sql = "DELETE FROM {$this->table}";
		}

		if ( ! empty( $where_clause ) ) {
			$sql .= " WHERE {$where_clause}";
		}

		$db = self::get_db();

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- WHERE values prepared inline.
		$result = $db->query( $sql );

		if ( false === $result ) {
			throw new DatabaseException( esc_html( $db->last_error ) );
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
	 * @since 4.2.0
	 *
	 * @return string The generated SQL string.
	 */
	public function to_sql(): string {
		$sql = $this->build_select_query();
		$db  = self::get_db();

		if ( isset( $db ) && method_exists( $db, 'remove_placeholder_escape' ) ) {
			return $db->remove_placeholder_escape( $sql );
		}

		return $sql;
	}

	// ─────────────────────────────────────────────
	// Internal WHERE helpers
	// ─────────────────────────────────────────────

	/**
	 * Internal method to add array-based WHERE conditions.
	 *
	 * Supports both associative column-value pairs and numeric array triples/tuples.
	 *
	 * @since 4.3.0
	 *
	 * @param array  $conditions Array of conditions.
	 * @param string $boolean    Boolean connector (AND or OR).
	 *
	 * @return self
	 */
	private function add_array_where( array $conditions, string $boolean = 'AND' ): self {
		if ( empty( $conditions ) ) {
			return $this;
		}

		$saved_wheres = $this->wheres;
		$this->wheres = array();

		foreach ( $conditions as $key => $val ) {
			if ( is_string( $key ) ) {
				if ( is_array( $val ) ) {
					$this->add_where_in( $key, $val, 'IN', 'AND' );
				} else {
					// Associative array: column => value.
					$this->add_where( $key, $val, null, 'AND', true );
				}
			} elseif ( is_array( $val ) ) {
				// Numeric array: ['col', 'val'] or ['col', 'op', 'val'].
				$items = array_values( $val );
				$count = count( $items );

				if ( 2 === $count && is_string( $items[0] ) ) {
					if ( is_array( $items[1] ) ) {
						$this->add_where_in( $items[0], $items[1], 'IN', 'AND' );
					} else {
						$this->add_where( $items[0], $items[1], null, 'AND', true );
					}
				} elseif ( 3 === $count && is_string( $items[0] ) ) {
					$this->add_where( $items[0], $items[1], $items[2], 'AND', false );
				} else {
					$this->wheres = $saved_wheres;
					throw new InvalidArgumentException( 'Invalid condition tuple in where() array.' );
				}
			} else {
				$this->wheres = $saved_wheres;
				throw new InvalidArgumentException( 'Invalid condition format in where() array.' );
			}
		}

		$group_wheres = $this->wheres;
		$this->wheres = $saved_wheres;

		if ( empty( $group_wheres ) ) {
			return $this;
		}

		if ( 'OR' === $boolean && count( $group_wheres ) > 1 ) {
			$parts = array();
			foreach ( $group_wheres as $index => $where ) {
				$parts[] = 0 === $index ? $where['sql'] : "{$where['boolean']} {$where['sql']}";
			}
			$this->wheres[] = array(
				'sql'     => '(' . implode( ' ', $parts ) . ')',
				'boolean' => 'OR',
			);
		} else {
			foreach ( $group_wheres as $index => $where ) {
				$this->wheres[] = array(
					'sql'     => $where['sql'],
					'boolean' => 0 === $index ? $boolean : 'AND',
				);
			}
		}

		return $this;
	}

	/**
	 * Internal dispatcher for where() and or_where().
	 *
	 * @since 4.3.0
	 *
	 * @param mixed  $column            Column name, array of conditions, Closure, or LHS subquery.
	 * @param mixed  $operator_or_value Operator or value.
	 * @param mixed  $value             Value when operator is specified.
	 * @param string $boolean           AND or OR.
	 * @param int    $arg_count         Number of arguments passed to the caller.
	 *
	 * @throws InvalidArgumentException If arguments are invalid or closure produces no conditions.
	 *
	 * @return self
	 */
	private function dispatch_where( $column, $operator_or_value, $value, string $boolean, int $arg_count ): self {
		if ( is_array( $column ) ) {
			return $this->add_array_where( $column, $boolean );
		}

		if ( $column instanceof Closure ) {
			if ( $arg_count < 2 ) {
				$sub = new self();
				$column( $sub );
				$group_sql = $sub->build_where_sql();
				if ( '' === $group_sql ) {
					throw new InvalidArgumentException( 'Where closure produced no conditions.' );
				}

				$this->wheres[] = array(
					'sql'     => "({$group_sql})",
					'boolean' => $boolean,
				);

				return $this;
			}

			return $this->add_where_sub( $column, $operator_or_value, $value, $boolean, $arg_count < 3 );
		}

		if ( $column instanceof self ) {
			if ( $arg_count < 2 ) {
				throw new InvalidArgumentException( 'Subquery in where() requires an operator and value.' );
			}

			return $this->add_where_sub( $column, $operator_or_value, $value, $boolean, $arg_count < 3 );
		}

		if ( ! is_string( $column ) ) {
			throw new InvalidArgumentException( 'Column name must be a string, array, or Closure.' );
		}

		if ( $arg_count < 2 ) {
			throw new InvalidArgumentException( 'where() requires at least 2 arguments when column is a string.' );
		}

		return $this->add_where( $column, $operator_or_value, $value, $boolean, $arg_count < 3 );
	}

	/**
	 * Internal method to add a WHERE condition with a subquery on the left-hand side.
	 *
	 * @since 4.3.0
	 *
	 * @param Closure|self $subquery          Subquery closure or DB instance.
	 * @param mixed        $operator_or_value Operator or value.
	 * @param mixed        $value             Value when operator is specified.
	 * @param string       $boolean           AND or OR.
	 * @param bool         $is_two_arg        Whether this is a two-argument call.
	 *
	 * @return self
	 */
	private function add_where_sub( $subquery, $operator_or_value, $value, string $boolean, bool $is_two_arg ): self {
		$sub_sql = self::resolve_subquery( $subquery );

		return $this->append_where_condition( "({$sub_sql})", $operator_or_value, $value, $boolean, $is_two_arg );
	}

	/**
	 * Internal method to add a basic WHERE condition.
	 *
	 * Handles two-argument (default '=') and three-argument (custom operator)
	 * forms. Null values are auto-converted to IS NULL / IS NOT NULL.
	 *
	 * @since 4.2.0
	 *
	 * @param string $column           Column name.
	 * @param mixed  $operator_or_value Operator or value.
	 * @param mixed  $value            Value (null when in two-arg form).
	 * @param string $boolean          AND or OR.
	 * @param bool   $is_two_arg       Whether this is a two-argument call.
	 *
	 * @throws InvalidArgumentException If column name is invalid.
	 *
	 * @return self
	 */
	private function add_where( string $column, $operator_or_value, $value, string $boolean, bool $is_two_arg ): self {
		if ( ! self::is_valid_column( $column ) ) {
			throw new InvalidArgumentException( 'Invalid column name in where().' );
		}

		return $this->append_where_condition( $column, $operator_or_value, $value, $boolean, $is_two_arg );
	}

	/**
	 * Shared helper to format and append a WHERE condition for a validated LHS expression.
	 *
	 * @since 4.3.0
	 *
	 * @param string $lhs              Validated column or parenthesized subquery.
	 * @param mixed  $operator_or_value Operator or value.
	 * @param mixed  $value            Value (null when in two-arg form).
	 * @param string $boolean          AND or OR.
	 * @param bool   $is_two_arg       Whether this is a two-argument call.
	 *
	 * @throws InvalidArgumentException If operator or value is invalid.
	 *
	 * @return self
	 */
	private function append_where_condition( string $lhs, $operator_or_value, $value, string $boolean, bool $is_two_arg ): self {
		// Determine operator and value.
		if ( $is_two_arg ) {
			$value    = $operator_or_value;
			$operator = '=';
		} else {
			$operator = strtoupper( (string) $operator_or_value );
		}

		// Handle subquery value.
		if ( $value instanceof Closure || $value instanceof self ) {
			$allowed_subquery_ops = array_merge( self::$comparison_operators, array( 'IN', 'NOT IN' ) );
			if ( ! in_array( $operator, $allowed_subquery_ops, true ) ) {
				throw new InvalidArgumentException( "Invalid operator '{$operator}' for subquery comparison." );
			}

			$allow_limit    = ! in_array( $operator, array( 'IN', 'NOT IN' ), true );
			$sub_sql        = self::resolve_subquery( $value, $allow_limit );
			$this->wheres[] = array(
				'sql'     => "{$lhs} {$operator} ({$sub_sql})",
				'boolean' => $boolean,
			);

			return $this;
		}

		if ( is_array( $value ) ) {
			throw new InvalidArgumentException( 'Array values are not supported in scalar where() comparisons; use where_in() instead.' );
		}

		// Handle null values → IS NULL / IS NOT NULL.
		if ( is_null( $value ) ) {
			$null_op        = in_array( $operator, array( '!=', '<>' ), true ) ? 'IS NOT' : 'IS';
			$this->wheres[] = array(
				'sql'     => "{$lhs} {$null_op} NULL",
				'boolean' => $boolean,
			);

			return $this;
		}

		if ( ! self::is_valid_operator( $operator ) ) {
			throw new InvalidArgumentException( "Invalid operator '{$operator}'." );
		}

		$prepared_value = self::prepare_value( $value );

		$this->wheres[] = array(
			'sql'     => "{$lhs} {$operator} {$prepared_value}",
			'boolean' => $boolean,
		);

		return $this;
	}

	/**
	 * Internal method to add WHERE IN / NOT IN conditions or subqueries.
	 *
	 * @since 4.2.0
	 *
	 * @param string             $column  Column name.
	 * @param array|Closure|self $values  Array of values, Closure, or DB subquery.
	 * @param string             $type    IN or NOT IN.
	 * @param string             $boolean AND or OR.
	 *
	 * @throws InvalidArgumentException If column, values, or subquery is invalid.
	 *
	 * @return self
	 */
	private function add_where_in( string $column, $values, string $type, string $boolean ): self {
		if ( ! self::is_valid_column( $column ) ) {
			throw new InvalidArgumentException( 'Invalid column name in where_in().' );
		}

		if ( $values instanceof Closure || $values instanceof self ) {
			$sub_sql        = self::resolve_subquery( $values, false );
			$this->wheres[] = array(
				'sql'     => "{$column} {$type} ({$sub_sql})",
				'boolean' => $boolean,
			);

			return $this;
		}

		if ( ! is_array( $values ) || empty( $values ) ) {
			throw new InvalidArgumentException( 'where_in() values must be a non-empty array or subquery.' );
		}

		$escaped_values = self::prepare_in_clause( $values );

		$this->wheres[] = array(
			'sql'     => "{$column} {$type} ({$escaped_values})",
			'boolean' => $boolean,
		);

		return $this;
	}

	/**
	 * Internal method to add WHERE EXISTS / NOT EXISTS condition.
	 *
	 * @since 4.3.0
	 *
	 * @param Closure|self $subquery Subquery closure or DB instance.
	 * @param string       $type     EXISTS or NOT EXISTS.
	 * @param string       $boolean  AND or OR.
	 *
	 * @throws InvalidArgumentException If subquery is invalid or empty.
	 *
	 * @return self
	 */
	private function add_where_exists( $subquery, string $type, string $boolean ): self {
		$sub_sql        = self::resolve_subquery( $subquery );
		$this->wheres[] = array(
			'sql'     => "{$type} ({$sub_sql})",
			'boolean' => $boolean,
		);

		return $this;
	}

	/**
	 * Internal method to add a WHERE condition comparing two columns.
	 *
	 * @since 4.3.0
	 *
	 * @param string      $first              First column name.
	 * @param string      $operator_or_second Operator or second column if 2 args.
	 * @param string|null $second             Second column name if 3 args.
	 * @param string      $boolean            AND or OR.
	 *
	 * @throws InvalidArgumentException If columns or operator are invalid.
	 *
	 * @return self
	 */
	private function add_where_column( string $first, string $operator_or_second, ?string $second, string $boolean ): self {
		if ( null === $second ) {
			$operator = '=';
			$second   = $operator_or_second;
		} else {
			$operator = strtoupper( $operator_or_second );
		}

		if ( ! self::is_valid_column( $first )
			|| ! self::is_valid_comparison_operator( $operator )
			|| ! self::is_valid_column( $second )
		) {
			throw new InvalidArgumentException( 'Invalid column or operator in where_column().' );
		}

		$this->wheres[] = array(
			'sql'     => "{$first} {$operator} {$second}",
			'boolean' => $boolean,
		);

		return $this;
	}

	/**
	 * Internal method for BETWEEN / NOT BETWEEN conditions.
	 *
	 * @since 4.2.0
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
	 * @since 4.2.0
	 *
	 * @param string $column  Column name.
	 * @param string $value   Search term.
	 * @param string $type    LIKE or NOT LIKE.
	 * @param string $boolean AND or OR.
	 *
	 * @return self
	 */
	private function add_where_like( string $column, string $value, string $type, string $boolean ): self {
		if ( ! self::is_valid_column( $column ) ) {
			return $this;
		}

		$db = self::get_db();

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Column validated, type is hardcoded.
		$sql = $db->prepare( "{$column} {$type} %s", '%' . $db->esc_like( $value ) . '%' );

		$this->wheres[] = array(
			'sql'     => $sql,
			'boolean' => $boolean,
		);

		return $this;
	}

	/**
	 * Internal method for raw WHERE expressions.
	 *
	 * @since 4.2.0
	 *
	 * @param string $expression Raw SQL expression.
	 * @param array  $bindings   Values to bind.
	 * @param string $boolean    AND or OR.
	 *
	 * @return self
	 */
	private function add_where_raw( string $expression, array $bindings, string $boolean ): self {
		if ( ! empty( $bindings ) ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Raw expression with user-controlled bindings.
			$expression = self::get_db()->prepare( $expression, $bindings );
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
	 * @since 4.2.0
	 *
	 * @param bool $calc_found_rows Whether to include SQL_CALC_FOUND_ROWS.
	 *
	 * @return string The complete SQL query.
	 */
	private function build_select_query( bool $calc_found_rows = false ): string {
		$select = $this->build_select_sql();
		$from   = $this->build_from_sql();

		if ( empty( $from ) && ( empty( $this->raw_columns ) || ! empty( $this->columns ) || ! empty( $this->joins ) || ! empty( $this->wheres ) || ! empty( $this->group_by_columns ) || ! empty( $this->havings ) || ! empty( $this->order_by_clauses ) ) ) {
			throw new InvalidArgumentException( 'Query requires a FROM table unless selecting raw expressions only.' );
		}

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
		if ( ! empty( $from ) ) {
			$parts[] = "FROM {$from}";
		}

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

		if ( ! empty( $this->lock ) ) {
			$parts[] = $this->lock;
		}

		return implode( ' ', $parts );
	}

	/**
	 * Build SELECT column list.
	 *
	 * @since 4.2.0
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
	 * @since 4.2.0
	 *
	 * @return string
	 */
	private function build_from_sql(): string {
		if ( $this->is_derived && empty( $this->alias ) ) {
			throw new InvalidArgumentException( 'Derived table subquery must have an alias.' );
		}

		if ( ! empty( $this->alias ) ) {
			return "{$this->table} AS {$this->alias}";
		}

		return $this->table;
	}

	/**
	 * Build JOIN clauses.
	 *
	 * @since 4.2.0
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
	 * @since 4.2.0
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
	 * @since 4.2.0
	 *
	 * @return string
	 */
	private function build_group_by_sql(): string {
		return implode( ', ', $this->group_by_columns );
	}

	/**
	 * Build HAVING clause (without the HAVING keyword).
	 *
	 * @since 4.2.0
	 *
	 * @return string
	 */
	private function build_having_sql(): string {
		return implode( ' AND ', $this->havings );
	}

	/**
	 * Build ORDER BY clause (without the ORDER BY keywords).
	 *
	 * @since 4.2.0
	 *
	 * @return string
	 */
	private function build_order_by_sql(): string {
		return implode( ', ', $this->order_by_clauses );
	}

	/**
	 * Build LIMIT/OFFSET clause.
	 *
	 * @since 4.2.0
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
	 * Parse a column expression that may contain an alias.
	 *
	 * Supports: 'col AS alias', 'col as alias', 'col alias'.
	 * Returns normalized 'col AS alias', or null if invalid.
	 *
	 * @since 4.2.0
	 *
	 * @param string $expression Column expression.
	 *
	 * @return string|null Normalized 'column AS alias' or null.
	 */
	public static function parse_column_alias( string $expression ): ?string {
		$parts = preg_split( '/\s+(?:AS\s+)?/i', trim( $expression ), 2 );

		if ( 2 !== count( $parts ) ) {
			return null;
		}

		$column = trim( $parts[0] );
		$alias  = trim( $parts[1] );

		if ( self::is_valid_column( $column ) && self::is_valid_identifier( $alias ) ) {
			return "{$column} AS {$alias}";
		}

		return null;
	}

	/**
	 * Validate a SQL column identifier.
	 *
	 * Allows: column, table.column, t.*, *, _col, col123, etc.
	 *
	 * @since 4.2.0
	 *
	 * @param string $column Column identifier.
	 *
	 * @return bool True if valid.
	 */
	public static function is_valid_column( string $column ): bool {
		return (bool) preg_match( '/^[A-Za-z_*][A-Za-z0-9_.*]*$/', $column );
	}

	/**
	 * Validate a SQL identifier (table name, alias).
	 *
	 * @since 4.2.0
	 *
	 * @param string $identifier Identifier to validate.
	 *
	 * @return bool True if valid.
	 */
	public static function is_valid_identifier( string $identifier ): bool {
		return (bool) preg_match( '/^[A-Za-z_][A-Za-z0-9_]*$/', $identifier );
	}

	/**
	 * Check whether a comparison operator is supported.
	 *
	 * @since 4.2.0
	 *
	 * @param string $operator SQL operator.
	 *
	 * @return bool True if the operator is valid.
	 */
	public static function is_valid_operator( string $operator ): bool {
		return in_array( strtoupper( $operator ), self::$valid_operators, true );
	}

	/**
	 * Check whether a comparison operator is valid for column-to-column comparisons.
	 *
	 * @since 4.3.0
	 *
	 * @param string $operator SQL operator.
	 *
	 * @return bool True if the operator is valid for column comparisons.
	 */
	public static function is_valid_comparison_operator( string $operator ): bool {
		return in_array( strtoupper( $operator ), self::$comparison_operators, true );
	}

	/**
	 * Get the wpdb format placeholder for a value based on its PHP type.
	 *
	 * @since 4.2.0
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
	 * Resolve a subquery from a Closure or DB instance into a SQL string.
	 *
	 * Keeps wpdb placeholder escapes intact via build_select_query() so
	 * outer prepare() calls or wpdb->query() cannot misinterpret '%' in values.
	 *
	 * @since 4.3.0
	 *
	 * @param mixed $query       Closure or DB instance.
	 * @param bool  $allow_limit Whether LIMIT is allowed inside this subquery.
	 *
	 * @throws InvalidArgumentException If subquery is invalid, empty, or uses unsupported clauses.
	 *
	 * @return string The compiled SQL string.
	 */
	private static function resolve_subquery( $query, bool $allow_limit = true ): string {
		if ( $query instanceof Closure ) {
			$sub    = new self();
			$result = $query( $sub );
			$sub    = ( $result instanceof self ) ? $result : $sub;
		} elseif ( $query instanceof self ) {
			$sub = clone $query;
		} else {
			throw new InvalidArgumentException( 'Subquery must be a Closure or DB instance.' );
		}

		if ( empty( $sub->table ) && empty( $sub->raw_columns ) ) {
			throw new InvalidArgumentException( 'Subquery must define a FROM table or raw select expression.' );
		}

		if ( ! $allow_limit && null !== $sub->limit_value ) {
			throw new InvalidArgumentException( 'LIMIT is not supported inside IN/NOT IN subqueries.' );
		}

		$sub->lock = '';

		return $sub->build_select_query();
	}

	/**
	 * Prepare a single value for safe SQL inclusion using $wpdb->prepare().
	 *
	 * Numbers and booleans are formatted accordingly; strings are escaped and quoted.
	 * NULL is returned as the unquoted literal string 'NULL'.
	 *
	 * @since 4.2.0
	 *
	 * @param mixed $value Value to prepare.
	 *
	 * @return string Escaped SQL value literal.
	 */
	public static function prepare_value( $value ): string {
		if ( is_null( $value ) ) {
			return 'NULL';
		}

		$db = self::get_db();

		if ( is_bool( $value ) ) {
			return $db->prepare( '%d', $value ? 1 : 0 );
		}

		if ( is_int( $value ) ) {
			return $db->prepare( '%d', $value );
		}

		if ( is_float( $value ) ) {
			$str_val = (string) $value;
			$dot_pos = strpos( $str_val, '.' );
			if ( false !== $dot_pos ) {
				$decimals = strlen( substr( $str_val, $dot_pos + 1 ) );
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnsupportedPlaceholder, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
				return $db->prepare( '%.' . $decimals . 'f', $value );
			}
			return $db->prepare( '%f', $value );
		}

		return $db->prepare( '%s', $value );
	}

	/**
	 * Make sanitized SQL IN clause values from an array.
	 *
	 * @since 4.2.0
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
	 * @since 4.2.0
	 *
	 * @param string $table Table name.
	 *
	 * @return string Prefixed table name.
	 */
	public static function prepare_table_name( string $table ): string {
		$db     = self::get_db();
		$prefix = isset( $db->prefix ) ? $db->prefix : '';
		if ( ! empty( $prefix ) && 0 !== strpos( $table, $prefix ) ) {
			return $prefix . $table;
		}

		return $table;
	}

	/**
	 * Get the WordPress database table prefix.
	 *
	 * @since 4.2.0
	 *
	 * @return string Table prefix.
	 */
	public static function get_table_prefix(): string {
		$db = self::get_db();

		return isset( $db->prefix ) ? $db->prefix : '';
	}

	/**
	 * Get the last executed query from $wpdb.
	 *
	 * @since 4.2.0
	 *
	 * @return string Last executed SQL query.
	 */
	public static function get_last_query(): string {
		$db = self::get_db();

		return isset( $db->last_query ) ? $db->last_query : '';
	}

	// ─────────────────────────────────────────────
	// Schema Validation Utilities
	// ─────────────────────────────────────────────

	/**
	 * Check whether a table exists in the database.
	 *
	 * Handles both prefixed and non-prefixed table names safely without
	 * SQL injection vulnerability.
	 *
	 * @since 4.2.0
	 *
	 * @param string $table Table name to check.
	 *
	 * @return bool True if table exists, false otherwise.
	 */
	public static function has_table( string $table ): bool {
		return null !== self::resolve_existing_table( $table );
	}

	/**
	 * Check whether a specific column exists in a table.
	 *
	 * @since 4.2.0
	 *
	 * @param string $table  Table name.
	 * @param string $column Column name to check.
	 *
	 * @return bool True if column exists, false otherwise.
	 */
	public static function has_column( string $table, string $column ): bool {
		return self::has_columns( $table, array( $column ) );
	}

	/**
	 * Check whether all specified columns exist in a table.
	 *
	 * @since 4.2.0
	 *
	 * @param string $table   Table name.
	 * @param array  $columns Array of column names to check.
	 *
	 * @return bool True if all columns exist, false otherwise.
	 */
	public static function has_columns( string $table, array $columns ): bool {
		if ( empty( $columns ) ) {
			return false;
		}

		$table_name = self::resolve_existing_table( $table );
		if ( null === $table_name ) {
			return false;
		}

		foreach ( $columns as $column ) {
			if ( ! is_string( $column ) || ! self::is_valid_identifier( $column ) ) {
				return false;
			}
		}

		$db = self::get_db();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$existing = $db->get_col( "SHOW COLUMNS FROM `{$table_name}`" );
		if ( empty( $existing ) ) {
			return false;
		}

		$existing = array_map( 'strtolower', $existing );
		$columns  = array_map( 'strtolower', $columns );

		return empty( array_diff( $columns, $existing ) );
	}

	/**
	 * Internal helper to resolve an existing table name in the database.
	 *
	 * @since 4.2.0
	 *
	 * @param string $table Table name identifier.
	 *
	 * @return string|null Resolved existing table name or null if invalid or not found.
	 */
	private static function resolve_existing_table( string $table ): ?string {
		$table = trim( $table );
		if ( '' === $table ) {
			return null;
		}

		// Check prefixed table name first.
		$prefixed = self::prepare_table_name( $table );
		if ( self::is_valid_identifier( $prefixed ) && self::table_exists_in_db( $prefixed ) ) {
			return $prefixed;
		}

		return null;
	}

	/**
	 * Internal helper to query MySQL for table existence.
	 *
	 * @since 4.2.0
	 *
	 * @param string $table_name Validated table identifier.
	 *
	 * @return bool True if table exists in database.
	 */
	private static function table_exists_in_db( string $table_name ): bool {
		$db = self::get_db();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$result = $db->get_var(
			$db->prepare(
				'SHOW TABLES LIKE %s',
				$db->esc_like( $table_name )
			)
		);

		return null !== $result && 0 === strcasecmp( $result, $table_name );
	}

	// ─────────────────────────────────────────────
	// Transaction Management
	// ─────────────────────────────────────────────

	/**
	 * Start a database transaction.
	 *
	 * Supports nested transactions using savepoints.
	 *
	 * @since 4.2.0
	 *
	 * @throws DatabaseException If the transaction or savepoint cannot be started.
	 *
	 * @return bool True on success.
	 */
	public static function begin_transaction(): bool {
		$db  = self::get_db();
		$sql = 0 === self::$transaction_level
		? 'START TRANSACTION'
		: 'SAVEPOINT trans_' . self::$transaction_level;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		if ( false === $db->query( $sql ) ) {
			throw new DatabaseException( esc_html( $db->last_error ) );
		}

		++self::$transaction_level;

		return true;
	}

	/**
	 * Commit the active database transaction.
	 *
	 * If nested, releases the current savepoint. The level is only decremented
	 * after the query succeeds, so a failed commit can still be rolled back.
	 *
	 * @since 4.2.0
	 *
	 * @throws DatabaseException If the commit or savepoint release fails.
	 *
	 * @return bool True on success, false if no transaction is active.
	 */
	public static function commit(): bool {
		if ( self::$transaction_level <= 0 ) {
			return false;
		}

		$db  = self::get_db();
		$sql = 1 === self::$transaction_level
		? 'COMMIT'
		: 'RELEASE SAVEPOINT trans_' . ( self::$transaction_level - 1 );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		if ( false === $db->query( $sql ) ) {
			throw new DatabaseException( esc_html( $db->last_error ) );
		}

		--self::$transaction_level;

		return true;
	}

	/**
	 * Roll back the active database transaction.
	 *
	 * If nested, rolls back to the current savepoint. A failed top-level
	 * rollback still resets the level, since the transaction is unusable anyway
	 * (typically the connection is gone).
	 *
	 * @since 4.2.0
	 *
	 * @throws DatabaseException If the rollback fails.
	 *
	 * @return bool True on success, false if no transaction is active.
	 */
	public static function rollback(): bool {
		if ( self::$transaction_level <= 0 ) {
			return false;
		}

		$db           = self::get_db();
		$is_top_level = 1 === self::$transaction_level;
		$sql          = $is_top_level
		? 'ROLLBACK'
		: 'ROLLBACK TO SAVEPOINT trans_' . ( self::$transaction_level - 1 );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		$result = $db->query( $sql );

		if ( $is_top_level || false !== $result ) {
			--self::$transaction_level;
		}

		if ( false === $result ) {
			throw new DatabaseException( esc_html( $db->last_error ) );
		}

		return true;
	}

	/**
	 * Execute a callback within a database transaction.
	 *
	 * Automatically commits on success and rolls back on exception.
	 *
	 * @since 4.2.0
	 *
	 * @param callable $callback Operation(s) to execute within the transaction.
	 *
	 * @throws \Throwable If the callback or transaction fails.
	 *
	 * @return mixed The return value of the callback.
	 */
	public static function transaction( callable $callback ) {
		self::begin_transaction();

		try {
			$result = $callback();
			self::commit();

			return $result;
		} catch ( \Throwable $e ) {
			try {
				self::rollback();
			} catch ( \Throwable $rollback_exception ) {
				// phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch -- Preserve the original exception.
				unset( $rollback_exception );
			}

			throw $e;
		}
	}

	/**
	 * Check whether there is an active database transaction.
	 *
	 * @since 4.2.0
	 *
	 * @return bool True if a transaction is currently active.
	 */
	public static function in_transaction(): bool {
		return self::$transaction_level > 0;
	}

	/**
	 * Get the current transaction nesting level.
	 *
	 * @since 4.2.0
	 *
	 * @return int 0 if outside a transaction, >= 1 if inside one.
	 */
	public static function transaction_level(): int {
		return self::$transaction_level;
	}

	/**
	 * Reset the internal nesting counter.
	 *
	 * Only resets the PHP-side counter, it does not touch the database. If a
	 * transaction is actually open, it stays open. Intended for unit tests.
	 *
	 * @since 4.2.0
	 *
	 * @return void
	 */
	public static function reset_transaction_state(): void {
		self::$transaction_level = 0;
	}
}
