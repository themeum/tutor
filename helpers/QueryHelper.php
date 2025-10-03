<?php
/**
 * Query helper class contains static helper methods to perform basic
 * operations
 *
 * @package Tutor\Helper
 * @since 2.0.7
 */

namespace Tutor\Helpers;

/**
 * Do the common db operations through helper
 * methods
 */
class QueryHelper {

	/**
	 * Insert data in the table
	 *
	 * @since 2.0.7
	 * @since 3.2.0 sanitize_mapping param added to override sanitize function to specific keys.
	 *
	 * @param string $table  table name.
	 * @param array  $data | data to insert in the table.
	 * @param array  $sanitize_mapping sanitize mapping.
	 *
	 * @return int inserted id.
	 *
	 * @throws \Exception Database error if occur.
	 */
	public static function insert( string $table, array $data, array $sanitize_mapping = array() ): int {
		global $wpdb;

		$table = self::prepare_table_name( $table );
		$data  = \TUTOR\Input::sanitize_array( $data, $sanitize_mapping );

		$insert = $wpdb->insert(
			$table,
			$data
		);

		if ( $wpdb->last_error ) {
			throw new \Exception( $wpdb->last_error );
		}

		return $insert ? $wpdb->insert_id : 0;
	}

	/**
	 * Update data
	 *
	 * @since 2.0.7
	 * @since 3.2.0 IN clause support added.
	 *
	 * @param string $table  table name.
	 * @param array  $data | data to update in the table.
	 * @param array  $where | condition array.
	 *
	 * @return bool  true on success false on failure
	 */
	public static function update( string $table, array $data, array $where ): bool {
		global $wpdb;

		$table        = self::prepare_table_name( $table );
		$set_clause   = self::prepare_set_clause( $data );
		$where_clause = self::prepare_where_clause( $where );

		// phpcs:ignore
		$query = $wpdb->prepare( "UPDATE {$table} {$set_clause} WHERE {$where_clause} AND 1 = %d", 1 );

		// phpcs:ignore
		$wpdb->query( $query );

		if ( $wpdb->last_error ) {
			error_log( $wpdb->last_error );
			return false;
		}

		return true;
	}

	/**
	 * Delete a row from table with where clause.
	 * Limitation: It can only delete one row by wpdb::delete
	 *
	 * @param string $table  table name.
	 * @param array  $where  key value pairs.Where key is the name of
	 * column & value is the value to match.
	 * For ex: [ 'id' => 1 ].
	 *
	 * @since v2.0.7
	 */
	public static function delete( string $table, array $where ): bool {
		global $wpdb;

		$table  = self::prepare_table_name( $table );
		$delete = $wpdb->delete(
			$table,
			$where
		);
		return $delete ? true : false;
	}

	/**
	 * Bulk record delete by where clause.
	 *
	 * @since 3.7.0
	 *
	 * @param string $table table name.
	 * @param array  $where where clause.
	 *
	 * @return int|boolean
	 */
	public static function bulk_delete( $table, array $where ): bool {
		global $wpdb;

		$table        = self::prepare_table_name( $table );
		$where_clause = self::prepare_where_clause( $where );

		return $wpdb->query( "DELETE FROM {$table} WHERE {$where_clause}" ); //phpcs:ignore --$where clause sanitized.
	}

	/**
	 * Delete rows from table
	 *
	 * @since 3.0.0
	 *
	 * @param string $table  table name.
	 * @param array  $ids array of ids.
	 *
	 * @see prepare_in_clause
	 *
	 * @throws \Exception Throw database error if occurred.
	 *
	 * @return true on success
	 */
	public static function bulk_delete_by_ids( string $table, array $ids ): bool {
		global $wpdb;

		$table = self::prepare_table_name( $table );
		$ids   = self::prepare_in_clause( $ids );
		//phpcs:ignore --ids already sanitized.
		$wpdb->query( "DELETE FROM {$table} WHERE id IN ( $ids )");

		if ( $wpdb->last_error ) {
			throw new \Exception( $wpdb->last_error );
		}

		return true;
	}

	/**
	 * Clean everything from table
	 *
	 * @since v2.0.7
	 *
	 * @param string $table  table name.
	 *
	 * @return bool
	 */
	public static function table_clean( string $table ): bool {
		global $wpdb;

		$table  = self::prepare_table_name( $table );
		$delete = $wpdb->query(
			//phpcs:ignore
			$wpdb->prepare( "DELETE FROM {$table} WHERE 1 = %d", 1 )
		);
		return $delete ? true : false;
	}

	/**
	 * Insert multiple rows without knowing key value
	 *
	 * @since v2.0.7
	 * @since 3.6.0 param $return_ids added.
	 *
	 * @param string $table  table name.
	 * @param array  $request two dimensional array
	 * for ex: [ [id => 1], [id => 2] ].
	 * @param bool   $return_ids if true returns the last inserted data ids.
	 * @param bool   $do_sanitize sanitize data or not.
	 *
	 * @return mixed  wpdb response true or int on success, false on failure.
	 * @throws \Exception If error occur.
	 */
	public static function insert_multiple_rows( $table, $request, $return_ids = false, $do_sanitize = true ) {
		global $wpdb;

		$table         = self::prepare_table_name( $table );
		$column_keys   = '';
		$column_values = '';
		$sql           = '';
		$last_key      = array_key_last( $request );
		$first_key     = array_key_first( $request );
		foreach ( $request as $k => $value ) {
			$keys = array_keys( $value );

			// Prepare column keys & values.
			foreach ( $keys as $v ) {
				$column_keys   .= sanitize_key( $v ) . ',';
				$sanitize_value = $value[ $v ];
				if ( $sanitize_value && $do_sanitize ) {
					$sanitize_value = sanitize_text_field( $sanitize_value );
				}
				$column_values .= is_numeric( $sanitize_value ) ? $sanitize_value . ',' : "'$sanitize_value'" . ',';
			}
			// Trim trailing comma.
			$column_keys   = rtrim( $column_keys, ',' );
			$column_values = rtrim( $column_values, ',' );
			if ( $first_key === $k ) {
				$sql .= "INSERT INTO {$table} ($column_keys) VALUES ($column_values)";
				if ( count( $request ) > 1 ) {
					$sql .= ',';
				}
			} elseif ( $last_key == $k ) {
				$sql .= "($column_values)";
			} else {
				$sql .= "($column_values),";
			}

			// Reset keys & values to avoid duplication.
			$column_keys   = '';
			$column_values = '';
		}

		$wpdb->query( $sql );//phpcs:ignore

		// If error occurred then throw new exception.
		if ( $wpdb->last_error ) {
			throw new \Exception( $wpdb->last_error );
		}

		if ( $return_ids ) {
			$query_ids = $wpdb->get_results(
				//phpcs:ignore
				"SELECT ID FROM {$table} WHERE ID >= LAST_INSERT_ID()",
				'ARRAY_N'
			);

			return $query_ids;
		}

		return true;
	}

	/**
	 * Make tge where clause base on its column operator and values.
	 *
	 * If the operator is IN then make the clause like `WHERE column_name IN (value1, value2, ...)`
	 * Otherwise the clause would be `WHERE column_name = 'value'`
	 *
	 * @since 3.0.0
	 *
	 * @param array $where  The where clause array. e.g. array( 'id', 'IN', array(1, 2, 3) ) or array( 'id', '=', 1 ).
	 *
	 * @return string
	 */
	public static function make_clause( array $where ) {
		list ( $field, $operator, $value ) = $where;

		$upper_operator = strtoupper( $operator );
		if ( in_array( $upper_operator, array( 'IN', 'NOT IN' ), true ) ) {
			$value = '(' . self::prepare_in_clause( $value ) . ')';
		}

		return "{$field} {$upper_operator} {$value}";
	}

	/**
	 * Check operator is supported.
	 *
	 * @since 3.5.0
	 *
	 * @param string $operator operator like =, !=, > , < etc.
	 *
	 * @return boolean
	 */
	public static function is_support_operator( $operator ) {
		$operator = strtoupper( $operator );

		return in_array(
			$operator,
			array(
				'=',
				'!=',
				'<>',
				'>',
				'<',
				'>=',
				'<=',
				'LIKE',
				'NOT LIKE',
				'IN',
				'NOT IN',
				'IS',
				'IS NOT',
				'BETWEEN',
				'NOT BETWEEN',
				'RAW',
			),
			true
		);
	}

	/**
	 * Prepare where clause string
	 *
	 * @since 2.0.9
	 * @since 3.0.0 Null value support added, if need to check with null: [name => 'null']
	 * @since 3.5.0 All common SQL comparison operators support added.
	 *              $where = array(
	 *                  'id'         => ['BETWEEN', [10, 20]],
	 *                  'status'     => ['!=', 'draft'],
	 *                  'email'      => ['LIKE', '%@gmail.com'],
	 *                  'type'       => ['NOT IN', ['test', 'sample']],
	 *                  'age'        => ['>=', 18],
	 *                  'active'     => true,
	 *                  'deleted_at' => 'null',
	 *                  'role'       => 'editor',
	 *              )
	 * @since 3.6.0 Added raw query support. Make sure the query written is not sql injectable.
	 *              $where = array(
	 *                  'username = %s' =>  [ 'RAW' , array( 'test' ) ]
	 *              )
	 * @param   array $where assoc array with field and value.
	 *
	 * @return  string
	 */
	public static function prepare_where_clause( array $where ) {
		$arr = array();
		foreach ( $where as $field => $value ) {
			$operator = null;
			if ( is_array( $value ) && isset( $value[0] ) && is_string( $value[0] ) && self::is_support_operator( $value[0] ) ) {
				$operator = strtoupper( $value[0] );
				$val      = $value[1];
				switch ( $operator ) {
					case 'IN':
					case 'NOT IN':
						if ( is_array( $val ) ) {
							$clause = array( $field, $operator, $val );
						}
						break;

					case 'BETWEEN':
					case 'NOT BETWEEN':
						if ( is_array( $val ) && count( $val ) === 2 ) {
							$val1   = is_numeric( $val[0] ) ? $val[0] : "'" . $val[0] . "'";
							$val2   = is_numeric( $val[1] ) ? $val[1] : "'" . $val[1] . "'";
							$clause = array( $field, $operator, "{$val1} AND {$val2}" );
						}
						break;

					case 'IS':
					case 'IS NOT':
						$val    = strtoupper( $val ) === 'NULL' ? 'NULL' : "'" . $val . "'";
						$clause = array( $field, $operator, $val );
						break;
					case 'RAW':
						$final_query = '';
						if ( ! empty( $field ) && is_array( $val ) ) {
							$final_query = self::prepare_raw_query( $field, $val );
						}
						$clause = $final_query;
						break;
					default: // =, !=, <, >, <=, >=, LIKE, NOT LIKE, <>
						$val    = is_numeric( $val ) ? $val : "'" . $val . "'";
						$clause = array( $field, $operator, $val );
						break;
				}
			} elseif ( is_array( $value ) ) {
				$clause = array( $field, 'IN', $value );
			} elseif ( 'null' === strtolower( $value ) ) {
					$clause = array( $field, 'IS', 'NULL' );
			} else {
				$value  = is_numeric( $value ) ? $value : "'" . $value . "'";
				$clause = array( $field, '=', $value );
			}

			$arr[] = ( 'RAW' === $operator ) ? $clause : self::make_clause( $clause );
		}

		return implode( ' AND ', $arr );
	}

	/**
	 * Prepare raw query for query helper.
	 *
	 * @since 3.6.0
	 *
	 * @param string $raw_query the query to execute.
	 * @param array  $parameters the parameters to pass to the query.
	 *
	 * @return string
	 */
	public static function prepare_raw_query( $raw_query, $parameters ) {
		/**
		 * Not allowed unsafe SQL control characters  [;, --, /*]
		 * Allowed safe SQL control characters only.
		 */
		$is_safe = preg_match( '/^[a-zA-Z0-9_%\.=\s\'"<>\(\)\-\[\],]+$/', $raw_query );
		if ( ! $is_safe ) {
			return '';
		}

		if ( ! count( $parameters ) ) {
			return $raw_query;
		}

		global $wpdb;

		$final_query = $wpdb->prepare( $raw_query, $parameters ); //phpcs:ignore

		return $final_query;
	}

	/**
	 * Prepare like clause string with or
	 *
	 * @since 1.0.0
	 *
	 * @param array  $where assoc array with field and value.
	 * @param string $relation default is OR.
	 *
	 * @return string
	 */
	public static function prepare_like_clause( array $where, $relation = 'OR' ) {
		global $wpdb;

		$like_conditions = array();

		foreach ( $where as $column_name => $term ) {
			//phpcs:ignore
			$like_conditions[] = $wpdb->prepare( "$column_name LIKE %s", '%' . $wpdb->esc_like( $term ) . '%' );
		}

		$where_clause = implode( ' OR ', $like_conditions );

		return $where_clause;
	}

	/**
	 * Sanitize assoc array
	 *
	 * @param array $array an assoc array.
	 * @return array
	 *
	 * @since 2.0.9
	 */
	private static function sanitize_assoc_array( array $array ) {
		return array_map(
			function ( $value ) {
				return sanitize_text_field( $value );
			},
			$array
		);
	}

	/**
	 * Delete comment with associate meta data
	 *
	 * @param array $where associative array with field and value.
	 *              Example: array( 'comment_type' => 'comment', 'comment_id' => 1 ).
	 * @return bool
	 *
	 * @since 2.0.9
	 */
	public static function delete_comment_with_meta( array $where ) {
		if ( count( $where ) === 0 || ! tutor_utils()->is_assoc( $where ) ) {
			return false;
		}

		$where = self::prepare_where_clause( self::sanitize_assoc_array( $where ) );

		global $wpdb;
		$ids = $wpdb->get_col( "SELECT comment_id FROM {$wpdb->comments} WHERE {$where}" );//phpcs:ignore

		if ( is_array( $ids ) && count( $ids ) ) {
			$ids_str = "'" . implode( "','", $ids ) . "'";
			// delete comment metas.
			$wpdb->query( "DELETE FROM {$wpdb->commentmeta} WHERE comment_id IN({$ids_str}) " );//phpcs:ignore
			// delete comment.
			$wpdb->query( "DELETE FROM {$wpdb->comments} WHERE {$where}" );//phpcs:ignore

			return true;
		}

		return false;
	}

	/**
	 * Delete post with associate meta data
	 *
	 * @param array $where associative array with field and value.
	 *              Example: array( 'post_type' => 'post', 'id' => 1 ).
	 * @return bool
	 *
	 * @since 2.0.9
	 */
	public static function delete_post_with_meta( array $where ) {
		if ( count( $where ) === 0 || ! tutor_utils()->is_assoc( $where ) ) {
			return false;
		}

		$where = self::prepare_where_clause( self::sanitize_assoc_array( $where ) );

		global $wpdb;
		$ids = $wpdb->get_col( "SELECT id FROM {$wpdb->posts} WHERE {$where}" );//phpcs:ignore

		if ( is_array( $ids ) && count( $ids ) ) {
			$ids_str = "'" . implode( "','", $ids ) . "'";
			// delete post metas.
			$wpdb->query( "DELETE FROM {$wpdb->postmeta} WHERE post_id IN({$ids_str}) " );//phpcs:ignore
			// delete post.
			$wpdb->query( "DELETE FROM {$wpdb->posts} WHERE {$where}" );//phpcs:ignore

			return true;
		}

		return false;
	}

	/**
	 * Prepare SELECT clause.
	 *
	 * @since 3.8.0
	 *
	 * @param mixed $columns Column name or list of columns.
	 *
	 * @return string
	 */
	protected static function prepare_select_clause( $columns = '' ) {
		if ( empty( $columns ) ) {
			return '*';
		}

		if ( is_array( $columns ) ) {
			return implode( ',', $columns );
		}

		return $columns;
	}

	/**
	 * Prepare JOIN clause.
	 *
	 * @since 3.8.0
	 *
	 * @param array $joins Array of joins, each item:
	 *   - type: join type (LEFT, INNER, RIGHT etc).
	 *   - table: table name.
	 *   - on: join condition.
	 *
	 * @return string
	 */
	protected static function prepare_join_clause( $joins = array() ) {
		if ( empty( $joins ) || ! is_array( $joins ) ) {
			return '';
		}

		$clause = '';
		foreach ( $joins as $join ) {
			$type  = strtoupper( $join['type'] ?? 'LEFT' );
			$table = self::prepare_table_name( $join['table'] );
			$on    = $join['on'];
			if ( $table && $on ) {
				$clause .= " {$type} JOIN {$table} ON {$on} ";
			}
		}

		return $clause;
	}

	/**
	 * Prepare WHERE + SEARCH clause together.
	 *
	 * @since 3.8.0
	 *
	 * @param array  $where  Array of key => value pairs.
	 * @param array  $search Array of key => search string pairs.
	 * @param string $search_operator Operator for search conditions (AND/OR).
	 *
	 * @return string
	 */
	protected static function prepare_where_search_clause( $where = array(), $search = array(), $search_operator = 'OR' ) {
		$clauses = array();

		// Handle WHERE conditions.
		if ( ! empty( $where ) && is_array( $where ) ) {
			$clauses[] = self::prepare_where_clause( $where );
		}

		// Handle SEARCH conditions.
		if ( ! empty( $search ) && is_array( $search ) ) {
			$clauses[] = self::prepare_like_clause( $search, $search_operator );
		}

		if ( empty( $clauses ) ) {
			return '';
		}

		return 'WHERE ' . implode( ' AND ', $clauses );
	}

	/**
	 * Prepare order by clause.
	 *
	 * @since 3.8.0
	 *
	 * @param string $orderby order by column.
	 * @param string $order order ASC|DESC.
	 *
	 * @return string
	 */
	protected static function prepare_order_clause( $orderby = '', $order = 'DESC' ) {
		if ( empty( $orderby ) ) {
			return '';
		}

		// Allowed: foo, foo_bar, _foo, foo.bar etc.
		if ( ! preg_match( '/^[A-Za-z_][A-Za-z0-9._]*$/', $orderby ) ) {
			return '';
		}

		$order = strtoupper( $order ) === 'ASC' ? 'ASC' : 'DESC';
		return "ORDER BY {$orderby} {$order}";
	}

	/**
	 * Prepare LIMIT clause.
	 *
	 * @since 3.8.0
	 *
	 * @param int $limit limit.
	 * @param int $offset offset.
	 *
	 * @return string
	 */
	protected static function prepare_limit_clause( $limit = 0, $offset = 0 ) {
		if ( $limit < 1 || $offset < 0 ) {
			return '';
		}

		return sprintf( 'LIMIT %d OFFSET %d', $limit, $offset );
	}

	/**
	 * Run a database query with flexible arguments.
	 *
	 * Supports SELECT, JOIN, WHERE, SEARCH, GROUP BY, HAVING, ORDER BY,
	 * LIMIT (pagination), and can return count, single row or full result set.
	 *
	 * @since 3.8.0
	 *
	 * @param string $table table name.
	 * @param array  $args {
	 *     Query arguments.
	 *
	 *     @type string|array $select   Columns to select, defaults to "*".
	 *     @type string       $alias    Table alias.
	 *     @type array        $where    WHERE conditions [ 'col' => 'val', ... ].
	 *     @type array        $search   LIKE conditions [ 'col' => 'keyword', ... ].
	 *     @type array        $joins    JOIN clauses [ [ 'type' => 'LEFT', 'table' => '...', 'on' => '...' ], ... ].
	 *     @type string       $groupby  GROUP BY clause.
	 *     @type string       $having   HAVING clause.
	 *     @type string       $orderby  Column to order by.
	 *     @type string       $order    ASC|DESC, default DESC.
	 *     @type int          $limit    Limit.
	 *     @type int          $offset   Offset.
	 *     @type int          $per_page Results per page for pagination.
	 *     @type int          $page     Current page number for pagination.
	 *     @type bool         $count    If true, return only total count.
	 *     @type bool         $single   If true, return only single row.
	 *     @type string       $output   OBJECT|ARRAY_A default is OBJECT.
	 * }
	 *
	 * @return mixed          Result set, count or single row.
	 */
	public static function query( $table, $args = array() ) {
		// Flags.
		$count      = isset( $args['count'] ) && $args['count'];
		$single     = isset( $args['single'] ) && $args['single'];
		$pagination = isset( $args['per_page'], $args['page'] );
		$output     = $args['output'] ?? 'OBJECT';

		// Primary table.
		$table            = self::prepare_table_name( $table );
		$alias            = $args['alias'] ?? 'main';
		$table_with_alias = "{$table} AS {$alias}";

		// Build clauses.
		$select_clause   = self::prepare_select_clause( $args['select'] ?? '' );
		$join_clause     = self::prepare_join_clause( $args['joins'] ?? array() );
		$where_clause    = self::prepare_where_search_clause( $args['where'] ?? array(), $args['search'] ?? array() );
		$groupby_clause  = empty( $args['groupby'] ) ? '' : 'GROUP BY ' . $args['groupby'];
		$having_clause   = empty( $args['having'] ) ? '' : 'HAVING ' . $args['having'];
		$order_by_clause = self::prepare_order_clause( $args['orderby'] ?? '', $args['order'] ?? 'DESC' );

		global $wpdb;

		// Count only.
		if ( $count ) {
			$sql_query = "SELECT COUNT(*)
						FROM {$table_with_alias} 
						{$join_clause} 
						{$where_clause} 
						{$groupby_clause} 
						{$having_clause}";

			return (int) $wpdb->get_var( $sql_query ); //phpcs:ignore
		}

		// Single record.
		if ( $single ) {
			$sql_query = "SELECT {$select_clause} 
						FROM {$table_with_alias} 
						{$join_clause} 
						{$where_clause} 
						{$groupby_clause} 
						{$having_clause}
						{$order_by_clause}
						LIMIT 1";

			return $wpdb->get_row( $sql_query, $output ); //phpcs:ignore
		}

		$calc_found_rows = $pagination ? 'SQL_CALC_FOUND_ROWS' : '';
		$limit           = isset( $args['limit'] ) ? (int) $args['limit'] : 0;
		$offset          = isset( $args['offset'] ) ? (int) $args['offset'] : 0;

		if ( $pagination ) {
			$limit  = (int) $args['per_page'];
			$offset = (int) ( $args['page'] - 1 ) * $limit;
		}

		$limit_clause = self::prepare_limit_clause( $limit, $offset );

		$sql_query = "SELECT {$calc_found_rows} {$select_clause} 
					FROM {$table_with_alias} 
					{$join_clause} 
					{$where_clause} 
					{$groupby_clause} 
					{$having_clause}
					{$order_by_clause}
					{$limit_clause}";

		$rows = $wpdb->get_results( $sql_query, $output ); //phpcs:ignore

		if ( $pagination ) {
			$has_records  = is_array( $rows ) && count( $rows );
			$page         = (int) $args['page'];
			$per_page     = (int) $args['per_page'];
			$total_record = (int) $has_records ? $wpdb->get_var( 'SELECT FOUND_ROWS()' ) : 0;
			$total_page   = (int) ceil( $total_record / $per_page );

			return array(
				'total_record' => (int) $total_record,
				'per_page'     => $per_page,
				'current_page' => $page,
				'total_page'   => $total_page,
				'data'         => $rows,
			);

		}

		return $rows;
	}

	/**
	 * Get a single row from any table with where clause
	 *
	 * @param string $table  table name with prefix.
	 *
	 * @param array  $where  assoc_array. For ex: [col_name => value ].
	 * @param string $order_by  order by column name.
	 * @param string $order  DESC or ASC, default is DESC.
	 * @param string $output  expected output type, default is object.
	 *
	 * @return mixed  based on output param, default object
	 */
	public static function get_row( string $table, array $where, string $order_by, string $order = 'DESC', string $output = 'OBJECT' ) {
		global $wpdb;

		$table        = self::prepare_table_name( $table );
		$where_clause = self::prepare_where_clause( $where );

		//phpcs:disable
		$query = $wpdb->prepare(
			"SELECT *
				FROM {$table}
				WHERE {$where_clause}
				ORDER BY {$order_by} {$order}
				LIMIT %d
			",
			1
		);

		return $wpdb->get_row(
			$query,
			$output
		);
		//phpcs:enable
	}

	/**
	 * Get all row from any table with where clause
	 *
	 * @since 2.2.1
	 * @since 3.0.0  added support for -1 value in the limit parameter.
	 *
	 * @param string $table  table name with prefix.
	 *
	 * @param array  $where  assoc_array. For ex: [col_name => value ].
	 * @param string $order_by  order by column name.
	 * @param int    $limit default is 1000, -1 for no limit.
	 * @param string $order  DESC or ASC, default is DESC.
	 * @param string $output  expected output type, default is object.
	 *
	 * @return mixed  based on output param, default object
	 */
	public static function get_all( string $table, array $where, string $order_by, $limit = 1000, string $order = 'DESC', string $output = 'OBJECT' ) {
		global $wpdb;

		$table        = self::prepare_table_name( $table );
		$where_clause = self::prepare_where_clause( $where );
		$limit        = (int) sanitize_text_field( $limit );
		$limit_clause = ( -1 === $limit ) ? '' : 'LIMIT ' . $limit;

		//phpcs:disable
		$query = "SELECT *
				FROM {$table}
				WHERE {$where_clause}
				ORDER BY {$order_by} {$order}
				{$limit_clause}";

		return $wpdb->get_results(
			$query,
			$output
		);
		//phpcs:enable
	}

	/**
	 * Update multiple rows by using where in
	 * clause
	 *
	 * @since v2.1.0
	 *
	 * @param string $table  table name.
	 * @param array  $data assoc_array data to update
	 *                ex: [id => 2, name => 'john' ].
	 * @param string $where_in comma separated values, ex: 1,2,3.
	 * @param string $where_col default is ID but could be other.
	 *
	 * @return bool true on success, false on failure
	 */
	public static function update_where_in( string $table, array $data, string $where_in, string $where_col = 'ID' ) {
		global $wpdb;

		$table = self::prepare_table_name( $table );
		if ( empty( $where_in ) || empty( $where_col ) ) {
			return false;
		}
		$set_clause = self::prepare_set_clause( $data );
		if ( '' === $set_clause ) {
			return false;
		}
		// @codingStandardsIgnoreStart
		$query      = $wpdb->prepare(
			"UPDATE {$table}
				{$set_clause}
				WHERE $where_col IN ( $where_in )
				AND 1 = %d
			",
			1
		);
		return $wpdb->query( $query ) ? true : false;
	}

	/**
	 * Prepare MySQL SET clause for update query
	 *
	 * @since v2.1.0
	 *
	 * @param array $data  single dimension assoc_array.
	 *
	 * @return string
	 */
	public static function prepare_set_clause( array $data ) {
		$set   = '';
		foreach ( $data as $key => $value ) {
			if ( $key === array_key_first ( $data ) ) {
				$set .= "SET ";
			}
			// Multi dimension not allowed.
			if ( is_array( $value ) ) {
				continue;
			}

			if ( is_null( $value ) ) {
				$set  .= "$key = null";
			} else {
				$value = esc_sql( sanitize_text_field( $value ) );
				$set  .= is_numeric( $value ) ? "$key = $value" : "$key = '" . $value ."'";
			}
			
			$set .= ",";
		}
		return rtrim( $set, ',' );
	}

	/**
	 * Make sanitized SQL IN clause value from an array
	 *
	 * @param array $arr a sequential array.
	 * @return string
	 * @since 2.1.1
	 */
	public static function prepare_in_clause( array $arr ) {
		$escaped = array_map(
			function( $value ) {
				global $wpdb;
				$escaped_value = null;
				if ( is_int( $value ) ) {
					$escaped_value = $wpdb->prepare( '%d', $value );
				} else if( is_float( $value ) ) {
					list( $whole, $decimal ) = explode( '.', $value );
					$expression = '%.'. strlen( $decimal ) . 'f';
					$escaped_value = $wpdb->prepare( $expression, $value );
				} else {
					$escaped_value = $wpdb->prepare( '%s', $value );
				}
				return $escaped_value;
			},
			$arr
		);
	
		return implode( ',', $escaped );
	}

	/**
	 * Check table exist in database.
	 *
	 * @since 2.5.0
	 *
	 * @param string $table table name.
	 *
	 * @return bool
	 */
	public static function table_exists( $table ) {
		global $wpdb;

		$table = self::prepare_table_name( $table );
		$sql   = "SHOW TABLES LIKE '{$table}'";
		return $wpdb->get_var( $sql ) === $table;
	}

	/**
	 * Check column exist in a table
	 *
	 * @since 3.0.0
	 *
	 * @param string $table table name.
	 * @param string $column column name.
	 *
	 * @return bool
	 */
	public static function column_exist( $table, $column ) {
		global $wpdb;

		$table = self::prepare_table_name( $table );
		$sql   = "SHOW COLUMNS FROM {$table} LIKE '{$column}'";
		return $wpdb->get_var( $sql ) === $column;
	}

	/**
	 * Get data by joining multiple tables with specified join relations.
	 *
	 * Argument should be SQL escaped.
	 *
	 * @since 3.0.0
	 * @since 3.8.2 param $get_row added.
	 *
	 * @param string $primary_table The primary table name with prefix.
	 * @param array  $joining_tables An array of join relations. Each relation should be an array with keys 'type', 'table', 'on'.
	 * @param array  $select_columns An array of columns to select.
	 * @param array  $where  An associative array for the WHERE clause. For example: [col_name => value]. Without sql esc.
	 * @param array  $search An associative array for the search clause. For example: [col_name => value]. Without sql esc.
	 * @param string $order_by  Order by column name.
	 * @param int    $limit Maximum number of rows to return.
	 * @param int    $offset Offset for pagination.
	 * @param string $order  DESC or ASC, default is DESC.
	 * @param string $output  Expected output type, default is OBJECT.
	 * @param bool   $get_row Get a single row.
	 *
	 * @throws \Exception If an error occurred during the query execution.
	 *
	 * @return mixed  Based on output param, default OBJECT.
	 */
	public static function get_joined_data(
		string $primary_table,
		array $joining_tables,
		array $select_columns,
		array $where = [],
		array $search = [],
		string $order_by = '',
		$limit = 10,
		$offset = 0,
		string $order = 'DESC',
		string $output = 'OBJECT',
		bool $get_row = false
	) {
		global $wpdb;

		$select_clause   = implode( ', ', $select_columns );
		$from_clause     = self::prepare_table_name( $primary_table );
		$join_clauses    = self::prepare_join_clause( $joining_tables );
		$where_clause    = self::prepare_where_search_clause( $where, $search );
		$order_by_clause = self::prepare_order_clause( $order_by, $order );
		$limit_clause    = self::prepare_limit_clause( $limit, $offset );

		$query = "SELECT SQL_CALC_FOUND_ROWS 
				{$select_clause}
				FROM {$from_clause}
				{$join_clauses}
				{$where_clause}
				{$order_by_clause}
				{$limit_clause}";

		if ( $get_row ) {
			return $wpdb->get_row( $query, $output );
		}

		$results     = $wpdb->get_results( $query, $output );
		$has_records = is_array( $results ) && count( $results );	
		$total_count = $has_records ? (int) $wpdb->get_var( 'SELECT FOUND_ROWS()' ) : 0;

		// Throw exception if error occurred.
		if ( $wpdb->last_error ) {
			throw new \Exception( $wpdb->last_error );
		}

		// Prepare response array.
		$response = array(
			'total_count' => $total_count,
			'results'     => $results,
		);

		return $response;
	}

	/**
	 * Get count var
	 *
	 * Argument should be SQL escaped.
	 *
	 * @since 1.0.0
	 *
	 * @param string $table table name with prefix.
	 * @param array  $where array of where condition.
	 * @param array  $search array of search conditions for LIKE operator.
	 * @param string $count_column column name to count, default id.
	 *
	 * @return int
	 */
	public static function get_count( $table, $where = [], $search = [], $count_column = 'id' ): int {
		global $wpdb;

		$table         = self::prepare_table_name( $table );
		$where_clause  = self::prepare_where_search_clause( $where, $search, 'AND' );

		$count = $wpdb->get_var(
			"SELECT COUNT($count_column)
			FROM $table
			{$where_clause}"
		);

		// If error occurred then throw new exception.
		if ( $wpdb->last_error ) {
			throw new \Exception( $wpdb->last_error );
		}

		return (int) $count;
	}

	/**
	 * Get count by joining multiple tables with specified join relations.
	 *
	 * Argument should be SQL escaped.
	 *
	 * @since 3.0.0
	 *
	 * @param string $primary_table The primary table name with prefix.
	 * @param array  $joining_tables An array of join relations. Each relation should be an array with keys 'type', 'table', 'on'.
	 * @param array  $where array of where conditions.
	 * @param array  $search array of search conditions for LIKE operator.
	 * @param string $count_column column name to count, default id.
	 *
	 * @return int
	 */
	public static function get_joined_count(string $primary_table, array $joining_tables, array $where = [], array $search = [], string $count_column = '*'): int {
		global $wpdb;
		
		$from_clause  = self::prepare_table_name( $primary_table );
		$join_clauses = self::prepare_join_clause( $joining_tables );
		$where_clause = self::prepare_where_search_clause( $where, $search, 'AND' );

		$count_query = "
			SELECT COUNT($count_column) as total_count
			FROM {$from_clause}
			{$join_clauses}
			{$where_clause}
		";

		$total_count = $wpdb->get_var( $count_query );

		// If error occurred then throw new exception.
		if ( $wpdb->last_error ) {
			throw new \Exception( $wpdb->last_error );
		}

		return (int) $total_count;
	}

	/**
	 * Get all rows from any table with where and search clauses.
	 *
	 * @since 3.0.0
	 *
	 * @param string $table  Table name with prefix.
	 * @param array  $where  Assoc array for exact match. For example: [col_name => value]. Without sql esc.
	 * @param array  $search  Assoc array for LIKE match. For example: [col_name => search_term]. Without sql esc.
	 * @param string $order_by  Order by column name.
	 * @param int    $limit  Maximum number of rows to return, default is 10.
	 * @param int    $offset  Offset for pagination, default is 0.
	 * @param string $order  DESC or ASC, default is DESC.
	 * @param string $output  Expected output type, default is OBJECT.
	 *
	 * @throws \Exception Throw exception if error occurred during query execution.
	 *
	 * @return mixed  Based on output param, default OBJECT.
	 */
	public static function get_all_with_search( string $table, array $where, array $search, string $order_by, $limit = 10, $offset = 0, string $order = 'DESC', string $output = 'OBJECT' ): array {
		global $wpdb;

		$table           = self::prepare_table_name( $table );
		$where_clause    = self::prepare_where_search_clause( $where, $search, 'AND' );
		$order_by_clause = self::prepare_order_clause( $order_by, $order );
		$limit_clause    = self::prepare_limit_clause( $limit, $offset );
	
		// If error occurred then throw new exception.
		if ( $wpdb->last_error ) {
			throw new \Exception( $wpdb->last_error );
		}
	
		$query = "SELECT SQL_CALC_FOUND_ROWS *
			 FROM {$table}
			 {$where_clause}
			 {$order_by_clause}
			 {$limit_clause}";
	
		$results     = $wpdb->get_results( $query, $output );
		$has_records = is_array( $results ) && count( $results );
		$total_count = $has_records ? (int) $wpdb->get_var( 'SELECT FOUND_ROWS()' ) : 0;
	
		// If error occurred then throw new exception.
		if ( $wpdb->last_error ) {
			throw new \Exception( $wpdb->last_error );
		}
	
		// Prepare response array.
		$response = array(
			'results'     => $results,
			'total_count' => $total_count,
		);
	
		return $response;
	}

	/**
	 * Get period clause based on the provided period.
	 *
	 * @since 3.0.0
	 *
	 * @param string $column Table.column name, ex: table.created_at.
	 * @param string $period Period for filter refund data.
	 *
	 * @return string
	 */
	public static function get_period_clause( string $column, string $period = '' ) {
		$period_clause = '';
		switch ( $period ) {
			case 'today':
				$period_clause = "AND DATE($column) = CURDATE()";
				break;
			case 'monthly':
				$period_clause = "AND MONTH($column) = MONTH(CURDATE())";
				break;
			case 'yearly':
				$period_clause = "AND YEAR($column) = YEAR(CURDATE())";
				break;
			case 'last30days':
				$period_clause = "AND DATE($column) BETWEEN DATE_SUB(CURDATE(), INTERVAL 30 DAY) AND CURDATE()";
				break;
			case 'last90days':
				$period_clause = "AND DATE($column) BETWEEN DATE_SUB(CURDATE(), INTERVAL 90 DAY) AND CURDATE()";
				break;
			case 'last365days':
				$period_clause = "AND DATE($column) BETWEEN DATE_SUB(CURDATE(), INTERVAL 365 DAY) AND CURDATE()";
				break;
			default:
				break;
		}

		return $period_clause;
	}

	/**
	 * Get last executed SQL query.
	 *
	 * @since 3.6.0
	 *
	 * @return string
	 */
	public static function get_last_query(){
		global $wpdb;
		return $wpdb->last_query;
	}

	/**
	 * Get table prefix.
	 *
	 * @since 3.7.0
	 *
	 * @return string
	 */
	public static function get_table_prefix() {
		global $wpdb;
		return $wpdb->prefix;
	}

	/**
	 * Prepare table name with prefix.
	 *
	 * @since 3.7.0
	 *
	 * @param string $table_name table name.
	 *
	 * @return string
	 */
	public static function prepare_table_name( string $table_name ) {
		$table_prefix = self::get_table_prefix();
		if ( strpos( $table_name,$table_prefix ) !== 0 ) {
			$table_name = $table_prefix . $table_name;
		}

		return $table_name;
	}

	/**
	 * Duplicate a row with modification callback support.
	 *
	 * @since 3.7.0
	 *
	 * @param string             $table_name name of the database table (with prefix if needed).
	 * @param array              $where      associative array of WHERE conditions.
	 * @param callable|null      $modifier   optional callback to modify or exclude fields before insertion.
	 *
	 * @return int|WP_Error      New row ID on success, or WP_Error on failure.
	 */
	public static function duplicate_row( $table_name, array $where, ?callable $modifier = null ) {
		global $wpdb;

		$table_name = self::prepare_table_name( $table_name );
		if ( empty( $where ) ) {
			return new \WP_Error( 'missing_where', 'No WHERE condition provided.' );
		}

		$where_clause = self::prepare_where_clause( $where );
		$sql          = $wpdb->prepare( "SELECT * FROM `$table_name` WHERE {$where_clause} LIMIT %d", 1 );
		$row          = $wpdb->get_row( $sql, ARRAY_A );

		if ( ! $row ) {
			return new \WP_Error( 'not_found', 'No matching row found to duplicate.' );
		}

		// Apply user-defined modifications (ex: remove ID, change field value)
		if ( is_callable( $modifier ) ) {
			$row = call_user_func( $modifier, $row );

			if ( ! is_array( $row ) || empty( $row ) ) {
				return new \WP_Error( 'invalid_modified_row', 'Modified row is invalid or empty.' );
			}
		}

		// Prepare insert
		$columns      = array_keys( $row );
		$placeholders = array_fill( 0, count( $columns ), '%s' );
		$values       = array_values( $row );

		$insert_sql = $wpdb->prepare(
			"INSERT INTO `$table_name` (`" . implode( '`, `', $columns ) . "`) 
			VALUES (" . implode( ', ', $placeholders ) . ")",
			...$values
		);

		$result = $wpdb->query( $insert_sql );

		if ( false === $result ) {
			return new \WP_Error( 'insert_failed', 'Failed to insert duplicate row.' );
		}

		return $wpdb->insert_id;
	}

	/**
	 * Get valid sort order.
	 *
	 * @since 3.7.1
	 *
	 * @param string $order order.
	 *
	 * @return string
	 */
	public static function get_valid_sort_order( $order ) {
		return 'ASC' === strtoupper( $order ) ? 'ASC' : 'DESC';
	}

	/**
	 * Get the schema of a database table.
	 *
	 * @since 3.8.1
	 *
	 * @param string $table_name The name of the database table.
	 *
	 * @throws \Exception Throws an exception if there is a database error.
	 *
	 * @return array Returns an array of table columns and their details.
	 */
	public static function get_table_schema( $table_name) {
		
		global $wpdb;

		$result = $wpdb->get_results( "DESCRIBE {$table_name}", ARRAY_A );

		// If error occurred then throw new exception.
		if ($wpdb->last_error) {
			throw new \Exception($wpdb->last_error);
		}

		return $result;
	}

}
