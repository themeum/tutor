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


	public $option;
	public $status;
	public $options_attr;
	public $options_tools;

	public function __construct() {
		 $this->option       = (array) maybe_unserialize( get_option( 'tutor_option' ) );
		$this->status        = $this->status();
		$this->options_attr  = $this->options_attr();
		$this->options_tools = $this->options_tools();
		$this->get_param_val = $this->get_param_val( '' );
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
		$option = $this->option;

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
	 * Funcation to get params val
	 *
	 * @param  mixed $param
	 * @return void
	 */
	public function get_param_val( $param = '' ) {
		return isset( $_GET[ $param ] ) ? $_GET[ $param ] : null;
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

		$attr       = $this->options_attr();
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

	/**
	 * Function options_tools
	 *
	 * @return void
	 */
	public function options_tools() {
		$pages = tutor_utils()->get_pages();

		// $course_base = tutor_utils()->course_archive_page_url();
		$lesson_url                    = site_url() . '/course/' . 'sample-course/<code>lessons</code>/sample-lesson/';
		$student_url                   = tutor_utils()->profile_url();
		$attempts_allowed              = array();
		$attempts_allowed['unlimited'] = __( 'Unlimited', 'tutor' );
		$attempts_allowed              = array_merge( $attempts_allowed, array_combine( range( 1, 20 ), range( 1, 20 ) ) );

		$video_sources = array(
			'html5'        => __( 'HTML 5 (mp4)', 'tutor' ),
			'external_url' => __( 'External URL', 'tutor' ),
			'youtube'      => __( 'Youtube', 'tutor' ),
			'vimeo'        => __( 'Vimeo', 'tutor' ),
			'embedded'     => __( 'Embedded', 'tutor' ),
		);

		$course_filters = array(
			'search'           => __( 'Keyword Search', 'tutor' ),
			'category'         => __( 'Category', 'tutor' ),
			'tag'              => __( 'Tag', 'tutor' ),
			'difficulty_level' => __( 'Difficulty Level', 'tutor' ),
			'price_type'       => __( 'Price Type', 'tutor' ),
		);

		$attr_tools = array(
			'tools' => array(
				'label'    => __( 'Tools', 'tutor' ),
				'sections' => array(
					'status'        => array(
						'label'    => __( 'Status', 'tutor' ),
						'slug'     => 'status',
						'icon'     => 'icon-chart-filled',
						'desc'     => __( 'Status Settings', 'tutor' ),
						'template' => 'status',
						'icon'     => 'icon-chart-filled',
						'blocks'   => array(
							'wordpress_environment' => array(
								'label'      => __( 'WordPress environment', 'tutor' ),
								'slug'       => 'wordpress_environment',
								'classes'    => 'wordpress_environment',
								'block_type' => 'column',
								'fieldset'   => array(
									array(
										array(
											'key'     => 'home_url',
											'type'    => 'info_row',
											'label'   => __( 'Home URL', 'tutor' ),
											'status'  => 'default',
											'default' => $this->status( 'home_url' ),
										),
									),
									array(
										array(
											'key'     => 'wordpress_version',
											'type'    => 'info_col',
											'label'   => __( 'WordPress version', 'tutor' ),
											'status'  => 'default',
											'default' => $this->status( 'wordpress_version' ),

										),
										array(
											'key'     => 'wordpress_multisite',
											'type'    => 'info_col',
											'label'   => __( 'WordPress multisite', 'tutor' ),
											'status'  => 'default',
											'default' => $this->status( 'wordpress_multisite' ),

										),
										array(
											'key'     => 'wordpress_debug_mode',
											'type'    => 'info_col',
											'label'   => __( 'WordPress debug mode', 'tutor' ),
											'status'  => ( defined( 'WP_DEBUG' ) && true === WP_DEBUG ) ? 'success' : 'default',
											'default' => $this->status( 'wordpress_debug_mode' ),

										),
										array(
											'key'     => 'language',
											'type'    => 'info_col',
											'label'   => __( 'Language', 'tutor' ),
											'status'  => 'default',
											'default' => $this->status( 'language' ),

										),
									),
									array(
										array(
											'key'     => 'site_url',
											'type'    => 'info_row',
											'label'   => __( 'Site URL', 'tutor' ),
											'status'  => 'default',
											'default' => $this->status( 'site_url' ),

										),
									),
									array(
										array(
											'key'     => 'tutor_version',
											'type'    => 'info_col',
											'label'   => __( 'Tutor version', 'tutor' ),
											'status'  => 'success',
											'default' => $this->status( 'tutor_version' ),

										),
										array(
											'key'     => 'wordpress_memory_limit',
											'type'    => 'info_col',
											'label'   => __( 'WordPress memory limit', 'tutor' ),
											'status'  => 'success',
											'default' => $this->status( 'wordpress_memory_limit' ),

										),
										array(
											'key'     => 'wordpress_cron',
											'type'    => 'info_col',
											'label'   => __( 'WordPress corn', 'tutor' ),
											'status'  => ! empty( _get_cron_array() ) ? 'success' : 'default',
											'default' => $this->status( 'wordpress_cron' ),

										),
										array(
											'key'     => 'external_object_cache',
											'type'    => 'info_col',
											'label'   => __( 'External object cache', 'tutor' ),
											'status'  => 'default',
											'default' => $this->status( 'external_object_cache' ),

										),
									),
								),
							),
							'server_environment'    => array(
								'label'      => __( 'Server environment', 'tutor' ),
								'slug'       => 'server_environment',
								'block_type' => 'column',
								'fieldset'   => array(
									array(
										array(
											'key'     => 'server_info',
											'type'    => 'info_col',
											'label'   => __( 'Server info', 'tutor' ),
											'status'  => 'default',
											'default' => $this->status( 'server_info' ),

										),
										array(
											'key'     => 'php_version',
											'type'    => 'info_col',
											'label'   => __( 'PHP version', 'tutor' ),
											'status'  => 'default',
											'default' => $this->status( 'php_version' ),

										),
										array(
											'key'     => 'php_post_max_size',
											'type'    => 'info_col',
											'label'   => __( 'PHP post max size', 'tutor' ),
											'status'  => 'default',
											'default' => $this->status( 'php_post_max_size' ),

										),
										array(
											'key'     => 'php_time_limit',
											'type'    => 'info_col',
											'label'   => __( 'PHP time limit', 'tutor' ),
											'status'  => 'default',
											'default' => $this->status( 'php_time_limit' ),

										),
										array(
											'key'     => 'curl_version',
											'type'    => 'info_col',
											'label'   => __( 'cURL version', 'tutor' ),
											'status'  => 'default',
											'default' => $this->status( 'curl_version' ),

										),
										array(
											'key'     => 'wordpress_debug_mode',
											'type'    => 'info_col',
											'label'   => __( 'WordPress debug mode', 'tutor' ),
											'status'  => 'default',
											'default' => $this->status( 'wordpress_debug_mode' ),

										),
										array(
											'key'     => 'language',
											'type'    => 'info_col',
											'label'   => __( 'Language', 'tutor' ),
											'status'  => 'default',
											'default' => $this->status( 'language' ),

										),
										array(
											'key'     => 'wordpress_debug_mode',
											'type'    => 'info_col',
											'label'   => __( 'WordPress debug mode', 'tutor' ),
											'status'  => 'default',
											'default' => $this->status( 'wordpress_debug_mode' ),

										),
									),
									array(
										array(
											'key'     => 'site_url',
											'type'    => 'info_row',
											'label'   => __( 'Site URL', 'tutor' ),
											'status'  => 'default',
											'default' => $this->status( 'site_url' ),

										),
									),
									array(
										array(
											'key'     => 'tutor_version',
											'type'    => 'info_col',
											'label'   => __( 'Tutor version', 'tutor' ),
											'status'  => 'default',
											'default' => $this->status( 'tutor_version' ),

										),
										array(
											'key'     => 'wordpress_memory_limit',
											'type'    => 'info_col',
											'label'   => __( 'WordPress memory limit', 'tutor' ),
											'status'  => 'default',
											'default' => $this->status( 'wordpress_memory_limit' ),

										),
										array(
											'key'     => 'wordpress_cron',
											'type'    => 'info_col',
											'label'   => __( 'WordPress corn', 'tutor' ),
											'status'  => 'default',
											'default' => $this->status( 'wordpress_cron' ),

										),
										array(
											'key'     => 'external_object_cache',
											'type'    => 'info_col',
											'label'   => __( 'External object cache', 'tutor' ),
											'status'  => 'default',
											'default' => $this->status( 'external_object_cache' ),

										),
									),
								),
							),
						),
					),
					'import_export' => array(
						'label'    => __( 'Import/Export', 'tutor' ),
						'slug'     => 'import_export',
						'icon'     => 'icon-chart-filled',
						'desc'     => __( 'Import/Export Settings', 'tutor' ),
						'template' => 'import_export',
						'icon'     => 'icon-import-export-filled',
						'blocks'   => array(),
					),
					'tutor_pages'   => array(
						'label'    => __( 'Tutor Pages', 'tutor' ),
						'slug'     => 'tutor_pages',
						'icon'     => 'icon-chart-filled',
						'desc'     => __( 'Tutor Pages Settings', 'tutor' ),
						'template' => 'tutor_pages',
						'icon'     => 'icon-review-line',
						'blocks'   => array(
							'block' => array(),
						),
					),
					'tutor-setup'   => array(
						'label'    => __( 'Setup Wizard', 'tutor' ),
						'slug'     => 'tutor-setup',
						'icon'     => 'icon-chart-filled',
						'desc'     => __( 'Setup Wizard Settings', 'tutor' ),
						'template' => 'tutor-setup',
						'icon'     => 'icon-earth-filled',
						'blocks'   => array(
							'block' => array(),
						),
					),
				),
			),
		);

		return $attr_tools;
	}

	public function options_attr() {
		$pages = tutor_utils()->get_pages();

		// $course_base = tutor_utils()->course_archive_page_url();
		$lesson_url                    = site_url() . '/course/' . 'sample-course/<code>lessons</code>/sample-lesson/';
		$student_url                   = tutor_utils()->profile_url();
		$attempts_allowed              = array();
		$attempts_allowed['unlimited'] = __( 'Unlimited', 'tutor' );
		$attempts_allowed              = array_merge( $attempts_allowed, array_combine( range( 1, 20 ), range( 1, 20 ) ) );

		$video_sources = array(
			'html5'        => __( 'HTML 5 (mp4)', 'tutor' ),
			'external_url' => __( 'External URL', 'tutor' ),
			'youtube'      => __( 'Youtube', 'tutor' ),
			'vimeo'        => __( 'Vimeo', 'tutor' ),
			'embedded'     => __( 'Embedded', 'tutor' ),
		);

		$course_filters = array(
			'search'           => __( 'Keyword Search', 'tutor' ),
			'category'         => __( 'Category', 'tutor' ),
			'tag'              => __( 'Tag', 'tutor' ),
			'difficulty_level' => __( 'Difficulty Level', 'tutor' ),
			'price_type'       => __( 'Price Type', 'tutor' ),
		);

		$attr = array(
			'basic' => array(
				'label'    => __( 'Basic', 'tutor' ),
				'sections' => array(
					'gradebook'          => array(
						'label'    => __( 'Gradebook', 'tutor' ),
						'slug'     => 'gradebook',
						'desc'     => __( 'Gradebook Settings', 'tutor' ),
						'template' => 'gradebook',
						'icon'     => __( 'gradebook', 'tutor' ),
						'blocks'   => array(
							'email_to_student' => array(
								'label'      => __( '', 'tutor' ),
								'slug'       => 'e_mail_to_students',
								'block_type' => 'isolate',
								'fields'     => array(
									array(
										'key'         => 'use_points_instead_of_grades',
										'type'        => 'toggle_switch',
										'label'       => __( 'Use Points Instead of Grades', 'tutor' ),
										'default'     => 'off',
										'label_title' => __( '', 'tutor' ),
										'desc'        => __( 'Enable this option to use numerical points instead of letter grades.', 'tutor' ),
									),
									array(
										'key'         => 'show_highest_possible_points',
										'type'        => 'toggle_switch',
										'label'       => __( 'Show Highest Possible Points', 'tutor' ),
										'default'     => 'off',
										'label_title' => __( '', 'tutor' ),
										'desc'        => __( 'Display the highest possible points next to a student’s score such as 3.8/4.0', 'tutor' ),
									),
									array(
										'key'         => 'separator_between_scores',
										'type'        => 'text',
										'label'       => __( 'Separator Between Scores', 'tutor' ),
										'default'     => 0,
										'label_title' => __( '', 'tutor' ),
										'desc'        => __( 'Input the separator text or symbol to display. Example: Insert / to display 3.8/4.0 or “out of” 3.8 out of 4.', 'tutor' ),
									),
									array(
										'key'         => 'grade_scale',
										'type'        => 'text',
										'label'       => __( 'Grade Scale', 'tutor' ),
										'default'     => 0,
										'label_title' => __( '', 'tutor' ),
										'desc'        => __( 'Insert the grade point out of which the final results will be calculated.', 'tutor' ),
									),
								),
							),
						),
					),
					'email_notification' => array(
						'label'    => __( 'Email', 'tutor' ),
						'slug'     => 'email',
						'desc'     => __( 'Email Settings', 'tutor' ),
						'template' => 'basic',
						'icon'     => __( 'envelope', 'tutor' ),
						'blocks'   => array(
							'course' => array(
								'label'      => __( 'Course', 'tutor' ),
								'slug'       => 'course',
								'block_type' => 'uniform',
								'fields'     => array(
									array(
										'key'     => 'email_from_name',
										'type'    => 'text',
										'label'   => __( 'Name', 'tutor' ),
										'default' => get_option( 'blogname' ),
										'desc'    => __( 'The name under which all the emails will be sent', 'tutor' ),
									),
									array(
										'key'     => 'email_from_address',
										'type'    => 'text',
										'label'   => __( 'E-Mail Address', 'tutor' ),
										'default' => get_option( 'admin_email' ),

										'desc'    => __( 'The E-Mail address from which all emails will be sent', 'tutor' ),
									),
									array(
										'key'     => 'email_footer_text',
										'type'    => 'textarea',
										'label'   => __( 'E-Mail Footer Text', 'tutor' ),
										'default' => '',

										'desc'    => __( 'The text to appear in E-Mail template footer', 'tutor' ),
									),
								),
							),
						),
					),
					'general'            => array(
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
								'label'      => __( 'Video', 'tutor' ),
								'slug'       => 'video',
								'block_type' => 'uniform',
								'fields'     => array(
									array(
										'key'         => 'supported_video_sources',
										'type'        => 'checkbox_horizontal',
										'label'       => __( 'Preferred Video Source', 'tutor' ),
										'label_title' => __( 'Preferred Video Source', 'tutor' ),
										'default'     => array(
											'external_url' => true,
											'html5'        => true,
										),
										'options'     => $video_sources,
										'desc'        => __( 'Choose video sources you\'d like to support. Unchecking all will not disable video feature.', 'tutor' ),
									),
									array(
										'key'     => 'default_video_source',
										'type'    => 'select',
										'label'   => __( 'Default Video Source', 'tutor' ),
										'options' => $video_sources,
										'default' => '0',
										'desc'    => __( 'Choose video source to be selected by default.', 'tutor' ),
									),
								),
							),
							array(
								'label'      => __( 'Course', 'tutor' ),
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
										'key'     => 'course_archive_page',
										'type'    => 'select',
										'label'   => __( 'Course Archive Page', 'tutor' ),
										'default' => '0',
										'options' => $pages,
										'desc'    => __( 'This page will be used to list all the published courses.', 'tutor' ),
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
								),
							),
							array(
								'label'      => __( 'Others', 'tutor' ),
								'slug'       => 'others',
								'block_type' => 'isolate',
								'fields'     => array(
									array(
										'key'     => 'attachment_open_mode',
										'type'    => 'radio_horizontal_full',
										'label'   => __( 'Attachment Open Mode', 'tutor' ),
										'default' => '4',
										'options' => array(
											'one'   => 'One',
											'two'   => 'Two',
											'three' => 'Three',
											'four'  => 'Four',
										),
										'desc'    => __( 'Choose how you want users to view attached files.', 'tutor' ),
									),
									array(
										'key'         => 'enable_lesson_classic_editor',
										'type'        => 'toggle_switch',
										'label'       => __( 'Enable Classic Editor Support', 'tutor' ),
										'label_title' => __( '', 'tutor' ),
										'default'     => 'off',
										'desc'        => __( 'Enable classic editor to get full support of any editor/page builder.', 'tutor' ),
									),
									array(
										'key'         => 'enable_course_marketplace',
										'type'        => 'toggle_switch',
										'label'       => __( 'Enable Marketplace', 'tutor' ),
										'label_title' => __( '', 'tutor' ),
										'default'     => 'off',
										'desc'        => __( 'Allow multiple instructors to upload their courses.', 'tutor' ),
									),
									array(
										'key'     => 'lesson_permalink_base',
										'type'    => 'text',
										'label'   => __( 'Lesson Permalink Base', 'tutor' ),
										'default' => 'lessons',

										'desc'    => $lesson_url,
									),
									array(
										'key'     => 'student_register_page',
										'type'    => 'select',
										'label'   => __( 'Student Registration Page', 'tutor' ),
										'default' => '0',

										'options' => $pages,
										'desc'    => __( 'Choose the page for student registration page', 'tutor' ),
									),
								),
							),
							array(
								'label'      => __( 'Instructor', 'tutor' ),
								'slug'       => 'instructor',
								'block_type' => 'uniform',
								'fields'     => array(
									array(
										'key'     => 'instructor_register_page',
										'type'    => 'select',
										'label'   => __( 'Instructor Registration Page', 'tutor' ),
										'default' => '0',
										'options' => $pages,
										'desc'    => __( 'This page will be used to sign up new instructors.', 'tutor' ),
									),
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
									),
								),
							),
						),
					),
					'course'             => array(
						'label'    => __( 'Course', 'tutor' ),
						'slug'     => 'course',
						'desc'     => __( 'Course Settings', 'tutor' ),
						'template' => 'basic',
						'icon'     => __( 'book-open', 'tutor' ),
						'blocks'   => array(
							array(
								'label'      => __( 'Lesson', 'tutor' ),
								'slug'       => 'lesson',
								'block_type' => 'uniform',
								'fields'     => array(
									array(
										'key'     => 'enable_video_player',
										'type'    => 'checkbox-horizontal-full',
										'label'   => __( 'Enable Video Player', 'tutor' ),
										'default' => array(
											'youtube' => 'on',
											'vimeo'   => 'off',
										),
										'options' => array(
											'youtube' => '<i class="lab la-youtube"></i> YouTube',
											'vimeo'   => '<i class="lab la-vimeo"></i> Vimeo',
										),
										'desc'    => __( 'Define how many column you want to use to display courses.', 'tutor' ),
									),
									array(
										'key'         => 'student_must_login_to_view_course',
										'type'        => 'toggle_switch',
										'label'       => __( 'Course Visibility', 'tutor' ),
										'label_title' => __( 'Logged in only', 'tutor' ),
										'default'     => 'off',
										'desc'        => __( 'Students must be logged in to view course', 'tutor' ),
									),
									'course_content_access_for_ia' => array(
										'key'         => 'autoload_next_course_content',
										'type'        => 'toggle_switch',
										'label'       => __( 'Course Content Access', 'tutor' ),
										'label_title' => __( '', 'tutor' ),
										'default'     => 'off',
										'desc'        => __( 'Allow instructors and admins to view the course content without enrolling', 'tutor' ),
									),
								),
							),
							array(
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
										'label'   => __( 'Attempts allowed', 'tutor' ),
										'default' => '10',

										'desc'    => __( 'The highest number of attempts students are allowed to take for a quiz. 0 means unlimited attempts.', 'tutor' ),
									),
									array(
										'key'            => 'quiz_grade_method',
										'type'           => 'radio_horizontal_full',
										'label'          => __( 'Final grade calculation', 'tutor' ),
										'default'        => 'minutes',
										'select_options' => false,
										'options'        => array(
											'highest_grade' => __( 'Highest Grade', 'tutor' ),
											'average_grade' => __( 'Average Grade', 'tutor' ),
											'first_attempt' => __( 'First Attempt', 'tutor' ),
											'last_attempt' => __( 'Last Attempt', 'tutor' ),
										),
										'desc'           => __( 'When multiple attempts are allowed, which method should be used to calculate a student\'s final grade for the quiz.', 'tutor' ),
									),
									array(
										'key'         => 'course_content_access_for_ia',
										'type'        => 'toggle_switch',
										'label'       => __( 'Course Content Access', 'tutor' ),
										'label_title' => __( '', 'tutor' ),
										'default'     => 'off',
										'desc'        => __( 'Allow instructors and admins to view the course content without enrolling', 'tutor' ),
									),
								),
							),
						),
					),
					'monitization'       => array(
						'label'    => __( 'Monitization', 'tutor' ),
						'slug'     => 'monitization',
						'desc'     => __( 'Monitization Settings', 'tutor' ),
						'template' => 'basic',
						'icon'     => __( 'discount-filled', 'tutor' ),
						'blocks'   => array(
							array(
								'label'      => false,
								'block_type' => 'uniform',
								'fields'     => array(
									array(
										'key'            => 'monetize_by',
										'type'           => 'toggle_switch',
										'label'          => __( 'Disable Monetization', 'tutor' ),
										'label_title'    => __( '', 'tutor' ),
										'default'        => 'off',
										'select_options' => false,
										'options'        => apply_filters(
											'tutor_monetization_options',
											array(
												'free' => __( 'Disable Monetization', 'tutor' ),
											)
										),
										'desc'           => __( 'Select a monetization option to generate revenue by selling courses. Supports: WooCommerce, Easy Digital Downloads, Paid Memberships Pro', 'tutor' ),
									),
								),
							),
							array(
								'label'      => __( 'Options', 'tutor' ),
								'slug'       => 'options',
								'block_type' => 'uniform',
								'fields'     => array(
									array(
										'key'     => 'select_e_commerce_engine',
										'type'    => 'select',
										'label'   => __( 'Select eCommerce Engine', 'tutor' ),
										'options' => $video_sources,
										'default' => '0',
										'desc'    => __( 'Choose video sources you\'d like to support. Unchecking all will not disable video feature.', 'tutor' ),
									),
									array(
										'key'         => 'enable_guest_mode',
										'type'        => 'toggle_switch',
										'label'       => __( 'Enable Guest Mode', 'tutor' ),
										'label_title' => __( '', 'tutor' ),
										'default'     => 'off',
										'desc'        => __( 'Select a monetization option to generate revenue by selling courses. Supports: WooCommerce, Easy Digital Downloads, Paid Memberships Pro', 'tutor' ),
									),
									array(
										'key'         => 'sharing_percentage',
										'type'        => 'double_input',
										'label'       => __( 'Sharing Percentage', 'tutor' ),
										'label_title' => __( '', 'tutor' ),
										'default'     => '',
										'fields'      => array(
											'instructor_takes' => array(
												'id'      => 'revenue-instructor',
												'type'    => 'ratio',
												'title'   => 'Instructor Takes',
												'default' => 10,
											),
											'admin_takes' => array(
												'id'      => 'revenue-admin',
												'type'    => 'ratio',
												'title'   => 'Admin Takes',
												'default' => 100,
											),
										),
										'desc'        => __( 'Select a monetization option to generate revenue by selling courses. Supports: WooCommerce, Easy Digital Downloads, Paid Memberships Pro', 'tutor' ),
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
										'key'         => 'deduct_fees',
										'type'        => 'toggle_switch',
										'label'       => __( 'Deduct Fees', 'tutor' ),
										'label_title' => __( '', 'tutor' ),
										'default'     => 'off',
										'desc'        => __( 'content goes here', 'tutor' ),
									),
									array(
										'key'         => 'fee_description',
										'type'        => 'textarea',
										'label'       => __( 'Fee Description', 'tutor' ),
										'label_title' => __( '', 'tutor' ),
										'default'     => 'free',
										'desc'        => __( 'content goes here', 'tutor' ),
									),
									array(
										'key'          => 'fee_amount_type',
										'type'         => 'group_fields',
										'label'        => __( 'Fee Amount & Type', 'tutor' ),
										'desc'         => __( 'content goes here', 'tutor' ),
										'group_fields' => array(
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
											'value' => array(
												'type'    => 'text',
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
										'label'   => __( 'Minimum Withdraw Amount', 'tutor' ),
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
										'key'         => 'enable_withdraw_method',
										'type'        => 'checkbox_horizontal',
										'label'       => __( 'Enable withdraw method', 'tutor' ),
										'label_title' => __( '', 'tutor' ),
										'options'     => $course_filters,
										'desc'        => __( 'Choose preferred filter options you\'d like to show in course archive page.', 'tutor' ),
									),
									array(
										'key'         => 'bank_instructions',
										'type'        => 'toggle_switch',
										'label'       => __( 'Bank Instructions', 'tutor' ),
										'label_title' => __( '', 'tutor' ),
										'default'     => 'off',
										'desc'        => __( 'content goes here', 'tutor' ),
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
							array(
								'label'      => __( 'Course', 'tutor' ),
								'slug'       => 'course',
								'block_type' => 'uniform',
								'fields'     => array(
									array(
										'key'     => 'course_builder_page_logo',
										'type'    => 'upload_full',
										'label'   => __( 'Course Builder Page Logo', 'tutor' ),
										'default' => tutor()->icon_dir . 'tutor-logo-course-builder.svg',
										'desc'    => __(
											'<p>Size: <strong>200x40 pixels;</strong> File Support:<strong>jpg, .jpeg or .png.</strong></p>',
											'tutor'
										),
									),
									array(
										'key'     => 'courses_col_per_row',
										'type'    => 'radio_horizontal',
										'label'   => __( 'Column Per Row', 'tutor' ),
										'default' => 'four',

										'options' => array(
											'one'   => 'One',
											'two'   => 'Two',
											'three' => 'Three',
											'four'  => 'Four',
										),
										'desc'    => __( 'Define how many column you want to use to display courses.', 'tutor' ),
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
										'options'     => $course_filters,
										'desc'        => __( 'Choose preferred filter options you\'d like to show in course archive page.', 'tutor' ),
									),
								),
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
												'portrait' => array(
													'title' => 'Portrait',
													'image' => 'instructor-layout/intructor-portrait.svg',
												),
												'cover'    => array(
													'title' => 'Cover',
													'image' => 'instructor-layout/instructor-cover.svg',
												),
												'minimal'  => array(
													'title' => 'Minimal',
													'image' => 'instructor-layout/instructor-minimal.svg',
												),
											),
											'horizontal' => array(
												'portrait' => array(
													'title' => 'Horizontal Portrait',
													'image' => 'instructor-layout/instructor-horizontal-portrait.svg',
												),
												'minimal'  => array(
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
											'modern'  => array(
												'title' => 'Modern',
												'image' => 'profile-layout/profile-modern.svg',
											),
											'minimal' => array(
												'title' => 'Minimal',
												'image' => 'profile-layout/profile-minimal.svg',
											),
											'classic' => array(
												'title' => 'Classic',
												'image' => 'profile-layout/profile-classic.svg',
											),
										),
										'desc'          => __( 'Content Needed Here...', 'tutor' ),
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
												'label_title' => __( 'Enable', 'tutor' ),
												'default' => '0',
												'desc'    => __( 'Show instructor bio on each page', 'tutor' ),
											),
											array(
												'key'     => 'enable_q_and_a_on_course',
												'type'    => 'toggle_single',
												'label'   => __( 'Question and Answer', 'tutor' ),
												'label_title' => __( 'Enable', 'tutor' ),
												'default' => '0',
												'desc'    => __( 'Enabling this feature will add a Q&amp;A section on every course.', 'tutor' ),
											),
											array(
												'key'     => 'disable_course_author',
												'type'    => 'toggle_single',
												'label'   => __( 'Author', 'tutor' ),
												'label_title' => __( 'Disable', 'tutor' ),
												'default' => '0',
												'desc'    => __( 'Disabling this feature will be removed course author name from the course page.', 'tutor' ),
											),
											array(
												'key'     => 'disable_course_level',
												'type'    => 'toggle_single',
												'label'   => __( 'Course Level', 'tutor' ),
												'label_title' => __( 'Disable', 'tutor' ),
												'default' => '0',
												'desc'    => __( 'Disabling this feature will be removed course level from the course page.', 'tutor' ),
											),
											array(
												'key'     => 'disable_course_share',
												'type'    => 'toggle_single',
												'label'   => __( 'Course Share', 'tutor' ),
												'label_title' => __( 'Disable', 'tutor' ),
												'default' => '0',
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
								'label'      => __( 'Options', 'tutor' ),
								'slug'       => 'options',
								'block_type' => 'uniform',
								'fields'     => array(
									array(
										'key'         => 'login_error_message',
										'type'        => 'toggle_switch',
										'label'       => __( 'Error message for wrong login credentials', 'tutor' ),
										'label_title' => __( '', 'tutor' ),
										'default'     => 'off',
										'desc'        => __( 'Login error message displayed when the user puts wrong login credentials.', 'tutor' ),
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
										'key'         => 'enable_tutor_maintenance_mode',
										'type'        => 'toggle_switch',
										'label'       => __( 'Maintenance Mode', 'tutor' ),
										'label_title' => __( '', 'tutor' ),
										'default'     => 'off',
										'desc'        => __( 'Enabling the maintenance mode allows you to display a custom message on the frontend. During this time, visitors can not access the site content. But the wp-admin dashboard will remain accessible.', 'tutor' ),
									),
								),
							),
						),
					),
					'tutor_certificate'  => array(
						'label'    => __( 'Certificate', 'tutor' ),
						'slug'     => 'certificate',
						'desc'     => __( 'Certificate Settings', 'tutor' ),
						'template' => 'certificate',
						'icon'     => __( 'certificate', 'tutor' ),
						'blocks'   => array(
							array(
								'label'      => __( 'Options', 'tutor' ),
								'slug'       => 'options',
								'block_type' => 'uniform',
								'fields'     => array(
									array(
										'key'         => 'login_error_message',
										'type'        => 'toggle_switch',
										'label'       => __( 'Error message for wrong login credentials', 'tutor' ),
										'label_title' => __( '', 'tutor' ),
										'default'     => 'off',
										'desc'        => __( 'Login error message displayed when the user puts wrong login credentials.', 'tutor' ),
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
										'key'         => 'enable_tutor_maintenance_mode',
										'type'        => 'toggle_switch',
										'label'       => __( 'Maintenance Mode', 'tutor' ),
										'label_title' => __( '', 'tutor' ),
										'default'     => 'off',
										'desc'        => __( 'Enabling the maintenance mode allows you to display a custom message on the frontend. During this time, visitors can not access the site content. But the wp-admin dashboard will remain accessible.', 'tutor' ),
									),
								),
							),
						),
					),
				),
			),
		);
		$attrs   = apply_filters( 'tutor/options/attr', $attr );
		$extends = apply_filters( 'tutor/options/extend/attr', array() );
		// $addons  = apply_filters( 'tutor/options/addons/attr', array() );

		if ( tutils()->count( $extends ) ) {
			foreach ( $extends as $section_key => $extended_sections ) {

				if ( array_key_exists( 'pro_feature', $extended_sections ) ) {
					unset( $extended_sections['pro_feature'] );
					foreach ( $extended_sections as $extend_key => $extended_blocks ) {
						$sections         = $attrs[ $extend_key ]['sections'];
						$existing_section = $sections[ $section_key ];
						$attrs[ $extend_key ]['sections'][ $section_key ]['blocks'] = array_merge( $existing_section['blocks'], $extended_blocks );

					}
				}
				// pr( array( $section_key => $extended_sections ) );
			}
		}

		// if ( tutils()->count( $addons ) ) {
		// pr( $addons );
		// die;
		// }

		return $attrs;

	}

	/**
	 * Check if query string
	 *
	 * @param  mixed $dataArr
	 * @param  mixed $url_page
	 * @return void
	 */
	public function url_exists( $dataArr = array(), $url_page = null ) {
		$url_exist = false;
		$is_active = false;
		$j         = 0;

		foreach ( $dataArr as $key => $section ) {
			// pr($section);
			$j        += 1;
			$is_active = isset( $url_page ) && $url_page === $section[ $key ] ? true : ( ! isset( $url_page ) && $j === 1 ? true : false );

			if ( $is_active === true ) {
				$url_exist = true;
				break;
			}
		};

		return $url_exist;
	}

	public function get_active( int $index = null, string $page = null, $slug, $url_exist ) {
		$is_active = false;
		$is_active = ( $index === 1 && $url_exist === false ) ? true : ( isset( $page ) && $page === $slug ? true : ( ! isset( $page ) && $index === 1 ? true : false ) );

		return $is_active;
	}

	public function status( $type = '' ) {
		ob_start();
		$data         = array();
		$data[ null ] = 'null';

		// $environment  = tutor_admin()->get_environment_info();
		$environment = Admin::get_environment_info();

		$data['home_url'] = $environment['home_url'] ?? null;

		$data['site_url'] = $environment['site_url'] ?? null;

		$latest_version = get_transient( 'tutor_system_status_wp_version_check' );

		if ( false === $latest_version ) {
			$version_check = wp_remote_get( 'https://api.wordpress.org/core/version-check/1.7/' );
			$api_response  = json_decode( wp_remote_retrieve_body( $version_check ), true );

			$latest_version = ( $api_response && isset( $api_response['offers'], $api_response['offers'][0], $api_response['offers'][0]['version'] ) )
				? $api_response['offers'][0]['version']
				: $environment['wp_version'];
			set_transient( 'tutor_system_status_wp_version_check', $latest_version, DAY_IN_SECONDS );
		}

		$data['wordpress_version'] = ( version_compare( $environment['wp_version'], $latest_version, '<' ) )
			? sprintf( esc_html__( '%1$s - There is a newer version of WordPress available (%2$s)', 'tutor' ), esc_html( $environment['wp_version'] ), esc_html( $latest_version ) )
			: esc_html( $environment['wp_version'] );

		$data['tutor_version'] = esc_html( $environment['version'] );

		$data['wordpress_multisite'] = $environment['wp_multisite'] ? '✓' : '-';

		$data['wordpress_debug_mode'] = $environment['wp_debug_mode'] ? '✓' : '-';

		$data['language'] = esc_html( $environment['language'] );

		$data['wordpress_memory_limit'] = ( $environment['wp_memory_limit'] < 67108864 )
			? sprintf( esc_html__( '%1$s - We recommend setting memory to at least 64MB. See: %2$s', 'tutor' ), esc_html( size_format( $environment['wp_memory_limit'] ) ), '<a href="https://codex.wordpress.org/Editing_wp-config.php#Increasing_memory_allocated_to_PHP" target="_blank">' . esc_html__( 'Increasing memory allocated to PHP', 'tutor' ) . '</a>' )
			: esc_html( size_format( $environment['wp_memory_limit'] ) );

		$data['wordpress_cron'] = $environment['wp_cron'] ? '✓' : '-';

		$data['external_object_cache'] = $environment['external_object_cache'] ? '✓' : '-';

		$data['server_info'] = $environment['server_info'] ?? null;

		$data['php_version'] = ( version_compare( $environment['php_version'], '7.2', '>=' ) )
			? esc_html( $environment['php_version'] )
			: ( ( version_compare( $environment['php_version'], '5.6', '<' ) )
				? __( 'Tutor will run under this version of PHP, however, it has reached end of life. We recommend using PHP version 7.2 or above for greater performance and security.', 'tutor' )
				: __( 'We recommend using PHP version 7.2 or above for greater performance and security.', 'tutor' ) );

		$data['php_post_max_size'] = esc_html( size_format( $environment['php_post_max_size'] ) ) ?? null;

		$data['php_time_limit'] = esc_html( $environment['php_max_execution_time'] ) ?? null;

		$data['php_max_input_vars'] = esc_html( $environment['php_max_input_vars'] ) ?? null;

		$data['curl_version'] = esc_html( $environment['curl_version'] ) ?? null;

		$data['suhosin_installed'] = $environment['suhosin_installed'] ? '✓' : '-';

		$data['max_upload_size'] = esc_html( size_format( $environment['max_upload_size'] ) ) ?? null;

		$data['mysql_version'] = ( version_compare( $environment['mysql_version'], '5.6', '<' ) && ! strstr( $environment['mysql_version_string'], 'MariaDB' ) )
			? sprintf( esc_html__( '%1$s - We recommend a minimum MySQL version of 5.6. See: %2$s', 'tutor' ), esc_html( $environment['mysql_version_string'] ), '<a href="https://wordpress.org/about/requirements/" target="_blank">' . esc_html__( 'WordPress requirements', 'tutor' ) . '</a>' )
			: esc_html( $environment['mysql_version_string'] );

		$data['default_timezone_is_utc'] = ( 'UTC' !== $environment['default_timezone'] )
			? sprintf( esc_html__( 'Default timezone is %s - it should be UTC', 'tutor' ), esc_html( $environment['default_timezone'] ) )
			: '-';

		$data['fsockopen_curl'] = $environment['fsockopen_or_curl_enabled']
			? '✓'
			: esc_html__( 'Your server does not have fsockopen or cURL enabled - PayPal IPN and other scripts which communicate with other servers will not work. Contact your hosting provider.', 'tutor' );

		$data['dom_document'] = $environment['domdocument_enabled']
			? '✓'
			: sprintf( esc_html__( 'Your server does not have the %s class enabled - HTML/Multipart emails, and also some extensions, will not work without DOMDocument.', 'tutor' ), '<a href="https://php.net/manual/en/class.domdocument.php">DOMDocument</a>' );

		$data['gzip'] = ( $environment['gzip_enabled'] )
			? '✓'
			: sprintf( esc_html__( 'Your server does not support the %s function - this is required to use the GeoIP database from MaxMind.', 'tutor' ), '<a href="https://php.net/manual/en/zlib.installation.php">gzopen</a>' );

		$data['multibyte_string'] = ( $environment['mbstring_enabled'] )
			? '✓'
			: sprintf( esc_html__( 'Your server does not support the %s functions - this is required for better character encoding. Some fallbacks will be used instead for it.', 'tutor' ), '<a href="https://php.net/manual/en/mbstring.installation.php">mbstring</a>' );

		if ( ! null == $type ) {
			return $data[ $type ];
		}

		return $data[ null ];
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

	public function generate() {
		ob_start();
		include tutor()->path . 'views/options/options_generator.php';

		return ob_get_clean();
	}

	/**
	 * tools
	 *
	 * @return void
	 */
	public function tools() {
		ob_start();
		include tutor()->path . 'views/options/options_tools.php';

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

	/**
	 * Definition of get_all_fields
	 *
	 * @return array
	 */
	public function get_all_fields() {
		foreach ( $this->options_attr() as $sections ) :
			foreach ( $sections as $section ) :
				if ( is_array( $section ) ) :
					foreach ( $section as $blocks ) :
						foreach ( $blocks['blocks'] as $blocks ) :
							foreach ( $blocks['fields'] as $field ) :
								$field['block'] = $blocks['label'];
								$fields[]       = $field;
							endforeach;
						endforeach;
					endforeach;
				endif;
			  endforeach;
		endforeach;
		return $fields;
	}


	/**
	 * Definition of get_field_by_key
	 *
	 * @param  mixed $key
	 * @return array
	 */
	public function get_field_by_key( $key = '' ) {
		$fields = $this->get_all_fields();

		foreach ( $fields as $field ) :
			if ( isset( $field['key'] ) && $field['key'] === $key ) :
				return $field;
			endif;
		endforeach;
	}


	/**
	 * Definition of get_field_by_type
	 *
	 * @param  mixed $type
	 * @return array
	 */
	public function get_field_by_type( $type = '' ) {
		$fields = $this->get_all_fields();

		foreach ( $fields as $field ) :
			if ( isset( $field['type'] ) && $field['type'] === $type ) :
				$all_fields[] = $field;
			endif;
		endforeach;
		return $all_fields;
	}
}
