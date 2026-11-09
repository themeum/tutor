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

use Tutor\Database\TableManager;
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
			// Required to use dbDelta.
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';

			$upgrades[] = 'upgrade_to_1_3_1';
			$upgrades[] = 'upgrade_to_2_6_0';
			$upgrades[] = 'upgrade_to_3_0_0';
			$upgrades[] = 'upgrade_to_3_7_1';
			$upgrades[] = 'upgrade_to_3_8_0';
			$upgrades[] = 'upgrade_to_3_8_2';
			$upgrades[] = 'upgrade_to_4_2_0';
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
			if ( QueryHelper::table_exists( $order_items_table ) && ! QueryHelper::column_exist( $order_items_table, 'discount_price' ) ) {
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
			if ( QueryHelper::table_exists( $order_table ) && ! QueryHelper::column_exist( $order_table, $coupon_amount ) ) {
				$wpdb->query( "ALTER TABLE {$order_table} ADD COLUMN $coupon_amount DECIMAL(13, 2) DEFAULT NULL AFTER coupon_code" );//phpcs:ignore
			}

			/**
			 * Tax Type: inclusive, exclusive
			 */
			$tax_type = 'tax_type';
			if ( QueryHelper::table_exists( $order_table ) && ! QueryHelper::column_exist( $order_table, $tax_type ) ) {
				$wpdb->query( "ALTER TABLE {$order_table} ADD COLUMN $tax_type VARCHAR(50) DEFAULT NULL AFTER pre_tax_price" );//phpcs:ignore
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
	 * Migration logic when user upgrade to 3.8.0
	 *
	 * @return void
	 */
	public function upgrade_to_3_8_0() {
		if ( version_compare( TUTOR_VERSION, '3.8.0', '>=' ) ) {
			global $wpdb;
			$order_table = $wpdb->prefix . 'tutor_orders';
			if ( QueryHelper::table_exists( $order_table ) && ! QueryHelper::column_exist( $order_table, 'pre_tax_price' ) ) {
				$wpdb->query( "ALTER TABLE {$order_table} ADD COLUMN pre_tax_price DECIMAL(13, 2) NOT NULL AFTER subtotal_price" ); //phpcs:ignore
			}

			$charset_collate = $wpdb->get_charset_collate();
			$scheduler_table = QueryHelper::table_exists( $wpdb->prefix . 'tutor_scheduler' );
			$item_meta_table = QueryHelper::table_exists( $wpdb->prefix . 'tutor_order_itemmeta' );

			// Create tutor_scheduler table.
			if ( ! $scheduler_table ) {
				$table_schema = "CREATE TABLE {$wpdb->prefix}tutor_scheduler (
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
				) $charset_collate;";
				dbDelta( $table_schema );
			}

			// Create order_item_meta table.
			if ( ! $item_meta_table ) {
				$item_meta_table = "CREATE TABLE {$wpdb->prefix}tutor_order_itemmeta (
				id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				item_id BIGINT(20) UNSIGNED NOT NULL,
				meta_key VARCHAR(255) NOT NULL,
				meta_value LONGTEXT NOT NULL,
				PRIMARY KEY (id),
				KEY item_id (item_id),
				KEY meta_key (meta_key),
				CONSTRAINT fk_tutor_itemmeta FOREIGN KEY (item_id) REFERENCES {$wpdb->prefix}tutor_order_items(id) ON DELETE CASCADE
			) $charset_collate;";
				dbDelta( $item_meta_table );
			}

			// Check if the column already exists.
			$cart_item_table = $wpdb->prefix . 'tutor_cart_items';
			$detail_column   = 'item_details';
			$type_column     = 'item_type';

			if ( QueryHelper::table_exists( $cart_item_table ) ) {
				$is_detail_column_exists = QueryHelper::column_exist( $cart_item_table, $detail_column );
				$is_type_column_exists   = QueryHelper::column_exist( $cart_item_table, $type_column );

				if ( ! $is_detail_column_exists ) {
					$wpdb->query( "ALTER TABLE {$cart_item_table} ADD {$detail_column} JSON AFTER course_id" );
				}

				if ( ! $is_type_column_exists ) {
					$wpdb->query( "ALTER TABLE {$cart_item_table} ADD {$type_column} VARCHAR(255) AFTER course_id" );
				}
			}
		}
	}

	/**
	 * Upgrade to version 3.8.2
	 *
	 * @since 3.8.2
	 *
	 * @return void
	 */
	public function upgrade_to_3_8_2() {
		if ( version_compare( $this->installed_version, '3.8.2', '<' ) ) {
			update_option( 'tutor_version', '3.8.2' );

			global $wpdb;
			$earnings_table = $wpdb->prefix . 'tutor_earnings';

			/**
			 * Index added to improve performance.
			 */
			$wpdb->query(
				//phpcs:ignore
				"ALTER TABLE {$earnings_table}
					ADD INDEX (user_id),
					ADD INDEX (course_id),
					ADD INDEX (order_id),
					ADD INDEX (process_by);"
			);

			/**
			 * Update process by to 'tutor' where it is 'Tutor'
			 */
			QueryHelper::update( $earnings_table, array( 'process_by' => 'tutor' ), array( 'process_by' => 'Tutor' ) );
		}
	}


	/**
	 * Upgrade to version 4.2.0
	 *
	 * Add order_id, status, and expiration to tutor_coupon_usages.
	 *
	 * @since 4.2.0
	 *
	 * @return void
	 */
	public function upgrade_to_4_2_0() {
		if ( QueryHelper::table_exists( 'tutor_coupon_usages' ) && ! QueryHelper::column_exist( 'tutor_coupon_usages', 'order_id' ) ) {
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta( Tutor::get_coupon_usage_table_schema( false ) );
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

			if ( $is_enable && class_exists( \TutorPro\Database\Schema::class ) ) {
				TableManager::create_table( \TutorPro\Database\Schema::get_email_queue_table_schema( false ) );
			}
		}
	}
}
