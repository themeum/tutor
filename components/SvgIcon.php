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
 * ```php
 * // Render icon at default size (16×16)
 * SvgIcon::make()
 *     ->name( Icon::CHECK )
 *     ->render();
 *
 * // Render with explicit size
 * SvgIcon::make()
 *     ->name( Icon::CHECK )
 *     ->size( 24 )
 *     ->render();
 *
 * // Render with independent width and height
 * SvgIcon::make()
 *     ->name( Icon::DELETE )
 *     ->width( 20 )
 *     ->height( 24 )
 *     ->render();
 *
 * // Render with a design-system color token
 * SvgIcon::make()
 *     ->name( Icon::DELETE )
 *     ->size( 16 )
 *     ->color( Color::SECONDARY )
 *     ->render();
 *
 * // Render with a custom CSS class
 * SvgIcon::make()
 *     ->name( Icon::STAR_FILL )
 *     ->size( 20 )
 *     ->attr( 'class', 'tutor-icon-warning' )
 *     ->render();
 *
 * // Render directional icon that flips in RTL layouts
 * SvgIcon::make()
 *     ->name( Icon::CHEVRON_RIGHT )
 *     ->size( 16 )
 *     ->flip_rtl()
 *     ->render();
 *
 * // Ignore kids-mode icon override (always use default icon)
 * SvgIcon::make()
 *     ->name( Icon::PLAY_LINE )
 *     ->size( 24 )
 *     ->ignore_kids()
 *     ->render();
 *
 * // Retrieve HTML string without echoing
 * $html = SvgIcon::make()->name( Icon::DELETE )->size( 16 )->color( Color::CRITICAL )->get();
 *
 * // Multiple attrs
 * SvgIcon::make()
 *     ->name( Icon::SPINNER )
 *     ->size( 14 )
 *     ->attr( 'class', 'tutor-animate-spin' )
 *     ->attr( 'aria-label', 'Loading' )
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
	 * @param bool $flip Whether to flip in RTL.
	 *
	 * @return $this
	 */
	public function flip_rtl( bool $flip = true ): self {
		$this->flip_rtl = $flip;
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
