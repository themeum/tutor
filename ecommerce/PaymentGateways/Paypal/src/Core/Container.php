<?php
namespace Ollyo\PaymentHub\Core;

use Ollyo\PaymentHub\Contracts\Core\ContainerContract;

final class Container implements ContainerContract
{
	protected $services = [];

	public function __construct()
	{
	}

	public function has($id): bool
	{
		return isset($this->services[$id]);
	}

	public function get($id)
	{
		if (!$this->has($id)) {
			return null;
		}

		return $this->services[$id];
	}

	public function set($id, $service): void
	{
		if (is_callable($service)) {
			$serviceValue = $service();
		} elseif (is_object($service)) {
			$serviceValue = $service;
		} elseif (is_string($service)) {
			$serviceValue = class_exists($service) ? new $service(): $service;
		} else {
			$serviceValue = $service;
		}

		$this->services[$id] = $serviceValue;
	}
}