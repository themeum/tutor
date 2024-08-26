<?php
namespace Ollyo\PaymentHub\Contracts\Core;

interface ContainerContract
{
	public function has($id): bool;
	public function get($id): mixed;
	public function set($id, $service): void;
}