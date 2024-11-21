<?php

namespace Ollyo\PaymentHub\Core\Payment;

use Ollyo\PaymentHub\Contracts\Config\RepositoryContract;
use Ollyo\PaymentHub\Contracts\Payment\ConfigContract;
use Ollyo\PaymentHub\Core\Application;
use Ollyo\PaymentHub\Core\Factory;

class BaseConfig implements ConfigContract
{
	/**
	 * The payment config repository
	 *
	 * @var RepositoryContract
	 */
	protected $config;

	/**
	 * Name of the payment
	 *
	 * @var string
	 */
	protected $name;

	public function __construct()
	{
	}

	public function getMode(): string
	{
		return '';
	}

	public function getSecretKey(): string
	{
		return '';
	}

	public function getPublicKey(): string
	{
		return '';
	}

	public function getSuccessUrl(): string
	{
		return '';
	}

	public function getCancelUrl(): string
	{
		return '';
	}

	public function getWebhookSecretKey(): string
	{
		return '';
	}

	public function getWebhookUrl(): string
	{
		return '';
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

	public function createConfig(): void
	{
		$config = Factory::getContainer()->get(Application::class)->makeRepository(static::class);
		$config->set([
			'mode' => $this->getMode(),
			'secret_key' => $this->getSecretKey(),
			'public_key' => $this->getPublicKey(),
			'webhook_secret_key' => $this->getWebhookSecretKey(),
			'webhook_url' => $this->getWebhookUrl(),
			'success_url' => $this->getSuccessUrl(),
			'cancel_url' => $this->getCancelUrl(),
			'additional_information' => $this->getAdditionalInformation(),
			'title' => $this->getTitle(),
			'name' => $this->getName(),
		]);

		$this->config = $config;
	}

	public function updateConfig($key, $value = null): void
	{
		if (is_array($key)) {
			$newConfig = $key;
		} else {
			$newConfig = [$key => $value];
		}

		$config = $this->toRepository();

		foreach ($newConfig as $key => $value) {
			$config->set($key, $value);
		}

		$this->config = $config;
	}

	public function toRepository(): RepositoryContract
	{
		return $this->config;
	}
}