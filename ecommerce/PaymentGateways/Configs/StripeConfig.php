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

	use PaymentUrlsTrait;

	/**
	 * Constants for configuration keys.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const ENV_KEY               = 'stripe_environment';
	const SECRET_KEY            = 'stripe_secret_key';
	const PUBLIC_KEY            = 'stripe_public_key';
	const WEBHOOK_SIGNATURE_KEY = 'stripe_webhook_signature_key';

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
		return tutor_utils()->get_option( self::ENV_KEY, 'test' );
	}

	/**
	 * Retrieves the secret key for the Stripe payment gateway.
	 *
	 * @since 3.0.0
	 *
	 * @return string The secret key.
	 */
	public function getSecretKey(): string {
		return tutor_utils()->get_option( self::SECRET_KEY );
	}

	/**
	 * Retrieves the public key for the Stripe payment gateway.
	 *
	 * @since 3.0.0
	 *
	 * @return string The public key.
	 */
	public function getPublicKey(): string {
		return tutor_utils()->get_option( self::PUBLIC_KEY );
	}

	/**
	 * Retrieves the webhook key for the Stripe payment gateway.
	 *
	 * @since 3.0.0
	 *
	 * @return string The public key.
	 */
	public function getWebhookSecretKey(): string {
		return tutor_utils()->get_option( self::WEBHOOK_SIGNATURE_KEY );
	}

}
