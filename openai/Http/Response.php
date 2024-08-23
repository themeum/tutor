<?php
/**
 * Helper class for handling magic ai functionalities
 *
 * @package Tutor\MagicAI
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 3.0.0
 */

namespace Tutor\OpenAI\Http;

/**
 * Handle the response from the request.
 *
 * @since 3.0.0
 */
final class Response {
	/**
	 * The request instance.
	 *
	 * @var Request
	 */
	private Request $request;

	/**
	 * The constructor method for making the response from the request.
	 *
	 * @param Request $request The request instance.
	 * @since 3.0.0
	 */
	private function __construct( Request $request ) {
		$this->request = $request;
	}

	/**
	 * Create the response instance.
	 *
	 * @param Request $request The request instance.
	 * @return self
	 */
	public static function create( Request $request ) {
		return new self( $request );
	}

	/**
	 * Parse tht request and make the response.
	 *
	 * @param string $type The response type. Available values json|raw.
	 * @return array
	 */
	public function parse( string $type ) {
		$response = array(
			'status_code' => $this->request->get_status_code(),
		);

		$has_error = $this->request->has_error();

		if ( $has_error ) {
			$response['message'] = $this->request->get_error_message();

			return $response;
		}

		switch ( $type ) {
			case 'json':
				$response['data'] = $this->request->get_json();
				break;
			case 'raw':
				$response['data'] = $this->request->get_body();
				break;
		}

		return $response;
	}

	/**
	 * Get the JSON response.
	 *
	 * @return array
	 * @since 3.0.0
	 */
	public function request_json() {
		return $this->parse( 'json' );
	}

	/**
	 * The raw response body
	 *
	 * @return array
	 * @since 3.0.0
	 */
	public function request_raw() {
		return $this->parse( 'raw' );
	}
}
