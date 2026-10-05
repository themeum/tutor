<?php
namespace Ollyo\PaymentHub\Core\Support;

use stdClass;
use Brick\Money\Money;
use Brick\Math\RoundingMode;
use Tutor\Helpers\HttpHelper;
use Ollyo\PaymentHub\Exceptions\NotFoundException;
use Ollyo\PaymentHub\Exceptions\InvalidDataException;
use Ollyo\PaymentHub\Exceptions\HttpRequestException;

class System {

	public static function createClassInstance( $class ) {
		if ( ! class_exists( $class ) ) {
			throw new NotFoundException( esc_html( sprintf( 'The class %s does not found!', $class ) ) );
		}

		return new $class();
	}

	public static function parseUrl( $url, $component = -1 ) {
		if ( extension_loaded( 'mbstring' ) && mb_convert_encoding( $url, 'ISO-8859-1', 'UTF-8' ) === $url ) {
			return wp_parse_url( $url, $component );
		}

		// Build the reserved uri encoded characters map.
		$reservedUriCharactersMap = array(
			'%21' => '!',
			'%2A' => '*',
			'%27' => "'",
			'%28' => '(',
			'%29' => ')',
			'%3B' => ';',
			'%3A' => ':',
			'%40' => '@',
			'%26' => '&',
			'%3D' => '=',
			'%24' => '$',
			'%2C' => ',',
			'%2F' => '/',
			'%3F' => '?',
			'%23' => '#',
			'%5B' => '[',
			'%5D' => ']',
		);

		$parts = wp_parse_url( strtr( rawurlencode( $url ), $reservedUriCharactersMap ), $component );

		return $parts ? array_map( 'urldecode', $parts ) : $parts;
	}

	/**
	 * Create an object for the the default order data.
	 *
	 * @return object
	 */
	public static function defaultOrderData( $type = 'payment' ): object {
		$returnData = new stdClass();

		if ( $type === 'payment' ) {
			$returnData = (object) array(
				'type'                 => 'payment',
				'id'                   => null,
				'payment_status'       => 'unpaid',
				'payment_error_reason' => '',
				'transaction_id'       => '',
				'payment_method'       => '',
				'payment_payload'      => '',
				'tax_amount'           => '',
				'fees'                 => '',
				'earnings'             => '',
			);

		} elseif ( $type === 'refund' ) {
			$returnData = (object) array(
				'type'                => 'refund',
				'id'                  => null,
				'refund_status'       => '',
				'refund_id'           => '',
				'payment_method'      => '',
				'refund_amount'       => '',
				'refund_error_reason' => '',
				'refund_payload'      => '',
			);
		}

		return $returnData;
	}


	/**
	 * Validates and sanitizes an email address.
	 *
	 * This method takes an email address as input, sanitizes it to remove any illegal
	 * characters or sequences, and then validates it against the standard email format.
	 * If the email address fails either sanitization or validation, an InvalidDataException
	 * is thrown with an appropriate error message.
	 *
	 * @param  string $email        The email address to be validated and sanitized.
	 * @return string               The sanitized email address if valid.
	 * @throws InvalidDataException If the email address is invalid according to `FILTER_SANITIZE_EMAIL`
	 *                              or `FILTER_VALIDATE_EMAIL` filters.
	 * @since  1.0.0
	 */
	public static function validateAndSanitizeEmailAddress( string $email ) {
		$sanitizeEmail = filter_var( $email, FILTER_SANITIZE_EMAIL );
		$validateEmail = filter_var( $email, FILTER_VALIDATE_EMAIL );

		if ( ! $sanitizeEmail || ! $validateEmail ) {
			throw new InvalidDataException( 'Invalid Email Address' );
		}

		return $email;
	}

	/**
	 * Extracts the first and last name parts from a full name string.
	 *
	 * @param  string|null $name    The full name string to be processed.
	 * @return array                An array containing the first and last name, or null if the name is empty.
	 * @since  1.0.0
	 */
	public static function extractNameParts( $name ): array {
		if ( empty( $name ) ) {
			return array();
		}

		// Trim leading and trailing spaces, split the full name into an array, and remove empty elements
		$nameParts = array_filter( explode( ' ', trim( $name ) ) );
		// Extract the last name.
		$lastName = array_pop( $nameParts );
		// Extract the first name
		$firstName = implode( ' ', $nameParts );

		return array( $firstName, $lastName );
	}

	/**
	 * Splits the address into two parts if it exceeds a certain length.
	 *
	 * @param  object $data  Address data.
	 * @param  int    $maxLength     The maximum length for the first part of the street address.
	 * @return array                The formatted part of the street address.
	 * @since  1.0.0
	 */
	public static function splitAddress( $data, $maxLength ) {
		if ( empty( $data->address1 ) ) {
			return array();
		}

		$address_1 = mb_strimwidth( $data->address1, 0, $maxLength );
		$address_2 = ( strlen( $data->address1 ) > $maxLength ) ? mb_strimwidth( $data->address1, $maxLength, $maxLength ) : $data->address2;

		return array( $address_1, $address_2 );
	}

	/**
	 * Converts a major currency amount to its minor unit.
	 *
	 * @param  float|string $amount     The major currency amount to convert.
	 * @param  string       $currency   The currency code to use for the conversion.
	 *
	 * @return int|null                 Returns the minor currency amount as an integer, or null if the amount is invalid.
	 * @since  1.0.0
	 * @since  4.1.2 Simplified the null check; behavior is unchanged.
	 */
	public static function getMinorAmountBasedOnCurrency( $amount, $currency ) {
		if ( ! empty( $amount ) ) {
			return Money::of( (float) $amount, $currency, null, RoundingMode::HALF_UP )->getMinorAmount()->toInt();
		}

		return null;
	}

	/**
	 * Converts a minor currency amount to its major unit equivalent.
	 *
	 * @param  int|string $amount   The minor currency amount to convert.
	 * @param  string     $currency The currency code to use for the conversion.
	 *
	 * @return float|null           Returns the major currency amount as a float, or null if the amount is invalid.
	 * @since  1.0.0
	 * @since  4.1.2 Simplified the null check; behavior is unchanged.
	 */
	public static function convertMinorAmountToMajor( $amount, $currency ) {
		if ( null !== $amount ) {
			return Money::ofMinor( $amount, $currency, null, RoundingMode::HALF_UP )->getAmount()->toFloat();
		}

		return null;
	}

	/**
	 * Determines if the total amount is zero or not.
	 *
	 * @param  object $data Contains the order's necessary charges.
	 * @return bool         Returns true if the total amount equals zero, false otherwise.
	 * @since  1.0.0
	 */
	public static function isTotalAmountZero( &$data ): bool {
		$data->subtotal        ??= 0;
		$data->tax             ??= 0;
		$data->shipping_charge ??= 0;
		$data->coupon_discount ??= 0;

		$totalAmount = ( $data->subtotal + $data->tax + $data->shipping_charge ) - $data->coupon_discount;

		return empty( filter_var( $totalAmount, FILTER_VALIDATE_FLOAT ) ) ? true : false;
	}

	/**
	 * Updates and returns a webhook URL by encoding success and cancel URLs as parameters.
	 *
	 * @param   object $config  Configuration object that provides the URLs and payment method.
	 * @return  string          The updated webhook URL with encoded data.
	 * @since   1.0.0
	 */
	public static function updateWebhookUrl( $config ): string {
		$encodedUrlData = base64_encode(
			wp_json_encode(
				array(
					'success_url' => $config->get( 'success_url' ),
					'cancel_url'  => $config->get( 'cancel_url' ),
				)
			)
		);

		$webhookUrl = Uri::getInstance( $config->get( 'webhook_url' ) );
		$webhookUrl->setVar( 'encodedData', $encodedUrlData );
		$webhookUrl->setVar( 'payment_method', $config->get( 'name' ) );

		return $webhookUrl->__toString();
	}

	/**
	 * Sends an HTTP request using the specified method and options.
	 *
	 * @since   1.0.0
	 * @since   4.1.2 Sends the request through HttpHelper instead of Guzzle.
	 *
	 * @param   array|object $request_data Request data with url, optional method, and options.
	 * @param   bool         $return_raw_payload Whether to return the raw response object
	 *                                         instead of the JSON-decoded body. Default false.
	 *
	 * @return  object|array|null The decoded JSON response body.
	 *
	 * @throws  \InvalidArgumentException If the HTTP method is not supported.
	 * @throws  HttpRequestException      On a transport error or a 4xx/5xx response.
	 */
	public static function sendHttpRequest( $request_data, $return_raw_payload = false ) {
		$request_data = (object) $request_data;
		$url          = $request_data->url;
		$options      = (array) ( $request_data->options ?? array() );
		$method       = strtoupper( $options['method'] ?? $request_data->method ?? HttpHelper::METHOD_GET );
		$headers      = $options['headers'] ?? array();
		$body         = $options['body'] ?? array();

		unset( $options['method'] );

		switch ( $method ) {
			case HttpHelper::METHOD_GET:
				$response = HttpHelper::get( $url, $body, $headers );
				break;

			case HttpHelper::METHOD_POST:
				$response = HttpHelper::post( $url, $body, $headers );
				break;

			case HttpHelper::METHOD_PUT:
				$response = HttpHelper::put( $url, $body, $headers );
				break;

			case HttpHelper::METHOD_PATCH:
				$response = HttpHelper::patch( $url, $body, $headers );
				break;

			case HttpHelper::METHOD_DELETE:
				$response = HttpHelper::delete( $url, $body, $headers );
				break;

			default:
				$error_message = sprintf(
					/* translators: %s: HTTP method name */
					__( 'Unsupported HTTP method: %s', 'tutor' ),
					$method
				);
				throw new \InvalidArgumentException( esc_html( $error_message ) );
		}

		if ( $response->has_error() ) {
			throw new HttpRequestException( esc_html( $response->get_error_message() ) );
		}

		$status = (int) $response->get_status_code();

		if ( $status >= 400 ) {
			throw new HttpRequestException( esc_html( sprintf( 'HTTP %d returned from %s', $status, $url ) ), $status, $response ); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
		}

		if ( $return_raw_payload ) {
			return $response;
		}

		return json_decode( (string) $response->get_body() );
	}

	/**
	 * Builds the Tutor metadata attached to every payment gateway.
	 *
	 * @since 4.1.2
	 *
	 * @param   string $env  The payment mode.
	 * @param   int    $order_user_id The Tutor order ID.
	 * @return  array  The metadata as string key/value pairs.
	 */
	public static function get_tutor_metadata( $env, $order_user_id ): array {
		$user = $order_user_id ? get_userdata( $order_user_id ) : false;

		return array(
			'tutor_version' => defined( 'TUTOR_VERSION' ) ? TUTOR_VERSION : '',
			'wp_user'       => $user ? "{$user->ID} | {$user->user_email}" : '',
			'site_url'      => get_site_url(),
			'env'           => $env,
		);
	}
}
