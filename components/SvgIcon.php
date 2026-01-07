<?php
/**
 * SvgIcon Component Class.
 *
 * Provides a fluent builder for rendering SVG icons.
 *
 * @package Tutor\Components
 * @author Themeum
 * @link https://themeum.com
 * @since 4.0.0
 */

namespace Tutor\Components;

defined( 'ABSPATH' ) || exit;

/**
 * SvgIcon Component Class.
 *
 * Example usage:
 * ```
 * SvgIcon::make()
 *     ->name( Icon::CHECK )
 *     ->size( 24 )
 *     ->render();
 *
 * SvgIcon::make()
 *     ->name( Icon::DELETE )
 *     ->size( 16 )
 *     ->attr( 'class', 'tutor-icon-secondary' )
 *     ->render();
 *
 * SvgIcon::make()
 *     ->name( Icon::DELETE )
 *     ->size( 16 )
 *     ->color( 'secondary' )
 *     ->render();
 * ```
 *
 * @since 4.0.0
 */
class SvgIcon extends BaseComponent {

	/**
	 * Icon name.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	protected $name = '';

	/**
	 * Icon width.
	 *
	 * @since 4.0.0
	 *
	 * @var int
	 */
	protected $width = 16;

	/**
	 * Icon height.
	 *
	 * @since 4.0.0
	 *
	 * @var int
	 */
	protected $height = 16;
	/**
	 * Icon color.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	protected $color = '';

	/**
	 * Set icon name.
	 *
	 * @since 4.0.0
	 *
	 * @param string $name Icon name.
	 *
	 * @return $this
	 */
	public function name( string $name ): self {
		$this->name = $name;
		return $this;
	}

	/**
	 * Set icon width.
	 *
	 * @since 4.0.0
	 *
	 * @param int $width Icon width.
	 *
	 * @return $this
	 */
	public function width( int $width ): self {
		$this->width = $width;
		return $this;
	}

	/**
	 * Set icon height.
	 *
	 * @since 4.0.0
	 *
	 * @param int $height Icon height.
	 *
	 * @return $this
	 */
	public function height( int $height ): self {
		$this->height = $height;
		return $this;
	}

	/**
	 * Set icon size (both width and height).
	 *
	 * @since 4.0.0
	 *
	 * @param int $size Icon size.
	 *
	 * @return $this
	 */
	public function size( int $size ): self {
		$this->width  = $size;
		$this->height = $size;
		return $this;
	}

	/**
	 * Set icon color.
	 *
	 * @since 4.0.0
	 *
	 * @param string $color Icon color (idle|idle-inverse|hover|secondary|subdued|disabled|brand|exception1|exception2|success|exception4|exception5|caution|critical|warning).
	 *
	 * @return $this
	 */
	public function color( string $color ): self {
		$this->color = $color;
		return $this;
	}

	/**
	 * Set custom HTML attribute.
	 *
	 * @since 4.0.0
	 *
	 * @param string $key   Attribute name.
	 * @param string $value Attribute value.
	 *
	 * @return $this
	 */
	public function attr( $key, $value ) {
		$this->attributes[ $key ] = esc_attr( $value );
		return $this;
	}

	/**
	 * Get the final icon HTML.
	 *
	 * @since 4.0.0
	 *
	 * @return string HTML output.
	 */
	public function get(): string {
		if ( empty( $this->name ) ) {
			return '';
		}

		$icon_path = tutor()->path . 'assets/icons/' . $this->name . '.svg';
		if ( ! file_exists( $icon_path ) ) {
			return '';
		}

		$svg = file_get_contents( $icon_path );
		if ( ! $svg ) {
			return '';
		}

		preg_match( '/<svg[^>]*viewBox="([^"]+)"[^>]*>(.*?)<\/svg>/is', $svg, $matches );
		if ( ! $matches ) {
			return '';
		}

		list( $svg_tag, $view_box, $inner_svg ) = $matches;

		$this->attributes['width']       = $this->width;
		$this->attributes['height']      = $this->height;
		$this->attributes['viewBox']     = $view_box;
		$this->attributes['fill']        = $this->attributes['fill'] ?? 'none';
		$this->attributes['role']        = $this->attributes['role'] ?? 'presentation';
		$this->attributes['aria-hidden'] = $this->attributes['aria-hidden'] ?? 'true';

		if ( ! empty( $this->color ) ) {
			$color_class               = "tutor-icon-{$this->color}";
			$this->attributes['class'] = trim( ( $this->attributes['class'] ?? '' ) . " {$color_class}" );
		}

		$attributes = $this->render_attributes();

		$this->component_string = sprintf(
			'<svg %s>%s</svg>',
			$attributes,
			$inner_svg
		);

		return $this->component_string;
	}
}
