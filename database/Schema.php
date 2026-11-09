<?php
/**
 * Database Schemas for Tutor LMS.
 *
 * Houses table schema definitions with dynamic prefix resolution and
 * unique foreign key constraint naming for WordPress Multisite compatibility.
 *
 * @package Tutor\Database
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.3.0
 */

namespace Tutor\Database;

use Tutor\Core\DB;

defined( 'ABSPATH' ) || exit;

/**
 * Class Schemas
 *
 * @since 4.3.0
 */
class Schema {

	/**
	 * Generate unique constraint name per blog prefix.
	 * Truncates to 64 chars (MySQL max identifier length) using md5 hash if needed.
	 *
	 * @since 4.3.0
	 *
	 * @param string $name Base constraint name.
	 *
	 * @return string Unique constraint identifier.
	 */
	public static function fk_name( string $name ): string {
		global $wpdb;
		$fk = $wpdb->prefix . $name;
		return strlen( $fk ) <= 64 ? $fk : 'fk_' . substr( md5( $fk ), 0, 32 );
	}

	/**
	 * Get only the Core schemas needed for initial site creation.
	 * Addon tables (gradebook, email queue, subscriptions) are excluded here.
	 *
	 * @since 4.3.0
	 *
	 * @param bool $include_constraints Whether to include foreign key constraints.
	 *
	 * @return array<string>
	 */
	public static function get_core_schemas( bool $include_constraints = true ): array {
		return array(
			self::get_quiz_attempts_schema( $include_constraints ),
			self::get_quiz_attempt_answers_schema( $include_constraints ),
			self::get_quiz_questions_schema( $include_constraints ),
			self::get_quiz_question_answers_schema( $include_constraints ),
			self::get_earnings_schema( $include_constraints ),
			self::get_withdraws_schema( $include_constraints ),
			self::get_orders_schema( $include_constraints ),
			self::get_ordermeta_schema( $include_constraints ),
			self::get_order_items_schema( $include_constraints ),
			self::get_order_itemmeta_schema( $include_constraints ),
			self::get_coupons_schema( $include_constraints ),
			self::get_coupon_applications_schema( $include_constraints ),
			self::get_coupon_usages_schema( $include_constraints ),
			self::get_carts_schema( $include_constraints ),
			self::get_cart_items_schema( $include_constraints ),
			self::get_customers_schema( $include_constraints ),
			self::get_scheduler_schema( $include_constraints ),
		);
	}

	/**
	 * Get all tutor database schemas.
	 *
	 * @since 4.3.0
	 *
	 * @param bool $include_constraints Whether to include foreign key constraints.
	 *
	 * @return array<string>
	 */
	public static function get_all_schema( $include_constraints = true ): array {
		$core_schema = static::get_core_schemas( $include_constraints );
		return apply_filters( 'tutor_database_schemas', $core_schema, $include_constraints );
	}

	/* ---------------- Core Schemas ---------------- */

	/**
	 * Get quiz attempts schema.
	 *
	 * @since 4.3.0
	 *
	 * @param bool $include_constraints Whether to include foreign key constraints.
	 * @return string
	 */
	public static function get_quiz_attempts_schema( bool $include_constraints = true ): string {
		global $wpdb;
		$table   = DB::prepare_table_name( 'tutor_quiz_attempts' );
		$collate = $wpdb->get_charset_collate();

		return "CREATE TABLE {$table} (
			attempt_id bigint(20) NOT NULL AUTO_INCREMENT,
			course_id bigint(20) DEFAULT NULL,
			quiz_id bigint(20) DEFAULT NULL,
			user_id bigint(20) DEFAULT NULL,
			total_questions int(11) DEFAULT NULL,
			total_answered_questions int(11) DEFAULT NULL,
			total_marks decimal(9,2) DEFAULT NULL,
			earned_marks decimal(9,2) DEFAULT NULL,
			attempt_info text,
			attempt_status varchar(50) DEFAULT NULL,
			attempt_ip varchar(250) DEFAULT NULL,
			attempt_started_at datetime DEFAULT NULL,
			attempt_ended_at datetime DEFAULT NULL,
			is_manually_reviewed int(1) DEFAULT NULL,
			manually_reviewed_at datetime DEFAULT NULL,
			result varchar(10) DEFAULT NULL,
			PRIMARY KEY  (attempt_id),
			INDEX (course_id),
			INDEX (quiz_id),
			INDEX (user_id),
			INDEX (result)
		) {$collate};";
	}

	/**
	 * Get quiz attempt answers schema.
	 *
	 * @since 4.3.0
	 *
	 * @param bool $include_constraints Whether to include foreign key constraints.
	 * @return string
	 */
	public static function get_quiz_attempt_answers_schema( bool $include_constraints = true ): string {
		global $wpdb;
		$table   = DB::prepare_table_name( 'tutor_quiz_attempt_answers' );
		$collate = $wpdb->get_charset_collate();

		return "CREATE TABLE {$table} (
			attempt_answer_id bigint(20) NOT NULL AUTO_INCREMENT,
			user_id bigint(20) DEFAULT NULL,
			quiz_id bigint(20) DEFAULT NULL,
			question_id bigint(20) DEFAULT NULL,
			quiz_attempt_id bigint(20) DEFAULT NULL,
			given_answer longtext,
			question_mark decimal(8,2) DEFAULT NULL,
			achieved_mark decimal(8,2) DEFAULT NULL,
			minus_mark decimal(8,2) DEFAULT NULL,
			is_correct tinyint(4) DEFAULT NULL,
			PRIMARY KEY  (attempt_answer_id)
		) {$collate};";
	}

	/**
	 * Get quiz questions schema.
	 *
	 * @since 4.3.0
	 *
	 * @param bool $include_constraints Whether to include foreign key constraints.
	 * @return string
	 */
	public static function get_quiz_questions_schema( bool $include_constraints = true ): string {
		global $wpdb;
		$table   = DB::prepare_table_name( 'tutor_quiz_questions' );
		$collate = $wpdb->get_charset_collate();

		return "CREATE TABLE {$table} (
			question_id bigint(20) NOT NULL AUTO_INCREMENT,
			quiz_id bigint(20) DEFAULT NULL,
			question_title text,
			question_description longtext,
			answer_explanation longtext DEFAULT '',
			question_type varchar(50) DEFAULT NULL,
			question_mark decimal(9,2) DEFAULT NULL,
			question_settings longtext,
			question_order int(11) DEFAULT NULL,
			PRIMARY KEY (question_id)
		) {$collate};";
	}

	/**
	 * Get quiz question answers schema.
	 *
	 * @since 4.3.0
	 *
	 * @param bool $include_constraints Whether to include foreign key constraints.
	 * @return string
	 */
	public static function get_quiz_question_answers_schema( bool $include_constraints = true ): string {
		global $wpdb;
		$table   = DB::prepare_table_name( 'tutor_quiz_question_answers' );
		$collate = $wpdb->get_charset_collate();

		return "CREATE TABLE {$table} (
			answer_id bigint(20) NOT NULL AUTO_INCREMENT,
			belongs_question_id bigint(20) DEFAULT NULL,
			belongs_question_type varchar(250) DEFAULT NULL,
			answer_title text,
			is_correct tinyint(4) DEFAULT NULL,
			image_id bigint(20) DEFAULT NULL,
			answer_two_gap_match text,
			answer_view_format varchar(250) DEFAULT NULL,
			answer_settings text,
			answer_order int(11) DEFAULT '0',
			PRIMARY KEY (answer_id)
		) {$collate};";
	}

	/**
	 * Get earnings schema.
	 *
	 * @since 4.3.0
	 *
	 * @param bool $include_constraints Whether to include foreign key constraints.
	 * @return string
	 */
	public static function get_earnings_schema( bool $include_constraints = true ): string {
		global $wpdb;
		$table   = DB::prepare_table_name( 'tutor_earnings' );
		$collate = $wpdb->get_charset_collate();

		return "CREATE TABLE IF NOT EXISTS {$table} (
			earning_id bigint(20) NOT NULL AUTO_INCREMENT,
			user_id bigint(20) DEFAULT NULL,
			course_id bigint(20) DEFAULT NULL,
			order_id bigint(20) DEFAULT NULL,
			order_status varchar(50) DEFAULT NULL,
			course_price_total decimal(16,2) DEFAULT NULL,
			course_price_grand_total decimal(16,2) DEFAULT NULL,
			instructor_amount decimal(16,2) DEFAULT NULL,
			instructor_rate decimal(16,2) DEFAULT NULL,
			admin_amount decimal(16,2) DEFAULT NULL,
			admin_rate decimal(16,2) DEFAULT NULL,
			commission_type varchar(20) DEFAULT NULL,
			deduct_fees_amount decimal(16,2) DEFAULT NULL,
			deduct_fees_name varchar(250) DEFAULT NULL,
			deduct_fees_type varchar(20) DEFAULT NULL,
			process_by varchar(20) DEFAULT NULL,
			created_at datetime DEFAULT NULL,
			PRIMARY KEY (earning_id),
			INDEX (user_id),
			INDEX (course_id),
			INDEX (order_id),
			INDEX (process_by)
		) {$collate};";
	}

	/**
	 * Get withdraws schema.
	 *
	 * @since 4.3.0
	 *
	 * @param bool $include_constraints Whether to include foreign key constraints.
	 * @return string
	 */
	public static function get_withdraws_schema( bool $include_constraints = true ): string {
		global $wpdb;
		$table   = DB::prepare_table_name( 'tutor_withdraws' );
		$collate = $wpdb->get_charset_collate();

		return "CREATE TABLE IF NOT EXISTS {$table} (
			withdraw_id bigint(20) NOT NULL AUTO_INCREMENT,
			user_id bigint(20) DEFAULT NULL,
			amount decimal(16,2) DEFAULT NULL,
			method_data text DEFAULT NULL,
			status varchar(50) DEFAULT NULL,
			updated_at datetime DEFAULT NULL,
			created_at datetime DEFAULT NULL,
			PRIMARY KEY (withdraw_id)
		) {$collate};";
	}

	/**
	 * Get orders schema.
	 *
	 * @since 4.3.0
	 *
	 * @param bool $include_constraints Whether to include foreign key constraints.
	 * @return string
	 */
	public static function get_orders_schema( bool $include_constraints = true ): string {
		global $wpdb;
		$table   = DB::prepare_table_name( 'tutor_orders' );
		$collate = $wpdb->get_charset_collate();

		return "CREATE TABLE {$table} (
			id BIGINT(20) UNSIGNED AUTO_INCREMENT,
			parent_id BIGINT(20) UNSIGNED DEFAULT 0,
			transaction_id VARCHAR(255),
			user_id BIGINT(20) UNSIGNED NOT NULL,
			order_type VARCHAR(50) NOT NULL,
			order_status VARCHAR(50) NOT NULL,
			payment_status VARCHAR(50) NOT NULL,
			subtotal_price DECIMAL(13, 2) NOT NULL,
			pre_tax_price DECIMAL(13, 2) NOT NULL,
			tax_type VARCHAR(50),
			tax_rate DECIMAL(13, 2),
			tax_amount DECIMAL(13, 2),
			total_price DECIMAL(13, 2) NOT NULL,
			net_payment DECIMAL(13, 2) NOT NULL,
			coupon_code VARCHAR(255),
			coupon_amount DECIMAL(13, 2),
			discount_type ENUM('percentage', 'flat') DEFAULT NULL,
			discount_amount DECIMAL(13, 2),
			discount_reason TEXT,
			fees DECIMAL(13, 2),
			earnings DECIMAL(13, 2),
			refund_amount DECIMAL(13, 2),
			payment_method VARCHAR(255),
			payment_payloads LONGTEXT,
			note TEXT,
			created_at_gmt DATETIME NOT NULL,
			created_by BIGINT(20) UNSIGNED NOT NULL,
			updated_at_gmt DATETIME,
			updated_by BIGINT(20) UNSIGNED NOT NULL,
			PRIMARY KEY (id),
			KEY user_id (user_id),
			KEY order_type (order_type),
			KEY payment_status (payment_status),
			KEY order_status (order_status),
			KEY transaction_id (transaction_id)
		) {$collate};";
	}

	/**
	 * Get ordermeta schema.
	 *
	 * @since 4.3.0
	 *
	 * @param bool $include_constraints Whether to include foreign key constraints.
	 * @return string
	 */
	public static function get_ordermeta_schema( bool $include_constraints = true ): string {
		global $wpdb;
		$table        = DB::prepare_table_name( 'tutor_ordermeta' );
		$orders_table = DB::prepare_table_name( 'tutor_orders' );
		$collate      = $wpdb->get_charset_collate();

		$constraints = '';
		if ( $include_constraints ) {
			$fk_name     = self::fk_name( 'fk_tutor_ordermeta_order_id' );
			$constraints = ", CONSTRAINT {$fk_name} FOREIGN KEY (order_id) REFERENCES {$orders_table}(id) ON DELETE CASCADE";
		}

		return "CREATE TABLE {$table} (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			order_id BIGINT(20) UNSIGNED NOT NULL,
			meta_key VARCHAR(255) NOT NULL,
			meta_value LONGTEXT NOT NULL,
			created_at_gmt DATETIME NOT NULL,
			created_by BIGINT(20) UNSIGNED NOT NULL,
			updated_at_gmt DATETIME,
			updated_by BIGINT(20) UNSIGNED NOT NULL,
			PRIMARY KEY (id),
			KEY order_id (order_id),
			KEY meta_key (meta_key){$constraints}
		) {$collate};";
	}

	/**
	 * Get order items schema.
	 *
	 * @since 4.3.0
	 *
	 * @param bool $include_constraints Whether to include foreign key constraints.
	 * @return string
	 */
	public static function get_order_items_schema( bool $include_constraints = true ): string {
		global $wpdb;
		$table        = DB::prepare_table_name( 'tutor_order_items' );
		$orders_table = DB::prepare_table_name( 'tutor_orders' );
		$collate      = $wpdb->get_charset_collate();

		$constraints = '';
		if ( $include_constraints ) {
			$fk_name     = self::fk_name( 'fk_tutor_order_item_order_id' );
			$constraints = ", CONSTRAINT {$fk_name} FOREIGN KEY (order_id) REFERENCES {$orders_table}(id) ON DELETE CASCADE";
		}

		return "CREATE TABLE {$table} (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			order_id BIGINT(20) UNSIGNED NOT NULL,
			item_id BIGINT(20) UNSIGNED NOT NULL,
			regular_price DECIMAL(13, 2) NOT NULL,
			sale_price VARCHAR(13) DEFAULT NULL,
			discount_price VARCHAR(13) DEFAULT NULL,
			coupon_code VARCHAR(255) DEFAULT NULL,
			PRIMARY KEY (id),
			KEY order_id (order_id),
			KEY item_id (item_id){$constraints}
		) {$collate};";
	}

	/**
	 * Get order itemmeta schema.
	 *
	 * @since 4.3.0
	 *
	 * @param bool $include_constraints Whether to include foreign key constraints.
	 * @return string
	 */
	public static function get_order_itemmeta_schema( bool $include_constraints = true ): string {
		global $wpdb;
		$table             = DB::prepare_table_name( 'tutor_order_itemmeta' );
		$order_items_table = DB::prepare_table_name( 'tutor_order_items' );
		$collate           = $wpdb->get_charset_collate();

		$constraints = '';
		if ( $include_constraints ) {
			$fk_name     = self::fk_name( 'fk_tutor_itemmeta' );
			$constraints = ", CONSTRAINT {$fk_name} FOREIGN KEY (item_id) REFERENCES {$order_items_table}(id) ON DELETE CASCADE";
		}

		return "CREATE TABLE {$table} (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			item_id BIGINT(20) UNSIGNED NOT NULL,
			meta_key VARCHAR(255) NOT NULL,
			meta_value LONGTEXT NOT NULL,
			PRIMARY KEY (id),
			KEY item_id (item_id),
			KEY meta_key (meta_key){$constraints}
		) {$collate};";
	}

	/**
	 * Get coupons schema.
	 *
	 * @since 4.3.0
	 *
	 * @param bool $include_constraints Whether to include foreign key constraints.
	 * @return string
	 */
	public static function get_coupons_schema( bool $include_constraints = true ): string {
		global $wpdb;
		$table   = DB::prepare_table_name( 'tutor_coupons' );
		$collate = $wpdb->get_charset_collate();

		return "CREATE TABLE {$table} (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			coupon_status VARCHAR(50),
			coupon_type VARCHAR(100) DEFAULT 'code',
			coupon_code VARCHAR(50) NOT NULL,
			coupon_title VARCHAR(255) NOT NULL,
			coupon_description TEXT,
			discount_type ENUM('percentage', 'flat') NOT NULL,
			discount_amount DECIMAL(13, 2) NOT NULL,
			applies_to VARCHAR(100) DEFAULT 'all_courses_and_bundles',
			total_usage_limit INT(10) UNSIGNED DEFAULT NULL,
			per_user_usage_limit TINYINT(4) UNSIGNED DEFAULT NULL,
			purchase_requirement VARCHAR(50) DEFAULT 'no_minimum',
			purchase_requirement_value DECIMAL(13, 2),
			start_date_gmt DATETIME NOT NULL,
			expire_date_gmt DATETIME DEFAULT NULL,
			created_at_gmt DATETIME NOT NULL,
			created_by BIGINT(20) UNSIGNED NOT NULL,
			updated_at_gmt DATETIME,
			updated_by BIGINT(20) UNSIGNED NOT NULL,
			PRIMARY KEY (id),
			UNIQUE KEY coupon_code (coupon_code),
			KEY start_date_gmt (start_date_gmt),
			KEY expire_date_gmt (expire_date_gmt)
		) {$collate};";
	}

	/**
	 * Get coupon applications schema.
	 *
	 * @since 4.3.0
	 *
	 * @param bool $include_constraints Whether to include foreign key constraints.
	 * @return string
	 */
	public static function get_coupon_applications_schema( bool $include_constraints = true ): string {
		global $wpdb;
		$table         = DB::prepare_table_name( 'tutor_coupon_applications' );
		$coupons_table = DB::prepare_table_name( 'tutor_coupons' );
		$collate       = $wpdb->get_charset_collate();

		$constraints = '';
		if ( $include_constraints ) {
			$fk_name     = self::fk_name( 'fk_tutor_coupon_application_coupon_code' );
			$constraints = ", CONSTRAINT {$fk_name} FOREIGN KEY (coupon_code) REFERENCES {$coupons_table}(coupon_code) ON DELETE CASCADE";
		}

		return "CREATE TABLE {$table} (
			coupon_code VARCHAR(50) NOT NULL,
			reference_id BIGINT(20) UNSIGNED NOT NULL,
			KEY coupon_code (coupon_code),
			KEY reference_id (reference_id){$constraints}
		) {$collate};";
	}

	/**
	 * Get coupon usages schema.
	 *
	 * @since 4.3.0
	 *
	 * @param bool $include_constraints Whether to include foreign key constraints.
	 * @return string
	 */
	public static function get_coupon_usages_schema( bool $include_constraints = true ): string {
		global $wpdb;
		$table           = DB::prepare_table_name( 'tutor_coupon_usages' );
		$coupons_table   = DB::prepare_table_name( 'tutor_coupons' );
		$users_table     = DB::prepare_table_name( 'users' );
		$collate         = $wpdb->get_charset_collate();
		$status_consumed = 'consumed';

		$constraints = '';
		if ( $include_constraints ) {
			$fk_coupon   = self::fk_name( 'fk_tutor_coupon_usage_coupon_code' );
			$fk_user     = self::fk_name( 'fk_tutor_coupon_usage_user_id' );
			$constraints = ",
			CONSTRAINT {$fk_coupon} FOREIGN KEY (coupon_code) REFERENCES {$coupons_table}(coupon_code) ON DELETE CASCADE,
			CONSTRAINT {$fk_user} FOREIGN KEY (user_id) REFERENCES {$users_table}(ID) ON DELETE CASCADE";
		}

		return "CREATE TABLE {$table} (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			coupon_code VARCHAR(50) NOT NULL,
			user_id BIGINT(20) UNSIGNED NOT NULL,
			order_id BIGINT(20) UNSIGNED DEFAULT NULL,
			status VARCHAR(20) NOT NULL DEFAULT '{$status_consumed}',
			expires_at_gmt DATETIME DEFAULT NULL,
			created_at_gmt DATETIME NOT NULL,
			PRIMARY KEY (id),
			UNIQUE KEY coupon_order (coupon_code, order_id),
			KEY coupon_code (coupon_code),
			KEY user_id (user_id),
			KEY order_id (order_id),
			KEY status (status){$constraints}
		) {$collate};";
	}

	/**
	 * Get carts schema.
	 *
	 * @since 4.3.0
	 *
	 * @param bool $include_constraints Whether to include foreign key constraints.
	 * @return string
	 */
	public static function get_carts_schema( bool $include_constraints = true ): string {
		global $wpdb;
		$table       = DB::prepare_table_name( 'tutor_carts' );
		$users_table = DB::prepare_table_name( 'users' );
		$collate     = $wpdb->get_charset_collate();

		$constraints = '';
		if ( $include_constraints ) {
			$fk_user     = self::fk_name( 'fk_tutor_cart_user_id' );
			$constraints = ", CONSTRAINT {$fk_user} FOREIGN KEY (user_id) REFERENCES {$users_table}(ID) ON DELETE CASCADE";
		}

		return "CREATE TABLE {$table} (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			user_id BIGINT(20) UNSIGNED DEFAULT NULL,
			coupon_code VARCHAR(50) DEFAULT NULL,
			created_at_gmt DATETIME NOT NULL,
			updated_at_gmt DATETIME,
			PRIMARY KEY (id),
			KEY user_id (user_id),
			KEY coupon_code (coupon_code){$constraints}
		) {$collate};";
	}

	/**
	 * Get cart items schema.
	 *
	 * @since 4.3.0
	 *
	 * @param bool $include_constraints Whether to include foreign key constraints.
	 * @return string
	 */
	public static function get_cart_items_schema( bool $include_constraints = true ): string {
		global $wpdb;
		$table       = DB::prepare_table_name( 'tutor_cart_items' );
		$carts_table = DB::prepare_table_name( 'tutor_carts' );
		$posts_table = DB::prepare_table_name( 'posts' );
		$collate     = $wpdb->get_charset_collate();

		$constraints = '';
		if ( $include_constraints ) {
			$fk_cart     = self::fk_name( 'fk_tutor_cart_item_cart_id' );
			$fk_post     = self::fk_name( 'fk_tutor_cart_item_course_id' );
			$constraints = ",
			CONSTRAINT {$fk_cart} FOREIGN KEY (cart_id) REFERENCES {$carts_table}(id) ON DELETE CASCADE,
			CONSTRAINT {$fk_post} FOREIGN KEY (course_id) REFERENCES {$posts_table}(ID) ON DELETE CASCADE";
		}

		return "CREATE TABLE {$table} (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			cart_id BIGINT(20) UNSIGNED NOT NULL,
			course_id BIGINT(20) UNSIGNED NOT NULL,
			item_type VARCHAR(255),
			item_details JSON,
			PRIMARY KEY (id),
			KEY cart_id (cart_id),
			KEY course_id (course_id){$constraints}
		) {$collate};";
	}

	/**
	 * Get customers schema.
	 *
	 * @since 4.3.0
	 *
	 * @param bool $include_constraints Whether to include foreign key constraints.
	 * @return string
	 */
	public static function get_customers_schema( bool $include_constraints = true ): string {
		global $wpdb;
		$table   = DB::prepare_table_name( 'tutor_customers' );
		$collate = $wpdb->get_charset_collate();

		return "CREATE TABLE {$table} (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			user_id BIGINT(20) UNSIGNED DEFAULT NULL,
			billing_first_name VARCHAR(255) NOT NULL,
			billing_last_name VARCHAR(255) NOT NULL,
			billing_email VARCHAR(255) NOT NULL,
			billing_phone VARCHAR(20) NOT NULL,
			billing_zip_code VARCHAR(20) NOT NULL,
			billing_address TEXT NOT NULL,
			billing_country VARCHAR(100) NOT NULL,
			billing_state VARCHAR(100) NOT NULL,
			billing_city VARCHAR(100) NOT NULL,
			PRIMARY KEY (id),
			KEY user_id (user_id),
			KEY billing_email (billing_email)
		) {$collate};";
	}

	/**
	 * Get scheduler schema.
	 *
	 * @since 4.3.0
	 *
	 * @param bool $include_constraints Whether to include foreign key constraints.
	 * @return string
	 */
	public static function get_scheduler_schema( bool $include_constraints = true ): string {
		global $wpdb;
		$table   = DB::prepare_table_name( 'tutor_scheduler' );
		$collate = $wpdb->get_charset_collate();

		return "CREATE TABLE {$table} (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			type VARCHAR(50) NOT NULL COMMENT 'Type of schedule, e.g., gift, email, reminder',
			reference_id VARCHAR(255) NOT NULL COMMENT 'Unique reference id, token, etc',
			scheduled_at_gmt DATETIME NOT NULL COMMENT 'When the action should be executed',
			status VARCHAR(255) NOT NULL DEFAULT 'processing',
			payload LONGTEXT,
			created_at_gmt DATETIME,
			updated_at_gmt DATETIME,
			scheduled_by BIGINT UNSIGNED COMMENT 'User who scheduled the action',
			scheduled_for BIGINT UNSIGNED COMMENT 'Target user of the scheduled action',
			PRIMARY KEY (id),
			KEY idx_context_status (type, status),
			KEY idx_status (status),
			KEY idx_scheduled_at_gmt (scheduled_at_gmt)
		) {$collate};";
	}
}
