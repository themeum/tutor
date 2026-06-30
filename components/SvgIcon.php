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
 *     ->color( Color::SECONDARY )
 *     ->render();
 * ```
 *
 * Note: The color() property only works with the version 4 design system tokens.
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
	 * Note: this only works with the version 4 design system tokens.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	protected $color = '';

	/**
	 * Ignore kids mode variant.
	 *
	 * @since 4.0.0
	 *
	 * @var bool
	 */
	protected $ignore_kids = false;

	/**
	 * Flip the icon in RTL.
	 *
	 * @since 4.0.0
	 *
	 * @var bool
	 */
	protected $flip_rtl = false;

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
	 * Note: this only works with the version 4 design system tokens.
	 *
	 * @since 4.0.0
	 *
	 * @param string $color Icon color (use \Tutor\Components\Constants\Color).
	 *
	 * @return $this
	 */
	public function color( string $color ): self {
		$this->color = $color;
		return $this;
	}

	/**
	 * Set to ignore kids variant.
	 *
	 * @since 4.0.0
	 *
	 * @param bool $ignore_kids Ignore kids mode variant.
	 *
	 * @return $this
	 */
	public function ignore_kids( bool $ignore_kids = true ): self {
		$this->ignore_kids = $ignore_kids;
		return $this;
	}

	/**
	 * Flip the icon in RTL.
	 *
	 * Use this only for directional icons such as arrows,
	 * chevrons and pagination controls.
	 *
	 * @since 4.0.0
	 *
	 * @return $this
	 */
	public function flip_rtl(): self {
		$this->flip_rtl = true;
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

		// If learning mode is kids, check kids folder first.
		if ( tutor_utils()->is_kids_mode() && ! $this->ignore_kids ) {
			$kids_path = tutor()->path . 'assets/icons/kids/' . $this->name . '.svg';
			if ( file_exists( $kids_path ) ) {
				$icon_path = $kids_path;
			}
		}

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

		if ( $this->flip_rtl ) {
			$this->attributes['class'] = trim(
				( $this->attributes['class'] ?? '' ) . ' tutor-icon-flip-rtl'
			);
		}

		$attributes = $this->get_attributes_string();

		$this->component_string = sprintf(
			'<svg %s>%s</svg>',
			$attributes,
			$inner_svg
		);

		return $this->component_string;
	}
}
