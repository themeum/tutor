<?php

namespace Ollyo\PaymentHub\Payments\Stripe;

use Ollyo\PaymentHub\Core\Payment\BaseConfig;
use Ollyo\PaymentHub\Contracts\Payment\ConfigContract;

class Config extends BaseConfig implements ConfigContract
{
	protected $name = 'stripe';
	
	public function __construct()
	{
		parent::__construct();
	}

	public function getMode(): string
	{
		return 'test';
	}

	public function getSecretKey(): string
	{
		return 'sk_test_51OqvUtJyMznDJzqj9g7WK5VtJT74zFM7g8ThxhA3xRi0MHdgWHo80jOVhuLTw43t7cyFNBAA1wJub0f0Y7y6zZgi00RU2ICFWB';
	}

	public function getPublicKey(): string
	{
		return 'pk_test_51NpAwGGaf8qt0ZC3Rl5BDL7PG5gVbcWq8UZ6P5rlR0yKa0bKpT3yurs86kC38Id5fBP8uG7vRcneQydCMWhbu23B00SZC7lKza';
	}

	public function getWebhookSecretKey(): string
	{
		return 'whsec_Ry1wTuFrQbCuqAtS4tNPghV9KP6Raixb';
	}

	public function getWebhookUrl(): string
	{
		return 'https://example.com/webhook/2';
	}

	public function getSuccessUrl(): string
	{
		return 'https://example.com/success';
	}

	public function getCancelUrl(): string
	{
		return 'https://example.com/cancel';	
	}
}