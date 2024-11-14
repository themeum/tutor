<?php
namespace Ollyo\PaymentHub;

use Ollyo\PaymentHub\Core\Application;
use Ollyo\PaymentHub\Core\Factory;
use Ollyo\PaymentHub\Core\Payment\BaseConfig;
use Ollyo\PaymentHub\Core\Payment\BasePayment;
use Ollyo\PaymentHub\Core\Support\System;
use Ollyo\PaymentHub\Core\Support\Uri;
use Ollyo\PaymentHub\Exceptions\InvalidConfigurationException;

define('PAYMENT_HUB_ROOT', __DIR__);

class PaymentHub
{
	/**
	 * the payment class name
	 *
	 * @var object
	 */
	protected $payment;

	/**
	 * The payment config class associate with the payment
	 *
	 * @var object
	 */
	protected $config;

	public function __construct($payment, $config)
	{
		$this->payment = $payment;
		$this->config = $config;

		$this->boot();
	}

	/**
	 * Boot the payment client along with the config and setting the container services.
	 *
	 * @return void
	 */
	protected function boot()
	{

		$container = Factory::getContainer();
		$container->set(Application::class, function() {
			$app = new Application();
			$app->makeRepository(Application::class);

			return $app;
		});

		$container->set($this->payment, function() {
			/** @var BaseConfig */
			$configInstance = System::createClassInstance($this->config);
			$configInstance->createConfig();

			/** @var BasePayment */
			$paymentInstance = System::createClassInstance($this->payment);
			$paymentInstance->setConfigInstance($configInstance);

			// Set the Repository version of the ConfigContract to the payment instance
			$paymentInstance->setConfig(
				$configInstance->toRepository()
			);

			if (!$paymentInstance->check()) {
				throw new InvalidConfigurationException(sprintf('%s payment method is not configured properly! Contact with Site Administrator.', ucfirst($configInstance->getName())));
			}

			$paymentInstance->setup();

			return $paymentInstance;
		});
	}



	/**
	 * Make and return the payment class instance stored into the container
	 *
	 * @return BasePayment
	 */
	public function make(): BasePayment
	{
		return Factory::getContainer()->get($this->payment);
	}

	/**
	 * Make the payment instance with order ID and return the instance.
	 *
	 * @return BasePayment
	 */
	public function makeWith($orderId = null): BasePayment
	{
		$payment = $this->make();

		if (!is_null($orderId)) {
			$config = $payment->getConfig();
			$configInstance = $payment->getConfigInstance();

			$successUrl = Uri::getInstance($config->get('success_url'));
			$successUrl->setVar('order_id', $orderId);

			$cancelUrl = Uri::getInstance($config->get('cancel_url'));
			$cancelUrl->setVar('order_id', $orderId);

			$configInstance->updateConfig([
				'success_url' => $successUrl->toString(),
				'cancel_url' => $cancelUrl->toString(),
			]);
		}

		return $payment;
	}
}