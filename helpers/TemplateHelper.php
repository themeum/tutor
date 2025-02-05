<?php
/**
 * TemplateHelper methods
 *
 * @package Tutor\Helpers
 * @author Tutor <support@themeum.com>
 * @link https://tutor.com
 * @since 3.3.3
 */

namespace Tutor\Helpers;

use Tutor\Traits\JsonResponse;

/**
 * TemplateHelper methods
 */
class TemplateHelper {

	use JsonResponse;

	/**
	 * Get Template list.
	 *
	 * @throws \Exception If there is an error fetching or decoding the templates.
	 */
	public static function get_template_list() {
		try {
			$response             = wp_remote_get(
				TEMPLATE_LIST_ENDPOINT,
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
			error_log( $e->getMessage() );
			return array();
		}
	}

	/**
	 * Get Template download url
	 *
	 * @param string $template_id The ID of the template to download.
	 */
	public static function get_template_download_url( $template_id ) {
		$tutor_license_info = get_option( 'tutor_license_info' );
		$website_url        = get_site_url();
		$args               = array(
			'body'    => json_encode(
				array(
					'slug'        => $template_id,
					'website_url' => $website_url,
					'license_key' => $tutor_license_info['license_key'] ?? '',
				)
			),
			'headers' => array(
				'Content-Type' => 'application/json',
				'Secret-Key'   => 't344d5d71sae7dcb546b8cf55e594808',
			),
		);
		$response           = wp_remote_post( TEMPLATES_DOWNLOAD_ENDPOINT, $args );
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
