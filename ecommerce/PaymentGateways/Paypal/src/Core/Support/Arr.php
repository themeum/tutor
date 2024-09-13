<?php
namespace Ollyo\PaymentHub\Core\Support;

use ArrayAccess;


class Arr implements ArrayAccess
{
	protected $items = [];

	public function __construct(array $items = [])
	{
		$this->items = $items;
	}

	public static function make(array $items = [])
	{
		return (new static($items));
	}

	public function has($key): bool
	{
		return isset($this->items[$key]);
	}

	public function get($key, $default = null)
	{
		if (!$this->has($key)) {
			return $default;
		}

		return $this->items[$key];
	}

	public function set($key, $value): void
	{
		$this->items[$key] = $value;
	}

	public function count(): int
	{
		return count($this->items);
	}

	public function push($value): int
	{
		$this->items[] = $value;

		return $this->count();
	}

	public function prepend($value): int
	{
		array_unshift($this->items, $value);

		return $this->count();
	}

	public function pop()
	{
		return array_pop($this->items);
	}

	public function shift() 
	{
		return array_shift($this->items);
	}

	public function top()
	{
		if ($this->count() === 0) {
			return null;
		}

		$length = $this->count() - 1;

		return $this->items[$length - 1];
	}

	public function front() 
	{
		if ($this->count() === 0) {
			return null;
		}

		return $this->items[0];
	}

	public function map(callable $callable) : array
	{
		$newArray = [];

		foreach ($this->items as $index => $value) {
			$newArray[] = $callable($value, $index);
		}

		return $newArray;
	}

	public function filter(callable $callable): array
	{
		$filteredArray = [];

		foreach ($this->items as $index => $value) {
			$result = $callable($value, $index);

			if ($result) {
				$filteredArray[] = $value;
			}
		}

		return $filteredArray;
	}

	public function find(callable $callable)
	{
		foreach ($this->items as $index => $value) {
			if ($callable($value, $index)) {
				return $value;
			}
		}

		return null;
	}
	
	public function findIndex(callable $callable): int
	{
		foreach ($this->items as $index => $value) {
			if ($callable($value, $index)) {
				return $index;
			}
		}

		return -1;
	}

	public function some(callable $callable): bool
	{
		foreach ($this->items as $index => $value) {
			if ($callable($value, $index)) {
				return true;
			}
		}

		return false;
	}
	
	public function every(callable $callable): bool
	{

		foreach ($this->items as $index => $value) {
			if(!$callable($value, $index)) {
				return false;
			}
		}

		return true;
	}

	public function join($glue = ',')
	{
		return implode($glue, $this->items);
	}

	public function offsetExists( $key): bool
	{
		return $this->has($key);
	}

	public function offsetGet( $key) : mixed
	{
		return $this->get($key);
	}

	public function offsetSet( $key,  $value): void
	{
		$this->set($key, $value);
	}

	public function offsetUnset( $key): void
	{
		unset($this->items[$key]);
	}
}