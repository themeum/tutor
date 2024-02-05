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
