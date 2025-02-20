<?php

namespace Ollyo\PaymentHub\Contracts\Payment;

use Ollyo\PaymentHub\Contracts\Config\RepositoryContract;

interface ConfigContract
{
	public function getMode(): string;
	public function getSecretKey(): string;
	public function getPublicKey(): string;
	public function createConfig(): void;
	public function updateConfig($key, $value = null): void;
	public function toRepository(): RepositoryContract;
	public function getSuccessUrl(): string;
	public function getCancelUrl(): string;
	public function getWebhookUrl(): string;
	public function getTitle(): string;
	public function getAdditionalInformation(): string;
	public function getName(): string;
}