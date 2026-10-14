<?php
/**
 * Exception for failed payment gateway HTTP requests.
 *
 * @package Tutor\Ecommerce
 * @author Themeum
 * @link https://themeum.com
 * @since 4.2.0
 */

namespace Tutor\PaymentGateways\Exceptions;

defined( 'ABSPATH' ) || exit;

use RuntimeException;
use Throwable;
use Tutor\Helpers\HttpHelper;

/**
 * Thrown when an HTTP request fails at the transport level or returns a 4xx/5xx status.
 *
 * @since 4.2.0
 */
class HttpRequestException extends RuntimeException {

	/**
	 * The response, or null when the request never received one.
	 *
	 * @since 4.2.0
	 *
	 * @var HttpHelper|null
	 */
	protected $response;

	/**
	 * Create a new exception instance.
	 *
	 * @since 4.2.0
	 *
	 * @param string          $message  The exception message.
	 * @param int             $code     The HTTP status code, or 0 for transport errors.
	 * @param HttpHelper|null $response The response, if one was received.
	 * @param Throwable|null  $previous The previous exception.
	 */
	public function __construct( string $message = '', int $code = 0, ?HttpHelper $response = null, ?Throwable $previous = null ) {
		parent::__construct( $message, $code, $previous );

		$this->response = $response;
	}

	/**
	 * Get the response, or null for transport errors.
	 *
	 * @since 4.2.0
	 *
	 * @return HttpHelper|null
	 */
	public function get_response(): ?HttpHelper {
		return $this->response;
	}

	/**
	 * Determine if a response was received.
	 *
	 * @since 4.2.0
	 *
	 * @return bool
	 */
	public function has_response(): bool {
		return ! is_null( $this->response );
	}
}
