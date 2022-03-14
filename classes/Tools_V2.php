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

	private $environment_status;
	private $tools_fields;

	public function tutor_apply_settings() {
		$tutor_settings_log = get_option( 'tutor_settings_log' );
		$apply_id           = $this->get_request_data( 'apply_id' );

		update_option( 'tutor_option', $tutor_settings_log[ $apply_id ]['dataset'] );

		wp_send_json_success( $tutor_settings_log[ $apply_id ] );
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
		$attr = $this->get_setting_fields();
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

	public function load_tools_page() {
		$tools_fields = $this->get_tools_fields();
		$tutor_setup  = array( 'tutor-setup' => $tools_fields['tutor-setup'] );
		unset( $tools_fields['tutor-setup'] );
		$tools_fields = array_merge( $tools_fields, $tutor_setup );

		$active_tab = tutor_utils()->array_get( 'sub_page', $_GET, 'status' );
		include tutor()->path . '/views/options/tools.php';
	}

	/**
	 * Function options_tools
	 *
	 * @return void
	 */
	private function get_tools_fields() {
		global $wpdb;

		if ( $this->tools_fields ) {
			// Return fields if already prepared
			return $this->tools_fields;
		}

		$attr_tools = array(
			'status'        => array(
				'label'     => __( 'Status', 'tutor' ),
				'slug'      => 'status',
				'desc'      => __( 'Status Settings', 'tutor' ),
				'template'  => 'status',
				'view_path' => tutor()->path . 'views/options/template/',
				'icon'      => 'tutor-icon-chart-filled',
				'blocks'    => array(
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
									'key'     => 'tutor_version',
									'type'    => 'info_col',
									'label'   => __( 'MySQL version', 'tutor' ),
									'status'  => 'default',
									'default' => sprintf( 'Server: %s, Client: %s', $wpdb->dbh->server_info, $wpdb->dbh->client_info ),
								),
								array(
									'key'     => 'wordpress_memory_limit',
									'type'    => 'info_col',
									'label'   => __( 'Default timezone is UTC', 'tutor' ),
									'status'  => 'default',
									'default' => $this->status( 'default_timezone_is_utc' ),
								),
								array(
									'key'     => 'wordpress_cron',
									'type'    => 'info_col',
									'label'   => __( 'fsockopen/cURL', 'tutor' ),
									'status'  => 'default',
									'default' => $this->status( 'fsockopen_curl' ),
								),
								array(
									'key'     => 'external_object_cache',
									'type'    => 'info_col',
									'label'   => __( 'DOMDocument', 'tutor' ),
									'status'  => 'default',
									'default' => $this->status( 'dom_document' ),
								),
								array(
									'key'     => 'external_object_cache',
									'type'    => 'info_col',
									'label'   => __( 'GZip', 'tutor' ),
									'status'  => 'default',
									'default' => $this->status( 'gzip' ),
								),
								array(
									'key'     => 'external_object_cache',
									'type'    => 'info_col',
									'label'   => __( 'Multibyte string', 'tutor' ),
									'status'  => 'default',
									'default' => $this->status( 'multibyte_string' ),
								),
							),
						),
					),
				),
			),
			'import_export' => array(
				'label'     => __( 'Import/Export', 'tutor' ),
				'slug'      => 'import_export',
				'desc'      => __( 'Import/Export Settings', 'tutor' ),
				'template'  => 'import_export',
				'view_path' => tutor()->path . 'views/options/template/',
				'icon'      => 'tutor-icon-import-export-filled',
				'blocks'    => array(),
			),
			'tutor_pages'   => array(
				'label'     => __( 'Tutor Pages', 'tutor' ),
				'slug'      => 'tutor_pages',

				'desc'      => __( 'Tutor Pages Settings', 'tutor' ),
				'template'  => 'tutor_pages',
				'view_path' => tutor()->path . 'views/options/template/',
				'icon'      => 'tutor-icon-review-line',
				'blocks'    => array(
					'block' => array(),
				),
			),
			'tutor-setup'   => array(
				'label'  => __( 'Setup Wizard', 'tutor' ),
				'slug'   => 'tutor-setup',
				'desc'   => __( 'Setup Wizard Settings', 'tutor' ),
				'icon'   => 'tutor-icon-earth-filled',
				'blocks' => array(
					'block' => array(),
				),
			),
		);

		$attr_tools = apply_filters( 'tutor/tools/extend/attr', apply_filters( 'tutor/tools/attr', apply_filters( 'tutor_tool_pages', $attr_tools ) ) );

		$this->tools_fields = $attr_tools;

		return $this->tools_fields;
	}

	private function get_environment_info() {

		if ( $this->environment_status ) {
			// Use runtime cache for repetitve call
			return $this->environment_status;
		}

		// Figure out cURL version, if installed.
		$curl_version = '';
		if ( function_exists( 'curl_version' ) ) {
			$curl_version = curl_version();
			$curl_version = $curl_version['version'] . ', ' . $curl_version['ssl_version'];
		}

		// WP memory limit.
		$wp_memory_limit = tutor_utils()->let_to_num( WP_MEMORY_LIMIT );
		if ( function_exists( 'memory_get_usage' ) ) {
			$wp_memory_limit = max( $wp_memory_limit, tutor_utils()->let_to_num( @ini_get( 'memory_limit' ) ) );
		}

		$database_version = tutor_utils()->get_db_version();

		$this->environment_status = array(
			'home_url'                  => get_option( 'home' ),
			'site_url'                  => get_option( 'siteurl' ),
			'version'                   => TUTOR_VERSION,
			'wp_version'                => get_bloginfo( 'version' ),
			'wp_multisite'              => is_multisite(),
			'wp_memory_limit'           => $wp_memory_limit,
			'wp_debug_mode'             => ( defined( 'WP_DEBUG' ) && WP_DEBUG ),
			'wp_cron'                   => ! ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ),
			'language'                  => get_locale(),
			'external_object_cache'     => wp_using_ext_object_cache(),
			'server_info'               => isset( $_SERVER['SERVER_SOFTWARE'] ) ? wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) : '',
			'php_version'               => phpversion(),
			'php_post_max_size'         => tutor_utils()->let_to_num( ini_get( 'post_max_size' ) ),
			'php_max_execution_time'    => ini_get( 'max_execution_time' ),
			'php_max_input_vars'        => ini_get( 'max_input_vars' ),
			'curl_version'              => $curl_version,
			'suhosin_installed'         => extension_loaded( 'suhosin' ),
			'max_upload_size'           => wp_max_upload_size(),
			'mysql_version'             => $database_version['number'],
			'mysql_version_string'      => $database_version['string'],
			'default_timezone'          => date_default_timezone_get(),
			'fsockopen_or_curl_enabled' => ( function_exists( 'fsockopen' ) || function_exists( 'curl_init' ) ),
			'soapclient_enabled'        => class_exists( 'SoapClient' ),
			'domdocument_enabled'       => class_exists( 'DOMDocument' ),
			'gzip_enabled'              => is_callable( 'gzopen' ),
			'mbstring_enabled'          => extension_loaded( 'mbstring' ),
		);

		return $this->environment_status;
	}

	public function status( $type = '' ) {

		$data         = array();
		$data[ null ] = 'null';

		$environment = $this->get_environment_info();

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
			: '✓';

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

	public function blocks( $blocks = array() ) {
		ob_start();
		include tutor()->path . 'views/options/option_blocks.php';
		return ob_get_clean();
	}

	public function template( $section = array() ) {
		ob_start();
		include $section['view_path'] . $section['template'] . '.php';
		return ob_get_clean();
	}
	/**
	 * Load template inside template dirctory
	 *
	 * @param  mixed $template_slug
	 * @param  mixed $section
	 * @return void
	 */
	public function view_template( $template_slug, $section = array() ) {
		ob_start();
		require tutor()->path . "views/options/template/{$template_slug}";
		return ob_get_clean();
	}
}
