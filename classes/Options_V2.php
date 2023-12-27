<?php
/**
 * Settings options
 *
 * @package Tutor\Settings
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.0
 */

namespace Tutor;

use TUTOR\Input;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Contains all the settings options
 *
 * @since 2.0.0
 */
class Options_V2 {

	/**
	 * Undocumented variable
	 *
	 * @since 2.0.0
	 *
	 * @var array $options
	 */
	private $options;

	/**
	 * Settings fields
	 *
	 * @since 2.0.0
	 *
	 * @var mixed $setting_fields
	 */
	private $setting_fields;

	/**
	 * Register hooks
	 *
	 * @since 2.0.0
	 *
	 * @param boolean $register_hook should register hook or not.
	 *
	 * @return void
	 */
	public function __construct( $register_hook = true ) {
		if ( ! $register_hook ) {
			return;
		}

		// Saving option.
		add_action( 'wp_ajax_tutor_option_save', array( $this, 'tutor_option_save' ) );
		add_action( 'wp_ajax_tutor_option_default_save', array( $this, 'tutor_option_default_save' ) );
		add_action( 'wp_ajax_tutor_option_search', array( $this, 'tutor_option_search' ) );
		add_action( 'wp_ajax_tutor_export_settings', array( $this, 'tutor_export_settings' ) );
		add_action( 'wp_ajax_tutor_export_single_settings', array( $this, 'tutor_export_single_settings' ) );
		add_action( 'wp_ajax_tutor_delete_single_settings', array( $this, 'tutor_delete_single_settings' ) );
		add_action( 'wp_ajax_tutor_import_settings', array( $this, 'tutor_import_settings' ) );
		add_action( 'wp_ajax_tutor_apply_settings', array( $this, 'tutor_apply_settings' ) );
		add_action( 'wp_ajax_load_saved_data', array( $this, 'load_saved_data' ) );
		add_action( 'wp_ajax_reset_settings_data', array( $this, 'reset_settings_data' ) );
	}

	/**
	 * Get settings value
	 *
	 * @since 2.0.0
	 *
	 * @param mixed $key option key.
	 * @param mixed $default default value.
	 *
	 * @return mixed
	 */
	private function get( $key = null, $default = false ) {

		if ( ! $this->options ) {
			// Get if already not prepared.
			$this->options = (array) maybe_unserialize( get_option( 'tutor_option' ) );
		}

		$option = $this->options;

		if ( empty( $option ) || ! is_array( $option ) ) {
			return $default;
		}

		if ( ! $key ) {
			return $option;
		}

		if ( array_key_exists( $key, $option ) ) {
			return apply_filters( $key, $option[ $key ] );
		}

		// Access array value via dot notation, such as option->get('value.subvalue').
		if ( strpos( $key, '.' ) ) {
			$option_key_array = explode( '.', $key );
			$new_option       = $option;
			foreach ( $option_key_array as $dot_key ) {
				if ( isset( $new_option[ $dot_key ] ) ) {
					$new_option = $new_option[ $dot_key ];
				} else {
					return $default;
				}
			}

			return apply_filters( $key, $new_option );
		}

		return $default;
	}

	/**
	 * Function to get all fields for search tutor_option_search
	 *
	 * @since 2.0.0
	 *
	 * @return void send wp_json response
	 */
	public function tutor_option_search() {
		 tutor_utils()->checking_nonce();

		$data_array = array();
		foreach ( $this->get_setting_fields() as $sections ) {
			if ( is_array( $sections ) && ! empty( $sections ) ) {
				foreach ( tutils()->sanitize_recursively( $sections ) as $section ) {
					foreach ( $section['blocks'] as $blocks ) {
						if ( isset( $blocks['fields'] ) && ! empty( $blocks['fields'] ) ) {
							foreach ( $blocks['fields'] as $fields ) {
								$fields['section_label'] = isset( $section['label'] ) ? $section['label'] : '';
								$fields['section_slug']  = isset( $section['slug'] ) ? $section['slug'] : '';
								$fields['block_label']   = isset( $blocks['label'] ) ? $blocks['label'] : '';
								$data_array['fields'][]  = $fields;
							}
						}
					}
				}
			}
		}

		wp_send_json_success( $data_array );
	}

	/**
	 * Export settings
	 *
	 * @since 2.0.0
	 *
	 * @return void send wp_json response
	 */
	public function tutor_export_settings() {
		// Check if user is privileged.
		if ( ! current_user_can( 'administrator' ) ) {
			wp_send_json_error( tutor_utils()->error_message() );
		}

		$tutor_option = get_option( 'tutor_option' );
		wp_send_json_success( maybe_unserialize( $tutor_option ) );
	}

	/**
	 * Export single settings
	 *
	 * @since 2.0.0
	 *
	 * @return void send wp_json response
	 */
	public function tutor_export_single_settings() {

		// Check if user is privileged.
		if ( ! current_user_can( 'administrator' ) ) {
			wp_send_json_error( tutor_utils()->error_message() );
		}

		$tutor_settings_log = get_option( 'tutor_settings_log' );
		$export_id          = $this->get_request_data( 'export_id' );
		wp_send_json_success( $tutor_settings_log[ $export_id ] );
	}

	/**
	 * Apply settings
	 *
	 * @since 2.0.0
	 *
	 * @return void send wp_json response
	 */
	public function tutor_apply_settings() {

		// Check if user is privileged.
		if ( ! current_user_can( 'administrator' ) ) {
			wp_send_json_error( tutor_utils()->error_message() );
		}

		$tutor_settings_log = get_option( 'tutor_settings_log' );
		$apply_id           = $this->get_request_data( 'apply_id' );

		update_option( 'tutor_option', $tutor_settings_log[ $apply_id ]['dataset'] );

		wp_send_json_success( $tutor_settings_log[ $apply_id ] );
	}

	/**
	 * Delete single setting
	 *
	 * @since 2.0.0
	 *
	 * @return void send wp_json response
	 */
	public function tutor_delete_single_settings() {
		// Check if user is privileged.
		if ( ! current_user_can( 'administrator' ) ) {
			wp_send_json_error( tutor_utils()->error_message() );
		}

		$tutor_settings_log = get_option( 'tutor_settings_log' );
		$delete_id          = $this->get_request_data( 'delete_id' );
		unset( $tutor_settings_log[ $delete_id ] );
		update_option( 'tutor_settings_log', $tutor_settings_log );

		wp_send_json_success( $tutor_settings_log );
	}

	/**
	 * Get request data
	 *
	 * @since 2.0.0
	 *
	 * @param mixed $var option key.
	 *
	 * @return mixed
	 */
	public function get_request_data( $var ) {
		return isset( $_REQUEST[ $var ] ) ? sanitize_text_field( wp_unslash( $_REQUEST[ $var ] ) ) : null;
	}

	/**
	 * Tutor default settings update options
	 * and send json response
	 *
	 * @since 2.0.0
	 *
	 * @return void send wp_json response
	 */
	public function tutor_default_settings() {
		$attr = $this->get_setting_fields();

		foreach ( $attr as $sections ) {

			foreach ( $sections as $section ) {
				foreach ( $section['blocks'] as $blocks ) {
					foreach ( $blocks['fields'] as $field ) {
						if ( isset( $field['default'] ) ) {
							$attr_default[ $field['key'] ] = $field['default'];
						}
					}
				}
			}
		}

		update_option( 'tutor_option', $attr_default );

		wp_send_json_success( $attr_default );
	}

	/**
	 * Tutor settings log
	 *
	 * @since 2.0.0
	 *
	 * @return void send wp_json response
	 */
	public function load_saved_data() {
		tutor_utils()->checking_nonce();

		// Check if user is privileged.
		if ( ! current_user_can( 'administrator' ) ) {
			wp_send_json_error( tutor_utils()->error_message() );
		}

		wp_send_json_success( get_option( 'tutor_settings_log' ) );
	}

	/**
	 * Reset settings
	 *
	 * @since 2.0.0
	 *
	 * @return void send wp_json response
	 */
	public function reset_settings_data() {
		tutor_utils()->checking_nonce();

		// Check if user is privileged.
		if ( ! current_user_can( 'administrator' ) ) {
			wp_send_json_error( tutor_utils()->error_message() );
		}

		$reset_fields = $return_fields = $return_fields_group = array(); //phpcs:ignore
		$reset_page   = Input::post( 'reset_page' );
		$setting_data = $this->get_setting_fields()['option_fields'][ $reset_page ]['blocks'];

		foreach ( $setting_data as $blocks ) {

			$block_fields = isset( $blocks['fields'] ) ? $blocks['fields'] : array();
			foreach ( $block_fields as $fields ) {
				$return_fields[] = $fields;
			}

			$block_fields_group = isset( $blocks['fields_group'] ) ? $blocks['fields_group'] : array();
			foreach ( $block_fields_group as $fields ) {
				$return_fields_group[] = $fields;
			}
		}

		$reset_fields = array_merge( $return_fields, $return_fields_group );

		wp_send_json_success( $reset_fields );
	}

	/**
	 * Import settings
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	public function tutor_import_settings() {
		tutor_utils()->checking_nonce();

		// Check if user is privileged.
		if ( ! current_user_can( 'administrator' ) ) {
			wp_send_json_error( tutor_utils()->error_message() );
		}

		$request = $this->get_request_data( 'tutor_options' );
		$request = json_decode( stripslashes( $request ), true );

		$time = $this->get_request_data( 'time' );

		$save_import_data['datetime']             = (int) $time;
		$save_import_data['history_date']         = gmdate( 'j M, Y, g:i a', $time );
		$save_import_data['datatype']             = 'imported';
		$save_import_data['dataset']              = $request['data'];
		$import_data[ 'tutor-imported-' . $time ] = $save_import_data;

		$get_option_data = get_option( 'tutor_settings_log' );
		if ( empty( $get_option_data ) ) {
			$get_option_data = array();
		}
		if ( ! empty( $get_option_data ) && null !== $save_import_data['dataset'] ) {

			$update_option = array_merge( $import_data, $get_option_data );

			$update_option = tutor_utils()->sanitize_recursively( $update_option );

			if ( ! empty( $update_option ) ) {
				update_option( 'tutor_settings_log', $update_option );
			}

			if ( ! empty( $save_import_data ) ) {
				update_option( 'tutor_option', $save_import_data['dataset'] );
			}

			$get_final_data = get_option( 'tutor_settings_log' );
		} else {
			if ( ! empty( $import_data ) ) {
				update_option( 'tutor_settings_log', $import_data );
			}

			if ( ! empty( $save_import_data ) ) {
				update_option( 'tutor_option', $save_import_data['dataset'] );
			}
			$get_final_data = get_option( 'tutor_settings_log' );
		}

		wp_send_json_success( $get_final_data );
	}


	/**
	 * Function tutor_option_save
	 *
	 * @since 2.0.0
	 *
	 * @return void send wp_json response
	 */
	public function tutor_option_save() {
		tutor_utils()->checking_nonce();

		! current_user_can( 'manage_options' ) ? wp_send_json_error() : 0;

		$data_before = get_option( 'tutor_option' );
		do_action( 'tutor_option_save_before' );

		$option = (array) tutor_utils()->array_get( 'tutor_option', $_POST, array() ); //phpcs:ignore

		$option = tutor_utils()->sanitize_recursively( $option );
		$option = apply_filters( 'tutor_option_input', $option );

		$time                                  = strtotime( 'now' ) + ( 6 * 60 * 60 );
		$save_import_data['datetime']          = $time;
		$save_import_data['history_date']      = gmdate( 'j M, Y, g:i a', $time );
		$save_import_data['datatype']          = 'saved';
		$save_import_data['dataset']           = $option;
		$import_data[ 'tutor-saved-' . $time ] = $save_import_data;
		$update_option                         = array();
		$get_option_data                       = get_option( 'tutor_settings_log', array() );

		if ( ! empty( $get_option_data ) ) {
			$update_option = array_merge( $import_data, $get_option_data );
		} else {
			$update_option = array_merge( $update_option, $import_data );
		}

		$update_option = array_slice( $update_option, 0, 10 );

		update_option( 'tutor_settings_log', $update_option );
		update_option( 'tutor_option', $option );
		update_option( 'tutor_option_update_time', gmdate( 'j M, Y, g:i a', $time ) );

		/**
		 * Hook for each tutor settings option change detection.
		 * Example: `tutor_option_{course_permalink_base}_changed`
		 *
		 * @since 2.6.0
		 */
		$data_after = get_option( 'tutor_option' );
		if ( $data_before !== $data_after && is_array( $data_after ) ) {
			foreach ( $data_after as $key => $value ) {
				$from = $data_before[ $key ] ?? null;
				$to   = $value;
				if ( $from !== $to ) {
					do_action( "tutor_option_{$key}_changed", $from, $to );
				}
			}
		}

		do_action( 'tutor_option_save_after' );

		$data = apply_filters(
			'tutor_option_saved_data',
			array(
				'success' => true,
				'message' => __( 'Settings Saved', 'tutor' ),
				'options' => $option,
			)
		);

		wp_send_json( $data );
	}

	/**
	 * Function tutor_option_save
	 *
	 * @since 2.0.0
	 *
	 * @return void send wp_json response
	 */
	public function tutor_option_default_save() {
		tutor_utils()->checking_nonce();

		! current_user_can( 'manage_options' ) ? wp_send_json_error() : 0;
		$attr                 = $this->get_setting_fields();
		$tutor_default_option = get_option( 'tutor_default_option' );
		$tutor_saved_option   = get_option( 'tutor_option' );

		foreach ( $attr as $sections ) {
			foreach ( $sections as $section ) {
				foreach ( $section['blocks'] as $blocks ) {
					foreach ( $blocks['fields'] as $field ) {
						if ( isset( $tutor_default_option[ $field['key'] ] ) ) {
							$attr_default[ $field['key'] ] = $tutor_saved_option[ $field['key'] ];
						} else {
							if ( null !== $field['key'] ) {
								$attr_default[ $field['key'] ] = $field['default'];
							}
						}
					}
				}
			}
		}

		update_option( 'tutor_option', $attr_default );

		wp_send_json_success( $attr_default );
	}

	/**
	 * Load settings page
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	public function load_settings_page() {
		extract( $this->get_setting_fields() ); //phpcs:ignore

		if ( ! $template_path ) {
			$template_path = tutor()->path . '/views/options/settings.php';
		}
		include $template_path;
	}

	/**
	 * Get settings fields
	 *
	 * @since 2.0.0
	 *
	 * @return mixed
	 */
	public function get_setting_fields() {
		if ( $this->setting_fields ) {
			// Return from property if already prepared.
			return $this->setting_fields;
		}

		$pages = tutor_utils()->get_pages();

		$site_url    = site_url();
		$course_base = $this->get( 'course_permalink_base', 'courses' );
		$lesson_key  = $this->get( 'lesson_permalink_base', 'lessons' );
		$quiz_key    = $this->get( 'quiz_permalink_base', 'quizzes' );

		$course_url = $site_url . '/<code>' . $course_base . '</code>/sample-course';
		$lesson_url = $site_url . '/' . $course_base . '/sample-course/<code>' . $lesson_key . '</code>/sample-lesson/';
		$quiz_url   = $site_url . '/' . $course_base . '/sample-course/<code>' . $quiz_key . '</code>/sample-quiz/';

		$student_url       = tutor_utils()->profile_url( 0, false );
		$methods_array     = array();
		$withdrawl_methods = apply_filters( 'tutor_withdrawal_methods_all', array() );

		foreach ( $withdrawl_methods as $key => $method ) {
			$methods_array[ $key ] = $method['method_name'];
		}

		$page_args = array(
			'post_type'      => 'page',
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'title'          => 'Courses',
		);

		$page_posts             = get_posts( $page_args );
		$course_archive_page_id = ( is_array( $page_posts ) && count( $page_posts ) ) ? $page_posts[0] : null;

		$attr = array(
			'general'      => array(
				'label'    => __( 'General', 'tutor' ),
				'slug'     => 'general',
				'desc'     => __( 'General Settings', 'tutor' ),
				'template' => 'basic',
				'icon'     => 'tutor-icon-earth',
				'blocks'   => array(
					array(
						'label'      => false,
						'block_type' => 'uniform',
						'slug'       => 'general-page',
						'fields'     => array(
							array(
								'key'     => 'tutor_dashboard_page_id',
								'type'    => 'select',
								'label'   => __( 'Dashboard Page', 'tutor' ),
								'default' => '0',
								'options' => $pages,
								'desc'    => __( 'This page will be used for student and instructor dashboard', 'tutor' ),
							),
						),
					),
					array(
						'label'      => false,
						'block_type' => 'uniform',
						'slug'       => 'general-page',
						'fields'     => array(
							array(
								'key'     => 'tutor_toc_page_id',
								'type'    => 'select',
								'label'   => __( 'Terms and Conditions Page', 'tutor' ),
								'default' => '0',
								'options' => $pages,
								'desc'    => __( 'This page will be used as the Terms and Conditions page', 'tutor' ),
							),
						),
					),
					array(
						'label'      => __( 'Others', 'tutor' ),
						'slug'       => 'others',
						'block_type' => 'isolate',
						'fields'     => array(
							array(
								'key'         => 'enable_course_marketplace',
								'type'        => 'toggle_switch',
								'label'       => __( 'Enable Marketplace', 'tutor' ),
								'label_title' => '',
								'default'     => 'off',
								'desc'        => __( 'Allow multiple instructors to upload their courses.', 'tutor' ),
							),
							array(
								'key'         => 'pagination_per_page',
								'type'        => 'number',
								'number_type' => 'integer',
								'label'       => __( 'Pagination', 'tutor' ),
								'default'     => '20',
								'desc'        => __( 'Set the number of rows to be displayed per page', 'tutor' ),
							),
						),
					),
					array(
						'label'      => __( 'Instructor', 'tutor' ),
						'slug'       => 'instructor',
						'block_type' => 'uniform',
						'fields'     => array(
							array(
								'key'         => 'instructor_can_publish_course',
								'type'        => 'toggle_switch',
								'label'       => __( 'Allow Instructors To Publish Courses', 'tutor' ),
								'label_title' => '',
								'default'     => 'off',
								'desc'        => __( 'Enable instructors to publish the course directly. If disabled, admins will be able to review course content before publishing.', 'tutor' ),
							),
							array(
								'key'         => 'enable_become_instructor_btn',
								'type'        => 'toggle_switch',
								'label'       => __( 'Become an Instructor Button', 'tutor' ),
								'label_title' => '',
								'default'     => 'off',
								'desc'        => __( 'Enable the option to display this button on the student dashboard.', 'tutor' ),
							),
						),
					),
				),
			),
			'course'       => array(
				'label'    => __( 'Course', 'tutor' ),
				'slug'     => 'course',
				'desc'     => __( 'Course Settings', 'tutor' ),
				'template' => 'basic',
				'icon'     => 'tutor-icon-book-open',
				'blocks'   => array(
					'block_course' => array(
						'label'      => '',
						'slug'       => 'course',
						'block_type' => 'uniform',
						'fields'     => array(
							array(
								'key'         => 'student_must_login_to_view_course',
								'type'        => 'toggle_switch',
								'label'       => __( 'Course Visibility', 'tutor' ),
								'label_title' => '',
								'default'     => 'off',
								'desc'        => __( 'Students must be logged in to view course', 'tutor' ),
							),
							array(
								'key'         => 'course_content_access_for_ia',
								'type'        => 'toggle_switch',
								'label'       => __( 'Course Content Access', 'tutor' ),
								'default'     => 'off',
								'label_title' => '',
								'desc'        => __( 'Allow instructors and admins to view the course content without enrolling', 'tutor' ),
							),
							array(
								'key'     => 'course_content_summary',
								'type'    => 'toggle_switch',
								'label'   => __( 'Content Summary', 'tutor' ),
								'default' => 'on',
								'desc'    => __( 'Enabling this feature will show a course content summary on the Course Details page.', 'tutor' ),
							),
							array(
								'key'         => 'wc_automatic_order_complete_redirect_to_courses',
								'type'        => 'toggle_switch',
								'label'       => __( 'Auto redirect to courses', 'tutor' ),
								'default'     => 'off',
								'label_title' => '',
								'desc'        => __( 'When a user\'s WooCommerce order is auto-completed, they will be redirected to enrolled courses', 'tutor' ),
							),
							array(
								'key'         => 'enable_spotlight_mode',
								'type'        => 'toggle_switch',
								'label'       => __( 'Spotlight mode', 'tutor' ),
								'default'     => 'off',
								'label_title' => '',
								'desc'        => __( 'This will hide the header and the footer and enable spotlight (full screen) mode when students view lessons.', 'tutor' ),
							),
							array(
								'key'         => 'auto_course_complete_on_all_lesson_completion',
								'type'        => 'toggle_switch',
								'label'       => __( 'Auto Complete Course on all Lesson Completion', 'tutor' ),
								'default'     => 'off',
								'label_title' => '',
								'desc'        => __( 'If enabled, an Enrolled Course will be automatically completed if all its Lessons, Quizzes, and Assignments are already completed by the Student', 'tutor' ),
							),
							array(
								'key'            => 'course_completion_process',
								'type'           => 'radio_vertical',
								'label'          => __( 'Course Completion Process', 'tutor' ),
								'default'        => 'flexible',
								'select_options' => false,
								'options'        => array(
									'flexible' => __( 'Students can complete courses anytime in the Flexible mode', 'tutor' ),
									'strict'   => __( 'Students have to complete, pass all the lessons and quizzes (if any) to mark a course as complete.', 'tutor' ),
								),
								'desc'           => __( 'Choose when a user can click on the <strong>“Complete Course”</strong> button', 'tutor' ),
							),
							array(
								'key'         => 'course_retake_feature',
								'type'        => 'toggle_switch',
								'label'       => __( 'Course Retake', 'tutor' ),
								'default'     => 'off',
								'label_title' => '',
								'desc'        => __( 'Enabling this feature will allow students to reset course progress and start over.', 'tutor' ),
							),
							array(
								'key'         => 'enable_course_review_moderation',
								'type'        => 'toggle_switch',
								'label'       => __( "Publish Course Review on Admin's Approval", 'tutor' ),
								'default'     => 'off',
								'label_title' => '',
								'desc'        => __( 'Enable to publish/re-publish Course Review after the approval of Site Admin', 'tutor' ),
							),
						),
					),
					'block_lesson' => array(
						'label'      => __( 'Lesson', 'tutor' ),
						'slug'       => 'lesson',
						'block_type' => 'uniform',
						'fields'     => array(
							array(
								'key'         => 'enable_lesson_classic_editor',
								'type'        => 'toggle_switch',
								'label'       => __( 'WP Editor for Lesson', 'tutor' ),
								'label_title' => '',
								'default'     => 'off',
								'desc'        => __( 'Enable classic editor to edit lesson.', 'tutor' ),
							),
							array(
								'key'         => 'autoload_next_course_content',
								'type'        => 'toggle_switch',
								'label'       => __( 'Automatically Load Next Course Content.', 'tutor' ),
								'label_title' => '',
								'default'     => 'off',
								'desc'        => __( 'Enable this feature to automatically load the next course content after the current one is finished.', 'tutor' ),
							),
							array(
								'key'         => 'enable_comment_for_lesson',
								'type'        => 'toggle_switch',
								'label'       => __( 'Enable Lesson Comment', 'tutor' ),
								'label_title' => '',
								'default'     => 'off',
								'desc'        => __( 'Enable this feature to allow students to post comments on lessons.', 'tutor' ),
							),
						),
					),
					'block_quiz'   => array(
						'label'      => __( 'Quiz', 'tutor' ),
						'slug'       => 'quiz',
						'block_type' => 'uniform',
						'fields'     => array(
							array(
								'key'            => 'quiz_when_time_expires',
								'type'           => 'radio_vertical',
								'label'          => __( 'When time expires', 'tutor' ),
								'default'        => 'auto_abandon',
								'select_options' => false,
								'options'        => array(
									'auto_submit'  => __( 'The current quiz answers are submitted automatically.', 'tutor' ),
									// 'grace_period' => __( 'The current quiz answers are submitted by students.', 'tutor' )
									'auto_abandon' => __( 'Attempts must be submitted before time expires, otherwise they will not be counted', 'tutor' ),
								),
								'desc'           => __( 'Choose which action to follow when the quiz time expires.', 'tutor' ),
							),
							array(
								'key'     => 'quiz_answer_display_time',
								'type'    => 'number',
								'label'   => __( 'Correct Answer Display Time (when Reveal Mode is enabled)', 'tutor' ),
								'default' => '2',
								'desc'    => __( 'Put the answer display time in seconds', 'tutor' ),
							),
							array(
								'key'         => 'quiz_attempts_allowed',
								'type'        => 'number',
								'number_type' => 'integer',
								'label'       => __( 'Default Quiz Attempt limit (when Retry Mode is enabled)', 'tutor' ),
								'default'     => '10',
								'desc'        => __( 'The highest number of attempts allowed for students to participate a quiz. 0 means unlimited. This will work as the default Quiz Attempt limit in case of Quiz Retry Mode.', 'tutor' ),
							),
							array(
								'key'     => 'quiz_previous_button_enabled',
								'type'    => 'toggle_switch',
								'label'   => __( 'Show Quiz Previous Button', 'tutor' ),
								'default' => 'on',
								'desc'    => __( 'Choose whether to show or hide the previous button for each question.', 'tutor' ),
							),
							array(
								'key'     => 'quiz_grade_method',
								'type'    => 'radio_horizontal_full',
								'label'   => __( 'Final Grade Calculation', 'tutor' ),
								'desc'    => __( 'When multiple attempts are allowed, select which method should be used to calculate a student\'s final grade for the quiz.', 'tutor' ),
								'default' => 'highest_grade',
								'options' => array(
									'highest_grade' => __( 'Highest Grade', 'tutor' ),
									'average_grade' => __( 'Average Grade', 'tutor' ),
									'first_attempt' => __( 'First Attempt', 'tutor' ),
									'last_attempt'  => __( 'Last Attempt', 'tutor' ),
								),
							),
						),
					),
					array(
						'label'      => __( 'Video', 'tutor' ),
						'slug'       => 'video',
						'block_type' => 'uniform',
						'fields'     => array(
							array(
								'key'         => 'supported_video_sources',
								'type'        => 'checkbox_vertical',
								'default'     => array( 'youtube', 'vimeo' ),
								'label'       => __( 'Preferred Video Source', 'tutor' ),
								'label_title' => __( 'Preferred Video Source', 'tutor' ),
								'options'     => tutor_utils()->get_video_sources( true ),
								'desc'        => __( 'Choose video sources you\'d like to support.', 'tutor' ),
							),
						),
					),
				),
			),
			'monetization' => array(
				'label'    => __( 'Monetization', 'tutor' ),
				'slug'     => 'monetization',
				'desc'     => __( 'Monitization Settings', 'tutor' ),
				'template' => 'basic',
				'icon'     => 'tutor-icon-badge-discount',
				'blocks'   => array(
					'block_options' => array(
						'label'      => __( 'Options', 'tutor' ),
						'slug'       => 'options',
						'block_type' => 'uniform',
						'fields'     => array(
							array(
								'key'            => 'monetize_by',
								'type'           => 'select',
								'label'          => __( 'Select eCommerce Engine', 'tutor' ),
								'select_options' => true,
								'options'        => apply_filters(
									'tutor_monetization_options',
									array(
										'free' => __( 'Disable Monetization', 'tutor' ),
									)
								),
								'default'        => 'free',
								'desc'           => __( 'Select a monetization option to generate revenue by selling courses. Supports: WooCommerce, Easy Digital Downloads, Paid Memberships Pro', 'tutor' ),
							),
							array(
								'key'         => 'tutor_woocommerce_order_auto_complete',
								'type'        => 'toggle_switch',
								'label'       => __( 'Automatically Complete WooCommerce Orders', 'tutor' ),
								'label_title' => '',
								'default'     => 'off',
								'desc'        => __( 'If enabled, in the case of Courses, WooCommerce Orders will get the "Completed" status .', 'tutor' ),
							),
							array(
								'key'           => 'enable_revenue_sharing',
								'type'          => 'toggle_switch',
								'label'         => __( 'Enable Revenue Sharing', 'tutor' ),
								'label_title'   => '',
								'default'       => 'off',
								'desc'          => __( 'Allow revenue generated from selling courses to be shared with course creators.', 'tutor' ),
								'toggle_fields' => 'sharing_percentage',
							),
							array(
								'key'         => 'sharing_percentage',
								'type'        => 'double_input',
								'label'       => __( 'Sharing Percentage', 'tutor' ),
								'label_title' => '',
								'default'     => '',
								'fields'      => array(
									'earning_instructor_commission' => array(
										'id'      => 'revenue-instructor',
										'type'    => 'ratio',
										'title'   => 'Instructor Takes',
										'default' => 20,
									),
									'earning_admin_commission' => array(
										'id'      => 'revenue-admin',
										'type'    => 'ratio',
										'title'   => 'Admin Takes',
										'default' => 80,
									),
								),
								'desc'        => __( 'Set how the sales revenue will be shared among admins and instructors.', 'tutor' ),
							),
							array(
								'key'         => 'statement_show_per_page',
								'type'        => 'number',
								'number_type' => 'integer',
								'label'       => __( 'Show Statement Per Page', 'tutor' ),
								'default'     => '20',

								'desc'        => __( 'Define the number of statements to show.', 'tutor' ),
							),
						),
					),
					array(
						'label'      => __( 'Fees', 'tutor' ),
						'slug'       => 'fees',
						'block_type' => 'uniform',
						'fields'     => array(
							array(
								'key'           => 'enable_fees_deducting',
								'type'          => 'toggle_switch',
								'label'         => __( 'Deduct Fees', 'tutor' ),
								'label_title'   => '',
								'default'       => 'off',
								'desc'          => __( 'Fees are charged from the entire sales amount. The remaining amount will be divided among admin and instructors.', 'tutor' ),
								'toggle_fields' => 'fees_name,fee_amount_type',
							),
							array(
								'key'         => 'fees_name',
								'type'        => 'textarea',
								'label'       => __( 'Fee Description', 'tutor' ),
								'placeholder' => __( 'Fee Description', 'tutor' ),
								'desc'        => __( 'Set a description for the fee that you are deducting. Make sure to give a reasonable explanation to maintain transparency with your site’s instructors.', 'tutor' ),
								'maxlength'   => 200,
								'rows'        => 5,
								'default'     => 'Maintenance Fees',
							),
							array(
								'key'          => 'fee_amount_type',
								'type'         => 'group_fields',
								'label'        => __( 'Fee Amount & Type', 'tutor' ),
								'desc'         => __( 'Select the fee type and add fee amount/percentage', 'tutor' ),
								'group_fields' => array(
									'fees_type'   => array(
										'type'    => 'select',
										'default' => 'fixed',
										'options' => array(
											'percent' => __( 'Percent', 'tutor' ),
											'fixed'   => __( 'Fixed', 'tutor' ),
										),
									),
									'fees_amount' => array(
										'type'    => 'number',
										'default' => '0',
									),
								),
							),
						),
					),
					array(
						'label'      => __( 'Withdraw', 'tutor' ),
						'slug'       => 'withdraw',
						'block_type' => 'uniform',
						'fields'     => array(
							array(
								'key'     => 'min_withdraw_amount',
								'type'    => 'number',
								'label'   => __( 'Minimum Withdrawal Amount', 'tutor' ),
								'default' => '80',
								'desc'    => __( 'Instructors should earn equal or above this amount to make a withdraw request.', 'tutor' ),
							),
							array(
								'key'         => 'minimum_days_for_balance_to_be_available',
								'type'        => 'number',
								'number_type' => 'integer',
								'label'       => __( 'Minimum Days Before Balance is Available', 'tutor' ),
								'default'     => '7',
								'min'         => 1,
								'desc'        => __( 'Any income has to remain this many days in the platform before it is available for withdrawal.', 'tutor' ),
							),
							array(
								'key'     => 'tutor_withdrawal_methods',
								'type'    => 'checkbox_horizontal',
								'label'   => __( 'Enable Withdraw Method', 'tutor' ),
								'default' => array( 'bank_transfer_withdraw' ),
								'options' => $methods_array,
								'desc'    => __( 'Set how you would like to withdraw money from the website.', 'tutor' ),
							),
							array(
								'key'     => 'tutor_bank_transfer_withdraw_instruction',
								'type'    => 'textarea',
								'label'   => __( 'Bank Instructions', 'tutor' ),
								'default' => __( 'Write the up to date bank informations of your instructor here.', 'tutor' ),
								'desc'    => __( 'Write bank instructions for the instructors to conduct withdrawals.', 'tutor' ),
							),
						),
					),
				),
			),
			'design'       => array(
				'label'    => __( 'Design', 'tutor' ),
				'slug'     => 'design',
				'desc'     => __( 'Design Settings', 'tutor' ),
				'template' => 'design',
				'icon'     => 'tutor-icon-color-palette',
				'blocks'   => array(
					'block_course'   => array(
						'label'      => __( 'Course', 'tutor' ),
						'slug'       => 'course',
						'block_type' => 'uniform',
						'fields'     => array(
							array(
								'key'     => 'courses_col_per_row',
								'type'    => 'radio_horizontal',
								'label'   => __( 'Column Per Row', 'tutor' ),
								'default' => '3',
								'options' => array(
									'1' => 'One',
									'2' => 'Two',
									'3' => 'Three',
									'4' => 'Four',
								),
								'desc'    => __( 'Define how many columns you want to use to display courses.', 'tutor' ),
							),
							array(
								'key'         => 'course_archive_filter',
								'type'        => 'toggle_switch',
								'label'       => __( 'Course Filter', 'tutor' ),
								'label_title' => '',
								'default'     => 'off',
								'desc'        => __( 'Show sorting and filtering options on course archive page', 'tutor' ),
							),
							array(
								'key'         => 'courses_per_page',
								'type'        => 'number',
								'number_type' => 'integer',
								'label'       => __( 'Courses Per Page', 'tutor' ),
								'default'     => '12',
								'desc'        => __( 'Set the number of courses to display per page on the Course List page.', 'tutor' ),
							),
							array(
								'key'     => 'supported_course_filters',
								'type'    => 'checkbox_horizontal',
								'label'   => __( 'Preferred Course Filters', 'tutor' ),
								'default' => array( 'search', 'category' ),
								'options' => array(
									'search'           => __( 'Keyword Search', 'tutor' ),
									'category'         => __( 'Category', 'tutor' ),
									'tag'              => __( 'Tag', 'tutor' ),
									'difficulty_level' => __( 'Difficulty Level', 'tutor' ),
									'price_type'       => __( 'Price Type', 'tutor' ),
								),
								'desc'    => __( 'Choose preferred filter options you\'d like to show on the course archive page.', 'tutor' ),
							),
							array(
								'key'         => 'course_archive_filter_sorting',
								'type'        => 'toggle_switch',
								'label'       => __( 'Course Sorting', 'tutor' ),
								'label_title' => '',
								'default'     => 'on',
								'desc'        => __( 'If enabled, the courses will be sortable by Course Name or Creation Date in either Ascending or Descending order', 'tutor' ),
							),
						),
					),
					'layout'         => array(
						'label'      => __( 'Layout', 'tutor' ),
						'slug'       => 'layout',
						'block_type' => 'uniform',
						'fields'     => array(
							array(
								'key'           => 'instructor_list_layout',
								'type'          => 'group_radio',
								'label'         => __( 'Instructor List Layout', 'tutor' ),
								'desc'          => __( 'Choose a layout for the list of instructors inside a course page. You can change this at any time.', 'tutor' ),
								'default'       => 'portrait',
								'group_options' => array(
									'vertical'   => array(
										'default' => array(
											'title' => 'Portrait',
											'image' => 'instructor-layout/instructor-portrait.svg',
										),
										'cover'   => array(
											'title' => 'Cover',
											'image' => 'instructor-layout/instructor-cover.svg',
										),
										'minimal' => array(
											'title' => 'Minimal',
											'image' => 'instructor-layout/instructor-minimal.svg',
										),
									),
									'horizontal' => array(
										'portrait-horizontal'   => array(
											'title' => 'Portrait Horizontal',
											'image' => 'instructor-layout/instructor-horizontal-portrait.svg',
										),
										'minimal-horizontal' => array(
											'title' => 'Minimal Horizontal',
											'image' => 'instructor-layout/instructor-horizontal-minimal.svg',
										),
									),
								),
							),
							array(
								'key'           => 'public_profile_layout',
								'type'          => 'group_radio_full_3',
								'label'         => __( 'Instructor Public Profile Layout', 'tutor' ),
								'desc'          => __( 'Choose a layout design for a instructor’s public profile', 'tutor' ),
								'default'       => 'pp-rectangle',
								'group_options' => array(
									'private'      => array(
										'title' => 'Private',
										'image' => 'profile-layout/profile-private.svg',
									),
									'pp-circle'    => array(
										'title' => 'Modern',
										'image' => 'profile-layout/profile-modern.svg',
									),
									'no-cp'        => array(
										'title' => 'Minimal',
										'image' => 'profile-layout/profile-minimal.svg',
									),
									'pp-rectangle' => array(
										'title' => 'Classic',
										'image' => 'profile-layout/profile-classic.svg',
									),
								),
							),
							array(
								'key'           => 'student_public_profile_layout',
								'type'          => 'group_radio_full_3',
								'label'         => __( 'Student Public Profile Layout', 'tutor' ),
								'desc'          => __( 'Choose a layout design for a student’s public profile', 'tutor' ),
								'default'       => 'pp-rectangle',
								'group_options' => array(
									'private'      => array(
										'title' => 'Private',
										'image' => 'profile-layout/profile-private.svg',
									),
									'pp-circle'    => array(
										'title' => 'Modern',
										'image' => 'profile-layout/profile-modern.svg',
									),
									'no-cp'        => array(
										'title' => 'Minimal',
										'image' => 'profile-layout/profile-minimal.svg',
									),
									'pp-rectangle' => array(
										'title' => 'Classic',
										'image' => 'profile-layout/profile-classic.svg',
									),
								),
							),
						),
					),
					'course-details' => array(
						'label'      => __( 'Course Details', 'tutor' ),
						'slug'       => 'course-details',
						'block_type' => 'isolate',
						'fields'     => array(
							array(
								'key'           => 'course_details_adjustments',
								'type'          => 'checkgroup',
								'label'         => __( 'Page Features', 'tutor' ),
								'desc'          => __( 'You can keep the following features active or inactive as per the need of your business model', 'tutor' ),
								'group_options' => array(
									array(
										'key'     => 'display_course_instructors',
										'type'    => 'toggle_single',
										'label'   => __( 'Instructor Info', 'tutor' ),
										'default' => 'on',
										'desc'    => __( 'Toggle to show instructor info', 'tutor' ),
									),
									array(
										'key'     => 'enable_q_and_a_on_course',
										'type'    => 'toggle_single',
										'label'   => __( 'Q&A', 'tutor' ),
										'default' => 'on',
										'desc'    => __( 'Enable to add a Q&A section', 'tutor' ),
									),
									array(
										'key'         => 'enable_course_author',
										'type'        => 'toggle_single',
										'label'       => __( 'Author', 'tutor' ),
										'label_title' => __( 'Enable', 'tutor' ),
										'default'     => 'off',
										'desc'        => __( 'Enable to show course author name', 'tutor' ),
									),
									array(
										'key'         => 'enable_course_level',
										'type'        => 'toggle_single',
										'label'       => __( 'Level', 'tutor' ),
										'label_title' => __( 'Enable', 'tutor' ),
										'default'     => 'on',
										'desc'        => __( 'Enable to show course level', 'tutor' ),
									),
									array(
										'key'         => 'enable_course_share',
										'type'        => 'toggle_single',
										'label'       => __( 'Social Share', 'tutor' ),
										'label_title' => __( 'Enable', 'tutor' ),
										'default'     => 'on',
										'desc'        => __( 'Toggle to enable course social share', 'tutor' ),
									),
									array(
										'key'         => 'enable_course_duration',
										'type'        => 'toggle_single',
										'label'       => __( 'Duration', 'tutor' ),
										'label_title' => __( 'Disable', 'tutor' ),
										'default'     => 'on',
										'desc'        => __( 'Enable to show course duration', 'tutor' ),
									),
									array(
										'key'         => 'enable_course_total_enrolled',
										'type'        => 'toggle_single',
										'label'       => __( 'Total Enrolled', 'tutor' ),
										'label_title' => __( 'Enable', 'tutor' ),
										'default'     => 'on',
										'desc'        => __( 'Enable to show total enrolled students', 'tutor' ),
									),
									array(
										'key'         => 'enable_course_update_date',
										'type'        => 'toggle_single',
										'label'       => __( 'Update Date', 'tutor' ),
										'label_title' => __( 'Enable', 'tutor' ),
										'default'     => 'on',
										'desc'        => __( 'Enable to show course update information', 'tutor' ),
									),
									array(
										'key'         => 'enable_course_progress_bar',
										'type'        => 'toggle_single',
										'label'       => __( 'Progress Bar', 'tutor' ),
										'label_title' => __( 'Enable', 'tutor' ),
										'default'     => 'on',
										'desc'        => __( 'Enable to show course progress for Students', 'tutor' ),
									),
									array(
										'key'         => 'enable_course_material',
										'type'        => 'toggle_single',
										'label'       => __( 'Material', 'tutor' ),
										'label_title' => __( 'Enable', 'tutor' ),
										'default'     => 'on',
										'desc'        => __( 'Enable to show course materials', 'tutor' ),
									),
									array(
										'key'         => 'enable_course_about',
										'type'        => 'toggle_single',
										'label'       => __( 'About', 'tutor' ),
										'label_title' => __( 'Enable', 'tutor' ),
										'default'     => 'on',
										'desc'        => __( 'Enable to show course about section', 'tutor' ),
									),
									array(
										'key'         => 'enable_course_description',
										'type'        => 'toggle_single',
										'label'       => __( 'Description', 'tutor' ),
										'label_title' => __( 'Enable', 'tutor' ),
										'default'     => 'on',
										'desc'        => __( 'Enable to show course description', 'tutor' ),
									),
									array(
										'key'         => 'enable_course_benefits',
										'type'        => 'toggle_single',
										'label'       => __( 'Benefits', 'tutor' ),
										'label_title' => __( 'Enable', 'tutor' ),
										'default'     => 'on',
										'desc'        => __( 'Enable to show course benefits section', 'tutor' ),
									),
									array(
										'key'         => 'enable_course_requirements',
										'type'        => 'toggle_single',
										'label'       => __( 'Requirements', 'tutor' ),
										'label_title' => __( 'Enable', 'tutor' ),
										'default'     => 'on',
										'desc'        => __( 'Enable to show courses requirements setion', 'tutor' ),
									),
									array(
										'key'         => 'enable_course_target_audience',
										'type'        => 'toggle_single',
										'label'       => __( 'Target Audience', 'tutor' ),
										'label_title' => __( 'Disable', 'tutor' ),
										'default'     => 'on',
										'desc'        => __( 'Enable to show course target audience section', 'tutor' ),
									),
									array(
										'key'         => 'enable_course_announcements',
										'type'        => 'toggle_single',
										'label'       => __( 'Announcements', 'tutor' ),
										'label_title' => __( 'Enable', 'tutor' ),
										'default'     => 'on',
										'desc'        => __( 'Enable to show course announcements section', 'tutor' ),
									),
									array(
										'key'         => 'enable_course_review',
										'type'        => 'toggle_single',
										'label'       => __( 'Review', 'tutor' ),
										'label_title' => __( 'Enable', 'tutor' ),
										'default'     => 'on',
										'desc'        => __( 'Enable to show course review section', 'tutor' ),
									),
								),
							),
						),
					),
					'colors'         => array(
						'label'        => __( 'Colors', 'tutor' ),
						'slug'         => 'colors',
						'block_type'   => 'color_picker',
						'fields_group' => array(
							array(
								'key'     => 'color_preset_type',
								'type'    => 'color_preset',
								'label'   => __( 'Preset Colors', 'tutor' ),
								'desc'    => __( 'These colors will be used throughout your website. Choose between these presets or create your own custom palette.', 'tutor' ),
								'default' => 'default',
								'fields'  => array(
									/* First 4 preset_name should be same as color_fields */
									array(
										'key'    => 'default',
										'label'  => 'Default',
										'colors' => array(
											array(
												'slug'  => 'tutor_primary_color',
												'preset_name' => 'primary',
												'value' => '#3E64DE',
											),
											array(
												'slug'  => 'tutor_primary_hover_color',
												'preset_name' => 'hover',
												'value' => '#395BCA',
											),
											array(
												'slug'  => 'tutor_text_color',
												'preset_name' => 'text',
												'value' => '#212327',
											),
											array(
												'slug'  => 'tutor_gray_color',
												'preset_name' => 'gray',
												'value' => '#E3E5EB',
											),
											array(
												'slug'  => 'tutor_border_color',
												'preset_name' => 'border',
												'value' => '#CDCFD5',
											),
										),
									),
									array(
										'key'    => 'landscape',
										'label'  => 'Landscape',
										'colors' => array(
											array(
												'slug'  => 'tutor_primary_color',
												'preset_name' => 'primary',
												'value' => '#239371',
											),
											array(
												'slug'  => 'tutor_primary_hover_color',
												'preset_name' => 'hover',
												'value' => '#117D5D',
											),
											array(
												'slug'  => 'tutor_text_color',
												'preset_name' => 'text',
												'value' => '#212327',
											),
											array(
												'slug'  => 'tutor_gray_color',
												'preset_name' => 'gray',
												'value' => '#E3E5EB',
											),
											array(
												'slug'  => 'tutor_border_color',
												'preset_name' => 'border',
												'value' => '#CDCFD5',
											),
										),
									),
									array(
										'key'    => 'ocean',
										'label'  => 'Ocean',
										'colors' => array(
											array(
												'slug'  => 'tutor_primary_color',
												'preset_name' => 'primary',
												'value' => '#5A18C2',
											),
											array(
												'slug'  => 'tutor_primary_hover_color',
												'preset_name' => 'hover',
												'value' => '#3F02A0',
											),
											array(
												'slug'  => 'tutor_text_color',
												'preset_name' => 'text',
												'value' => '#212327',
											),
											array(
												'slug'  => 'tutor_gray_color',
												'preset_name' => 'gray',
												'value' => '#E3E5EB',
											),
											array(
												'slug'  => 'tutor_border_color',
												'preset_name' => 'border',
												'value' => '#CDCFD5',
											),
										),
									),
									array(
										'key'    => 'custom',
										'label'  => 'Custom',
										'colors' => array(
											array(
												'slug'  => 'tutor_primary_color',
												'preset_name' => 'primary',
												'value' => '#3E64DE',
											),
											array(
												'slug'  => 'tutor_primary_hover_color',
												'preset_name' => 'hover',
												'value' => '#28408E',
											),
											array(
												'slug'  => 'tutor_text_color',
												'preset_name' => 'text',
												'value' => '#1A1B1E',
											),
											array(
												'slug'  => 'tutor_gray_color',
												'preset_name' => 'gray',
												'value' => '#E3E5EB',
											),
										),
									),
								),
							),
							array(
								'key'    => 'tutor_color_presets',
								'type'   => 'color_fields',
								'label'  => __( 'Preset Colors', 'tutor' ),
								'fields' => array(
									array(
										'key'          => 'tutor_primary_color',
										'type'         => 'color_field',
										'preset_name'  => 'primary',
										'preset_exist' => true,
										'label'        => __( 'Primary Color', 'tutor' ),
										'default'      => '#3E64DE',
										'desc'         => __( 'Choose a primary color', 'tutor' ),
									),
									array(
										'key'          => 'tutor_primary_hover_color',
										'type'         => 'color_field',
										'preset_name'  => 'hover',
										'preset_exist' => true,
										'label'        => __( 'Primary Hover Color', 'tutor' ),
										'default'      => '#395BCA',
										'desc'         => __( 'Choose a primary hover color', 'tutor' ),
									),
									array(
										'key'          => 'tutor_text_color',
										'type'         => 'color_field',
										'preset_name'  => 'text',
										'preset_exist' => true,
										'label'        => __( 'Text Color', 'tutor' ),
										'default'      => '#212327',
										'desc'         => __( 'Choose a text color for your website', 'tutor' ),
									),
									array(
										'key'          => 'tutor_gray_color',
										'type'         => 'color_field',
										'preset_name'  => 'gray',
										'preset_exist' => false,
										'label'        => __( 'Gray', 'tutor' ),
										'default'      => '#E3E5EB',
										'desc'         => __( 'Choose a color for elements like table, card etc', 'tutor' ),
									),
									array(
										'key'          => 'tutor_border_color',
										'type'         => 'color_field',
										'preset_name'  => 'border',
										'preset_exist' => false,
										'label'        => __( 'Border', 'tutor' ),
										'default'      => '#CDCFD5',
										'desc'         => __( 'Choose a border color for your website', 'tutor' ),
									),
								),
							),
						),
					),
					'video_player'   => array(
						'label'      => __( 'Video Player', 'tutor' ),
						'slug'       => 'video_player',
						'block_type' => 'uniform',
						'fields'     => array(
							array(
								'key'         => 'disable_default_player_youtube',
								'type'        => 'toggle_switch',
								'label'       => __( 'Use Tutor Player for YouTube', 'tutor' ),
								'label_title' => '',
								'default'     => 'off',
								'desc'        => __( 'Enable this option to use Tutor LMS video player for YouTube.', 'tutor' ),
							),
							array(
								'key'         => 'disable_default_player_vimeo',
								'type'        => 'toggle_switch',
								'label'       => __( 'Use Tutor Player for Vimeo', 'tutor' ),
								'label_title' => '',
								'default'     => 'off',
								'desc'        => __( 'Enable this option to use Tutor LMS video player for Vimeo.', 'tutor' ),
							),
						),
					),
				),
			),
			'advanced'     => array(
				'label'    => __( 'Advanced', 'tutor' ),
				'slug'     => 'advanced',
				'desc'     => __( 'Advanced Settings', 'tutor' ),
				'template' => 'basic',
				'icon'     => 'tutor-icon-filter',
				'blocks'   => array(
					array(
						'label'      => __( 'Course', 'tutor' ),
						'slug'       => 'options',
						'block_type' => 'uniform',
						'fields'     => array(
							array(
								'key'     => 'enable_gutenberg_course_edit',
								'type'    => 'toggle_switch',
								'label'   => __( 'Gutenberg Editor', 'tutor' ),
								'default' => 'off',
								'desc'    => __( 'Enable this to create courses using the Gutenberg Editor.', 'tutor' ),
							),
							array(
								'key'     => 'hide_course_from_shop_page',
								'type'    => 'toggle_switch',
								'label'   => __( 'Hide Course Products on Shop Page', 'tutor' ),
								'default' => 'off',
								'desc'    => __( 'Enable to hide course products on shop page.', 'tutor' ),
							),
							array(
								'key'     => 'course_archive_page',
								'type'    => 'select',
								'label'   => __( 'Course Archive Page', 'tutor' ),
								'default' => $course_archive_page_id->ID ?? '0',
								'options' => $pages,
								'desc'    => __( 'This page will be used to list all the published courses.', 'tutor' ),
							),
							array(
								'key'     => 'instructor_register_page',
								'type'    => 'select',
								'label'   => __( 'Instructor Registration Page', 'tutor' ),
								'default' => '0',
								'options' => $pages,
								'desc'    => __( 'Choose the page for instructor registration.', 'tutor' ),
							),
							array(
								'key'     => 'student_register_page',
								'type'    => 'select',
								'label'   => __( 'Student Registration Page', 'tutor' ),
								'default' => '0',
								'options' => $pages,
								'desc'    => __( 'Choose the page for student registration.', 'tutor' ),
							),
							array(
								'key'     => 'lesson_video_duration_youtube_api_key',
								'type'    => 'text',
								'label'   => __( 'Youtube API Key', 'tutor' ),
								'default' => '',
								'desc'    => __( 'Insert the YouTube API key to host live videos using YouTube.', 'tutor' ),
							),
						),
					),
					array(
						'label'      => __( 'Base Permalink', 'tutor' ),
						'slug'       => 'base_permalink',
						'block_type' => 'uniform',
						'fields'     => array(
							array(
								'key'     => 'course_permalink_base',
								'type'    => 'text',
								'label'   => __( 'Course Permalink', 'tutor' ),
								'default' => 'courses',
								'desc'    => $course_url,
							),
							array(
								'key'     => 'lesson_permalink_base',
								'type'    => 'text',
								'label'   => __( 'Lesson Permalink', 'tutor' ),
								'default' => 'lessons',
								'desc'    => $lesson_url,
							),
							array(
								'key'     => 'quiz_permalink_base',
								'type'    => 'text',
								'label'   => __( 'Quiz Permalink', 'tutor' ),
								'default' => 'quizzes',
								'desc'    => $quiz_url,
							),
						),
					),
					array(
						'label'      => __( 'Options', 'tutor' ),
						'slug'       => 'options',
						'block_type' => 'uniform',
						'fields'     => array(
							array(
								'key'         => 'enable_profile_completion',
								'type'        => 'toggle_switch',
								'label'       => __( 'Profile Completion', 'tutor' ),
								'label_title' => '',
								'default'     => 'off',
								'desc'        => __( 'Enabling this feature will show a notification bar to students and instructors to complete their profile information', 'tutor' ),
							),
							array(
								'key'         => 'enable_tutor_native_login',
								'type'        => 'toggle_switch',
								'label'       => __( 'Enable Tutor Login', 'tutor' ),
								'label_title' => '',
								'default'     => 'on',
								'desc'        => __( 'Enable to use the tutor login modal instead of the default WordPress login page', 'tutor' ),
							),
							array(
								'key'         => 'delete_on_uninstall',
								'type'        => 'toggle_switch',
								'label'       => __( 'Erase upon uninstallation', 'tutor' ),
								'label_title' => '',
								'default'     => 'off',
								'desc'        => __( 'Delete all data during uninstallation', 'tutor' ),
							),
							array(
								'key'         => 'enable_tutor_maintenance_mode',
								'type'        => 'toggle_switch',
								'label'       => __( 'Maintenance Mode', 'tutor' ),
								'label_title' => '',
								'default'     => 'off',
								'desc'        => __( 'Enabling the maintenance mode allows you to display a custom message on the frontend. During this time, visitors can not access the site content. But the wp-admin dashboard will remain accessible.', 'tutor' ),
							),
						),
					),
				),
			),
		);

		$attrs = apply_filters( 'tutor/options/extend/attr', apply_filters( 'tutor/options/attr', $attr ) );

		// Get the active tab.
		$tab_page = tutor_utils()->array_get( 'tab_page', $_REQUEST, 'general' );
		$tab_data = null;
		$template = null;

		foreach ( $attrs as $key => $section ) {
			if ( $tab_page == $key ) {
				if ( isset( $section['template_path'] ) && $section['template_path'] ) {
					$template = $section['template_path'];
					$tab_data = $section;
				}
				break;
			}
		}

		// Store in runtime cache.
		$this->setting_fields = array(
			'option_fields'   => $attrs,
			'active_tab'      => $tab_page,
			'active_tab_data' => $tab_data,
			'template_path'   => $template,
		);

		return $this->setting_fields;
	}

	/**
	 * Generate field
	 *
	 * @since 2.0.0
	 *
	 * @param array $field field array.
	 *
	 * @return void
	 *
	 * Generate Option Field
	 */
	public function generate_field( $field = array() ) {
		ob_start();
		if ( isset( $field['type'] ) ) {
			include tutor()->path . "views/options/field-types/{$field['type']}.php";
		}
		echo ob_get_clean();//phpcs:ignore
	}

	/**
	 * Include field type template & return buffered
	 * string data.
	 *
	 * @since 2.0.0
	 *
	 * @param array $field field.
	 *
	 * @return string
	 */
	public function field_type( $field = array() ) {
		ob_start();
		if ( isset( $field['type'] ) ) {
			include tutor()->path . "views/options/field-types/{$field['type']}.php";
		}
		return ob_get_clean();
	}

	/**
	 * Include Option blocks template & return
	 * buffered string data.
	 *
	 * @since 2.0.0
	 *
	 * @param array $blocks blocks.
	 *
	 * @return string
	 */
	public function blocks( $blocks = array() ) {
		ob_start();
		include tutor()->path . 'views/options/option_blocks.php';
		return ob_get_clean();
	}

	/**
	 * Include options template & returns
	 * buffered string data.
	 *
	 * @since 2.0.0
	 *
	 * @param array $section section.
	 *
	 * @return string
	 */
	public function template( $section = array() ) {
		ob_start();
		$blocks = $section['blocks'];
		if ( isset( $section['template'] ) ) {
			include tutor()->path . "views/options/template/{$section['template']}.php";
		}
		return ob_get_clean();
	}

	/**
	 * Load template inside template dirctory
	 *
	 * @since 2.0.0
	 *
	 * @param  mixed $template_slug template slug.
	 * @param  mixed $section section.
	 *
	 * @return string
	 */
	public function view_template( $template_slug, $section = array() ) {
		ob_start();
		if ( isset( $template_slug ) ) {
			require tutor()->path . "views/options/template/{$template_slug}";
		}
		return ob_get_clean();
	}
}
