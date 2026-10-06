<?php

namespace Ollyo\PaymentHub\Payments\Paypal;

use ErrorException;
use Ollyo\PaymentHub\Contracts\Config\RepositoryContract;
use Ollyo\PaymentHub\Exceptions\HttpRequestException;
use Tutor\Helpers\HttpHelper;
use Tutor\PaymentGateways\Http;

/**
 * Paypal Api Class
 */
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

	/**
	 * Class constructor.
	 *
	 * @since 3.9.0
	 *
	 * @param RepositoryContract $config Configuration repository instance.
	 */
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
	 * @since  3.0.0
	 */
	private function getAccessToken(): string {
		if ( empty( $this->accessToken ) ) {

			$requestData = array(
				'url'     => self::$config->get( 'api_url' ) . '/v1/oauth2/token',
				'options' => array(
					'headers' => array(
						'Authorization' => 'Basic ' . base64_encode( self::$config->get( 'client_id' ) . ':' . self::$config->get( 'client_secret' ) ), // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
						'Content-Type'  => 'application/x-www-form-urlencoded',
					),
					'body'    => array( 'grant_type' => 'client_credentials' ),
					'method'  => HttpHelper::METHOD_POST,
				),
			);

			$response = Http::send( $requestData );

			if ( $response->token_type && $response->access_token ) {
				$this->accessToken = "{$response->token_type}  {$response->access_token}";
			}
		}

		return $this->accessToken;
	}

	/**
	 * Creates a new PayPal order by sending a POST request to the PayPal API.
	 *
	 * @since  3.0.0
	 *
	 * @param object $data The order data to be sent in the request body.
	 * @param string $order_id A unique identifier for the order.
	 *
	 * @return object The response from the PayPal API, decoded from JSON.
	 */
	public static function createOrder( $data, $order_id ): object {

		self::$headers['PayPal-Request-Id'] = "order-id-{$order_id}";

		$request_data = array(
			'url'     => self::$config->get( 'api_url' ) . '/v2/checkout/orders',
			'options' => array(
				'headers' => self::$headers,
				'body'    => wp_json_encode( $data ),
				'method'  => HttpHelper::METHOD_POST,
			),
		);

		return Http::send( $request_data );
	}


	/**
	 * Capture a PayPal payment based on the provided webhook payload.
	 *
	 * @since 3.0.0
	 *
	 * @param object $payloadStream The decoded webhook payload containing payment resource data.
	 * @return void
	 */
	public static function capturePayment( $payloadStream ) {

		$capturePaymentUrl = Helper::getUrl( $payloadStream->resource->links, 'capture' );

		self::$headers['PayPal-Request-Id'] = "paypal-order-id-{$payloadStream->id}";
		self::$headers['Prefer']            = 'return=representation';

		$requestData = array(
			'url'     => $capturePaymentUrl,
			'options' => array(
				'headers' => self::$headers,
				'method'  => HttpHelper::METHOD_POST,
			),
		);

		Http::send( $requestData );
	}

	/**
	 * Validate the PayPal webhook signature to ensure the authenticity of the incoming event.
	 *
	 * @since 3.9.0
	 *
	 * @param object $payload The webhook payload containing stream and server headers.
	 * @return bool True if the webhook signature is verified successfully, false otherwise.
	 * @throws \ErrorException When the verification request fails or returns an error response.
	 */
	public static function webhook_signature_validation( $payload ): bool {

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

			$request_data = array(
				'url'     => self::$config->get( 'api_url' ) . '/v1/notifications/verify-webhook-signature',
				'options' => array(
					'headers' => self::$headers,
					'body'    => wp_json_encode( $data ),
					'method'  => HttpHelper::METHOD_POST,
				),
			);

			$response_data = Http::send( $request_data );

			return 'SUCCESS' === $response_data->verification_status ? true : false;
		} catch ( \Throwable $error ) {
			throw $error;
		}
	}

	/**
	 * Send an immediate HTTP 200 OK response to acknowledge receipt of the event.
	 *
	 * @since 3.9.0
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

	/**
	 * Retrieve PayPal order details using the provided API URL.
	 *
	 * @since 3.9.0
	 * @since 4.1.2 Catches HttpRequestException instead of Guzzle's RequestException.
	 *
	 * @param string $url The PayPal API endpoint URL for fetching order details.
	 * @return object|null The API response object containing order details.
	 * @throws \ErrorException When the HTTP request fails or returns an error.
	 */
	public static function get_order_details( $url ): ?object {
		try {

			$request_data = array(
				'url'     => $url,
				'options' => array(
					'headers' => self::$headers,
					'method'  => HttpHelper::METHOD_GET,
				),
			);

			return Http::send( $request_data );

		} catch ( HttpRequestException $error ) {
			$error_message = Helper::handleErrorResponse( $error ) ?? $error->getMessage();
			throw new ErrorException( esc_html( $error_message ) );
		}
	}

	/**
	 * Initiate a refund request for a PayPal order.
	 *
	 * @since 3.9.0
	 *
	 * @param string       $refund_url The PayPal API refund endpoint URL.
	 * @param string|int   $order_id The associated order ID for the refund request.
	 * @param array|object $data The refund request payload containing refund details.
	 * @return void
	 * @throws \Throwable If the refund process fails or an unexpected error occurs.
	 */
	public static function refund( $refund_url, $order_id, $data ) {

		try {

			$unique_id                          = uniqid( 'refund-' );
			self::$headers['PayPal-Request-Id'] = "{$unique_id}-order-id-{$order_id}";
			self::$headers['Prefer']            = 'return=representation';

			$request_data = array(
				'url'     => $refund_url,
				'options' => array(
					'headers' => self::$headers,
					'body'    => wp_json_encode( $data ),
					'method'  => HttpHelper::METHOD_POST,
				),
			);

			Http::send( $request_data );
		} catch ( \Throwable $th ) {
			throw $th;
		}
	}

	/**
	 * Retrieve PayPal vault details from a given URL.
	 *
	 * @since 3.9.0
	 * @since 4.1.2 Catches HttpRequestException instead of Guzzle's RequestException.
	 *
	 * @param string $url The PayPal vault API URL to retrieve details.
	 *
	 * @return object|null The decoded API response object containing vault details, or null on failure.
	 *
	 * @throws ErrorException If the HTTP request fails or the response cannot be handled.
	 */
	public static function get_vault_details( $url ): ?object {
		try {

			$request_data = array(
				'url'     => $url,
				'options' => array(
					'headers' => self::$headers,
					'method'  => HttpHelper::METHOD_GET,
				),
			);

			return Http::send( $request_data );

		} catch ( HttpRequestException $error ) {
			$error_message = Helper::handleErrorResponse( $error ) ?? $error->getMessage();
			throw new ErrorException( $error_message ); //phpcs:ignore
		}
	}
}
