<?php
/**
 * GDPR user contents table.
 *
 * @package Tutor\GDPR\DB
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

namespace Tutor\GDPR\DB;

defined( 'ABSPATH' ) || exit;

/**
 * User contents table class.
 */
class UserConsents extends DB {

	/**
	 * Get table name.
	 *
	 * @since 4.0.0
	 *
	 * @return string
	 */
	public static function get_table_name() {
		global $wpdb;
		return $wpdb->prefix . 'tutor_user_consents';
	}

	/**
	 * Get create table schema.
	 *
	 * @since 4.0.0
	 *
	 * @return string
	 */
	public static function get_schema() {
		global $wpdb;

		$table_name      = static::get_table_name();
		$charset_collate = $wpdb->get_charset_collate();

		return "CREATE TABLE {$table_name} (
			id BIGINT UNSIGNED AUTO_INCREMENT,
			user_id BIGINT UNSIGNED NULL,
			user_email VARCHAR(190) NOT NULL,
			consent_title VARCHAR(100) NOT NULL,
			label_snapshot TEXT NOT NULL,
			links_snapshot JSON,
			version VARCHAR(20) NOT NULL,
			consent_method VARCHAR(255) NOT NULL,
			ip_address VARCHAR(45),
			user_agent TEXT,
			source VARCHAR(50),
			created_at_gmt DATETIME NOT NULL,
			PRIMARY KEY  (id),
			KEY user_id (user_id),
			KEY consent_title (consent_title),
			KEY created_at_gmt (created_at_gmt)
		) {$charset_collate};";
	}
}
