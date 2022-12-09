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
	 * Register hooks
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'init_upgrader' ) );
		$base_name = tutor()->basename;
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
				flush_rewrite_rules();
			}
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
		$exists_email_queue_table = $wpdb->query( "SHOW TABLES LIKE '{$wpdb->tutor_email_queue}';" );
		$charset_collate          = $wpdb->get_charset_collate();

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		if ( ! $exists_email_queue_table ) {
			$table = "CREATE TABLE IF NOT EXISTS {$wpdb->tutor_email_queue} (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				mail_to varchar(255) NOT NULL,
				subject text NOT NULL,
				message text NOT NULL,
				headers text NOT NULL,
				PRIMARY KEY (id)
			) {$charset_collate};";

			dbDelta( $table );
		}
	}
}
