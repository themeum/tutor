<?php
/**
 * Manage Addons
 *
 * @package Tutor
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.0.0
 */

namespace TUTOR;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Addons Class
 *
 * @since 1.0.0
 */
class Addons {
	const OPTION_KEY = 'tutor_addons_config';

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function __construct() {
		add_filter( 'tutor_pro_addons_lists_for_display', array( $this, 'addons_lists_to_show' ) );
		add_action( 'wp_ajax_tutor_get_all_addons', array( $this, 'get_all_addons' ) );
		add_action( 'wp_ajax_addon_enable_disable', array( $this, 'addon_enable_disable' ) );
	}

	/**
	 * Obtain addons config list.
	 *
	 * @since 3.0.0
	 *
	 * @return array
	 */
	public static function get_addons_config() {
		$list = get_option( self::OPTION_KEY, array() );
		return $list;
	}

	/**
	 * Status update of tutor addon.
	 *
	 * @since 3.0.0
	 *
	 * @param string $basename basename of addon.
	 * @param bool   $status status 0,1.
	 * @return void
	 */
	public static function update_addon_status( $basename, $status ) {
		$config = self::get_addons_config();
		if ( isset( $config[ $basename ] ) ) {
			$config[ $basename ]['is_enable'] = $status;
			update_option( self::OPTION_KEY, $config );
		}
	}

	/**
	 * Get all addons data.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function get_all_addons() {

		// Check and verify the request.
		tutor_utils()->checking_nonce();

		if ( ! User::is_admin() ) {
			wp_send_json_error( tutor_utils()->error_message() );
		}

		// All good, let's proceed.
		$all_addons = $this->prepare_addons_data();

		wp_send_json_success(
			array(
				'addons' => $all_addons,
			)
		);
	}

	/**
	 * Prepare addons data.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function prepare_addons_data() {
		$addons       = apply_filters( 'tutor_addons_lists_config', array() );
		$plugins_data = $addons;

		if ( is_array( $addons ) && count( $addons ) ) {
			foreach ( $addons as $base_name => $addon ) {
				$addon_config = tutor_utils()->get_addon_config( $base_name );
				$is_enabled   = (bool) tutor_utils()->avalue_dot( 'is_enable', $addon_config );

				$plugins_data[ $base_name ]['is_enabled'] = $is_enabled;

				$thumbnail_url = tutor()->url . 'assets/images/tutor-plugin.png';
				if ( file_exists( $addon['path'] . 'assets/images/thumbnail.png' ) ) {
					$thumbnail_url = $addon['url'] . 'assets/images/thumbnail.png';
				} elseif ( file_exists( $addon['path'] . 'assets/images/thumbnail.jpg' ) ) {
					$thumbnail_url = $addon['url'] . 'assets/images/thumbnail.jpg';
				} elseif ( file_exists( $addon['path'] . 'assets/images/thumbnail.svg' ) ) {
					$thumbnail_url = $addon['url'] . 'assets/images/thumbnail.svg';
				}

				$plugins_data[ $base_name ]['thumb_url'] = $thumbnail_url;

				/**
				 * Checking if there any dependant plugin exists
				 */
				$depends          = tutor_utils()->array_get( 'depend_plugins', $addon );
				$plugins_required = array();
				if ( tutor_utils()->count( $depends ) ) {
					foreach ( $depends as $plugin_base => $plugin_name ) {
						if ( ! is_plugin_active( $plugin_base ) ) {
							$plugins_required[ $plugin_base ] = $plugin_name;
						}
					}
				}

				$depended_plugins = array();
				foreach ( $plugins_required as $required_plugin ) {
					array_push( $depended_plugins, $required_plugin );
				}

				$plugins_data[ $base_name ]['plugins_required'] = $depended_plugins;

				// Check if it's notifications.
				if ( function_exists( 'tutor_notifications' ) && tutor_notifications()->basename === $base_name ) {

					$required = array();
					version_compare( PHP_VERSION, '7.2.5', '>=' ) ? 0 : $required[] = __( 'PHP 7.2.5 or greater is required', 'tutor' );
					! is_ssl() ? $required[]                                        = __( 'SSL certificate', 'tutor' ) : 0;

					foreach ( array( 'curl', 'gmp', 'mbstring', 'openssl' ) as $ext ) {
						! extension_loaded( $ext ) ? $required[] = 'PHP extension <strong>' . $ext . '</strong>' : 0;
					}

					$plugins_data[ $base_name ]['ext_required'] = $required;
				}
			}
		}

		/**
		 * Keep same sorting order.
		 *
		 * @since 2.2.4
		 */
		$free_addon_list = apply_filters( 'tutor_pro_addons_lists_for_display', array() );
		$prepared_addons = array();

		foreach ( $free_addon_list as $addon_name => $addon ) {
			$key = "tutor-pro/addons/{$addon_name}/{$addon_name}.php";
			if ( isset( $plugins_data[ $key ] ) ) {
				$prepared_addons[] = $plugins_data[ $key ];
			}
		}

		return $prepared_addons;
	}

	/**
	 * Method for enable / disable addons
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function addon_enable_disable() {

		tutor_utils()->checking_nonce();

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Access Denied', 'tutor' ) ) );
		}

		$form_data     = json_decode( Input::post( 'addonFieldNames' ) );
		$before_config = self::get_addons_config();
		$addons_config = array();

		foreach ( $form_data as $addon_field_name => $is_enable ) {

			$before_status = ! isset( $before_config[ $addon_field_name ] ) ? 0 : $before_config[ $addon_field_name ]['is_enable'];
			$after_status  = $is_enable ? 1 : 0;

			if ( $before_status !== $after_status ) {
				do_action( 'tutor_addon_before_enable_disable' );
				if ( $is_enable ) {
					do_action( "tutor_addon_before_enable_{$addon_field_name}" );
					do_action( 'tutor_addon_before_enable', $addon_field_name );

					$addons_config[ $addon_field_name ]['is_enable'] = 1;
					update_option( self::OPTION_KEY, $addons_config );

					do_action( 'tutor_addon_after_enable', $addon_field_name );
					do_action( "tutor_addon_after_enable_{$addon_field_name}" );
				} else {
					do_action( "tutor_addon_before_disable_{$addon_field_name}" );
					do_action( 'tutor_addon_before_disable', $addon_field_name );

					$addons_config[ $addon_field_name ]['is_enable'] = 0;
					update_option( self::OPTION_KEY, $addons_config );

					do_action( 'tutor_addon_after_disable', $addon_field_name );
					do_action( "tutor_addon_after_disable_{$addon_field_name}" );
				}
				do_action( 'tutor_addon_after_enable_disable' );
			} else {
				$addons_config[ $addon_field_name ]['is_enable'] = $after_status;
				update_option( self::OPTION_KEY, $addons_config );
			}
		}

		wp_send_json_success();
	}

	/**
	 * Get tutor addons list
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function addons_lists_to_show() {
		$addons = array(
			'course-bundle'            => array(
				'name'        => __( 'Course Bundle', 'tutor' ),
				'description' => __( 'Group multiple courses to sell together.', 'tutor' ),
			),
			'subscription'             => array(
				'name'        => __( 'Subscription', 'tutor' ),
				'description' => __( 'Manage subscription', 'tutor' ),
			),
			'social-login'             => array(
				'name'        => __( 'Social Login', 'tutor' ),
				'description' => __( 'Let users register & login through social network like Facebook, Google, etc.', 'tutor' ),
			),
			'content-drip'             => array(
				'name'        => __( 'Content Drip', 'tutor' ),
				'description' => 'Unlock lessons by schedule or when students meet a specific condition.',
			),
			'tutor-multi-instructors'  => array(
				'name'        => __( 'Tutor Multi Instructors', 'tutor' ),
				'description' => 'Collaborate and add multiple instructors to a course.',
			),
			'tutor-assignments'        => array(
				'name'        => __( 'Tutor Assignments', 'tutor' ),
				'description' => 'Assess student learning with assignments.',
			),
			'tutor-course-preview'     => array(
				'name'        => __( 'Tutor Course Preview', 'tutor' ),
				'description' => 'Offer free previews of specific lessons before enrollment.',
			),
			'tutor-course-attachments' => array(
				'name'        => __( 'Tutor Course Attachments', 'tutor' ),
				'description' => 'Add unlimited attachments/ private files to any Tutor course',
			),
			'google-meet'              => array(
				'name'        => __( 'Tutor Google Meet Integration', 'tutor' ),
				'description' => __( 'Host live classes with Google Meet, directly from your lesson page.', 'tutor' ),
			),
			'tutor-report'             => array(
				'name'        => __( 'Tutor Report', 'tutor' ),
				'description' => __( 'Check your course performance through Tutor Report stats.', 'tutor' ),
			),
			'tutor-email'              => array(
				'name'        => __( 'Email', 'tutor' ),
				'description' => __( 'Send automated and customized emails for various Tutor events.', 'tutor' ),
			),
			'calendar'                 => array(
				'name'        => 'Calendar',
				'description' => __( 'Enable to let students view all your course events in one place.', 'tutor' ),
			),
			'tutor-notifications'      => array(
				'name'        => 'Notifications',
				'description' => __( 'Keep students and instructors notified of course events on their dashboard.', 'tutor' ),
			),
			'google-classroom'         => array(
				'name'        => __( 'Google Classroom Integration', 'tutor' ),
				'description' => __( 'Enable to integrate Tutor LMS with Google Classroom.', 'tutor' ),
			),
			'tutor-zoom'               => array(
				'name'        => __( 'Tutor Zoom Integration', 'tutor' ),
				'description' => __( 'Connect Tutor LMS with Zoom to host live online classes. Students can attend live classes right from the lesson page.', 'tutor' ),
			),
			'quiz-import-export'       => array(
				'name'        => __( 'Quiz Export/Import', 'tutor' ),
				'description' => __( 'Save time by exporting/importing quiz data with easy options.', 'tutor' ),
			),
			'enrollments'              => array(
				'name'        => __( 'Enrollment', 'tutor' ),
				'description' => __( 'Enable to manually enroll students in your courses.', 'tutor' ),
			),
			'tutor-certificate'        => array(
				'name'        => __( 'Tutor Certificate', 'tutor' ),
				'description' => __( 'Enable to award certificates upon course completion.', 'tutor' ),
			),
			'gradebook'                => array(
				'name'        => __( 'Gradebook', 'tutor' ),
				'description' => __( 'Track student progress with a centralized gradebook.', 'tutor' ),
			),
			'tutor-prerequisites'      => array(
				'name'        => __( 'Tutor Prerequisites', 'tutor' ),
				'description' => __( 'Set course prerequisites to guide learning paths effectively.', 'tutor' ),
			),
			'buddypress'               => array(
				'name'        => __( 'BuddyPress', 'tutor' ),
				'description' => __( 'Boost engagement with social features through BuddyPress for Tutor LMS.', 'tutor' ),
			),
			'wc-subscriptions'         => array(
				'name'        => __( 'WooCommerce Subscriptions', 'tutor' ),
				'description' => __( 'Capture Residual Revenue with Recurring Payments.', 'tutor' ),
			),
			'pmpro'                    => array(
				'name'        => __( 'Paid Memberships Pro', 'tutor' ),
				'description' => __( 'Maximize revenue by selling membership access to all of your courses.', 'tutor' ),
			),
			'restrict-content-pro'     => array(
				'name'        => __( 'Restrict Content Pro', 'tutor' ),
				'description' => __( 'Enable to manage content access through Restrict Content Pro. ', 'tutor' ),
			),
			'tutor-weglot'             => array(
				'name'        => 'Weglot',
				'description' => __( 'Translate & manage multilingual courses for global reach with full edit control.', 'tutor' ),
			),
			'tutor-wpml'               => array(
				'name'        => __( 'WPML Multilingual CMS', 'tutor' ),
				'description' => __( 'Create multilingual courses, lessons, dashboard and more for a global audience.', 'tutor' ),
			),
			'h5p'                      => array(
				'name'        => __( 'H5P Integration', 'tutor' ),
				'description' => __( 'Integrate H5P to add interactivity and engagement to your courses.', 'tutor' ),
			),
		);

		return $addons;
	}
}
