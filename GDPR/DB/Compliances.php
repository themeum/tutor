<?php
/**
 * GDPR compliances table.
 *
 * @package Tutor\GDPR\DB
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

namespace Tutor\GDPR\DB;

defined( 'ABSPATH' ) || exit;

/**
 * Compliances table class.
 */
class Compliances extends DB {

	/**
	 * Get table name.
	 *
	 * @since 4.0.0
	 *
	 * @return string
	 */
	public static function get_table_name() {
		global $wpdb;
		return $wpdb->prefix . 'tutor_gdpr_compliances';
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
            compliance_key VARCHAR(100) NOT NULL, -- privacy_policy, terms, marketing
            title VARCHAR(255) NOT NULL,
            label_text TEXT NOT NULL,
            policy_url TEXT NULL,
            version VARCHAR(20) NOT NULL,

            is_required TINYINT(1) DEFAULT 0,
            is_active TINYINT(1) DEFAULT 1,

            placements TEXT, -- signup,checkout, comma separate value
            settings JSON,   -- extra rules (geo, re-consent, etc)

            created_at_utc DATETIME NOT NULL,
            updated_at_utc DATETIME NULL,

            INDEX (compliance_key),
            INDEX (is_active)
		) {$charset_collate};";
	}
}
