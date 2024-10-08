<?php
/**
 * Array management support class.
 *
 * @package Tutor\Ecommerce
 * @author Themeum
 * @link https://themeum.com
 * @since 3.0.0
 */

namespace Tutor\Ecommerce\Supports;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ArrayAccess;

/**
 * The array helper class.
 *
 * @since 3.0.0
 */
class Arr implements ArrayAccess {

	/**
	 * The array items.
	 *
	 * @since 3.0.0
	 *
	 * @var array
	 */
	protected $items = array();

	/**
	 * The private constructor method that helps the class to instantiate from outside,
	 * Instead it enforce to use the Arr::make() method to instantiate the array instance.
	 *
	 * @param array $items
	 *
	 * @since 3.0.0
	 */
	private function __construct( array $items = array() ) {
		$this->items = $items;
	}

	/**
	 * Make an instance of the Arr class.
	 *
	 * @param array $items
	 *
	 * @since 3.0.0
	 *
	 * @return self
	 */
	public static function make( array $items = array() ) {
		return ( new self( $items ) );
	}

	/**
	 * Check if the array has the provided key
	 *
	 * @param string $key
	 *
	 * @since 3.0.0
	 *
	 * @return boolean
	 */
	public function has( $key ): bool {
		return isset( $this->items[ $key ] );
	}

	/**
	 * Get the value of the array by the key.
	 *
	 * @param string $key
	 * @param mixed  $default
	 *
	 * @since 3.0.0
	 *
	 * @return mixed
	 */
	public function get( $key, $default = null ) {
		if ( ! $this->has( $key ) ) {
			return $default;
		}

		return $this->items[ $key ];
	}

	/**
	 * Set the value into the array. If the key exists then it will update the value. Add a new one otherwise.
	 *
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function set( $key, $value ): void {
		$this->items[ $key ] = $value;
	}

	/**
	 * Count the total number of items into the array.
	 *
	 * @since 3.0.0
	 *
	 * @return integer
	 */
	public function count(): int {
		return count( $this->items );
	}

	/**
	 * Add a new item at the end of the array
	 *
	 * @param mixed $value The value to add.
	 *
	 * @since 3.0.0
	 *
	 * @return integer The updated array length.
	 */
	public function push( $value ): int {
		$this->items[] = $value;

		return $this->count();
	}

	/**
	 * Add a new item to the beginning of the array.
	 *
	 * @param mixed $value The value to add.
	 *
	 * @since 3.0.0
	 *
	 * @return integer The updated array length.
	 */
	public function prepend( $value ): int {
		array_unshift( $this->items, $value );

		return $this->count();
	}

	/**
	 * Remove an item from the end of the array.
	 *
	 * @since 3.0.0
	 *
	 * @return mixed The removed item.
	 */
	public function pop() {
		return array_pop( $this->items );
	}

	/**
	 * Remove and get an item from the beginning of the array.
	 *
	 * @since 3.0.0
	 *
	 * @return mixed The removed item.
	 */
	public function shift() {
		return array_shift( $this->items );
	}

	/**
	 * Pick the last element of the array.
	 * If the array is empty then it will return null.
	 * This will only pick the item, but does not remove it from the array.
	 *
	 * @since 3.0.0
	 *
	 * @return mixed
	 */
	public function top() {
		if ( $this->count() === 0 ) {
			return null;
		}

		$length = $this->count();

		return $this->items[ $length - 1 ];
	}

	/**
	 * Pick the first element of the array.
	 * If the array is empty then it will return null.
	 * This will only pick the item, but does not remove it from the array.
	 *
	 * @since 3.0.0
	 *
	 * @return mixed
	 */
	public function front() {
		if ( $this->count() === 0 ) {
			return null;
		}

		return $this->items[0];
	}

	/**
	 * Run a map operation using a callable to the array.
	 *
	 * @param callable $callable The callable function.
	 *
	 * @since 3.0.0
	 *
	 * @return array
	 */
	public function map( callable $callable ): array {
		$newArray = array();

		foreach ( $this->items as $index => $value ) {
			$newArray[] = $callable( $value, $index );
		}

		return $newArray;
	}

	/**
	 * Filter the array by a callable function.
	 * The function will return a true/false value and the return value is true then the value will be kept,
	 * otherwise removed.
	 *
	 * @param callable $callable
	 *
	 * @since 3.0.0
	 *
	 * @return array
	 */
	public function filter( callable $callable ): array {
		$filteredArray = array();

		foreach ( $this->items as $index => $value ) {
			$result = $callable( $value, $index );

			if ( $result ) {
				$filteredArray[] = $value;
			}
		}

		return $filteredArray;
	}

	/**
	 * Find an item into the array by a callable function condition.
	 *
	 * @param callable $callable
	 *
	 * @since 3.0.0
	 *
	 * @return mixed|null
	 */
	public function find( callable $callable ) {
		foreach ( $this->items as $index => $value ) {
			if ( $callable( $value, $index ) ) {
				return $value;
			}
		}

		return null;
	}

	/**
	 * Find the index of an item into the array.
	 * If not found then it will return -1.
	 *
	 * @param callable $callable
	 *
	 * @since 3.0.0
	 *
	 * @return integer
	 */
	public function findIndex( callable $callable ): int {
		foreach ( $this->items as $index => $value ) {
			if ( $callable( $value, $index ) ) {
				return $index;
			}
		}

		return -1;
	}

	/**
	 * A boolean method that checks if the array satisfies a specific condition.
	 * If it satisfies for only one item then it returns true.
	 *
	 * @param callable $callable
	 *
	 * @since 3.0.0
	 *
	 * @return boolean
	 */
	public function some( callable $callable ): bool {
		foreach ( $this->items as $index => $value ) {
			if ( $callable( $value, $index ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * A boolean method that checks if the array satisfies a specific condition.
	 * If every items satisfies the condition then it will return true.
	 *
	 * @param callable $callable
	 *
	 * @since 3.0.0
	 *
	 * @return boolean
	 */
	public function every( callable $callable ): bool {

		foreach ( $this->items as $index => $value ) {
			if ( ! $callable( $value, $index ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Replicate the array_reduce function for usage consistency
	 *
	 * @param callable $callable
	 * @param mixed    $initial_value
	 *
	 * @since 3.0.0
	 *
	 * @return mixed
	 */
	public function reduce( callable $callable, $initial_value ) {
		return array_reduce( $this->items, $callable, $initial_value );
	}

	/**
	 * Join the array using a glue
	 *
	 * @param string $glue The symbol by which it will be joined.
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	public function join( $glue = ',' ) {
		return implode( $glue, $this->items );
	}

	public function offsetExists( $key ): bool {
		return $this->has( $key );
	}

	public function offsetGet( $key ): mixed {
		return $this->get( $key );
	}

	public function offsetSet( $key, $value ): void {
		$this->set( $key, $value );
	}

	public function offsetUnset( $key ): void {
		unset( $this->items[ $key ] );
	}
}
