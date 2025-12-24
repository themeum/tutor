<?php
/**
 * Tutor Component: BaseComponent
 *
 * Provides a base abstract class for all Tutor UI components.
 * Handles attribute management and basic HTML escaping.
 *
 * @package Tutor\Components
 * @author Themeum
 * @link https://themeum.com
 * @since 4.0.0
 */

namespace Tutor\Components;

use Tutor\Components\Contracts\ComponentInterface;

defined( 'ABSPATH' ) || exit;

/**
 * Abstract Base Component class.
 *
 * Defines shared behavior for all UI components.
 *
 * @since 4.0.0
 */
abstract class BaseComponent {

	/**
	 * Keep the component as string
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	protected $component_string = '';

	/**
	 * Create a new component instance.
	 *
	 * @since 4.0.0
	 *
	 * @return static
	 */
	public static function make() {
		return new static();
	}

	/**
	 * HTML attributes for the component.
	 *
	 * Example:
	 * [
	 *   'id'    => 'tutor-button',
	 *   'class' => 'tutor-btn tutor-btn-primary'
	 * ]
	 *
	 * @since 4.0.0
	 *
	 * @var array
	 */
	protected $attributes = array();

	/**
	 * Set or merge multiple HTML attributes.
	 *
	 * This method allows components to attach
	 * dynamic attributes such as data-* or aria-*.
	 *
	 * @since 4.0.0
	 *
	 * @param array $attrs Key–value pairs of HTML attributes.
	 *
	 * @return self
	 */
	public function attrs( array $attrs ): self {
		$this->attributes = array_merge( $this->attributes, $attrs );
		return $this;
	}

	/**
	 * Compile the stored attributes into an HTML string.
	 *
	 * Converts attributes array into a properly escaped
	 * string suitable for rendering in HTML tags.
	 *
	 * Example output:
	 * `id="tutor-btn" class="tutor-btn tutor-btn-primary"`
	 *
	 * @since 4.0.0
	 *
	 * @return string Escaped and concatenated attributes.
	 */
	protected function render_attributes(): string {
		$compiled = array();

		foreach ( $this->attributes as $key => $value ) {
			$compiled[] = sprintf( '%s="%s"', esc_attr( $key ), esc_attr( $value ) );
		}

		return implode( ' ', $compiled );
	}

	/**
	 * Escape a value for safe HTML output.
	 *
	 * Wrapper for WordPress `esc_html()` function.
	 *
	 * @since 4.0.0
	 *
	 * @param mixed  $value Value to escape.
	 * @param string $esc_fn Callable esc func.
	 *
	 * @return string Escaped string.
	 */
	protected function esc( $value, $esc_fn = 'esc_html' ): string {
		return call_user_func( $esc_fn, $value );
	}

	/**
	 * Get the component output as an HTML string.
	 *
	 * Note: Child classes must implement this method and are responsible
	 * for preparing and properly sanitizing the component’s HTML output.
	 *
	 * @since 4.0.0
	 *
	 * @return string The component HTML output.
	 */
	abstract public function get(): string;

	/**
	 * Render the component
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function render(): void {
		// phpcs:ignore -- Sanitization is performed within each child class’s `get` method implementation.
		echo $this->get();
	}

}
