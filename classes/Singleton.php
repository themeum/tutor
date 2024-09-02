<?php
/**
 * An abstract base class to make Singleton class
 *
 * @package Tutor\Classes
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 3.0.0
 */

namespace TUTOR;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Singleton
 */
abstract class Singleton {

	/**
	 * Store instances for each child class.
	 *
	 * @var array
	 */
	private static $instances = array();

	/**
	 * Constructor prevent for new instance.
	 *
	 * @access protected
	 */
	protected function __construct() { }

	/**
	 * Prevent object clone
	 *
	 * @access protected
	 */
	protected function __clone() { }

	/**
	 * Get instance of class.
	 *
	 * @return static
	 */
	public static function get_instance() {
		$class = static::class;
		if ( ! isset( self::$instances[ $class ] ) ) {
			self::$instances[ $class ] = new static();
		}
		return self::$instances[ $class ];
	}

	/**
	 * Reset a class instance
	 *
	 * @return void
	 */
	public static function reset_instance() {
		$class = static::class;
		if ( isset( self::$instances[ $class ] ) ) {
			unset( self::$instances[ $class ] );
		}
	}
}
