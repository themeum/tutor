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

class Tools_V2 {

	public $option;
	public $status;
	private $setting_fields;
	private $tools_fields;

	public function __construct() {
		$this->option       = (array) maybe_unserialize( get_option( 'tutor_option' ) );
		$this->status        = $this->status();
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
	public function tutor_option_default_save() {
		tutils()->checking_nonce();

		! current_user_can( 'manage_options' ) ? wp_send_json_error() : 0;

		$default_options = tutils()->sanitize_recursively( $this->tutor_default_settings() );

		update_option( 'tutor_option', $default_options );

		wp_send_json_success( $default_options );
	}

	public function load_tools_page() {
		$tools_fields = $this->get_tools_fields();
		include tutor()->path . '/views/options/tools.php';
	}

	/**
	 * Function options_tools
	 *
	 * @return void
	 */
	private function get_tools_fields() {

        if($this->tools_fields) {
            // Return fields if already prepared
            return;
        }

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

        $this->tools_fields = $attr_tools;

		return $this->tools_fields;
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

	public function generate_settings() {
		ob_start();
		include tutor()->path . 'views/options/options_generator.php';

		return ob_get_clean();
	}

	/**
	 * tools
	 *
	 * @return void
	 */
	public function generate_tools() {
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
	 * Definition of tutor_load_email_template
	 *
	 * @return array
	 */
	public function tutor_load_email_template( $template ) {
		// ob_start();
		include tutor_pro()->path . "templates/email/{$template}.php";
		// return ob_get_clean();
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
