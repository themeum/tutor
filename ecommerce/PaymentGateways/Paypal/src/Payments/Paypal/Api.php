<?php

namespace Ollyo\PaymentHub\Payments\Paypal;

use ErrorException;
use Ollyo\PaymentHub\Core\Support\System;
use GuzzleHttp\Exception\RequestException;
use Ollyo\PaymentHub\Contracts\Config\RepositoryContract;

final class Api {

	/**
	 * The Paypal config Repository instance
	 *
	 * @var   RepositoryContract
	 * @since 1.0.0
	 */
	protected static $config;

	/**
	 * Stores the headers to be used in HTTP requests.
	 *
	 * @var     array $headers
	 * @since   1.0.0
	 */
	protected static $headers;

	/**
	 * @var string|null $accessToken
	 * This property holds the access token required for authenticating API requests.
	 * @since 1.0.0
	 */
	protected $accessToken;

	public function __construct( RepositoryContract $config ) {
		self::$config  = $config;
		self::$headers = array(
			'Content-Type'  => 'application/json',
			'Authorization' => $this->getAccessToken(),
		);
	}

	/**
	 * Retrieves an access token from the OAuth2 token endpoint.
	 *
	 * This method constructs and sends a POST request to the OAuth2 token endpoint using
	 * client credentials to obtain an access token. The access token is then returned in the format
	 * "token_type access_token".
	 *
	 * @return string The access token in the format "token_type access_token".
	 * @since  1.0.0
	 */
	private function getAccessToken(): string {
		if ( empty( $this->accessToken ) ) {

			$requestData = (object) array(
				'method'  => 'post',
				'url'     => self::$config->get( 'api_url' ) . '/v1/oauth2/token',
				'options' => array(
					'auth'        => array( self::$config->get( 'client_id' ), self::$config->get( 'client_secret' ) ),
					'headers'     => array( 'Content-Type' => 'application/x-www-form-urlencoded' ),
					'form_params' => array( 'grant_type' => 'client_credentials' ),
				),
			);

			$response = System::sendHttpRequest( $requestData );

			if ( $response->token_type && $response->access_token ) {
				$this->accessToken = "{$response->token_type}  {$response->access_token}";
			}
		}

		return $this->accessToken;
	}

	/**
	 * Creates a new PayPal order by sending a POST request to the PayPal API.
	 *
	 * @since  1.0.0
	 *
	 * @param object $data The order data to be sent in the request body.
	 * @param string $order_id A unique identifier for the order.
	 *
	 * @return object The response from the PayPal API, decoded from JSON.
	 */
	public static function createOrder( $data, $order_id ): object {

		self::$headers['PayPal-Request-Id'] = "order-id-{$order_id}";

		$requestData = (object) array(
			'method'  => 'post',
			'url'     => self::$config->get( 'api_url' ) . '/v2/checkout/orders',
			'options' => array(
				'headers' => self::$headers,
				'body'    => wp_json_encode( $data ),
			),
		);

		return System::sendHttpRequest( $requestData );
	}

		/**
		 * Captures a payment for an authorized PayPal order.
		 *
		 * This method constructs the capture payment URL and headers, including the access token,
		 * PayPal request ID. It then sends a POST request to the capture payment URL using the specified headers.
		 *
		 * @param  object $payloadStream  The payload stream sent from Paypal Webhook Notification.
		 * @return object                           The response object from the capture payment request.
		 * @throws ErrorException                   If the payment capture is incomplete or the order status is not completed.
		 * @since  1.0.0
		 */
	public static function capturePayment( $payloadStream ) {

		$capturePaymentUrl = Helper::getUrl( $payloadStream->resource->links, 'capture' );

		self::$headers['PayPal-Request-Id'] = "paypal-order-id-{$payloadStream->id}";
		self::$headers['Prefer']            = 'return=representation';

		$requestData = (object) array(
			'method'  => 'post',
			'url'     => $capturePaymentUrl,
			'options' => array( 'headers' => self::$headers ),
		);

		System::sendHttpRequest( $requestData );
	}

	public static function webhook_signature_validation( $payload ) {

		try {
			$payload_stream = json_decode( $payload->stream );
			$data           = array(
				'auth_algo'         => $payload->server['HTTP_PAYPAL_AUTH_ALGO'],
				'cert_url'          => $payload->server['HTTP_PAYPAL_CERT_URL'],
				'transmission_id'   => $payload->server['HTTP_PAYPAL_TRANSMISSION_ID'],
				'transmission_sig'  => $payload->server['HTTP_PAYPAL_TRANSMISSION_SIG'],
				'transmission_time' => $payload->server['HTTP_PAYPAL_TRANSMISSION_TIME'],
				'webhook_id'        => self::$config->get( 'webhook_id' ),
				'webhook_event'     => $payload_stream,
			);

			$requestData = (object) array(
				'method'  => 'post',
				'url'     => self::$config->get( 'api_url' ) . '/v1/notifications/verify-webhook-signature',
				'options' => array(
					'headers' => self::$headers,
					'body'    => json_encode( $data ),
				),
			);

			$responseData = System::sendHttpRequest( $requestData );

			return $responseData->verification_status === 'SUCCESS' ? true : false;
		} catch ( \Throwable $error ) {
			$errorMessage = Helper::handleErrorResponse( $error ) ?? $error->getMessage();
			throw new ErrorException( esc_html( $errorMessage ) );
		}
	}

	/**
	 * Send an immediate HTTP 200 OK response to acknowledge receipt of the event.
	 *
	 * @since 1.0.1
	 *
	 * @return void
	 */
	public static function respond_to_event(): void {

		http_response_code( 200 );
		echo 'OK';

		if ( function_exists( 'fastcgi_finish_request' ) ) {
			fastcgi_finish_request();
		}
	}

	public static function get_order_details( $url ) {
		try {

			$requestData = (object) array(
				'method'  => 'get',
				'url'     => $url,
				'options' => array( 'headers' => self::$headers ),
			);

			return System::sendHttpRequest( $requestData );

		} catch ( RequestException $error ) {
			$errorMessage = Helper::handleErrorResponse( $error ) ?? $error->getMessage();
			throw new ErrorException( esc_html( $errorMessage ) );
		}
	}

	public static function refund( $refund_url, $order_id, $data ) {

		try {

			$unique_id                          = uniqid( 'refund-' );
			self::$headers['PayPal-Request-Id'] = "{$unique_id}-order-id-{$order_id}";
			self::$headers['Prefer']            = 'return=representation';

			$request_data = (object) array(
				'method'  => 'post',
				'url'     => $refund_url,
				'options' => array(
					'headers' => self::$headers,
					'body'    => json_encode( $data ),
				),
			);

			System::sendHttpRequest( $request_data );
		} catch ( \Throwable $th ) {
			throw $th;
		}
	}
}
