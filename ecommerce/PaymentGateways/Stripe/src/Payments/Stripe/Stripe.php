<?php

namespace Ollyo\PaymentHub\Payments\Stripe;


use Throwable;
use Stripe\Webhook;
use RuntimeException;
use Stripe\StripeClient;
use Ollyo\PaymentHub\Core\Support\Arr;
use Ollyo\PaymentHub\Core\Support\System;
use Ollyo\PaymentHub\Core\Payment\BasePayment;
use Ollyo\PaymentHub\Exceptions\NotFoundException;
use Ollyo\PaymentHub\Contracts\Config\RepositoryContract;
use Ollyo\PaymentHub\Exceptions\InvalidSignatureException;

require_once __DIR__ . '/vendor/autoload.php';

class Stripe extends BasePayment
{
	/**
	 * The Stripe config Repository instance
	 *
	 * @var RepositoryContract
	 */
	protected $config;

	/**
	 * The Stripe Api Client Instance
	 *
	 * @var   StripeClient
	 * @since 1.0.0
	 */
	protected $client;

	/**
	 * Check if everything is OK before proceeding next.
	 *
	 * @return bool
	 */
	public function check(): bool
	{
		$configKeys = Arr::make(['secret_key', 'mode', 'success_url', 'cancel_url']);

		$isConfigOk = $configKeys->every(function($key) {
			return $this->config->has($key) && !empty($this->config->get($key));
		});

		return $isConfigOk;
	}

	/**
	 * Setup the Stripe SDK and other required settings for initiating the payment redirection.
	 *
	 * @return void
	 */
	public function setup(): void
	{
		try {
			$this->client = new StripeClient($this->config->get('secret_key'));
			header('Content-Type: application/json');
			
		} catch (Throwable $error) {
			throw $error;
		}
	}

	/**
	 * Set the payment line_item and other payment related data for the payment gateway.
	 *
	 * @param mixed $data
	 * @return void
	 */
	public function setData($data): void
	{
		try {
			
			$type = $data->type ?? 'one-time';
			
			switch ($type) {
				case 'one-time':
					parent::setData($this->prepareDataForOneTimePayment($data));		
					break;
				case 'recurring':
					parent::setData($this->prepareDataForRecurring($data));
					break;
			}
		} catch (Throwable $error) {
			throw $error;
		}
	}

	/**
	 * Prepare the payment data for initiating the stripe checkout
	 *
	 * @param object $data
	 * @return array
	 * @throws Throwable
	 */
	protected function prepareDataForOneTimePayment($data): array
	{
		$coupon = null;

		try {
			if ($data->coupon_discount_amount_in_smallest_unit > 0) {
				$coupon = $this->createCoupon($data->coupon_discount_amount_in_smallest_unit, $data->currency->code);
			}
		} catch (Throwable $error) {
			throw $error;
		}

		$couponId = !empty($coupon->id) ? $coupon->id : null;

		$returnData = [
			'line_items' 					=> static::getLineItems($data),
			'metadata' 						=> [
				'payment_type' 	=> 'stripe',
				'order_id' 		=> $data->order_id,
				'coupon_id' 	=> $couponId,
			],
			'mode' 							=> 'payment',
			'success_url' 					=> $this->config->get('success_url'),
			'cancel_url' 					=> $this->config->get('cancel_url'),
			'payment_intent_data' 			=> [
				'metadata' 				=> ['order_id' => $data->order_id, 'coupon_id' => $couponId],
			],
			'shipping_options' 				=> $this->getShippingOptions($data),
		];

		if (!is_null($couponId)) {
			$returnData['discounts'] = [['coupon' => $couponId]];
		}

		if ($this->config->get('save_payment_method')) {
			$returnData['payment_intent_data']['setup_future_usage'] = 'off_session';
			$returnData['saved_payment_method_options'] 			 = ['payment_method_save' => 'enabled'];
			$returnData['consent_collection'] 						 = [
				'payment_method_reuse_agreement' => ['position' => 'auto']
			];
			$returnData['customer_creation'] 						 = 'always';		
		}

		return $returnData;
	}

	/**
	 * Make the shipping options for the payment.
	 *
	 * @param object $data
	 * @return array
	 */
	protected function getShippingOptions($data)
	{
		$shipping = [];

		if ($data->shipping_charge_in_smallest_unit <= 0 || empty($data->currency->code)) {
			return [];
		}

		$shipping[] = [
			'shipping_rate_data' => [
				'display_name' => 'Shipping Charge',
				'type'         => 'fixed_amount',
				'fixed_amount' => [
					'amount'   => $data->shipping_charge_in_smallest_unit,
					'currency' => strtolower($data->currency->code)
				]
			]
		];

		return $shipping;
	}

	/**
	 * Generate the checkout url for redirecting to the payment page.
	 */
	public function createPayment()
	{
		try {
			$session = $this->client->checkout->sessions->create($this->getData());

			if ($session->url) {
				header("Location: {$session->url}");
			}
		} catch (Throwable $error) {
			throw $error;
		}
	}

	/**
	 * Create webhook for an endpoint if the endpoint has already been added
	 *
	 * @return \Stripe\WebhookEndpoint | null
	 * @throws Throwable
	 */
	public function createWebhook()
	{
		$config = $this->getConfig();

		try {
			$hooks  = $this->client->webhookEndpoints->all();

			$exists = Arr::make($hooks->data)->some(function($value) use($config) {
				return $value->url === $config->get('webhook_url');
			});

			return !$exists ? $this->createNewWebhook(): null;
		} catch (Throwable $error) {
			throw $error;
		}
	}

	/**
	 * Create the webhook for the webhook_url endpoint.
	 *
	 * @return \Stripe\WebhookEndpoint
	 * @throws Throwable
	 */
	protected function createNewWebhook()
	{
		$webhookUrl = $this->config->get('webhook_url');
		
		try {
			return $this->client->webhookEndpoints->create([
				'url' 				=> $webhookUrl,
				'enabled_events' 	=> [
					'payment_intent.payment_failed',
					'payment_intent.succeeded',
					'payment_intent.canceled'
				]
			]);
		} catch (Throwable $error) {
			throw $error;
		}
	}

	/**
	 * Create the webhook event and get the payment data.
	 *
	 * @param object $data 	An associative array with ['get' => $_GET, 'post' => $_POST, 'server' => $_SERVER, 'stream' => file_get_contents('php://input')]
	 *
	 * @return object
	 * @throws Throwable
	 */
	public function createWebhookEvent($data)
	{
		$webhookSecretKey 	= $this->config->get('webhook_secret_key');
		$event 				= null;

		if (empty($webhookSecretKey)) {
			http_response_code(400);
			throw new NotFoundException(sprintf('The webhook secret key is missing.'));
		}

		try {
			$signature = $data->server['HTTP_STRIPE_SIGNATURE'];

			if (empty($signature)) {
				http_response_code(400);
				throw new InvalidSignatureException('HTTP_STRIPE_SIGNATURE is missing.');
			}

			$event = Webhook::constructEvent($data->stream, $signature, $webhookSecretKey);

			$statusMap = [
				'payment_intent.payment_failed' => 'failed',
				'payment_intent.succeeded'    	=> 'paid',
				'payment_intent.canceled'       => 'canceled'
			];

			if (empty($event)) {
				http_response_code(400);
				throw new RuntimeException(sprintf('Webhook event is not created.'));
			}

			if (!isset($statusMap[$event->type])) {
				http_response_code(400);
				throw new RuntimeException(sprintf('Unknown event type %s', $event->type));	
			}

			$status = $statusMap[$event->type];

			http_response_code(200);

			return (object) [
				'payment_data' 	=> $event->data->object,
				'status' 		=> $status,
			];
		} catch (Throwable $error) {
			throw $error;
		}
	}

	public function verifyAndCreateOrderData(object $payload): object
	{
		try {
			$returnData = System::defaultOrderData();
			$event 		= $this->createWebhookEvent($payload);
			$data 		= $event->payment_data;

			if ($event->status === 'failed') {
				$returnData->payment_error_reason = $data->last_payment_error->message;
			}

			$returnData->id 				= $data->metadata->order_id;
			$returnData->payment_status 	= $event->status;
			$returnData->transaction_id 	= $data->id;
			$returnData->payment_payload 	= $payload->stream;
			$returnData->payment_method		= $this->config->get('name');

			return $returnData;
		} catch (Throwable $error) {
			throw $error;
		}
	}

	public function createCoupon($amount, $currency)
	{
		try {
			return $this->client->coupons->create([
				'amount_off' 	=>  $amount,
				'duration' 		=> 'once',
				'currency' 		=> strtolower($currency)
			]);
		} catch (Throwable $error) {
			throw $error;
		}
	}

	public function deleteCoupon($couponId)
	{
		try {
			$this->client->coupons->delete($couponId);
		} catch (Throwable $error) {
			throw $error;
		}
	}

	private function getLineItems($data) : array 
	{
		if (empty($data->items)) {
			throw new NotFoundException("Items Not Found.", 1);		
		}
		
		$lineItems = array_map(function ($item) use ($data) {

			$price = $item['discounted_price_in_smallest_unit'] > 0 ? $item['discounted_price_in_smallest_unit'] : $item['regular_price_in_smallest_unit'];

			return [
				'price_data' => [
					'product_data' 	=> ['name' => $item['item_name']],
					'unit_amount' 	=> $price,
					'currency' 		=> strtolower($data->currency->code),
				],
				'quantity' 	=> $item['quantity'],
			];
		}, (array) $data->items);

		// If Tax amount is given
		if ($data->tax_in_smallest_unit > 0) {

			$lineItems[] = [
				'price_data' => [
					'product_data' 	=> ['name' => 'Tax'],
					'unit_amount' 	=> $data->tax_in_smallest_unit,
					'currency' 		=> strtolower($data->currency->code),
				],
				'quantity' 	=> 1,
			];
		}
		return $lineItems;
	}

	/**
	 * Prepares the necessary data for processing a recurring payment.
	 *
	 * @param  object $data 	The data object containing payment and customer details.
	 * @return array 			The prepared data array for the recurring payment.
	 * @since  1.0.0
	 */
	protected function prepareDataForRecurring($data) : array
	{
		$previousPayload = json_decode($data->previous_payload)->data->object;
		
		return [
			'amount' 				=> $data->amount_in_smallest_unit,
			'currency' 				=> $data->currency->code,
			'confirm' 				=> true,
			'customer' 				=> $previousPayload->customer,
			'metadata' 				=> ['order_id' => $data->order_id],
			'payment_method' 		=> $previousPayload->payment_method,
			'receipt_email' 		=> $data->customer->email,
			'shipping' 				=> static::getShippingInfoForRecurring($data->shipping_address),
			'payment_method_types' 	=> $previousPayload->payment_method_types
		];
	}

	/**
	 * Retrieves the shipping information for a recurring payment.
	 *
	 * @param 	object $shipping 	The shipping data object.
	 * @return 	array 				The formatted shipping information array, or an empty array if no shipping data is 
	 * provided.
	 * @since   1.0.0
	 */
	private function getShippingInfoForRecurring($shipping) : array
	{
		if (empty($shipping)) {
			return [];
		}

		return [
			'address' => [
				'city' 			=> $shipping->city,
				'country' 		=> $shipping->country->alpha_2,
				'line1' 		=> $shipping->address1,
				'line2' 		=> $shipping->address2,
				'postal_code' 	=> $shipping->postal_code,
				'state' 		=> $shipping->state
			],
			'name' 	=> $shipping->name,
			'phone' => $shipping->phone_number
		];
	}

	/**
	 * Creates a recurring payment intent using the prepared data.
	 *
	 * @throws Throwable If the payment intent creation fails.
	 * @since  1.0.0
	 */
	public function createRecurringPayment() 
	{
		try {
			$this->client->paymentIntents->create($this->getData());
		} catch (Throwable $error) {
			throw $error;
		}
	}
}