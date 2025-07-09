<?php
/**
 * Container for managing dependencies and bindings.
 * Supports binding, singleton registration, and autowiring via reflection.
 *
 * @package TutorPro\Classes
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 3.7.0
 */

namespace TUTOR;

use ReflectionClass;

/**
 * Class Container
 */
class Container {
	/**
	 * Registered non-singleton bindings.
	 *
	 * @var array
	 */
	protected static $bindings = array();

	/**
	 * Registered singleton bindings.
	 *
	 * @var array
	 */
	protected static $singletons = array();

	/**
	 * Resolved singleton instances.
	 *
	 * @var array
	 */
	protected static $instances = array();

	/**
	 * Bind an abstract type to a concrete implementation.
	 *
	 * @param string          $abstract abstract.
	 * @param callable|string $concrete concrete.
	 * @return void
	 */
	public static function bind( string $abstract, $concrete ): void {
		self::$bindings[ $abstract ] = $concrete;
	}

	/**
	 * Get a registered binding, singleton, or instance without resolving it.
	 *
	 * @param string $abstract abstract.
	 *
	 * @return mixed|null Returns the binding, singleton, or instance if found; otherwise null.
	 */
	public static function get( string $abstract ) {
		if ( isset( self::$instances[ $abstract ] ) ) {
			return self::$instances[ $abstract ];
		}

		if ( isset( self::$singletons[ $abstract ] ) ) {
			return self::$singletons[ $abstract ];
		}

		if ( isset( self::$bindings[ $abstract ] ) ) {
			return self::$bindings[ $abstract ];
		}

		return null;
	}

	/**
	 * Check if a binding or instance exists.
	 *
	 * @param string $abstract abstract.
	 *
	 * @return bool
	 */
	public static function has( string $abstract ): bool {
		return isset( self::$bindings[ $abstract ] ) ||
				isset( self::$singletons[ $abstract ] ) ||
				isset( self::$instances[ $abstract ] );
	}

	/**
	 * Remove a binding, singleton, or instance.
	 *
	 * @param string $abstract abstract.
	 *
	 * @return void
	 */
	public static function remove( string $abstract ): void {
		unset(
			self::$bindings[ $abstract ],
			self::$singletons[ $abstract ],
			self::$instances[ $abstract ]
		);
	}

	/**
	 * Clear all bindings, singletons, and instances.
	 *
	 * @return void
	 */
	public static function clear(): void {
		self::$bindings   = array();
		self::$singletons = array();
		self::$instances  = array();
	}

	/**
	 * Register a singleton binding.
	 *
	 * @param string          $abstract abstract.
	 * @param callable|string $concrete concrete.
	 *
	 * @return void
	 */
	public static function singleton( string $abstract, $concrete ): void {
		self::$singletons[ $abstract ] = $concrete;
	}

	/**
	 * Register an existing instance.
	 *
	 * @param string $abstract abstract.
	 * @param object $object object.
	 *
	 * @return void
	 */
	public static function instance( string $abstract, $object ): void {
		self::$instances[ $abstract ] = $object;
	}

	/**
	 * Resolve a class or binding.
	 *
	 * @param string $abstract abstract.
	 * @return object object.
	 *
	 * @throws \Exception If can not resolve the dependency.
	 */
	public static function make( string $abstract ) {
		// Return existing instance.
		if ( isset( self::$instances[ $abstract ] ) ) {
			return self::$instances[ $abstract ];
		}

		// Resolve singleton.
		if ( isset( self::$singletons[ $abstract ] ) ) {
			$concrete                     = self::$singletons[ $abstract ];
			$object                       = is_callable( $concrete ) ? $concrete() : new $concrete();
			self::$instances[ $abstract ] = $object;
			return $object;
		}

		// Resolve normal binding.
		if ( isset( self::$bindings[ $abstract ] ) ) {
			$concrete = self::$bindings[ $abstract ];
			return is_callable( $concrete ) ? $concrete() : new $concrete();
		}

		// Autowire.
		if ( class_exists( $abstract ) ) {
			return self::resolve( $abstract );
		}

		throw new \Exception( "Cannot resolve: {$abstract}" );
	}

	/**
	 * Automatically resolve a class via Reflection.
	 *
	 * @param string $class class.
	 * @return object object.
	 *
	 * @throws \Exception If class is not instantiable or un-resolvable dependencies.
	 */
	protected static function resolve( string $class ) {
		$reflector = new ReflectionClass( $class );

		if ( ! $reflector->isInstantiable() ) {
			throw new \Exception( "Class {$class} is not instantiable." );
		}

		$constructor = $reflector->getConstructor();

		if ( ! $constructor ) {
			return new $class();
		}

		$params       = $constructor->getParameters();
		$dependencies = array();

		foreach ( $params as $param ) {
			$type = $param->getType();

			if ( $type && ! $type->isBuiltin() ) {
				$dependencies[] = self::make( $type->getName() );
			} elseif ( $param->isDefaultValueAvailable() ) {
				$dependencies[] = $param->getDefaultValue();
			} else {
				throw new \Exception( "Unresolvable dependency [{$param->getName()}] in class {$class}" );
			}
		}

		return $reflector->newInstanceArgs( $dependencies );
	}
}
