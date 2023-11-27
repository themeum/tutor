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
	 *
	 * @return void
	 */
	public function response_success( $message ) {
		wp_send_json(
			array(
				'success' => true,
				'message' => $message,
			)
		);
	}

	/**
	 * Response JSON fail message.
	 *
	 * @param string $message fail message.
	 *
	 * @return void
	 */
	public function response_fail( $message ) {
		wp_send_json(
			array(
				'success' => false,
				'message' => $message,
			)
		);
	}

	/**
	 * Response JSON data.
	 *
	 * @param array $data data.
	 *
	 * @return void
	 */
	public function response_data( $data ) {
		wp_send_json(
			array(
				'success' => true,
				'data'    => $data,
			)
		);
	}
}
