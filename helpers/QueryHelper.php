<?php
/**
 * Query helper class contains static helper methods to perform basic
 * operations
 *
 * @package Tutor\Helper
 * @since v2.0.7
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
	 * @param string $table  table name.
	 * @param array  $data | data to insert in the table.
	 *
	 * @return int, inserted id.
	 *
	 * @since v2.0.7
	 */
	public static function insert( string $table, array $data ): int {
		global $wpdb;
		// Sanitize text field.
		$data = array_map(
			function( $value ) {
				return sanitize_text_field( $value );
			},
			$data
		);

		$insert = $wpdb->insert(
			$table,
			$data
		);
		return $insert ? $wpdb->insert_id : 0;
	}

	/**
	 * Update data
	 *
	 * @param string $table  table name.
	 * @param array  $data | data to update in the table.
	 * @param array  $where | condition array.
	 *
	 * @return bool, true on success false on failure
	 *
	 * @since v2.0.7
	 */
	public static function update( string $table, array $data, array $where ): bool {
		global $wpdb;
		// Sanitize text field.
		$data = array_map(
			function( $value ) {
				return sanitize_text_field( $value );
			},
			$data
		);

		$where = array_map(
			function( $value ) {
				return sanitize_text_field( $value );
			},
			$where
		);

		$update = $wpdb->update(
			$table,
			$data,
			$where
		);
		return $update ? true : false;
	}

	/**
	 * Delete rows from table
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
		$delete = $wpdb->delete(
			$table,
			$where
		);
		return $delete ? true : false;
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
		$delete = $wpdb->query(
			$wpdb->prepare(
				"DELETE FROM
					{$table}
					WHERE 1 = %d
				",
				1
			)
		);
		return $delete ? true : false;
	}

	/**
	 * Insert multiple rows without knowing key value
	 *
	 * @since v2.0.7
	 *
	 * @param string $table  table name.
	 * @param array  $request two dimensional array
	 * for ex: [ [id => 1], [id => 2] ].
	 *
	 * @return mixed  wpdb response true or int on success,
	 * false on failure
	 */
	public static function insert_multiple_rows( $table, $request ) {
		global $wpdb;
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
				$sanitize_value = sanitize_text_field( $value[ $v ] );
				$column_values .= is_numeric( $sanitize_value ) ? $sanitize_value . ',' : "'$sanitize_value'" . ',';
			}
			// Trim trailing comma.
			$column_keys   = rtrim( $column_keys, ',' );
			$column_values = rtrim( $column_values, ',' );
			if ( $first_key === $k ) {
				$sql .= "INSERT INTO {$table} ($column_keys) VALUES ($column_values),";
			} elseif ( $last_key == $k ) {
				$sql .= "($column_values)";
			} else {
				$sql .= "($column_values),";
			}

			// Reset keys & values to avoid duplication.
			$column_keys   = '';
			$column_values = '';
		}
		return $wpdb->query( $sql );
	}

	/**
	 * Build where clause string
	 *
	 * @param	array $where assoc array with field and value
	 * @return	string
	 * 
	 * @since 2.0.9
	 */
	private function build_where_clause( array $where ) {
		$arr = [];
		foreach( $where as $field => $value ) {
			$value = is_numeric( $value ) ? ( $value + 0 ) : "'".$value."'";
			$arr[] = "{$field}={$value}";
		}

		return implode( ' AND ', $arr );
	}

	/**
	 * Sanitize assoc array
	 *
	 * @param array $array an assoc array
	 * @return array
	 * 
	 * @since 2.0.9
	 */
	private function sanitize_assoc_array( array $array ) {
		return array_map(
			function( $value ) {
				return sanitize_text_field( $value );
			},
			$array
		);
	}

	/**
	 * Delete comment with associate meta data
	 *
	 * @param array $where associative array with field and value. 
	 * 				Example: array( 'comment_type' => 'comment', 'comment_id' => 1 )
	 * @return bool
	 * 
	 * @since 2.0.9
	 */
	public static function delete_comment_with_meta( array $where ) {
		if ( count( $where ) === 0 || ! tutor_utils()->is_assoc( $where) ) {
			return false;
		}

		$obj	= new self();
		$where	= $obj->build_where_clause( $obj->sanitize_assoc_array ( $where ) );
		
		global $wpdb;
		$ids = $wpdb->get_col( "SELECT comment_id FROM {$wpdb->comments} WHERE {$where}" );
		
		if ( is_array( $ids ) && count( $ids ) ) {
			$ids_str = "'" . implode( "','", $ids ) . "'";
			// delete comment metas
			$wpdb->query( "DELETE FROM {$wpdb->commentmeta} WHERE comment_id IN({$ids_str}) " );
			// delete comment
			$wpdb->query( "DELETE FROM {$wpdb->comments} WHERE {$where}" );

			return true;
		}

		return false;
	}

	/**
	 * Delete post with associate meta data
	 *
	 * @param array $where associative array with field and value.
	 * 				Example: array( 'post_type' => 'post', 'id' => 1 )
	 * @return bool
	 * 
	 * @since 2.0.9
	 */
	public static function delete_post_with_meta( array $where ) {
		if ( count( $where ) === 0 || ! tutor_utils()->is_assoc( $where) ) {
			return false;
		}

		$obj	= new self();
		$where	= $obj->build_where_clause( $obj->sanitize_assoc_array ( $where ) );
		
		global $wpdb;
		$ids = $wpdb->get_col( "SELECT id FROM {$wpdb->posts} WHERE {$where}" );
		
		if ( is_array( $ids ) && count( $ids ) ) {
			$ids_str = "'" . implode( "','", $ids ) . "'";
			// delete post metas
			$wpdb->query( "DELETE FROM {$wpdb->postmeta} WHERE post_id IN({$ids_str}) " );
			// delete post
			$wpdb->query( "DELETE FROM {$wpdb->posts} WHERE {$where}" );

			return true;
		}

		return false;
	}
}