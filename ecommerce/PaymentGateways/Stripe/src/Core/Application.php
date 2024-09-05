<?php
namespace Ollyo\PaymentHub\Core;

use Ollyo\PaymentHub\Config\Repository;
use Ollyo\PaymentHub\Contracts\Config\RepositoryContract;

class Application
{
	/**
	 * The application version
	 *
	 * @var string
	 */
	const VERSION = '1.0.0';

	/**
	 * The repository instance
	 *
	 * @var Array<Repository>
	 */
	protected $repositories = [];

	/**
	 * Get the application version number
	 *
	 * @return string;
	 */
	public function getVersion()
	{
		return static::VERSION;
	}


	public function hasRepository($key)
	{
		return isset($this->repositories[$key]);
	}

	public function makeRepository($key): RepositoryContract
	{
		if (!$this->hasRepository($key)) {
			$this->repositories[$key] = new Repository();
		}

		return $this->repositories[$key];
	}

	public function getAppConfig()
	{
		$config = $this->makeRepository(static::class);
		$config->set([
			'name' => 'payment hub',
			'version' => $this->getVersion(),
			'payments' => ['paypal', 'stripe', 'authorised net']
		]);

		return $config;
	}
}