<?php
/**
 * Cache abstract for implementing by the derived class
 *
 * @package  Tutor\Cache
 *
 * @author   Themeum
 *
 * @since    v2.0.6
 */

namespace Tutor\Cache;

/**
 * Containing abstract and regular method for
 * caching
 */
abstract class AbstractCache {

	/**
	 * Cache key
	 *
	 * @return string
	 */
	abstract public function key(): string;

	/**
	 * Cache time
	 *
	 * @return int
	 */
	abstract public function cache_time(): int;

	/**
	 * Cache data
	 *
	 * @return array
	 */
	abstract public function cache_data();

	/**
	 * Set cache data
	 *
	 * @return void
	 *
	 * @since v2.0.6
	 */
	public function set_cache(): void {
		do_action( 'tutor_cache_before_' . $this->key(), $this->cache_data() );
		set_transient( $this->key(), $this->cache_data(), $this->cache_time() );
		do_action( 'tutor_cache_after_' . $this->key(), $this->cache_data() );
	}

	/**
	 * Get user data from cache
	 *
	 * @return object  cache data
	 *
	 * @since v2.0.6
	 */
	public function get_cache() {
		$data = get_transient( $this->key() );
		return $data;
	}

	/**
	 * If cache don't have value or expired or not exists
	 * will return false
	 *
	 * @return bool | true on success, false on fail
	 *
	 * @since v2.0.6
	 */
	public function has_cache(): bool {
		return $this->get_cache() ? true : false;
	}

	/**
	 * Delete cache
	 *
	 * @return void
	 *
	 * @since v2.0.6
	 */
	public function delete_cache(): void {
		delete_transient( $this->key );
	}
}
