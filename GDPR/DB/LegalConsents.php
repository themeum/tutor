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
			id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			consent_title VARCHAR(255) NOT NULL,
			display_on TEXT NOT NULL, -- comma separate value for multiple scopes
			consent_message TEXT NOT NULL,
			policy_urls TEXT, -- comma separate value for multiple url
			version VARCHAR(20) NOT NULL,
			is_required TINYINT(1) DEFAULT 0,
			is_active TINYINT(1) DEFAULT 1,
			settings JSON,
			created_at_utc DATETIME NOT NULL,
			updated_at_utc DATETIME,
			INDEX (consent_title),
			INDEX (is_active)
		) {$charset_collate};";
	}
}
