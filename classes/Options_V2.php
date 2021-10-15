<?php

/**
 * Options for TutorLMS
 *
 * @since v.2.0
 *
 * @author Themeum
 * @url https://themeum.com
 *
 * @package TutorLMS/Certificate
 * @version 2.0
 */

namespace Tutor;

use TUTOR\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Options_V2 {


	private $options;
	private $setting_fields;
	
	public function __construct() {
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
	}

	private function get( $key = null, $default = false ) {

		if(!$this->options) {
			// Get if already not prepared
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
			foreach ( $option_key_array as $dotKey ) {
				if ( isset( $new_option[ $dotKey ] ) ) {
					$new_option = $new_option[ $dotKey ];
				} else {
					return $default;
				}
			}

			return apply_filters( $key, $new_option );
		}

		return $default;
	}

	/**
	 * tutor_option_search
	 *
	 * @return array
	 */
	public function tutor_option_search() {
		 tutils()->checking_nonce();

		// !current_user_can('manage_options') ? wp_send_json_error() : 0;
		// $keyword = strtolower( $_POST['keyword'] );

		$attr = $this->options_attr();
		$data_array = array();
		foreach ( $attr as $block ) {
			foreach ( $block['sections'] as $sections ) {
				foreach ( $sections['blocks'] as $blocks ) {
					foreach ( $blocks['fields'] as $fields ) {
						$fields['section_label'] = $sections['label'];
						$fields['section_slug']  = $sections['slug'];
						$fields['block_label']   = $blocks['label'];
						$data_array['fields'][]  = $fields;
					}
				}
			}
		}

		wp_send_json_success( $data_array );
	}

	public function tutor_export_settings() {
		wp_send_json_success( (array) maybe_unserialize( get_option( 'tutor_option' ) ) );
	}

	public function tutor_export_single_settings() {
		$tutor_settings_log = get_option( 'tutor_settings_log' );
		$export_id          = $this->get_request_data( 'export_id' );
		wp_send_json_success( $tutor_settings_log[ $export_id ] );
	}


	public function tutor_apply_settings() {
		$tutor_settings_log = get_option( 'tutor_settings_log' );
		$apply_id           = $this->get_request_data( 'apply_id' );

		update_option( 'tutor_option', $tutor_settings_log[ $apply_id ]['dataset'] );

		wp_send_json_success( $tutor_settings_log[ $apply_id ] );
	}

	public function tutor_delete_single_settings() {
		$tutor_settings_log = get_option( 'tutor_settings_log' );
		$delete_id          = $this->get_request_data( 'delete_id' );
		unset( $tutor_settings_log[ $delete_id ] );

		update_option( 'tutor_settings_log', $tutor_settings_log );

		wp_send_json_success( $tutor_settings_log );
	}

	public function get_request_data( $var ) {
		return isset( $_REQUEST[ $var ] ) ? $_REQUEST[ $var ] : null;
	}

	/**
	 * tutor_default_settings
	 *
	 * @return JSON
	 */
	public function tutor_default_settings() {
		$attr = $this->options_attr();
		foreach ( $attr as $sections ) {
			foreach ( $sections['sections'] as $section ) {
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

	public function load_saved_data() {
		tutor_utils()->checking_nonce();
		wp_send_json_success( get_option( 'tutor_settings_log' ) );
	}

	public function tutor_import_settings() {
		tutor_utils()->checking_nonce();
		$request = $this->get_request_data( 'tutor_options' );

		$time    = $this->get_request_data( 'time' );
		$request = json_decode( str_replace( '\"', '"', $request ), true );

		$save_import_data['datetime']     = $time;
		$save_import_data['history_date'] = date( 'j M, Y, g:i a', $time );
		$save_import_data['datatype']     = 'imported';
		$save_import_data['dataset']      = $request['data'];

		$import_data[ 'tutor-imported-' . $time ] = $save_import_data;

		// update_option( 'tutor_settings_log', array() );
		$get_option_data = get_option( 'tutor_settings_log' );
		if ( empty( $get_option_data ) ) {
			$get_option_data = array();
		}
		if ( ! empty( $get_option_data ) && null !== $save_import_data['dataset'] ) {

			$update_option = array_merge( $get_option_data, $import_data );

			$update_option = tutils()->sanitize_recursively( $update_option );

			// $update_option = array();
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
	 * @return JSON
	 */
	public function tutor_option_save() {
		tutils()->checking_nonce();

		! current_user_can( 'manage_options' ) ? wp_send_json_error() : 0;

		do_action( 'tutor_option_save_before' );

		$option = (array) tutils()->array_get( 'tutor_option', $_POST, array() );

		$option = tutils()->sanitize_recursively( $option );

		$option = apply_filters( 'tutor_option_input', $option );

		// $request = $this->get_request_data( 'tutor_options' );
		$time                                     = strtotime( 'now' ) + ( 6 * 60 * 60 );
		$save_import_data['datetime']             = $time;
		$save_import_data['history_date']         = date( 'j M, Y, g:i a', $time );
		$save_import_data['datatype']             = 'saved';
		$save_import_data['dataset']              = $option;
		$import_data[ 'tutor-imported-' . $time ] = $save_import_data;
		$update_option                            = array();
		$get_option_data                          = get_option( 'tutor_settings_log', array() );

		if ( ! empty( $get_option_data ) ) {
			$update_option = array_merge( $get_option_data, $import_data );
		} else {
			$update_option = array_merge( $import_data );
		}

		update_option( 'tutor_settings_log', $update_option );

		update_option( 'tutor_option', $option );

		do_action( 'tutor_option_save_after' );

		// wp_send_json_success(array('msg' => __('Option Updated', 'tutor'), 'return' => $option));
		wp_send_json_success( $update_option );
	}

	/**
	 * Function tutor_option_save
	 *
	 * @return JSON
	 */
	public function tutor_option_default_save() {
		tutils()->checking_nonce();

		! current_user_can( 'manage_options' ) ? wp_send_json_error() : 0;

		$default_options = tutils()->sanitize_recursively( $this->tutor_default_settings() );

		update_option( 'tutor_option', $default_options );

		wp_send_json_success( $default_options );
	}

	public function load_settings_page() {
		extract($this->get_setting_fields());
		
		if(!$template_path) {
			$template_path = tutor()->path . '/views/options/settings.php';
		} 

		include $template_path;
	}

	private function get_setting_fields() {
		
		if($this->setting_fields) {
			// Return from property if already prepared
			return $this->setting_fields;
		}

		$pages 	      = tutor_utils()->get_pages();
		$lesson_url   = site_url() . '/course/' . 'sample-course/<code>lessons</code>/sample-lesson/';
		$student_url  = tutor_utils()->profile_url();

		$methods_array = array();
		$withdrawl_methods = apply_filters( 'tutor_withdrawal_methods_all', array() );
		foreach($withdrawl_methods as $key => $method) {
			$methods_array[$key] = $method['method_name'];
		}

		$attr = array(
			'general' => array(
				'label'    => __( 'General', 'tutor' ),
				'slug'     => 'general',
				'desc'     => __( 'General Settings', 'tutor' ),
				'template' => 'basic',
				'icon'     => __( 'earth', 'tutor' ),
				'blocks'   => array(
					array(
						'label'      => false,
						'block_type' => 'uniform',
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
						'label'      => __( 'Others', 'tutor' ),
						'slug'       => 'others',
						'block_type' => 'isolate',
						'fields'     => array(
							array(
								'key'         => 'enable_course_marketplace',
								'type'        => 'toggle_switch',
								'label'       => __( 'Enable Marketplace', 'tutor' ),
								'label_title' => __( '', 'tutor' ),
								'default'     => 'off',
								'desc'        => __( 'Allow multiple instructors to upload their courses.', 'tutor' ),
							),
							array(
								'key'     => 'pagination_per_page',
								'type'    => 'number',
								'label'   => __('Pagination', 'tutor'),
								'default' => '20',
								'desc'    => __('Number of items you would like displayed "per page" in the pagination', 'tutor'),
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
								'label'       => __( 'Allow Instructors Publishing Courses', 'tutor' ),
								'label_title' => __( '', 'tutor' ),
								'default'     => 'off',
								'desc'        => __( 'Enable instructors to publish the course directly. If disabled, admins will be able to review course content before publishing.', 'tutor' ),
							),
							array(
								'key'         => 'enable_become_instructor_btn',
								'type'        => 'toggle_switch',
								'label'       => __( 'Become Instructor Button', 'tutor' ),
								'label_title' => __( '', 'tutor' ),
								'default'     => 'off',
								'desc'        => __( 'Uncheck this option to hide the button from student dashboard.', 'tutor' ),
							)
						),
					),
				),
			),
			'course' => array(
				'label'    => __( 'Course', 'tutor' ),
				'slug'     => 'course',
				'desc'     => __( 'Course Settings', 'tutor' ),
				'template' => 'basic',
				'icon'     => __( 'book-open', 'tutor' ),
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
								'label_title' => __( 'Logged Only', 'tutor' ),
								'default'     => 'off',
								'desc'        => __( 'Students must be logged in to view course', 'tutor' ),
							),
							array(
								'key'         => 'course_content_access_for_ia',
								'type'        => 'toggle_switch',
								'label'       => __( 'Course Content Access', 'tutor' ),
								'default'     => 'off',
								'label_title' => __( '', 'tutor' ),
								'desc'        => __( 'Allow instructors and admins to view the course content without enrolling', 'tutor' ),
							),
							array(
								'key'         => 'enable_spotlight_mode',
								'type'        => 'toggle_switch',
								'label'       => __('Spotlight mode', 'tutor'),
								'default'     => 'off',
								'label_title' => __( '', 'tutor' ),
								'desc'        => __('This will hide the header and the footer and enable spotlight (full screen) mode when students view lessons.',	'tutor'),
							),
							array(
								'key'            => 'course_completion_process',
								'type'           => 'radio_vertical',
								'label'          => __( 'Course Completion Process', 'tutor' ),
								'default'        => 'flexible',
								'select_options' => false,
								'options'        => array(
									'flexible' => __( 'Flexible', 'tutor' ),
									'strict'   => __( 'Strict Mode', 'tutor' ),
								),
								'desc'           => __( 'Students can complete courses anytime in the Flexible mode. In the Strict mode, students have to complete, pass all the lessons and quizzes (if any) to mark a course as complete.', 'tutor' ),
							),
							array(
								'key'         => 'course_retake_feature',
								'type'        => 'toggle_switch',
								'label'       => __('Course Retake', 'tutor'),
								'default'     => 'off',
								'label_title' => __( '', 'tutor' ),
								'desc'        => __('Enabling this feature will allow students to reset course progress and start over.', 'tutor'),
							),
						),
					),
					array(
						'label'      => __( 'Lesson', 'tutor' ),
						'slug'       => 'lesson',
						'block_type' => 'uniform',
						'fields'     => array(
							array(
								'key'         => 'enable_lesson_classic_editor',
								'type'        => 'toggle_switch',
								'label'       => __( 'Classic Editor for Lesson', 'tutor' ),
								'label_title' => __( '', 'tutor' ),
								'default'     => 'off',
								'desc'        => __( 'Enable classic editor to edit lesson.', 'tutor' ),
							),
							array(
								'key'         => 'autoload_next_course_content',
								'type'        => 'toggle_switch',
								'label'       => __('Automatically load next course content.', 'tutor'),
								'label_title' => __( '', 'tutor' ),
								'default'     => 'off',
								'desc'        => __('Enabling this feature will be load next course content automatically after finishing current video.', 'tutor'),
							),
						),
					),
					'block_quiz' => array(
						'label'      => __( 'Quiz', 'tutor' ),
						'slug'       => 'quiz',
						'block_type' => 'uniform',
						'fields'     => array(
							array(
								'key'          => 'quiz_time_limit',
								'type'         => 'group_fields',
								'label'        => __( 'Time Limit', 'tutor' ),
								'desc'         => __( '0 means unlimited time.', 'tutor' ),
								'group_fields' => array(
									'value' => array(
										'type'    => 'text',
										'default' => '0',

									),
									'time'  => array(
										'type'    => 'select',
										'default' => 'minutes',
										'select_options' => false,
										'options' => array(
											'weeks' => __( 'Weeks', 'tutor' ),
											'days' => __( 'Days', 'tutor' ),
											'hours' => __( 'Hours', 'tutor' ),
											'minutes' => __( 'Minutes', 'tutor' ),
											'seconds' => __( 'Seconds', 'tutor' ),
										),
									),
								),
							),
							array(
								'key'            => 'quiz_when_time_expires',
								'type'           => 'radio_vertical',
								'label'          => __( 'When time expires', 'tutor' ),
								'default'        => 'minutes',

								'select_options' => false,
								'options'        => array(
									'auto_submit'  => __( 'The current quiz answers are submitted automatically.', 'tutor' ),
									'grace_period' => __( 'The current quiz answers are submitted by students.', 'tutor' ),
									'auto_abandon' => __( 'Attempts must be submitted before time expires, otherwise they will not be counted', 'tutor' ),
								),
								'desc'           => __( 'Choose which action to follow when the quiz time expires.', 'tutor' ),
							),
							array(
								'key'     => 'quiz_attempts_allowed',
								'type'    => 'number',
								'label'   => __( 'Quiz Attempts allowed', 'tutor' ),
								'default' => '10',
								'desc'    => __( 'The highest number of attempts students are allowed to take for a quiz. 0 means unlimited attempts.', 'tutor' ),
							),
							array(
								'key'     => 'quiz_previous_button_disabled',
								'type'    => 'toggle_switch',
								'label'   => __('Hide Quiz Previous Button', 'tutor'),
								'default' => 'off',
								'desc'    => __('Choose whether to show or hide previous button for single question.', 'tutor'),
							),
							array(
								'key'            => 'quiz_grade_method',
								'type'           => 'select',
								'label'          => __( 'Final grade calculation', 'tutor' ),
								'default'        => 'highest_grade',
								'select_options' => false,
								'options'        => array(
									'highest_grade' => __( 'Highest Grade', 'tutor' ),
									'average_grade' => __( 'Average Grade', 'tutor' ),
									'first_attempt' => __( 'First Attempt', 'tutor' ),
									'last_attempt' => __( 'Last Attempt', 'tutor' ),
								),
								'desc'           => __( 'When multiple attempts are allowed, which method should be used to calculate a student\'s final grade for the quiz.', 'tutor' ),
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
								'label'       => __( 'Preferred Video Source', 'tutor' ),
								'label_title' => __( 'Preferred Video Source', 'tutor' ),
								'options'     => array(
									'html5'        => __( 'HTML 5 (mp4)', 'tutor' ),
									'external_url' => __( 'External URL', 'tutor' ),
									'youtube'      => __( 'Youtube', 'tutor' ),
									'vimeo'        => __( 'Vimeo', 'tutor' ),
									'embedded'     => __( 'Embedded', 'tutor' ),
								),
								'desc'        => __( 'Choose video sources you\'d like to support. Unchecking all will not disable video feature.', 'tutor' ),
							)
						),
					),
				),
			),
			'monetization'       => array(
				'label'    => __( 'Monitization', 'tutor' ),
				'slug'     => 'monetization',
				'desc'     => __( 'Monitization Settings', 'tutor' ),
				'template' => 'basic',
				'icon'     => __( 'discount-filled', 'tutor' ),
				'blocks'   => array(
					array(
						'label'      => false,
						'block_type' => 'uniform',
						'fields'     => array(
							array(
								'key'            => 'enable_tutor_earning',
								'type'           => 'toggle_switch',
								'label'          => __( 'Enable Monetization', 'tutor' ),
								'label_title'    => __( '', 'tutor' ),
								'default'        => 'off',
								'desc'           => __( 'Enable monetization option to generate revenue by selling courses. Supports: WooCommerce, Easy Digital Downloads, Paid Memberships Pro', 'tutor' ),
							),
						),
					),
					'block_options' => array(
						'label'      => __( 'Options', 'tutor' ),
						'slug'       => 'options',
						'block_type' => 'uniform',
						'fields'     => array(
							array(
								'key'     => 'monetize_by',
								'type'    => 'select',
								'label'   => __( 'Select eCommerce Engine', 'tutor' ),
								'select_options' => false,
								'options' => apply_filters('tutor_monetization_options', array(
									'free' =>  __('Disable Monetization', 'tutor'),
								)),
								'default' => 'free',
								'desc'    => __('Select a monetization option to generate revenue by selling courses. Supports: WooCommerce, Easy Digital Downloads, Paid Memberships Pro',	'tutor'),
							),
							array(
								'key'         => 'sharing_percentage',
								'type'        => 'double_input',
								'label'       => __( 'Sharing Percentage', 'tutor' ),
								'label_title' => __( '', 'tutor' ),
								'default'     => '',
								'fields'      => array(
									'earning_instructor_commission' => array(
										'id'      => 'revenue-instructor',
										'type'    => 'ratio',
										'title'   => 'Instructor Takes',
										'default' => 0,
									),
									'earning_admin_commission' => array(
										'id'      => 'revenue-admin',
										'type'    => 'ratio',
										'title'   => 'Admin Takes',
										'default' => 100,
									),
								),
								'desc'        => __( 'Select a monetization option to generate revenue by selling courses. Supports: WooCommerce', 'tutor' ),
							),
							array(
								'key'         => 'enable_revenue_sharing',
								'type'        => 'toggle_switch',
								'label'       => __( 'Enable Revenue Sharing', 'tutor' ),
								'label_title' => __( '', 'tutor' ),
								'default'     => 'off',
								'desc'        => __( 'Content description', 'tutor' ),
							),
							array(
								'key'     => 'statement_show_per_page',
								'type'    => 'number',
								'label'   => __( 'Show Statement Per Page', 'tutor' ),
								'default' => '20',

								'desc'    => __( 'Define the number of statements to show.', 'tutor' ),
							),
						),
					),
					array(
						'label'      => __( 'Fees', 'tutor' ),
						'slug'       => 'fees',
						'block_type' => 'uniform',
						'fields'     => array(
							array(
								'key'         => 'enable_fees_deducting',
								'type'        => 'toggle_switch',
								'label'       => __( 'Deduct Fees', 'tutor' ),
								'label_title' => __( '', 'tutor' ),
								'default'     => 'off',
								'desc'        => __('Fees are charged from the entire sales amount. The remaining amount will be divided among admin and instructors.',	'tutor'),
							),
							array(
								'key'         => 'fees_name',
								'type'        => 'text',
								'label'       => __( 'Fee Description', 'tutor' ),
								'label_title' => __( '', 'tutor' ),
								'default'     => 'free',
							),
							array(
								'key'          => 'fee_amount_type',
								'type'         => 'group_fields',
								'label'        => __( 'Fee Amount & Type', 'tutor' ),
								'group_fields' => array(
									'fees_type'  => array(
										'type'    => 'select',
										'default' => 'minutes',
										'options' => array(
											'percent'     =>  __('Percent', 'tutor'),
											'fixed'      =>  __('Fixed', 'tutor'),
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
								'key'     => 'minimum_days_for_balance_to_be_available',
								'type'    => 'number',
								'label'   => __( 'Minimum Days for Balance to be Available', 'tutor' ),
								'default' => '80',
								'desc'    => __( 'Instructors should earn equal or above this amount to make a withdraw request.', 'tutor' ),
							),
							array(
								'key'         => 'tutor_withdrawal_methods',
								'type'        => 'checkbox_horizontal',
								'label'       => __( 'Enable withdraw method', 'tutor' ),
								'label_title' => __( '', 'tutor' ),
								'options'     => $methods_array,
								'desc'        => __( 'Choose preferred filter options you\'d like to show in course archive page.', 'tutor' ),
							),
							array(
								'key'         => 'tutor_bank_transfer_withdraw_instruction',
								'type'        => 'textarea',
								'label'       => __( 'Bank Instructions', 'tutor' ),
								'desc'        => __( 'Write instruction for the instructor to fill bank information', 'tutor' ),
							),
						),
					),
				),
			),
			'design'             => array(
				'label'    => __( 'Design', 'tutor' ),
				'slug'     => 'design',
				'desc'     => __( 'Design Settings', 'tutor' ),
				'template' => 'design',
				'icon'     => __( 'design', 'tutor' ),
				'blocks'   => array(
					'block_course' => array(
						'label'      => __( 'Course', 'tutor' ),
						'slug'       => 'course',
						'block_type' => 'uniform',
						'fields'     => array(
							array(
								'key'     => 'courses_col_per_row',
								'type'    => 'radio_horizontal',
								'label'   => __( 'Column Per Row', 'tutor' ),
								'default' => '4',
								'options' => array(
									'1'   => 'One',
									'2'   => 'Two',
									'3'   => 'Three',
									'4'   => 'Four'
								),
								'desc'    => __( 'Define how many column you want to use to display courses.', 'tutor' ),
							),
							array(
								'key'     => 'courses_per_page',
								'type'    => 'number',
								'label'   => __('Courses Per Page', 'tutor'),
								'default' => '12',
								'desc'    => __('Define how many courses you want to show per page', 'tutor'),
							),
							array(
								'key'         => 'course_archive_filter',
								'type'        => 'toggle_switch',
								'label'       => __( 'Course Filter', 'tutor' ),
								'label_title' => __( '', 'tutor' ),
								'default'     => 'off',
								'desc'        => __( 'Show sorting and filtering options on course archive page', 'tutor' ),
							),
							array(
								'key'         => 'supported_course_filters',
								'type'        => 'checkbox_horizontal',
								'label'       => __( 'Preferred Course Filters', 'tutor' ),
								'label_title' => __( '', 'tutor' ),
								'options'     => array(
									'search'           => __( 'Keyword Search', 'tutor' ),
									'category'         => __( 'Category', 'tutor' ),
									'tag'              => __( 'Tag', 'tutor' ),
									'difficulty_level' => __( 'Difficulty Level', 'tutor' ),
									'price_type'       => __( 'Price Type', 'tutor' ),
								),
								'desc'        => __( 'Choose preferred filter options you\'d like to show in course archive page.', 'tutor' ),
							),
						),
					),
					array(
						'label'      => __( 'Student Profile', 'tutor' ),
						'slug'       => 'student_profile',
						'block_type' => 'uniform',
						'fields'     => array(
							array(
								'key'     => 'students_own_review_show_at_profile',
								'type'    => 'toggle_switch',
								'label'   => __('Show reviews on profile', 'tutor'),
								'default' => 'on',
								'desc'    => __('Enabling this will show the reviews written by each student on their profile', 'tutor')."<br />" .$student_url,
							),
							array(
								'key'     => 'show_courses_completed_by_student',
								'type'    => 'toggle_switch',
								'label'   => __('Show completed courses', 'tutor'),
								'default' => 'on',
								'desc'    => __('Completed courses will be shown on student profiles. <br/> For example, you can see this link-',	'tutor').$student_url,
							),
						)
					),
					array(
						'label'      => __( 'Video Player', 'tutor' ),
						'slug'       => 'video_player',
						'block_type' => 'uniform',
						'fields'     => array(
							array(
								'key'         => 'disable_default_player_youtube',
								'type'        => 'toggle_switch',
								'label'       => __('Use Tutor Player for YouTube', 'tutor'),
								'label_title' => __( '', 'tutor' ),
								'default'     => 'off',
								'desc'        => __('Enable this option to use Tutor LMS video player.', 'tutor'),
							),
							array(
								'key'         => 'disable_default_player_vimeo',
								'type'        => 'toggle_switch',
								'label'       => __('Use Tutor Player for Vimeo', 'tutor'),
								'label_title' => __( '', 'tutor' ),
								'default'     => 'off',
								'desc'        => __('Enable this option to use Tutor LMS video player.', 'tutor'),
							),
						)
					),
					array(
						'label'      => __( 'Layout', 'tutor' ),
						'slug'       => 'layout',
						'block_type' => 'uniform',
						'fields'     => array(
							array(
								'key'           => 'instructor_list_layout',
								'type'          => 'group_radio',
								'label'         => __( 'Instructor List Layout', 'tutor' ),
								'group_options' => array(
									'vertical'   => array(
										'pp-top-full' => array(
											'title' => 'Portrait',
											'image' => 'instructor-layout/intructor-portrait.svg',
										),
										'pp-cp'    => array(
											'title' => 'Cover',
											'image' => 'instructor-layout/instructor-cover.svg',
										),
										'pp-top-left'  => array(
											'title' => 'Minimal',
											'image' => 'instructor-layout/instructor-minimal.svg',
										),
									),
									'horizontal' => array(
										'pp-left-full' => array(
											'title' => 'Horizontal Portrait',
											'image' => 'instructor-layout/instructor-horizontal-portrait.svg',
										),
										'pp-left-middle'  => array(
											'title' => 'Horizontal Minimal',
											'image' => 'instructor-layout/instructor-horizontal-minimal.svg',
										),
									),
								),
								'desc'          => __( 'Content Needed Here...', 'tutor' ),
							),
							array(
								'key'           => 'public_profile_layout',
								'type'          => 'group_radio_full_3',
								'label'         => __( 'Public Profile Layout', 'tutor' ),
								'group_options' => array(
									'private' => array(
										'title' => 'Private',
										'image' => 'profile-layout/profile-private.svg',
									),
									'pp-circle'  => array(
										'title' => 'Modern',
										'image' => 'profile-layout/profile-modern.svg',
									),
									'no-cp' => array(
										'title' => 'Minimal',
										'image' => 'profile-layout/profile-minimal.svg',
									),
									'pp-rectangle' => array(
										'title' => 'Classic',
										'image' => 'profile-layout/profile-classic.svg',
									),
								),
								'desc' => __( 'Content Needed Here...', 'tutor' ),
							),
						),
					),
					array(
						'label'      => __( 'Course Details', 'tutor' ),
						'slug'       => 'course-details',
						'block_type' => 'isolate',
						'fields'     => array(
							array(
								'key'           => 'Public Profile Layout',
								'type'          => 'checkgroup',
								'label'         => __( 'Public Profile Layout', 'tutor' ),
								'group_options' => array(
									array(
										'key'     => 'display_course_instructors',
										'type'    => 'toggle_single',
										'label'   => __( 'Instructor Info', 'tutor' ),
										'default' => 'on',
										'desc'    => __( 'Show instructor bio on each page', 'tutor' ),
									),
									array(
										'key'     => 'enable_q_and_a_on_course',
										'type'    => 'toggle_single',
										'label'   => __( 'Question and Answer', 'tutor' ),
										'default' => 'on',
										'desc'    => __( 'Enabling this feature will add a Q&amp;A section on every course.', 'tutor' ),
									),
									array(
										'key'     => 'disable_course_author',
										'type'    => 'toggle_single',
										'label'   => __( 'Disable Author', 'tutor' ),
										'label_title' => __( 'Disable', 'tutor' ),
										'default' => 'off',
										'desc'    => __( 'Disabling this feature will be removed course author name from the course page.', 'tutor' ),
									),
									array(
										'key'     => 'disable_course_level',
										'type'    => 'toggle_single',
										'label'   => __( 'Disable Course Level', 'tutor' ),
										'label_title' => __( 'Disable', 'tutor' ),
										'default' => 'off',
										'desc'    => __( 'Disabling this feature will be removed course level from the course page.', 'tutor' ),
									),
									array(
										'key'     => 'disable_course_share',
										'type'    => 'toggle_single',
										'label'   => __( 'Disbale Course Share', 'tutor' ),
										'label_title' => __( 'Disable', 'tutor' ),
										'default' => 'off',
										'desc'    => __( 'Disabling this feature will be removed course share option from the course page.', 'tutor' ),
									),
									array(
										'key'     => 'disable_course_duration',
										'type'    => 'toggle_single',
										'label'   => __( 'Course Duration', 'tutor' ),
										'label_title' => __( 'Disable', 'tutor' ),
										'default' => '0',
										'desc'    => __( 'Disabling this feature will be removed course duration from the course page.', 'tutor' ),
									),
									array(
										'key'     => 'disable_course_total_enrolled',
										'type'    => 'toggle_single',
										'label'   => __( 'Course Total Enrolled', 'tutor' ),
										'label_title' => __( 'Disable', 'tutor' ),
										'default' => '0',
										'desc'    => __( 'Disabling this feature will be removed course total enrolled from the course page.', 'tutor' ),
									),
									array(
										'key'     => 'disable_course_update_date',
										'type'    => 'toggle_single',
										'label'   => __( 'Course Update Date', 'tutor' ),
										'label_title' => __( 'Disable', 'tutor' ),
										'default' => '0',
										'desc'    => __( 'Disabling this feature will be removed course update date from the course page.', 'tutor' ),
									),
									array(
										'key'     => 'disable_course_progress_bar',
										'type'    => 'toggle_single',
										'label'   => __( 'Course Progress Bar', 'tutor' ),
										'label_title' => __( 'Disable', 'tutor' ),
										'default' => '0',
										'desc'    => __( 'Disabling this feature will be removed completing progress bar from the course page.', 'tutor' ),
									),
									array(
										'key'     => 'disable_course_material',
										'type'    => 'toggle_single',
										'label'   => __( 'Course Material', 'tutor' ),
										'label_title' => __( 'Disable', 'tutor' ),
										'default' => '0',
										'desc'    => __( 'Disabling this feature will be removed course material from the course page.', 'tutor' ),
									),
									array(
										'key'     => 'disable_course_about',
										'type'    => 'toggle_single',
										'label'   => __( 'Course About', 'tutor' ),
										'label_title' => __( 'Disable', 'tutor' ),
										'default' => '0',
										'desc'    => __( 'Disabling this feature will be removed course about from the course page.', 'tutor' ),
									),
									array(
										'key'     => 'disable_course_description',
										'type'    => 'toggle_single',
										'label'   => __( 'Course Description', 'tutor' ),
										'label_title' => __( 'Disable', 'tutor' ),
										'default' => '0',
										'desc'    => __( 'Disabling this feature will be removed course description from the course page.', 'tutor' ),
									),
									array(
										'key'     => 'disable_course_benefits',
										'type'    => 'toggle_single',
										'label'   => __( 'Course Benefits', 'tutor' ),
										'label_title' => __( 'Disable', 'tutor' ),
										'default' => '0',
										'desc'    => __( 'Disabling this feature will be removed course benefits from the course page.', 'tutor' ),
									),
									array(
										'key'     => 'disable_course_requirements',
										'type'    => 'toggle_single',
										'label'   => __( 'Course Requirements', 'tutor' ),
										'label_title' => __( 'Disable', 'tutor' ),
										'default' => '0',
										'desc'    => __( 'Disabling this feature will be removed course requirements from the course page.', 'tutor' ),
									),
									array(
										'key'     => 'disable_course_target_audience',
										'type'    => 'toggle_single',
										'label'   => __( 'Course Target Audience', 'tutor' ),
										'label_title' => __( 'Disable', 'tutor' ),
										'default' => '0',
										'desc'    => __( 'Disabling this feature will be removed course target audience from the course page.', 'tutor' ),
									),
									array(
										'key'     => 'disable_course_announcements',
										'type'    => 'toggle_single',
										'label'   => __( 'Course Announcements', 'tutor' ),
										'label_title' => __( 'Disable', 'tutor' ),
										'default' => '0',
										'desc'    => __( 'Disabling this feature will be removed course announcements from the course page.', 'tutor' ),
									),
									array(
										'key'     => 'disable_course_review',
										'type'    => 'toggle_single',
										'label'   => __( 'Course Review', 'tutor' ),
										'label_title' => __( 'Disable', 'tutor' ),
										'default' => '0',
										'desc'    => __( 'Disabling this feature will be removed course review system from the course page.', 'tutor' ),
									),
								),
								'desc'          => __( 'Content Needed Here...', 'tutor' ),
							),
						),
					),
				),
			),
			'advanced'           => array(
				'label'    => __( 'Advanced', 'tutor' ),
				'slug'     => 'advanced',
				'desc'     => __( 'Advanced Settings', 'tutor' ),
				'template' => 'basic',
				'icon'     => __( 'filter', 'tutor' ),
				'blocks'   => array(
					array(
						'label'      => __( 'Course', 'tutor' ),
						'slug'       => 'options',
						'block_type' => 'uniform',
						'fields'     => array(
							array(
								'key'         => 'enable_gutenberg_course_edit',
								'type'        => 'toggle_switch',
								'label'       => __('Gutenberg Editor', 'tutor'),
								'default'     => 'off',
								'label_title' => __( '', 'tutor' ),
								'desc'        => __('Use Gutenberg editor on course description area.', 'tutor'),
							),
							array(
								'key'         => 'hide_course_from_shop_page',
								'type'        => 'toggle_switch',
								'label'       => __('Hide course products from shop page', 'tutor'),
								'default'     => 'off',
								'label_title' => __( '', 'tutor' ),
								'desc'        => __('Enabling this feature will remove course products from the shop page.', 'tutor'),
							),
							array(
								'key'     => 'course_archive_page',
								'type'    => 'select',
								'label'   => __( 'Course Archive Page', 'tutor' ),
								'default' => '0',
								'options' => $pages,
								'desc'    => __( 'This page will be used to list all the published courses.', 'tutor' ),
							),
							array(
								'key'     => 'instructor_register_page',
								'type'    => 'select',
								'label'   => __( 'Instructor Registration Page', 'tutor' ),
								'default' => '0',
								'options' => $pages,
								'desc'    => __( 'This page will be used to sign up new instructors.', 'tutor' ),
							),
							array(
								'key'     => 'student_register_page',
								'type'    => 'select',
								'label'   => __( 'Student Registration Page', 'tutor' ),
								'default' => '0',
								'options' => $pages,
								'desc'    => __( 'Choose the page for student registration page', 'tutor' ),
							),
							array(
								'key'     => 'lesson_permalink_base',
								'type'    => 'text',
								'label'   => __( 'Lesson Permalink Base', 'tutor' ),
								'default' => 'lessons',
								'desc'    => $lesson_url,
							),
							array(
								'key'     => 'lesson_video_duration_youtube_api_key',
								'type'    => 'text',
								'label'   => __('Youtube API Key', 'tutor'),
								'default' => '',
								'desc'    => __('To get dynamic video duration from Youtube, you need to set API key first', 'tutor'),
							),
						)
					),
					array(
						'label'      => __( 'Options', 'tutor' ),
						'slug'       => 'options',
						'block_type' => 'uniform',
						'fields'     => array(
							array(
								'key'         => 'enable_profile_completion',
								'type'        => 'toggle_switch',
								'label'       => __('Profile Completion', 'tutor'),
								'label_title' => __( '', 'tutor' ),
								'default'     => 'off',
								'desc'        => __('Enabling this feature will show a notification bar to students and instructors to complete their profile information',	'tutor'),
							),
							array(
								'key'         => 'disable_tutor_native_login',
								'type'        => 'toggle_switch',
								'label'       => __('Disbale Tutor Login', 'tutor'),
								'label_title' => __( '', 'tutor' ),
								'default'     => 'on',
								'desc'        => __('Disable to use the default WordPress login page',	'tutor'),
							),
							array(
								'key'         => 'hide_admin_bar_for_users',
								'type'        => 'toggle_switch',
								'label'       => __( 'Hide Frontend Admin Bar', 'tutor' ),
								'label_title' => __( '', 'tutor' ),
								'default'     => 'off',
								'desc'        => __( 'Hide admin bar option allow you to hide WordPress admin bar entirely from the frontend. It will still show to administrator roles user', 'tutor' ),
							),
							array(
								'key'         => 'delete_on_uninstall',
								'type'        => 'toggle_switch',
								'label'       => __('Erase upon uninstallation', 'tutor'),
								'label_title' => __( '', 'tutor' ),
								'default'     => 'off',
								'desc'        => __('Delete all data during uninstallation', 'tutor'),
							),
							array(
								'key'         => 'hide_admin_bar_for_users',
								'type'        => 'toggle_switch',
								'label'       => __('Hide Frontend Admin Bar', 'tutor'),
								'label_title' => __( '', 'tutor' ),
								'default'     => 'off',
								'desc'        => __('Hide admin bar option allow you to hide WordPress admin bar entirely from the frontend. It will still show to administrator roles user',	'tutor'),
							),
							array(
								'key'         => 'enable_tutor_maintenance_mode',
								'type'        => 'toggle_switch',
								'label'       => __('Maintenance Mode', 'tutor'),
								'label_title' => __( '', 'tutor' ),
								'default'     => 'off',
								'desc'        => __('Enabling the maintenance mode allows you to display a custom message on the frontend. During this time, visitors can not access the site content. But the wp-admin dashboard will remain accessible.',	'tutor'),
							),
						),
					),
				),
			),
		);

		$attrs = apply_filters( 'tutor/options/extend/attr', apply_filters( 'tutor/options/attr', $attr ) );
	
		// Get the active tab
		$tab_page   = tutor_utils()->array_get('tab_page', $_REQUEST, 'general');
		$tab_data = null;
		$template = null;

		foreach ( $attrs as $key => $section ) {
			if($tab_page == $key) {
				if(isset($section['template_path']) && $section['template_path']) {
					$template = $section['template_path'];
					$tab_data = $section;
				}
				break;
			}
		}

		// Store in runtime cache
		$this->setting_fields = array(
			'option_fields' => $attrs,
			'active_tab' => $tab_page,
			'active_tab_data' => $tab_data,
			'template_path' => $template
		);

		return $this->setting_fields;
	}

	/**
	 * @param array $field
	 *
	 * @return string
	 *
	 * Generate Option Field
	 */
	public function generate_field( $field = array() ) {
		ob_start();
		include tutor()->path . "views/options/field-types/{$field['type']}.php";

		return ob_get_clean();
	}

	public function field_type( $field = array() ) {
		ob_start();
		include tutor()->path . "views/options/field-types/{$field['type']}.php";

		return ob_get_clean();
	}

	public function blocks( $blocks = array() ) {
		ob_start();
		include tutor()->path . 'views/options/option_blocks.php';
		return ob_get_clean();
	}

	public function template( $section = array() ) {
		ob_start();
		include tutor()->path . "views/options/template/{$section['template']}.php";
		return ob_get_clean();
	}
}
