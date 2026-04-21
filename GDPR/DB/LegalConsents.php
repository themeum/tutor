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
			compliance_key VARCHAR(100) NOT NULL,
			title VARCHAR(255) NOT NULL,
			label_text TEXT NOT NULL,
			policy_url TEXT NULL,
			version VARCHAR(20) NOT NULL,
			is_required TINYINT(1) DEFAULT 0,
			is_active TINYINT(1) DEFAULT 1,
			placements TEXT NULL,
			settings JSON NULL,
			created_at_utc DATETIME NOT NULL,
			updated_at_utc DATETIME NULL,
			INDEX (compliance_key),
			INDEX (is_active)
		) {$charset_collate};";
	}
}
