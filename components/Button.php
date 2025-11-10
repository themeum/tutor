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
	 * The SVG icon markup.
	 *
	 * @since 4.0.0
	 * @var string
	 */
	protected $icon = '';

	/**
	 * The icon position relative to label. Accepts 'left' or 'right'.
	 *
	 * @since 4.0.0
	 * @var string
	 */
	protected $icon_position = 'left';

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
	 * Set the SVG icon for the button.
	 *
	 * @since 4.0.0
	 *
	 * @param string $svg SVG markup (already escaped and sanitized).
	 * @param string $position Optional. Icon position: 'left' or 'right'.
	 * @return self
	 */
	public function icon( string $svg, string $position = 'left' ): self {
		$this->icon          = $svg;
		$this->icon_position = in_array( $position, array( 'left', 'right' ), true ) ? $position : 'left';
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
	public function back_render() {
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

		// Prepare icon HTML if exists.
		$icon_html = '';
		if ( ! empty( $this->icon ) ) {
			$icon_html = sprintf(
				'%1$s',
				$this->icon // SVG is expected to be escaped markup.
			);
		}

		// Build button inner HTML depending on icon position.
		$content = 'right' === ( $this->icon_position ?? 'left' )
			? sprintf( '%1$s%2$s', esc_html( $this->label ), $icon_html )
			: sprintf( '%1$s%2$s', $icon_html, esc_html( $this->label ) );

		return sprintf(
			'<%1$s %2$s>%3$s</%1$s>',
			esc_attr( $this->tag ),
			$attributes,
			$content
		);
	}

}
