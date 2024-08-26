<?php

namespace Ollyo\PaymentHub\Config;

use ArrayAccess;
use Ollyo\PaymentHub\Contracts\Config\RepositoryContract;

class Repository implements ArrayAccess, RepositoryContract
{
	/**
	 * All the configuration items
	 *
	 * @var array
	 */
	protected $items = [];

	public function __construct(array $items = [])
	{
		$this->items = $items;
	}

	/**
	 * Determine if a key exists in the repository
	 *
	 * @param mixed $key
	 * @return boolean
	 */
	public function has($key): bool
	{
		return isset($this->items[$key]);
	}

	/**
	 * Get the value from the repository using a key, if not found then return the default value.
	 *
	 * @param mixed $key
	 * @param mixed $default
	 * @return mixed
	 */
	public function get($key, $default = null)
	{
		if (is_array($key)) {
			return $this->getMany($key);
		}

		if (!$this->has($key)) {
			return $default;
		}

		return $this->items[$key];
	}

	public function getMany(array $keys)
	{
		$config = [];

		foreach ($keys as $key => $default) {
			if (is_numeric($key)) {
				[$key, $default] = [$default, null];
			}

			$config[$key] = $this->get($key, $default);
		}

		return $config;
	}

	/**
	 * Set a value to the repository by the key. This is for updating an existing value or add new.
	 *
	 * @param mixed $key
	 * @param mixed $value
	 * @return void
	 */
	public function set($key, $value = null)
	{
		$keys = is_array($key) ? $key: [$key => $value];

		foreach ($keys as $key => $value) {
			$this->items[$key] = $value;
		}
	}

	/**
	 * Get all the values
	 *
	 * @return array
	 */
	public function all()
	{
		return $this->items;
	}

	/**
	 * Prepend to the repository
	 *
	 * @param mixed $key
	 * @param mixed $value
	 * @return void
	 */
	public function prepend($key, $value)
	{
		$array = $this->get($key, []);

		array_unshift($array, $value);

		$this->set($key, $array);
	}

	/**
	 * Append to the repository
	 *
	 * @param mixed $key
	 * @param mixed $value
	 * @return void
	 */
	public function push($key, $value)
	{
		$array = $this->get($key, []);

		array_push($array, $value);

		$this->set($key, $array);
	}

	/**
	 * Check if the configuration option exists.
	 *
	 * @param string $key
	 * @return bool
	 */
	public function offsetExists($key): bool
	{
		return $this->has($key);
	}

	/**
	 * Get the value by the key
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function offsetGet($key): mixed
	{
		return $this->get($key);
	}

	/**
	 * Set value by offset key
	 *
	 * @param string $key
	 * @param mixed $value
	 * @return void
	 */
	public function offsetSet($key, $value): void
	{
		$this->set($key, $value);
	}

	/**
	 * Unset a value by setting it by null
	 *
	 * @param string $key
	 * @return void
	 */
	public function offsetUnset($key): void
	{
		$this->set($key, null);
	}
}