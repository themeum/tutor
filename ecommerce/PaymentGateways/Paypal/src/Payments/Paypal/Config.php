<?php

namespace Ollyo\PaymentHub\Payments\Paypal;

use Ollyo\PaymentHub\Core\Support\System;
use Ollyo\PaymentHub\Core\Payment\BaseConfig;
use Ollyo\PaymentHub\Contracts\Payment\ConfigContract;

class Config extends BaseConfig implements ConfigContract
{

    const API_URL_TEST = 'https://api-m.sandbox.paypal.com';
    const API_URL_LIVE = 'https://api-m.paypal.com';

    protected $name = 'paypal';

    public function __construct()
    {
        parent::__construct();
    }

    public function getMode(): string
    {
        return 'test';
    }

    public function getClientSecret(): string
    {
        return 'EGdgv1gf-kspceq-vWg_7ho4vChE1MtSWA9tW9RvZije2-Hdhv1oUi8K-H9kDMRSJX573NH4UcPa77B4';
    }

    public function getSuccessUrl(): string
    {
        return 'https://payment-hub.test/success.php';
    }

    public function getCancelUrl(): string
    {
        return 'https://payment-hub.test/cancel.php';
    }

    public function getWebhookUrl(): string
    {
        return 'https://payment-hub.test/webhook.php';
    }

    public function getAdditionalInformation(): string
    {
        return '';
    }

    public function getTitle(): string
    {
        return '';
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getClientID(): string
    {
        return 'AcwTzqdgfFQFBKq9QCHWUdqQIU58SYwARsZzKdexV8hnnocXUIswqglc4tjt7IHj8B2np8GacoYvpOGj';
    }

    public function getMerchantEmail(): string
    {
        return System::validateAndSanitizeEmailAddress('sb-svver31506661@business.example.com');
    }

    public function getApiURL()
    {
        return $this->getMode() === 'test' ? static::API_URL_TEST: static::API_URL_LIVE;
    }

	private function getWebhookID () : string
	{
		return '6S569475BJ933183P';
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
    public function createConfig(): void
    {
        parent::createConfig();

        $config = [
            'client_id' 			=> $this->getClientID(),
            'merchant_email' 		=> $this->getMerchantEmail(),
            'api_url' 				=> $this->getApiURL(),
            'client_secret' 		=> $this->getClientSecret(),
            'save_payment_method' 	=> $this->savePaymentMethodForFutureUse(),
			'webhook_id'			=> $this->getWebhookID()
        ];

        $this->updateConfig($config);
    }

    /**
     * Determines whether the payment method should be saved for future use.
     *
     * @return bool  returns true to indicate the payment method should be saved, false otherwise.
     * @since  1.0.0
     */
    private function savePaymentMethodForFutureUse(): bool
    {
        return true;
    }
}