<?php

namespace Ollyo\PaymentHub\Payments\Paypal;

use Throwable;
use ErrorException;
use Ollyo\PaymentHub\Core\Support\Arr;
use Ollyo\PaymentHub\Core\Support\System;
use GuzzleHttp\Exception\RequestException;
use Ollyo\PaymentHub\Core\Payment\BasePayment;
use Ollyo\PaymentHub\Exceptions\NotFoundException;
use Ollyo\PaymentHub\Exceptions\InvalidDataException;
use Ollyo\PaymentHub\Contracts\Config\RepositoryContract;

class Paypal extends BasePayment {

	/**
	 * The Paypal config Repository instance
	 *
	 * @var   RepositoryContract
	 * @since 3.0.0
	 */
	protected $config;

	/**
	 * @var string|null $orderID
	 * This property stores the ID of the current order being processed.
	 * @since 3.0.0
	 */
	protected $orderID;

	/**
	 * @var object|null $previousPayload
	 * This property contains the payload from a previous transaction, used in recurring payments.
	 */
	protected $previousPayload;

	/**
	 * The API endpoint URL used to process PayPal refund requests.
	 *
	 * @var string|null
	 */
	protected $refundLink;

	const CHECKOUT_ORDER_APPROVED   = 'CHECKOUT.ORDER.APPROVED';
	const PAYMENT_CAPTURE_COMPLETED = 'PAYMENT.CAPTURE.COMPLETED';
	const PAYMENT_CAPTURE_REFUNDED  = 'PAYMENT.CAPTURE.REFUNDED';

	/**
	 * Checks if all required configuration keys are present and not empty.
	 *
	 * This method ensures that the necessary configuration settings are available
	 * and properly set up before proceeding with any operations that depend on them.
	 *
	 * @return bool Returns true if all required configuration keys are present and not empty, otherwise false.
	 * @since  3.0.0
	 */
	public function check(): bool {
		$configKeys = Arr::make( array( 'client_id', 'client_secret', 'merchant_email', 'webhook_id' ) );

		$isConfigOk = $configKeys->every(
			function ( $key ) {
				return $this->config->has( $key ) && ! empty( $this->config->get( $key ) );
			}
		);

		return $isConfigOk;
	}

	public function setup(): void {

		try {
			new Api( $this->config );
		} catch ( Throwable $error ) {
			throw $error;
		}
	}

	/**
	 * Sets the data for processing after preparing it.
	 *
	 * This method overrides the setData method in the parent class,
	 * ensuring that the data is first prepared before setting it.
	 * If an error occurs during data preparation, it is re-thrown.
	 *
	 * @param  object $data The data to be set.
	 * @throws Throwable        If an error occurs during data preparation, it is re-thrown.
	 * @since  3.0.0
	 */
	public function setData( $data ): void {
		try {
			$type = $data->type ?? 'one-time';

			if ( 'refund' === $type ) {
				parent::setData( $this->prepareDataForRefund( $data ) );
			} else {
				parent::setData( $this->prepareData( $data ) );
			}
		} catch ( Throwable $error ) {
			throw $error;
		}
	}

	/**
	 * Prepares the data for sending to `Paypal Server`.
	 *
	 * This method takes raw data, processes it to create structured purchase unit data
	 * including amount, shipping information, and payment preferences. It uses configuration
	 * settings and ensures essential fields are set correctly.
	 *
	 * @param  object $data The raw data to be processed.
	 * @return array        The structured data for sending to `Paypal Server`.
	 * @since  3.0.0
	 */
	public function prepareData( $data ): array {
		if ( empty( $data ) ) {
			return array();
		}

		$this->orderID = $data->order_id;
		$type          = $data->type ?? 'one-time';
		$items         = 'one-time' === $type ? Helper::getItems( $data ) : null;
		$amount        = 'one-time' === $type ? Helper::createAmountData( $data ) : Helper::createAmountForRecurring( $data );

		$returnData = array(
			'purchase_units' => array(
				array(
					'custom_id' => $data->order_id,
					'items'     => $items,
					'amount'    => $amount,
					'payee'     => array( 'email_address' => $this->config->get( 'merchant_email' ) ),
				),
			),
			'intent'         => 'CAPTURE',
		);

		if ( 'one-time' === $type ) {
			Helper::getPaymentSourceForOneTime( $returnData, $this->config );

		} elseif ( 'recurring' === $type ) {

			$this->previousPayload = json_decode( stripslashes( $data->previous_payload ) );
			Helper::getPaymentSourceForRecurring( $returnData, $this->previousPayload );
		}

		if ( isset( $data->shipping_address ) && ! empty( $data->shipping_address ) ) {
			$returnData['purchase_units'][0]['shipping'] = Helper::getShippingInfo( $data->shipping_address );
		}

		return $returnData;
	}

	/**
	 * Initiates the payment creation process.
	 *
	 * This method retrieves an access token and use the token to return a checkout URL.
	 * If an error occurs during these operations, it handles the error and throws an exception.
	 *
	 * @throws ErrorException If there is an error retrieving the checkout URL or handling the response.
	 * @since  3.0.0
	 */
	public function createPayment() {
		try {
			$orderDetails = Api::createOrder( $this->getData(), $this->orderID );
			$checkoutUrl  = isset( $orderDetails->links ) ? Helper::getUrl( $orderDetails->links, 'payer-action' ) : null;

			if ( is_null( $checkoutUrl ) ) {
				throw new ErrorException( 'Checkout Link is Invalid' );
			}

			header( "Location: {$checkoutUrl}" );
			exit();
		} catch ( RequestException $error ) {

			$errorMessage = Helper::handleErrorResponse( $error ) ?? $error->getMessage();
			throw new ErrorException( esc_html( $errorMessage ) );
		}
	}

	/**
	 * Verifies the webhook signature and processes the order data.
	 *
	 * This method verifies the webhook signature from the payload. If the signature is valid,
	 * it processes the approved order. In case of an error, it handles the error
	 * response and sets the return data accordingly.
	 *
	 * @param  object $payload  The payload object containing the webhook data.
	 * @return object           Returns the processed order data or an error response.
	 * @throws RequestException If the request fails.
	 * @since  3.0.0
	 */
	public function verifyAndCreateOrderData( object $payload ): object {
		try {

			if ( ! Helper::checkWebhookVariables( $payload->server ) ) {
				return new \stdClass();
			}

			if ( ! Api::webhook_signature_validation( $payload ) ) {
				return new \stdClass();
			}

			Api::respond_to_event();

			$payload_stream = json_decode( $payload->stream );

			switch ( $payload_stream->event_type ) {
				case self::CHECKOUT_ORDER_APPROVED:
					Api::capturePayment( $payload_stream );
					return new \stdClass();

				case self::PAYMENT_CAPTURE_COMPLETED:
					return $this->setReturnData( $payload_stream );

				case self::PAYMENT_CAPTURE_REFUNDED:
					// $this->orderID = $paymentData->resource->custom_id;
					// return static::processRefund($paymentData);
					return new \stdClass();
				default:
					return new \stdClass();
			}
		} catch ( RequestException $error ) {

			// Handle the error response.
			$error_message = Helper::handleErrorResponse( $error ) ?? $error->getMessage();
			throw new \Exception( $error_message ); //phpcs:ignore
		}
	}

	/**
	 * Sets and returns order data based on the processing result and error message.
	 *
	 * Constructs order data based on the provided payload stream and optionally an error message.
	 * If an error message is provided, sets the order data with failure status and error details.
	 * Otherwise, sets the order data with successful transaction details from the payload stream.
	 *
	 * @param  object $payloadStream The payload stream object containing order and payment details.
	 * @return object                     The constructed order data object.
	 * @since  3.0.0
	 */
	private function setReturnData( $payloadStream ): object {
		$returnData = System::defaultOrderData();

		$statusMap = array(
			'DECLINED'  => 'failed',
			'COMPLETED' => 'paid',
			'PENDING'   => 'pending',
		);

		$transactionInfo            = $payloadStream->resource;
		$returnData->id             = $transactionInfo->custom_id;
		$returnData->payment_status = $statusMap[ $transactionInfo->status ];
		$returnData->transaction_id = $transactionInfo->id;
		$returnData->fees           = $transactionInfo->seller_receivable_breakdown->paypal_fee->value ?? null;
		$returnData->earnings       = $transactionInfo->seller_receivable_breakdown->net_amount->value ?? null;

		$returnData->payment_payload = addslashes( json_encode( $payloadStream ) );
		$returnData->payment_method  = $this->config->get( 'name' );

		return $returnData;
	}


	/**
	 * Creates a recurring payment by generating an order and handling the payment details.
	 *
	 * @throws ErrorException If there is an error during the payment process or request handling.
	 * @since  3.0.0
	 */
	public function createRecurringPayment() {
		try {
			Api::createOrder( $this->getData(), $this->orderID );
		} catch ( RequestException $error ) {

			$errorMessage = Helper::handleErrorResponse( $error ) ?? $error->getMessage();
			throw new ErrorException( esc_html( $errorMessage ) );
		}
	}

	/**
	 * Prepares the data required for a refund.
	 *
	 * @param  object $data The data object containing order and payment information.
	 * @return array        Returns an array containing refund-related data.
	 * @throws NotFoundException Throws an exception if payment payload is missing.
	 *
	 * @since 3.0.0
	 */
	private function prepareDataForRefund( $data ): array {
		if ( ! $data->payment_payload ) {
			throw new NotFoundException( 'Payment Payload Not Found' );
		}

		$this->orderID    = $data->order_id;
		$paymentPayload   = json_decode( stripslashes( $data->payment_payload ) );
		$links            = $paymentPayload->purchase_units[0]->payments->captures[0]->links ?? $paymentPayload->resource->links;
		$this->refundLink = Helper::getUrl( $links, 'refund' );

		return array(
			'custom_id'     => (string) $data->order_id,
			'note_to_payer' => $data->reason,
			'amount'        => (object) array(
				'currency_code' => $data->currency->code,
				'value'         => number_format( $data->amount, 2, '.', '' ),
			),
		);
	}

	/**
	 * Creates a refund payment by sending a refund request to PayPal and processes the response.
	 *
	 * @return void
	 *
	 * @throws ErrorException Throws an exception if an error occurs while making the HTTP request or processing the response.
	 * @since  3.0.0
	 */
	public function createRefund() {

		try {
			Api::refund( $this->refundLink, $this->orderID, $this->getData() );

		} catch ( RequestException $error ) {
			$errorMessage = Helper::handleErrorResponse( $error ) ?? $error->getMessage();
			throw new ErrorException( esc_html( $errorMessage ) );
		}
	}

	/**
	 * Retrieves the refund status based on the provided links and type.
	 *
	 * @param array  $links An array of links provided by the PayPal API.
	 * @param string|null $type The type of link to use.
	 *
	 * @since 1.0.0
	 */
	private function getRefundStatus( $links, $type = 'self' ): ?string {
		$url = $this->getUrl( $links, $type );

		$requestData = (object) array(
			'method'  => 'get',
			'url'     => $url,
			'options' => array( 'headers' => $this->headers ),
		);

		$responseData = System::sendHttpRequest( $requestData );

		return strtolower( $responseData->status ) ?? null;
	}

	/**
	 * Creates a webhook for PayPal notifications if it does not already exist.
	 *
	 * @return object|null Returns the webhook object or null if already registered.
	 *
	 * @throws ErrorException Throws an exception if there's an issue with the HTTP request or if webhook information is not found.
	 * @since  1.0.0
	 */
	public function createWebhook(): ?object {
		try {

			$webhookApiUrl = $this->config->get( 'api_url' ) . '/v1/notifications/webhooks';

			$requestData = (object) array(
				'method'  => 'get',
				'url'     => $webhookApiUrl,
				'options' => array( 'headers' => $this->headers ),
			);

			$responseData = $this->sendHttpRequest( $requestData );

			if ( isset( $responseData->webhooks ) && is_array( $responseData->webhooks ) ) {

				$registeredWebhookUrls  = array_column( $responseData->webhooks, 'url' );
				$isWebhookUrlRegistered = in_array( $this->config->get( 'webhook_url' ), $registeredWebhookUrls );

				return ! $isWebhookUrlRegistered ? $this->createNewWebhook() : null;
			}

			throw new ErrorException( 'Webhook Information Not Found' );

		} catch ( RequestException $error ) {
			$errorMessage = Helper::handleErrorResponse( $error ) ?? $error->getMessage();
			throw new ErrorException( esc_html( $errorMessage ) );
		}
	}

	/**
	 * Creates a new webhook for the specified events.
	 *
	 * @return object Returns an object containing the webhook_id and webhook_url of the newly created webhook.
	 *
	 * @throws InvalidDataException Throws an exception if the webhook information is invalid or the creation fails.
	 * @throws ErrorException       Throws an exception if the HTTP request fails.
	 * @since  1.0.0
	 */
	private function createNewWebhook() {
		try {

			$webhookApiUrl = $this->config->get( 'api_url' ) . '/v1/notifications/webhooks';

			$body = (object) array(
				'url'         => $this->config->get( 'webhook_url' ),
				'event_types' => array( (object) array( 'name' => 'PAYMENT.CAPTURE.REFUNDED' ) ),
			);

			$requestData = (object) array(
				'method'  => 'post',
				'url'     => $webhookApiUrl,
				'options' => array(
					'headers' => $this->headers,
					'body'    => json_encode( $body ),
				),
			);

			$responseData = $this->sendHttpRequest( $requestData );

			if ( $responseData->url === $this->config->get( 'webhook_url' ) && $responseData->id ) {
				return (object) array(
					'webhook_id'  => $responseData->id,
					'webhook_url' => $responseData->url,
				);
			}

			throw new InvalidDataException( 'Invalid Webhook Information' );

		} catch ( RequestException $error ) {
			$errorMessage = Helper::handleErrorResponse( $error ) ?? $error->getMessage();
			throw new ErrorException( esc_html( $errorMessage ) );
		}
	}

	/**
	 * Processes a refund payment based on the received payment data.
	 *
	 * @param  object $paymentData The payment data received from PayPal, containing refund information.
	 *
	 * @return object Returns an object containing refund-related data.
	 *
	 * @throws ErrorException Throws an exception if there is an error during the HTTP request or while processing the refund.
	 * @since  1.0.0
	 */
	private function processRefund( $paymentData ): object {
		$returnData = System::defaultOrderData( 'refund' );

		try {

			$payloadStream = $paymentData->resource;

			if ( strtoupper( $payloadStream->status ) === 'COMPLETED' ) {

				$returnData->id             = $payloadStream->custom_id;
				$returnData->refund_id      = $payloadStream->id;
				$returnData->payment_method = $this->config->get( 'name' );
				$returnData->refund_amount  = $payloadStream->amount->value;
				$returnData->refund_payload = json_encode( $paymentData );
			}

			$returnData->refund_status = static::getRefundStatus( $payloadStream->links, 'up' );

			return $returnData;

		} catch ( RequestException $error ) {
			$errorMessage = Helper::handleErrorResponse( $error ) ?? $error->getMessage();
			throw new ErrorException( esc_html( $errorMessage ) );
		}
	}
}
