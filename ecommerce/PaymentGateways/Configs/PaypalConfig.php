<?php

namespace Tutor\PaymentGateways\Configs;

use Ollyo\PaymentHub\Contracts\Payment\ConfigContract;
use Ollyo\PaymentHub\Core\Payment\BaseConfig;

/**
 * PaypalConfig class.
 *
 * This class handles the configuration for the Paypal payment gateway.
 * It extends the BaseConfig class and implements the ConfigContract interface.
 *
 * @since 3.0.0
 */
class PaypalConfig extends BaseConfig implements ConfigContract {

	use PaymentUrlsTrait;

	/**
	 * Constants for API URLs.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const API_URL_TEST = 'https://api-m.sandbox.paypal.com';
	const API_URL_LIVE = 'https://api-m.paypal.com';

	/**
	 * Constants for configuration keys.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	const ENV_KEY            = 'paypal_environment';
	const MERCHANT_EMAIL_KEY = 'paypal_merchant_email';
	const CLIENT_ID_KEY      = 'paypal_client_id';
	const CLIENT_SECRET_KEY  = 'paypal_client_secret';
	const WEBHOOK_ID_KEY     = 'paypal_webhook_id';

	/**
	 * The name of the payment gateway.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	protected $name = 'paypal';

	public function __construct() {
		 parent::__construct();
	}

	public function getMode(): string {
		return 'test';
	}

	public function getClientSecret(): string {
		return tutor_utils()->get_option( self::CLIENT_SECRET_KEY );
	}

	public function getWebhookID(): string {
		return tutor_utils()->get_option( self::WEBHOOK_ID_KEY );
	}

	public function getAdditionalInformation(): string {
		return '';
	}

	public function getTitle(): string {
		return '';
	}

	public function getName(): string {
		return $this->name;
	}

	public function getClientID() : string {
		return tutor_utils()->get_option( self::CLIENT_ID_KEY );
	}

	public function getMerchantEmail() : string {
		return tutor_utils()->get_option( self::MERCHANT_EMAIL_KEY );
	}

	public function getApiURL() {
		return $this->getMode() === 'test' ? static::API_URL_TEST : static::API_URL_LIVE;
	}

	/**
	 * Creates and updates configuration settings specific to the PayPal integration.
	 *
	 * Retrieves necessary configuration values using getter methods for webhook ID,
	 * client ID, merchant email, API URL, and client secret. Updates the configuration
	 * storage with these values to ensure they are available for subsequent operations
	 * such as API requests and webhook handling.
	 *
	 * @since 1.0.0
	 */
	public function createConfig(): void {
		parent::createConfig();

		$config = array(
			'webhook_id'     => $this->getWebhookID(),
			'client_id'      => $this->getClientID(),
			'merchant_email' => $this->getMerchantEmail(),
			'api_url'        => $this->getApiURL(),
			'client_secret'  => $this->getClientSecret(),
		);

		$this->updateConfig( $config );
	}
}
