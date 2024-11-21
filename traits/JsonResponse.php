<?php
/**
 * Trait for re-useable JSON response helper.
 *
 * @package Tutor\Traits
 * @since 2.5.0
 */

namespace Tutor\Traits;

/**
 * JsonResponse trait
 *
 * @since 2.5.0
 */
trait JsonResponse {

	/**
	 * JSON response
	 *
	 * @since 3.0.0
	 *
	 * @param string  $message message.
	 * @param mixed   $data data.
	 * @param integer $status_code status code.
	 *
	 * @return void   JSON data response.
	 */
	public function json_response( string $message = '', $data = null, int $status_code = 200 ) {
		wp_send_json(
			array(
				'status_code' => $status_code,
				'message'     => $message,
				'data'        => $data,
			),
			$status_code
		);
	}

	/**
	 * Response JSON success message.
	 *
	 * @param string $message success message.
	 * @param int    $status_code status code.
	 *
	 * @return void
	 */
	public function response_success( $message, $status_code = 200 ) {
		wp_send_json(
			array(
				'success' => true,
				'message' => $message,
			),
			$status_code
		);
	}

	/**
	 * Response JSON fail message.
	 *
	 * @param string $message fail message.
	 * @param int    $status_code status code.
	 *
	 * @return void
	 */
	public function response_fail( $message, $status_code = 200 ) {
		wp_send_json(
			array(
				'success' => false,
				'message' => $message,
			),
			$status_code
		);
	}

	/**
	 * Response JSON data.
	 *
	 * @param array $data data.
	 * @param int   $status_code status code.
	 *
	 * @return void
	 */
	public function response_data( $data, $status_code = 200 ) {
		wp_send_json(
			array(
				'success' => true,
				'data'    => $data,
			),
			$status_code
		);
	}
}
