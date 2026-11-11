<?php
/**
 * Ensure REST response
 *
 * @package Tutor\RestAPI
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.7.1
 */

namespace TUTOR;

use Tutor\Helpers\HttpHelper;
use WP_Error;
use WP_REST_Response;

defined( 'ABSPATH' ) || exit;

/**
 * Centralized REST response helpers (aligned with Tutor Pro RestResponse).
 *
 * @since 1.7.1
 */
trait REST_Response {

	/**
	 * Build a Tutor REST API response (same envelope rules as Tutor Pro).
	 *
	 * @since 4.2.0
	 *
	 * @param string $code        Operation / error code.
	 * @param string $message     Response message.
	 * @param mixed  $data        Response data or error details.
	 * @param int    $status_code HTTP status code.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function response( $code, string $message, $data = '', int $status_code = 200 ) {
		if ( $status_code >= HttpHelper::STATUS_BAD_REQUEST ) {
			$error_data = array( 'status' => $status_code );
			if ( '' !== $data && null !== $data ) {
				$error_data['details'] = $data;
			}

			$response = new WP_Error( $code, $message, $error_data );
		} else {
			$response = new WP_REST_Response(
				array(
					'code'    => $code,
					'message' => $message,
					'data'    => $data,
				),
				$status_code
			);
		}

		/**
		 * Filter Tutor REST API responses (including WP_Error).
		 *
		 * @since 2.7.0
		 *
		 * @param WP_REST_Response|WP_Error $response Response object.
		 */
		return rest_ensure_response( apply_filters( 'tutor_rest_api_response', $response ) );
	}

	/**
	 * Send validation error response.
	 *
	 * @since 4.2.0
	 *
	 * @param array       $errors  Validation errors.
	 * @param string      $code    Operation code.
	 * @param string|null $message Optional message.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function validation_error_response( $errors, $code, $message = null ) {
		$default_message = _n( 'Invalid input', 'Invalid inputs', count( $errors ), 'tutor' );

		return $this->response(
			$code,
			null !== $message ? $message : $default_message,
			$errors,
			HttpHelper::STATUS_BAD_REQUEST
		);
	}

	/**
	 * Log an unexpected failure and return a safe API response.
	 *
	 * @since 4.2.0
	 *
	 * @param string                      $code    Operation code.
	 * @param string                      $message Generic translated message for the client.
	 * @param \Throwable|\WP_Error|string $error   Error to log server-side only.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function exception_error_response( $code, $message, $error ) {
		self::log_api_error( $error );

		return $this->response(
			$code,
			$message,
			'',
			HttpHelper::STATUS_INTERNAL_SERVER_ERROR
		);
	}

	/**
	 * Log API error details server-side without exposing them to clients.
	 *
	 * @since 4.2.0
	 *
	 * @param \Throwable|\WP_Error|string $error Error to log.
	 *
	 * @return void
	 */
	protected static function log_api_error( $error ) {
		if ( $error instanceof \Throwable ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional server-side logging of API failures.
			error_log(
				sprintf(
					'Tutor REST API error: %s in %s at line %d',
					$error->getMessage(),
					$error->getFile(),
					$error->getLine()
				)
			);
			return;
		}

		if ( is_wp_error( $error ) ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional server-side logging of API failures.
			error_log( 'Tutor REST API error: ' . $error->get_error_message() );
			return;
		}

		if ( is_string( $error ) && '' !== $error ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional server-side logging of API failures.
			error_log( 'Tutor REST API error: ' . $error );
		}
	}
}
