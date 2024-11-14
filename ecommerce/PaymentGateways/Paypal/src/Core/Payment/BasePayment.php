<?php

namespace Ollyo\PaymentHub\Core\Payment;

use Ollyo\PaymentHub\Contracts\Payment\ConfigContract;
use Ollyo\PaymentHub\Contracts\Config\RepositoryContract;

abstract class BasePayment
{
	/**
	 * ConfigContract instance
	 *
	 * @var ConfigContract
	 */
	protected $configInstance;

	/**
	 * The config repository instance
	 *
	 * @var RepositoryContract
	 */
	protected $config;

	/**
	 * The payment data
	 *
	 * @var array
	 */
	protected $data = [];

	/**
	 * The payment setup method. This will responsible for init the payment plugin SDKs before navigating to the
	 * payment gateway page.
	 *
	 * @return void
	 */
	abstract public function setup(): void;

	/**
	 * Check if all the functionalities are okay before setting up the payment and other things.
	 * This is called before the setup method so you could check if the setup method uses any data in this method.
	 *
	 * @return bool
	 */
	abstract public function check(): bool;

	/**
	 * Set the payment data, like product prices, tax, etc. as per payment gateway requirements
	 *
	 * @param mixed $data
	 * @return void
	 */
	public function setData($data): void
	{
		$this->data = $data;
	}

	/**
	 * Get the payment data.
	 *
	 * @return mixed
	 */
	public function getData()
	{
		return $this->data;
	}

	/**
	 * Set the ConfigContract of the payment gateway.
	 *
	 * @param ConfigContract $config
	 * @return void
	 */
	public function setConfigInstance(ConfigContract $config): void
	{
		$this->configInstance = $config;
	}

	/**
	 * Get the original instance of the ConfigContract
	 *
	 * @return ConfigContract
	 */
	public function getConfigInstance(): ConfigContract
	{
		return $this->configInstance;
	}

	/**
	 * Get the config as RepositoryContract instead of the ConfigContraction
	 *
	 * @return RepositoryContract
	 */
	public function getConfig(): RepositoryContract
	{
		return $this->configInstance->toRepository();
	}

	/**
	 * Set the RepositoryContract config
	 *
	 * @param RepositoryContract $config
	 * @return void
	 */
	public function setConfig(RepositoryContract $config): void
	{
		$this->config = $config;
	}

	/**
	 * Initiate the payment gateway,
	 * This method is responsible for creating the payment gateway redirection url
	 * Or opening the payment gateway page.
	 *
	 * @return mixed
	 * @throws Throwable
	 */
	abstract public function createPayment();

	/**
	 * Verify after payment submission to the gateway and create the updated order data with payment status,
	 * Error reason and more.
	 *
	 * @param 	object 		$payload 	The payment object (object) ['get' => $_GET, 'post' => $_POST, 'server' => $_SERVER, 'stream' => file_get_contents('php://input')]
	 * @return 	object
	 * @throws 	Throwable
	 */
	abstract public function verifyAndCreateOrderData(object $payload): object;
}