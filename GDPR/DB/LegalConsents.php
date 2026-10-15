<?php
/**
 * GDPR legal consents table.
 *
 * @package Tutor\GDPR\DB
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

namespace Tutor\GDPR\DB;

defined( 'ABSPATH' ) || exit;

/**
 * Legal consents table class.
 */
class LegalConsents extends DB {

	/**
	 * Get table name.
	 *
	 * @since 4.0.0
	 *
	 * @return string
	 */
	public static function get_table_name() {
		global $wpdb;
		return $wpdb->prefix . 'tutor_legal_consents';
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
			consent_title VARCHAR(255) NOT NULL,
			display_on TEXT NOT NULL,
			consent_message TEXT NOT NULL,
			consent_map JSON,
			version VARCHAR(20) NOT NULL,
			consent_method VARCHAR(255) NOT NULL,
			is_active TINYINT(1) DEFAULT 1,
			settings JSON,
			created_at_gmt DATETIME NOT NULL,
			updated_at_gmt DATETIME,
			PRIMARY KEY  (id),
			KEY consent_title (consent_title),
			KEY is_active (is_active)
		) {$charset_collate};";
	}
}
