<?php
/**
 * Tutor Component: Button
 *
 * Provides a fluent builder for rendering buttons with
 * different sizes, colors, and styles.
 *
 * @package Tutor\Components
 * @since 4.0.0
 */

namespace Tutor\Components;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Button Component Class.
 *
 * Example usage:
 * ```
 * echo Button::make()
 *     ->label( 'Enroll Now' )
 *     ->size( 'large' )
 *     ->color( 'primary' )
 *     ->icon( 'tutor-icon-play' )
 *     ->attr( 'data-id', 101 )
 *     ->render();
 * ```
 *
 * @since 4.0.0
 */
class Button {

	/**
	 * Button label text.
	 *
	 * @since 4.0.0
	 * @var string
	 */
	protected $label = '';

	/**
	 * Button size (small|medium|large).
	 *
	 * @since 4.0.0
	 * @var string
	 */
	protected $size = 'medium';

	/**
	 * Button color style (primary|secondary|success|danger|link).
	 *
	 * @since 4.0.0
	 * @var string
	 */
	protected $color = 'primary';

	/**
	 * Button HTML tag (button|a).
	 *
	 * @since 4.0.0
	 * @var string
	 */
	protected $tag = 'button';

	/**
	 * Button attributes.
	 *
	 * @since 4.0.0
	 * @var array
	 */
	protected $attributes = array();

	/**
	 * Icon class name.
	 *
	 * @since 4.0.0
	 * @var string|null
	 */
	protected $icon = null;

	/**
	 * Whether button is disabled.
	 *
	 * @since 4.0.0
	 * @var bool
	 */
	protected $disabled = false;

	/**
	 * Create a new Button instance.
	 *
	 * @since 4.0.0
	 * @return static
	 */
	public static function make() {
		return new static();
	}

	/**
	 * Set button label text.
	 *
	 * @since 4.0.0
	 *
	 * @param string $label Button label text.
	 * @return $this
	 */
	public function label( $label ) {
		$this->label = esc_html( $label );
		return $this;
	}

	/**
	 * Set button size.
	 *
	 * @since 4.0.0
	 *
	 * @param string $size Button size (small|medium|large).
	 * @return $this
	 */
	public function size( $size ) {
		$allowed = array( 'small', 'medium', 'large' );
		if ( in_array( $size, $allowed, true ) ) {
			$this->size = $size;
		}
		return $this;
	}

	/**
	 * Set button color style.
	 *
	 * @since 4.0.0
	 *
	 * @param string $color Button color (primary|secondary|success|danger|link).
	 * @return $this
	 */
	public function color( $color ) {
		$this->color = sanitize_html_class( $color );
		return $this;
	}

	/**
	 * Set custom HTML attribute.
	 *
	 * @since 4.0.0
	 *
	 * @param string $key   Attribute name.
	 * @param string $value Attribute value.
	 * @return $this
	 */
	public function attr( $key, $value ) {
		$this->attributes[ $key ] = esc_attr( $value );
		return $this;
	}

	/**
	 * Add an icon before the button label.
	 *
	 * @since 4.0.0
	 *
	 * @param string $icon_class Icon class name.
	 * @return $this
	 */
	public function icon( $icon_class ) {
		$this->icon = sanitize_html_class( $icon_class );
		return $this;
	}

	/**
	 * Set the HTML tag for rendering.
	 *
	 * @since 4.0.0
	 *
	 * @param string $tag HTML tag (button|a).
	 *
	 * @return $this
	 */
	public function tag( $tag ) {
		if ( in_array( $tag, array( 'a', 'button' ), true ) ) {
			$this->tag = $tag;
		}
		return $this;
	}

	/**
	 * Set button disabled state.
	 *
	 * @since 4.0.0
	 *
	 * @param bool $disabled Whether the button is disabled.
	 *
	 * @return $this
	 */
	public function disabled( $disabled = true ) {
		$this->disabled = (bool) $disabled;
		return $this;
	}

	/**
	 * Generate HTML attributes string.
	 *
	 * @since 4.0.0
	 *
	 * @return string
	 */
	protected function build_attributes() {
		$attrs = array();
		foreach ( $this->attributes as $key => $value ) {
			$attrs[] = sprintf( '%s="%s"', esc_attr( $key ), esc_attr( $value ) );
		}
		return implode( ' ', $attrs );
	}

	/**
	 * Render the final button HTML.
	 *
	 * @since 4.0.0
	 *
	 * @return string HTML output.
	 */
	public function render() {
		$classes = sprintf(
			'tutor-btn tutor-btn-%1$s tutor-btn-%2$s',
			esc_attr( $this->color ),
			esc_attr( $this->size )
		);

		if ( $this->disabled ) {
			$this->attributes['disabled'] = 'disabled';
			$classes                     .= ' is-disabled';
		}

		$this->attributes['class'] = trim( "{$classes} " . ( $this->attributes['class'] ?? '' ) );

		$attributes = $this->build_attributes();

		$icon_html = $this->icon ? sprintf( '<i class="%s"></i> ', esc_attr( $this->icon ) ) : '';

		return sprintf(
			'<%1$s %2$s>%3$s%4$s</%1$s>',
			esc_attr( $this->tag ),
			$attributes,
			$icon_html,
			$this->label
		);
	}
}
