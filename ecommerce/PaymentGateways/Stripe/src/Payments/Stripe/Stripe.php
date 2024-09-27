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
use Ollyo\PaymentHub\Exceptions\InvalidDataException;
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
				case 'refund':
					parent::setData($this->prepareDataForRefund($data));
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
			if ($data->coupon_discount > 0) {
				$couponDiscount = System::getMinorAmountBasedOnCurrency($data->coupon_discount, $data->currency->code);
				$coupon = $this->createCoupon($couponDiscount, $data->currency->code);
			}

			$isTaxEnable = static::getTaxRegistrationList();
		} catch (Throwable $error) {
			throw $error;
		}

		$couponId = !empty($coupon->id) ? $coupon->id : null;

		$returnData = [
			'line_items' 					=> $this->getLineItems($data),
			'mode' 							=> 'payment',
			'success_url' 					=> $this->config->get('success_url'),
			'cancel_url' 					=> $this->config->get('cancel_url'),
			'payment_intent_data' => [
				'metadata'	=> [
					'order_id' 	=> $data->order_id,
					'coupon_id' => $couponId, 		
					'type'		=> 'one-time'
				],
			],
			'shipping_options' 				=> $this->getShippingOptions($data),
		];

		if (!is_null($couponId)) {
			$returnData['discounts'] = [['coupon' => $couponId]];
		}

		if ($isTaxEnable) {
			$returnData['automatic_tax']['enabled'] = true;
		}

		if ($this->config->get('save_payment_method')) {
			$returnData['payment_intent_data']['setup_future_usage'] = 'off_session';
			$returnData['saved_payment_method_options'] 			 = ['payment_method_save' => 'enabled'];
			$returnData['consent_collection'] 						 = [
				'payment_method_reuse_agreement' => ['position' => 'auto']
			];
			$returnData['customer_creation'] 						 = 'always';		
			$returnData['billing_address_collection']			     = 'auto';
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

		if ($data->shipping_charge <= 0 || empty($data->currency->code)) {
			return [];
		}

		$shipping[] = [
			'shipping_rate_data' => [
				'display_name' => 'Shipping Charge',
				'type'         => 'fixed_amount',
				'fixed_amount' => [
					'amount'   => System::getMinorAmountBasedOnCurrency($data->shipping_charge,$data->currency->code),
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
					'payment_intent.canceled',     
					'charge.refund.updated',			
					'charge.refunded'				
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

			if ($event->type === 'charge.refund.updated' && $event->data->object->status === 'succeeded') {
				exit();
			}

			$statusMap = [
				'payment_intent.payment_failed' => 'failed',
				'payment_intent.succeeded'    	=> 'paid',
				'payment_intent.canceled'       => 'canceled',
				'charge.refund.updated'			=> 'refunded',
				'charge.refunded'				=> 'refunded'
			];

			if (empty($event)) {
				http_response_code(400);
				throw new RuntimeException(sprintf('Webhook event is not created.'));
			}

			if (!isset($statusMap[$event->type])) {
				http_response_code(400);
				exit();
			}

			$status = $statusMap[$event->type];

			http_response_code(200);

			return (object) [
				'payment_data' 	=> $event->data,
				'status' 		=> $status,
			];
		} catch (Throwable $error) {
			throw $error;
		}
	}

	public function verifyAndCreateOrderData(object $payload): object
	{
		try {
			$event = $this->createWebhookEvent($payload);			

			if ($event->status === 'refunded') {
				
				$returnData = System::defaultOrderData('refund');
				static::processRefund($event, $returnData);
				$returnData->refund_payload = $payload->stream;

			} else {
				$returnData = System::defaultOrderData('payment');
				static::processPayment($event, $returnData);
				$returnData->payment_payload = $payload->stream;
			}
			
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

			$price = is_null($item['discounted_price']) ? $item['regular_price'] : $item['discounted_price'];

			return [
				'price_data' => [
					'product_data' 	=> ['name' => $item['item_name']],
					'unit_amount' 	=> System::getMinorAmountBasedOnCurrency($price,$data->currency->code),
					'currency' 		=> strtolower($data->currency->code),
				],
				'quantity' 	=> $item['quantity'],
			];
		}, (array) $data->items);

		// If Tax amount is given
		if ($data->tax > 0) {

			$lineItems[] = [
				'price_data' => [
					'product_data' 	=> ['name' => 'Tax'],
					'unit_amount' 	=> System::getMinorAmountBasedOnCurrency($data->tax,$data->currency->code),
					'currency' 		=> strtolower($data->currency->code),
				],
				'quantity' 	=> 1,
			];
		}

		$minimumCharge = Helper::calculateMinimumChargeDifference($data);

		if($minimumCharge > 0)
		{
			$lineItems[] = [
				'price_data' => [
					'product_data' => ['name' => 'Minimum Charge', 'description' => 'Minimum charge to process the payment'],
					'unit_amount' => System::getMinorAmountBasedOnCurrency($minimumCharge, $data->currency->code),
					'currency' => strtolower($data->currency->code),
				],
				'quantity' => 1,
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

		if ((string)$data->total_amount === '0') {
			$data->total_amount = static::getMinimumChargeAmount($previousPayload,$data->currency->code);
		}
		
		$tax		     	= static::getTaxAmountForRecurring($data);
		$totalAmount 		= System::getMinorAmountBasedOnCurrency($data->total_amount, $data->currency->code);
		$finalTotalAmount 	= !is_null($tax) ? $totalAmount + $tax : $totalAmount;
		$taxAmount	     	= System::convertMinorAmountToMajor($tax, $data->currency->code);
		
		return [
			'amount' 				=> $finalTotalAmount,
			'currency' 				=> $data->currency->code,
			'confirm' 				=> true,
			'customer' 				=> $previousPayload->customer,
			'metadata' 				=> ['order_id' => $data->order_id, 'type' => 'recurring', 'tax' => $taxAmount],
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

	
	/**
	 * Initiates the creation of a refund payment using the client API.
	 *
	 * @throws Throwable If an error occurs during the refund creation process.
	 * @since  1.0.0
	 */
	public function createRefundPayment()
	{
		try {
			$this->client->refunds->create($this->getData());
		} catch (Throwable $error) {
			throw $error;
		}
	}

	/**
	 * Prepares the data required for creating a refund.
	 *
	 * @param  object $data The data object containing refund details.
	 * @return array 		The prepared array of refund data to be passed to the refund creation API.
	 *
	 * @throws NotFoundException 	If the payment payload is not found in the provided data.
	 * @throws InvalidDataException If the payment payload is invalid or missing required information.
	 * 
	 * @since  1.0.0
	 */
	protected function prepareDataForRefund($data) : array
	{
		if (!$data->payment_payload) {
			throw new NotFoundException("Payment Payload Not Found");
		}

		$paymentPayload = json_decode($data->payment_payload);

		if ($paymentPayload->data->object->object !== 'payment_intent' || !isset($paymentPayload->data->object->id)) {
			throw new InvalidDataException("Invalid Payment Payload");	
		}
		
		return [
			'amount' 			=> System::getMinorAmountBasedOnCurrency($data->amount, $data->currency->code),
			'metadata' 			=> ['order_id' => $data->order_id],
			'payment_intent' 	=> $paymentPayload->data->object->id
		];
	}

	/**
	 * Processes refund event data and updates the provided return data object with refund details.
	 *
	 * @param object $event 		The event object containing refund-related payment data.
	 * @param object $returnData 	A reference to the object that will be updated with the refund details.
	 *
	 * @return void
	 * @since  1.0.0
	 */

	private function processRefund($event, &$returnData) : void
	{
		$data 			= $event->payment_data;
		$refundStatus 	= $data->object->status === 'succeeded' ? static::getRefundStatus($data) : $data->object->status;
		
		$returnData->id 					= $data->object->metadata->order_id;
		$returnData->refund_status 			= $refundStatus;
		$returnData->refund_id				= $data->object->id;
		$returnData->payment_method 		= $this->config->get('name');
		$returnData->refund_amount			= System::convertMinorAmountToMajor($data->object->amount_refunded,strtoupper($data->object->currency));
		$returnData->refund_error_reason	= $data->object->failure_reason ?? null;
	}


	/**
	 * Processes payment event data and updates the provided return data object with payment details.
	 *
	 * @param object $event 		The event object containing payment-related data.
	 * @param object $returnData 	A reference to the object that will be updated with the payment details.
	 *
	 * @return void
	 * @since  1.0.0
	 */
	private function processPayment($event, &$returnData) : void
	{
		$data = $event->payment_data->object;

		if ($event->status === 'failed') {
			$returnData->payment_error_reason = $data->last_payment_error->message;
		}
		
		$extraCharges = static::calculateExtraCharges($data->latest_charge);

		$returnData->id 				= $data->metadata->order_id;
		$returnData->payment_status 	= $event->status;
		$returnData->transaction_id 	= $data->id;
		$returnData->payment_method 	= $this->config->get('name');
		$returnData->tax_amount 		= static::getTaxAmount($data);
		$returnData->fees	    		= System::convertMinorAmountToMajor($extraCharges['fees'], strtoupper($data->currency));
		$returnData->earnings			= System::convertMinorAmountToMajor($extraCharges['earnings'], strtoupper($data->currency));
	}


	/**
	 * Determines the refund status based on the captured and refunded amounts.
	 *
	 * @param  object $data The data object containing information about the captured and refunded amounts.
	 * @return string 		The refund status, which can be 'refunded', 'partially_refunded', or an empty string if no conditions
	 * are met.
	 * @since  1.0.0
	 */
	private function getRefundStatus($data) : string
	{
		$remainingAmount 	= $data->object->amount_captured - $data->object->amount_refunded;
		$previousAmount 	= $data->previous_attributes->amount_refunded;
		$status				= '';
		
		if ($remainingAmount > 0) {
			$status = 'partially_refunded';
			
		} elseif ($remainingAmount === 0 && $previousAmount === 0) {
			$status = 'refunded';
			
		} elseif ($remainingAmount === 0 && $previousAmount !== 0) {
			$status = 'partially_refunded';
		}

		return $status;		
	}

	/**
	 * Checks if there are any tax registrations available through the client API.
	 *
	 * @return bool Returns true if there are tax registrations, otherwise false.
	 * @since  1.0.0
	 */
	private function getTaxRegistrationList() : bool
	{
		$taxList = $this->client->tax->registrations->all();

		return !empty($taxList->data) ? true : false;
	}

	/**
	 * Retrieves the tax amount for a one-time payment.
	 *
	 * @param  object 	$data 	The data object containing the payment intent ID.
	 * @return int|null 		The tax amount for the session, or null if not found.
	 * @since  1.0.0
	 */

	private function getTaxAmountForOneTime($data)
	{
		$sessionData = $this->client->checkout->sessions->all(['payment_intent' => $data->id]);
		$tax 		 = $sessionData->data[0]->total_details->amount_tax ?? null;

		return System::convertMinorAmountToMajor($tax, strtoupper($data->currency));			
	}

	/**
	 * Retrieves the tax amount based on the payment type.
	 *
	 * @param 	object $data 	The data object containing metadata about the payment type.
	 * @return 	int|null 		The tax amount, or null if not applicable.
	 * @since 	1.0.0
	 */
	private function getTaxAmount($data) 
	{
		$type = $data->metadata->type;

		switch ($type) {
			
			case 'one-time':
				return static::getTaxAmountForOneTime($data);
			
			case 'recurring':
				return $data->metadata->tax;
		}
	}

	/**
	 * Calculates the tax amount for a recurring payment.
	 *
	 * @param 	object 		$data 	The data object containing information about the recurring payment.
	 * @return 	int|null 			The calculated tax amount, or null if tax is not enabled or not applicable.
	 * @since 	1.0.0
	 */
	private function getTaxAmountForRecurring($data)
	{
		$isTaxEnable = static::getTaxRegistrationList();

		if (!$isTaxEnable) {
			return null;
		}
		
		$previousPayload = json_decode($data->previous_payload)->data->object;
		
		$taxCalculation = $this->client->tax->calculations->create([
			'currency' 		=> strtolower($data->currency->code),
			'line_items' 	=> [
				[
					'amount' 	=> System::getMinorAmountBasedOnCurrency($data->sub_total, $data->currency->code),
					'reference' => "Recurring-Order-id-{$data->order_id}"
				]
			],
			'customer' 		=> $previousPayload->customer,
		]);

		return $taxCalculation->tax_breakdown[0]->amount ?? null;
	}

	/**
	 * Calculates the extra charges, based on a charge ID.
	 *
	 * @param 	string $chargeID 	The ID of the charge for which extra charges are to be calculated.
	 * @return 	array 				An associative array containing extra charges, or null if not applicable.
	 * @since   1.0.0
	 */
	private function calculateExtraCharges($chargeID)
	{
		$processingFee = $earnings = null;
		$chargeDetails = $this->client->charges->retrieve($chargeID);

		if ($chargeDetails->balance_transaction) {
			$balanceTransaction = $this->client->balanceTransactions->retrieve($chargeDetails->balance_transaction);

			$processingFee = $balanceTransaction->fee_details[0]->amount ?? null;
			$earnings	   = $balanceTransaction->net ?? null;
		}

		return ['fees' => $processingFee, 'earnings' => $earnings];
	}


	/**
	 * Retrieves the minimum charge amount for the specified currency.
	 *
	 * @param  object $previousPayload  	The payload from a previous transaction containing currency details.
	 * @param  string $currencyCode     	The current currency code for which the minimum charge amount is requested.
	 *
	 * @return float 						The minimum charge amount, either in the original or converted currency.
	 *
	 * @throws InvalidDataException 		If the currency is invalid or a minimum charge can't be determined.
	 * @since  1.0.0
	 */
	private function getMinimumChargeAmount($previousPayload, $currencyCode)
	{
		$currency 					= strtoupper($currencyCode);
		$previousPayloadCurrency  	= strtoupper($previousPayload->currency);

		if (isset(Helper::$minimumCharges[$currency])) {
			return Helper::$minimumCharges[$currency];
		}

		if (isset(Helper::$minimumCharges[$previousPayloadCurrency])) {
			return Helper::$minimumCharges[$previousPayloadCurrency];
		}
			
		$chargeDetails = $this->client->charges->retrieve($previousPayload->latest_charge);

		if ($chargeDetails->balance_transaction) {
			$balanceTransaction = $this->client->balanceTransactions->retrieve($chargeDetails->balance_transaction);

			$settlementCurrency = strtoupper($balanceTransaction->currency);
			
			if (isset(Helper::$minimumCharges[$settlementCurrency])) {
				$minimumAmount = Helper::$minimumCharges[$settlementCurrency];
				return Helper::convertAmountByCurrency($minimumAmount, $currency, $balanceTransaction);
			}
		}
		
		throw new InvalidDataException("Invalid Currency");		
	}
}