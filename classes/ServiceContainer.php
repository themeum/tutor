<?php
/**
 * ServiceContainer to build instance and resolve dependency
 *
 * @package TutorPro\Classes
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 3.7.0
 */

namespace TUTOR;

use Closure;
use ReflectionClass;

/**
 * Class to create an instance
 */
class ServiceContainer {
	/**
	 * Hold the binding
	 *
	 * @since 3.7.0
	 *
	 * @var array
	 */
	protected $bindings = array();

	/**
	 * Class bindings
	 *
	 * @since 3.7.0
	 *
	 * @param string $abstract Abstract class.
	 * @param string $concrete Concrete class.
	 *
	 * @return void
	 */
	public function bind( $abstract, $concrete ) {
		$this->bindings[ $abstract ] = $concrete;
	}

	/**
	 * Make an instance using the bindings
	 *
	 * @since 3.7.0
	 *
	 * @param string $abstract The class string.
	 *
	 * @return instance
	 */
	public function make( $abstract ) {
		// If bound, resolve via binding.
		if ( isset( $this->bindings[ $abstract ] ) ) {
			$concrete = $this->bindings[ $abstract ];
			return $concrete instanceof Closure ? $concrete( $this ) : new $concrete();
		}

		// If not bound, try to auto-resolve.
		return $this->build( $abstract );
	}

	/**
	 * Build class instance & resolve dependency
	 *
	 * @since 3.7.0
	 *
	 * @param string $class Class string.
	 *
	 * @throws \Exception If class not instantiable.
	 *
	 * @return instance
	 */
	protected function build( $class ) {
		$reflector = new ReflectionClass( $class );

		if ( ! $reflector->isInstantiable() ) {
			throw new \Exception( "Class {$class} is not instantiable." );
		}

		$constructor = $reflector->getConstructor();
		if ( is_null( $constructor ) ) {
			return new $class();
		}

		$parameters   = $constructor->getParameters();
		$dependencies = array();

		foreach ( $parameters as $param ) {
			$type = $param->getType();
			if ( is_null( $type ) ) {
				throw new \Exception( "Cannot resolve untyped parameter \${$param->getName()}." );
			}

			$dependency_class = $type->getName();
			$dependencies[]   = $this->make( $dependency_class );
		}

		return $reflector->newInstanceArgs( $dependencies );
	}
}
