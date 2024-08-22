<?php

namespace Tutor\PaymentGateways\Configs;

use Ollyo\PaymentHub\Contracts\Payment\ConfigContract;
use Ollyo\PaymentHub\Core\Payment\BaseConfig;

/**
 * StripeConfig class.
 *
 * This class handles the configuration for the Stripe payment gateway.
 * It extends the BaseConfig class and implements the ConfigContract interface.
 *
 * @since 3.0.0
 */
class StripeConfig extends BaseConfig implements ConfigContract {

	/**
	 * The name of the payment gateway.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	protected $name = 'stripe';

	/**
	 * Constructor.
	 *
	 * Initializes the StripeConfig object.
	 *
	 * @since 3.0.0
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Retrieves the mode of the Stripe payment gateway.
	 *
	 * @since 3.0.0
	 *
	 * @return string The mode of the payment gateway ('test' or 'live').
	 */
	public function getMode(): string {
		return 'test';
	}

	/**
	 * Retrieves the secret key for the Stripe payment gateway.
	 *
	 * @since 3.0.0
	 *
	 * @return string The secret key.
	 */
	public function getSecretKey(): string {
		return 'sk_test_51OqvUtJyMznDJzqj9g7WK5VtJT74zFM7g8ThxhA3xRi0MHdgWHo80jOVhuLTw43t7cyFNBAA1wJub0f0Y7y6zZgi00RU2ICFWB';
	}

	/**
	 * Retrieves the public key for the Stripe payment gateway.
	 *
	 * @since 3.0.0
	 *
	 * @return string The public key.
	 */
	public function getPublicKey(): string {
		return 'pk_test_51NpAwGGaf8qt0ZC3Rl5BDL7PG5gVbcWq8UZ6P5rlR0yKa0bKpT3yurs86kC38Id5fBP8uG7vRcneQydCMWhbu23B00SZC7lKza';
	}

	/**
	 * Retrieves the webhook secret key for Stripe.
	 *
	 * @since 3.0.0
	 *
	 * @return string The webhook secret key.
	 */
	public function getWebhookSecretKey(): string {
		return 'whsec_Ry1wTuFrQbCuqAtS4tNPghV9KP6Raixb';
	}

	/**
	 * Retrieves the webhook URL for Stripe.
	 *
	 * @since 3.0.0
	 *
	 * @return string The webhook URL.
	 */
	public function getWebhookUrl(): string {
		return 'https://tutorabcd.com/webhook/2';
	}

	/**
	 * Retrieves the success URL for the payment process.
	 *
	 * @since 3.0.0
	 *
	 * @return string The success URL.
	 */
	public function getSuccessUrl(): string {
		return 'https://example.com/success';
	}

	/**
	 * Retrieves the cancel URL for the payment process.
	 *
	 * @since 3.0.0
	 *
	 * @return string The cancel URL.
	 */
	public function getCancelUrl(): string {
		return 'https://example.com/cancel';
	}
}
