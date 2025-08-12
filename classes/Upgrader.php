<?php
/**
 * Tutor up grader
 *
 * @package Tutor\UpGrader
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.0.0
 */

namespace TUTOR;

use Tutor\Ecommerce\CartController;
use Tutor\Ecommerce\CheckoutController;
use Tutor\Helpers\QueryHelper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Manage up grade
 *
 * @since 1.0.0
 */
class Upgrader {

	/**
	 * Installed version number
	 *
	 * @since 2.6.0
	 *
	 * @var string
	 */
	public $installed_version;

	/**
	 * Register hooks
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->installed_version = get_option( 'tutor_version' );

		add_action( 'admin_init', array( $this, 'init_upgrader' ) );

		/**
		 * Installing Gradebook Addon from TutorPro
		 */
		add_action( 'tutor_addon_before_enable_tutor-pro/addons/gradebook/gradebook.php', array( $this, 'install_gradebook' ) );
		add_action( 'tutor_addon_before_enable_tutor-pro/addons/tutor-email/tutor-email.php', array( $this, 'install_tutor_email_queue' ) );
		add_action( 'upgrader_process_complete', array( $this, 'init_email_table_deployment' ), 10, 2 );
	}

	/**
	 * Init up grader
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function init_upgrader() {
		$upgrades = $this->available_upgrades();

		if ( tutor_utils()->count( $upgrades ) ) {
			foreach ( $upgrades as $upgrade ) {
				$this->{$upgrade}();
			}
		}
	}

	/**
	 * Check availability
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function available_upgrades() {
		$version = get_option( 'tutor_version' );

		$upgrades = array();
		if ( $version ) {
			$upgrades[] = 'upgrade_to_1_3_1';
			$upgrades[] = 'upgrade_to_2_6_0';
			$upgrades[] = 'upgrade_to_3_0_0';
			$upgrades[] = 'upgrade_to_3_7_1';
		}

		return $upgrades;
	}

	/**
	 * Upgrade to version 1.3.1
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function upgrade_to_1_3_1() {
		if ( version_compare( get_option( 'tutor_version' ), '1.3.1', '<' ) ) {
			global $wpdb;

			if ( ! get_option( 'is_course_post_type_updated' ) ) {
				$wpdb->update( $wpdb->posts, array( 'post_type' => tutor()->course_post_type ), array( 'post_type' => 'course' ) );
				update_option( 'is_course_post_type_updated', true );
				update_option( 'tutor_version', '1.3.1' );
				Permalink::set_permalink_flag();
			}
		}
	}

	/**
	 * Migration logic when user upgrade to 2.6.0.
	 *
	 * @return void
	 */
	public function upgrade_to_2_6_0() {
		if ( version_compare( $this->installed_version, '2.6.0', '<' ) ) {
			if ( false === Permalink::update_required() ) {
				Permalink::set_permalink_flag();
			}

			do_action( 'before_tutor_version_upgrade_to_2_6_0', $this->installed_version );
			update_option( 'tutor_version', TUTOR_VERSION );
		}
	}

	/**
	 * Migration logic when user upgrade to 3.0.0.
	 *
	 * @return void
	 */
	public function upgrade_to_3_0_0() {
		global $wpdb;

		if ( version_compare( $this->installed_version, '3.0.0', '<' ) ) {
			$table_name = $wpdb->prefix . 'tutor_orders';
			if ( ! QueryHelper::table_exists( $table_name ) ) {
				Tutor::tutor_activate();
			}
			update_option( 'tutor_version', TUTOR_VERSION );
		}

		// Beta upgrade.
		if ( version_compare( TUTOR_VERSION, '3.0.0-beta2', '>=' ) ) {
			$order_items_table = $wpdb->prefix . 'tutor_order_items';
			if ( ! QueryHelper::column_exist( $order_items_table, 'discount_price' ) ) {
				// If 'discount_price' does not exist, alter the table to add 'discount_price' and 'coupon_code', and update 'sale_price'.
				$wpdb->query(
					//phpcs:ignore
					"ALTER TABLE {$order_items_table}
						ADD COLUMN discount_price VARCHAR(13) DEFAULT NULL,
						ADD COLUMN coupon_code VARCHAR(255) DEFAULT NULL,
						MODIFY COLUMN sale_price VARCHAR(13) NULL"
				);
			}
		}

		// New field added coupon_amount in orders table.
		if ( version_compare( TUTOR_VERSION, '3.0.0-beta4', '>=' ) ) {
			$order_table = $wpdb->prefix . 'tutor_orders';

			$coupon_amount = 'coupon_amount';
			if ( ! QueryHelper::column_exist( $order_table, $coupon_amount ) ) {
				$wpdb->query( "ALTER TABLE {$order_table} ADD COLUMN $coupon_amount DECIMAL(13, 2) DEFAULT NULL AFTER coupon_code" );//phpcs:ignore
			}

			/**
			 * Tax Type: inclusive, exclusive
			 */
			$tax_type = 'tax_type';
			if ( ! QueryHelper::column_exist( $order_table, $tax_type ) ) {
				$wpdb->query( "ALTER TABLE {$order_table} ADD COLUMN $tax_type VARCHAR(50) DEFAULT NULL AFTER discount_reason" );//phpcs:ignore
			}
		}

		CartController::create_cart_page();
		CheckoutController::create_checkout_page();
	}

	/**
	 * Migration logic when user upgrade to 3.7.1
	 *
	 * @return void
	 */
	public function upgrade_to_3_7_1() {
		global $wpdb;

		/**
		 * Result column and index added.
		 */
		$result        = 'result';
		$attempt_table = $wpdb->tutor_quiz_attempts;
		if ( QueryHelper::table_exists( $attempt_table ) && ! QueryHelper::column_exist( $attempt_table, $result ) ) {
			$wpdb->query( "ALTER TABLE {$attempt_table} ADD COLUMN result VARCHAR(10);" ); //phpcs:ignore

			// Index Added to improve query performance.
			$wpdb->query(
				//phpcs:ignore
				"ALTER TABLE {$attempt_table}
					ADD INDEX (course_id),
					ADD INDEX (quiz_id),
					ADD INDEX (user_id),
					ADD INDEX (result);"
			);
		}

		/**
		 * For content bank question.
		 * Column `content_id` is added to the `tutor_quiz_questions` table
		 *
		 * @since 3.7.0
		 */
		$question_table = $wpdb->prefix . 'tutor_quiz_questions';
		if ( QueryHelper::table_exists( $question_table ) && ! QueryHelper::column_exist( $question_table, 'content_id' ) ) {
			$wpdb->query( "ALTER TABLE {$question_table} ADD COLUMN content_id BIGINT UNSIGNED DEFAULT NULL AFTER question_id" ); //phpcs:ignore
			$wpdb->query( "ALTER TABLE {$question_table} ADD INDEX content_id(content_id)" );//phpcs:ignore
		}
	}

	/**
	 * Installing Gradebook if Tutor Pro exists
	 *
	 * @since v1.4.2
	 *
	 * @return void
	 */
	public function install_gradebook() {
		global $wpdb;

		$exists_gradebook_table         = $wpdb->query( "SHOW TABLES LIKE '{$wpdb->tutor_gradebooks}';" );
		$exists_gradebook_results_table = $wpdb->query( "SHOW TABLES LIKE '{$wpdb->tutor_gradebooks_results}';" );
		$charset_collate                = $wpdb->get_charset_collate();

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		if ( ! $exists_gradebook_table ) {
			$gradebook_table = "CREATE TABLE IF NOT EXISTS {$wpdb->tutor_gradebooks} (
				gradebook_id bigint(20) NOT NULL AUTO_INCREMENT,
				grade_name varchar(50) DEFAULT NULL,
				grade_point varchar(20) DEFAULT NULL,
				grade_point_to varchar(20) DEFAULT NULL,
				percent_from int(3) DEFAULT NULL,
				percent_to int(3) DEFAULT NULL,
				grade_config longtext,
				PRIMARY KEY (gradebook_id)
			) $charset_collate;";
			dbDelta( $gradebook_table );
		}
		if ( ! $exists_gradebook_results_table ) {
			$gradebook_results = "CREATE TABLE IF NOT EXISTS {$wpdb->tutor_gradebooks_results} (
				gradebook_result_id bigint(20) NOT NULL AUTO_INCREMENT,
				user_id bigint(20) DEFAULT NULL,
				course_id bigint(20) DEFAULT NULL,
				quiz_id bigint(20) DEFAULT NULL,
				assignment_id bigint(20) DEFAULT NULL,
				gradebook_id bigint(20) DEFAULT NULL,
				result_for varchar(50) DEFAULT NULL,
				grade_name varchar(50) DEFAULT NULL,
				grade_point varchar(20) DEFAULT NULL,
				earned_grade_point varchar(20) DEFAULT NULL,
				earned_percent int(3) DEFAULT NULL,
				generate_date datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
				update_date datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
				PRIMARY KEY (gradebook_result_id)
			) {$charset_collate};";
			dbDelta( $gradebook_results );
		}
	}

	/**
	 * Email table deployment
	 *
	 * @param mixed $upgrader_object up grader obj.
	 * @param mixed $options options.
	 *
	 * @return void
	 */
	public function init_email_table_deployment( $upgrader_object, $options ) {

		if ( is_object( $upgrader_object ) && is_array( $upgrader_object->result ) && isset( $upgrader_object->result['destination_name'] ) && 'tutor-pro' == $upgrader_object->result['destination_name'] ) {
			$addon_config = tutor_utils()->get_addon_config( 'tutor-pro/addons/tutor-email/tutor-email.php' );
			$is_enable    = (bool) tutor_utils()->avalue_dot( 'is_enable', $addon_config );

			$is_enable ? $this->install_tutor_email_queue() : 0;
		}
	}

	/**
	 * Installing email addon if Tutor Pro exists
	 *
	 * @since 1.8.6
	 *
	 * @return void
	 */
	public function install_tutor_email_queue() {

		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		if ( ! QueryHelper::table_exists( $wpdb->tutor_email_queue ) ) {
			$table = "CREATE TABLE IF NOT EXISTS {$wpdb->tutor_email_queue} (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				mail_to varchar(255) NOT NULL,
				subject text NOT NULL,
				message text NOT NULL,
				headers text NOT NULL,
				batch varchar(50) NULL,
				PRIMARY KEY (id)
			) {$charset_collate};";

			dbDelta( $table );
		}
	}
}
