<?php

namespace Tutor\PaymentGateways\Configs;

use Ollyo\PaymentHub\Contracts\Payment\ConfigContract;
use Ollyo\PaymentHub\Payments\Paypal\Config;
use phpDocumentor\Reflection\Types\Self_;
use Tutor\Ecommerce\Settings;

/**
 * PaypalConfig class.
 *
 * This class handles the configuration for the Paypal payment gateway.
 * It extends the BaseConfig class and implements the ConfigContract interface.
 *
 * @since 3.0.0
 */
class PaypalConfig extends Config implements ConfigContract {

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
	 * PayPal environment key.
	 *
	 * @var string
	 * @since 3.0.0
	 */
	private $environment;

	/**
	 * PayPal merchant email key.
	 *
	 * @var string
	 * @since 3.0.0
	 */
	private $merchant_email;

	/**
	 * PayPal client ID key.
	 *
	 * @var string
	 * @since 3.0.0
	 */
	private $client_id;

	/**
	 * PayPal client secret id.
	 *
	 * @var string
	 * @since 3.0.0
	 */
	private $secret_id;

	/**
	 * PayPal webhook ID key.
	 *
	 * @var string
	 * @since 3.0.0
	 */
	private $webhook_id;

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

		$settings = Settings::get_payment_gateway_settings( $this->name );

		$config_keys = $this->get_config_keys();
		foreach ( $config_keys as $key ) {
			$this->$key = $this->get_field_value( $settings, $key );
		}
	}

	public function getMode(): string {
		return 'test';
	}

	public function getClientSecret(): string {
		return $this->secret_id;
	}

	public function getWebhookID(): string {
		return $this->webhook_id;
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
		return $this->client_id;
	}

	public function getMerchantEmail() : string {
		return $this->merchant_email;
	}

	public function getApiURL() {
		return $this->environment === 'test' ? static::API_URL_TEST : static::API_URL_LIVE;
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

	/**
	 * Determine whether payment gateway configured properly
	 *
	 * @since 3.0.0
	 *
	 * @return boolean
	 */
	public function is_configured() {
		// Return true if all the settings are filled.
		return $this->merchant_email && $this->client_id && $this->secret_id;
	}

	/**
	 * Get config keys
	 *
	 * @since 3.0.0
	 *
	 * @return array
	 */
	private function get_config_keys() {
		return array_keys( Settings::get_paypal_config_keys() );
	}
}
