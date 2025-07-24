<?php
/**
 * TemplateImportHelper methods
 *
 * @package Tutor\Helpers
 * @author Tutor <support@themeum.com>
 * @link https://tutor.com
 * @since 3.6.0
 */

namespace Tutor\Helpers;

use Tutor\Traits\JsonResponse;

/**
 * TemplateImportHelper methods
 */
class TemplateImportHelper {

	use JsonResponse;

	/**
	 * Template list endpoint.
	 *
	 * @since 3.6.0
	 *
	 * @var string
	 */
	public $template_list_endpoint;

	/**
	 * Template download endpoint.
	 *
	 * @since 3.6.0
	 *
	 * @var string
	 */
	public $template_download_endpoint;

	/**
	 * Constructor.
	 *
	 * @since 3.6.0
	 *
	 * @return  void
	 */
	public function __construct() {
		$this->template_list_endpoint     = self::make_url( 'theme-templates' );
		$this->template_download_endpoint = self::make_url( 'theme-template-download' );
	}

	/**
	 * Get base url.
	 *
	 * @since 3.6.0
	 *
	 * @return string The base URL for the template import API.
	 */
	private static function get_base_url() {
		$url = 'https://tutorlms.com/wp-json/themeum-products/v1/tutor';
		if ( defined( 'TEMPLATE_IMPORT_BASE_URL' ) && TEMPLATE_IMPORT_BASE_URL ) {
			$url = TEMPLATE_IMPORT_BASE_URL;
		}

		return $url;
	}

	/**
	 * Make url
	 *
	 * @since 3.6.0
	 *
	 * @param  string $url_path  url path.
	 *
	 * @return  string full url.
	 */
	public static function make_url( $url_path ) {
		return self::get_base_url() . '/' . ltrim( $url_path, '/' );
	}

	/**
	 * Get Template list.
	 *
	 * @since 3.6.0
	 *
	 * @throws \Exception If there is an error fetching or decoding the templates.
	 */
	public function get_template_list() {
		try {
			$response             = wp_remote_get(
				$this->template_list_endpoint,
				array(
					'headers' => array(
						'Secret-Key' => 't344d5d71sae7dcb546b8cf55e594808',
					),
				)
			);
			$response_status_code = wp_remote_retrieve_response_code( $response );
			if ( is_wp_error( $response ) ) {
				throw new \Exception( 'Failed to fetch templates: ' . $response->get_error_message() );
			}
			$template_list = json_decode( wp_remote_retrieve_body( $response ), true );
			if ( json_last_error() !== JSON_ERROR_NONE ) {
				throw new \Exception( 'Failed to decode JSON response: ' . json_last_error_msg() );
			}
			if ( 200 !== $response_status_code ) {
				throw new \Exception( 'Failed to fetch templates: ' . $template_list['response'] );
			}
			return $template_list['body_response'];
		} catch ( \Exception $e ) {
			return array();
		}
	}

	/**
	 * Get Template download url
	 *
	 * @since 3.6.0
	 *
	 * @param string $template_id The ID of the template to download.
	 *
	 *
	 * return string The download URL for the specified template.
	 */
	public function get_template_download_url( $template_id ) {
		$tutor_license_info = get_option( 'tutor_license_info' );
		$website_url        = get_site_url();
		$args               = array(
			'body'    => array(
				'slug'        => $template_id,
				'website_url' => $website_url,
				'license_key' => $tutor_license_info['license_key'] ?? '',
			),
			'headers' => array(
				'Secret-Key' => 't344d5d71sae7dcb546b8cf55e594808',
			),
		);
		$response           = wp_remote_post( $this->template_download_endpoint, $args );
		$response_body      = wp_remote_retrieve_body( $response );
		$data               = json_decode( $response_body, true );
		if ( is_wp_error( $response ) ) {
			self::json_response( $data['response'], null, 400 );
		}
		if ( empty( $data['body_response'] ) ) {
			self::json_response( $data['response'], null, 400 );
		}
		$template_download_url = $data['body_response'];
		return $template_download_url;
	}
}
