<?php

namespace Ollyo\PaymentHub\Contracts\Config;

interface RepositoryContract
{
	/**
	 * Determine if a key exists in the repository
	 *
	 * @param mixed $key
	 * @return boolean
	 */
	public function has($key);

	/**
	 * Get the value from the repository using a key, if not found then return the default value.
	 *
	 * @param mixed $key
	 * @param mixed $default
	 * @return mixed
	 */
	public function get($key, $default = null);

	/**
	 * Set a value to the repository by the key. This is for updating an existing value or add new.
	 *
	 * @param mixed $key
	 * @param mixed $value
	 * @return void
	 */
	public function set($key, $value = null);

	/**
	 * Get all the values
	 *
	 * @return array
	 */
	public function all();

	/**
	 * Prepend to the repository
	 *
	 * @param mixed $key
	 * @param mixed $value
	 * @return void
	 */
	public function prepend($key, $value);

	/**
	 * Append to the repository
	 *
	 * @param mixed $key
	 * @param mixed $value
	 * @return void
	 */
	public function push($key, $value);
}