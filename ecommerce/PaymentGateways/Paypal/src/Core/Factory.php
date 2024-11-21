<?php
namespace Ollyo\PaymentHub\Core;

use Ollyo\PaymentHub\Contracts\Core\ContainerContract;

final class Factory
{
	private static $instance = null;

	/**
	 * Get the container singleton instance 
	 *
	 * @return Container
	 */
	public static function getContainer(): ContainerContract
	{
		if (is_null(static::$instance)) {
			static::$instance = new Container();
		}

		return static::$instance;
	}
}