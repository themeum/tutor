<?php
namespace Ollyo\PaymentHub\Exceptions;

use RuntimeException;
use Throwable;
use Ollyo\PaymentHub\Contracts\Support\ResponseContract;

/**
 * Thrown when an HTTP request fails at the transport level or returns a 4xx/5xx status.
 */
class HttpRequestException extends RuntimeException {

	/**
	 * The response, or null when the request never received one.
	 *
	 * @var ResponseContract|null
	 */
	protected $response;

	/**
	 * Create a new exception instance.
	 *
	 * @param string                $message  The exception message.
	 * @param int                   $code     The HTTP status code, or 0 for transport errors.
	 * @param ResponseContract|null $response The response, if one was received.
	 * @param Throwable|null        $previous The previous exception.
	 */
	public function __construct( string $message = '', int $code = 0, ?ResponseContract $response = null, ?Throwable $previous = null ) {
		parent::__construct( $message, $code, $previous );

		$this->response = $response;
	}

	/**
	 * Get the response, or null for transport errors.
	 *
	 * @return ResponseContract|null
	 */
	public function get_response(): ?ResponseContract {
		return $this->response;
	}

	/**
	 * Determine if a response was received.
	 *
	 * @return bool
	 */
	public function has_response(): bool {
		return ! is_null( $this->response );
	}
}
