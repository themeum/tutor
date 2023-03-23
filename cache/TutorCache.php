<?php
/**
 * A run-time cache management for tutor.
 *
 * @package Tutor\Cache
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.1.9
 */
namespace Tutor\Cache;

/**
 * TutorCache class
 *
 * @since 2.1.9
 */
final class TutorCache {
	/**
	 * Store instance once and provide it for entire lifecycle
	 *
	 * @since 2.1.9
	 *
	 * @var self
	 */
	private static $instance = null;

	/**
	 * Hold all run-time cache data.
	 *
	 * @since 2.1.9
	 *
	 * @var array
	 */
	private $data = array();

	// Prevent to make instance
	private function __construct(){}
	// Prevent to clone instance
	private function __clone(){}

	/**
	 * Get the current class instance.
	 *
	 * @since 2.1.9
	 * @return self
	 */
	public static function getInstance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Check valid cache key.
	 *
	 * @since 2.1.9
	 *
	 * @param string $key cache key.
	 *
	 * @return boolean
	 */
	public function is_valid_key( $key ) {
		if ( is_int( $key ) ) {
			return true;
		}

		if ( is_string( $key ) && trim( $key ) !== '' ) {
			return true;
		}

		return false;
	}

	/**
	 * Get cache data by key.
	 *
	 * @since 2.1.9
	 *
	 * @param string $key cache key.
	 * @param mixed  $default default value if key does not exit.
	 *
	 * @return mixed
	 */
	public static function get( $key, $default = false ) {
		$instance = self::getInstance();
		if ( ! $instance->is_valid_key( $key ) ) {
			return false;
		}

		if ( array_key_exists( $key, $instance->data ) ) {
			return $instance->data[ $key ];
		}

		return $default;
	}

	/**
	 * Set cache data to a cache key.
	 *
	 * @since 2.1.9
	 *
	 * @param string $key cache key.
	 * @param mixed  $value cache value.
	 *
	 * @return void
	 */
	public static function set( $key, $value ) {
		$instance = self::getInstance();
		if ( ! $instance->is_valid_key( $key ) ) {
			return false;
		}

		$instance->data[ $key ] = $value;
	}

	/**
	 * Get all cached data.
	 *
	 * @since 2.1.9
	 *
	 * @return array
	 */
	public static function get_all() {
		return self::getInstance()->data;
	}
}
