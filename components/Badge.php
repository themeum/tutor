<?php
/**
 * Badge Component Class.
 *
 * Provides a fluent builder for rendering badges with
 * different variants and styles.
 *
 * @package Tutor\Components
 * @author Themeum
 * @link https://themeum.com
 * @since 4.0.0
 */

namespace Tutor\Components;

defined( 'ABSPATH' ) || exit;

/**
 * Badge Component Class.
 *
 * Example usage:
 * ```
 * // Primary badge
 * Badge::make()
 *     ->label( 'New' )
 *     ->variant( Badge::PRIMARY )
 *     ->render();
 *
 * // Badge with SVG icon markup
 * Badge::make()
 *     ->label( 'Certified' )
 *     ->variant( Badge::SUCCESS )
 *     ->icon( '<svg>...</svg>' )
 *     ->render();
 *
 * // Badge with icon name (SvgIcon slug)
 * Badge::make()
 *     ->label( 'Warning' )
 *     ->variant( Badge::WARNING )
 *     ->icon( Icon::WARNING, 14, 14 )
 *     ->render();
 *
 * // Badge with icon as URL (img tag is generated)
 * Badge::make()
 *     ->label( 'Verified' )
 *     ->variant( Badge::SUCCESS_SOLID )
 *     ->icon( 'https://example.com/check.png', 16, 16 )
 *     ->render();
 *
 * // Rounded (pill) badge
 * Badge::make()
 *     ->label( 'Points: 20' )
 *     ->variant( Badge::HIGHLIGHT )
 *     ->rounded()
 *     ->render();
 *
 * // Error / disabled badge without icon
 * Badge::make()
 *     ->label( 'Inactive' )
 *     ->variant( Badge::DISABLED )
 *     ->render();
 *
 * // Badge with no variant (default style) and extra attrs
 * Badge::make()
 *     ->label( 'Draft' )
 *     ->attr( 'data-status', 'draft' )
 *     ->render();
 *
 * // Retrieve HTML string without echoing
 * $html = Badge::make()->label( 'Info' )->variant( Badge::INFO )->get();
 * ```
 *
 * @since 4.0.0
 */
class Badge extends BaseComponent {

	/**
	 * Variant constants.
	 *
	 * @since 4.0.0
	 */
	public const INFO          = 'info';
	public const PRIMARY       = 'primary';
	public const WARNING       = 'warning';
	public const SUCCESS       = 'success';
	public const SUCCESS_SOLID = 'success-solid';
	public const ERROR         = 'error';
	public const HIGHLIGHT     = 'highlight';
	public const DISABLED      = 'disabled';

	/**
	 * Badge label text.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	protected $label = '';

	/**
	 * Badge variant style.
	 *
	 * @since 4.0.0
	 *
	 * @see Variant constants.
	 *
	 * @var string
	 */
	protected $variant = '';

	/**
	 * Whether badge has rounded style.
	 *
	 * @since 4.0.0
	 *
	 * @var bool
	 */
	protected $rounded = false;

	/**
	 * Badge attributes.
	 *
	 * @since 4.0.0
	 *
	 * @var array
	 */
	protected $attributes = array();

	/**
	 * The SVG icon markup.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	protected $icon = '';

	/**
	 * Sort Icon width.
	 *
	 * @var int
	 */
	protected $icon_width = 16;

	/**
	 * Sort Icon height.
	 *
	 * @var int
	 */
	protected $icon_height = 16;

	/**
	 * Prefix content (e.g., subdued text before label).
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	protected $prefix = '';

	/**
	 * Additional CSS classes.
	 *
	 * @since 4.0.0
	 *
	 * @var array
	 */
	protected $extra_classes = array();

	/**
	 * Set badge label text.
	 *
	 * @since 4.0.0
	 *
	 * @param string $label Badge label text.
	 *
	 * @return $this
	 */
	public function label( $label ) {
		$this->label = $label;
		return $this;
	}

	/**
	 * Set badge variant style.
	 *
	 * @since 4.0.0
	 *
	 * @param string $variant Badge variant (primary|info|warning|success|success-solid|error|highlight).
	 *
	 * @return $this
	 */
	public function variant( $variant ) {
		$this->variant = esc_attr( $variant );
		return $this;
	}

	/**
	 * Enable rounded style for badge.
	 *
	 * @since 4.0.0
	 *
	 * @return $this
	 */
	public function rounded() {
		$this->rounded = true;
		return $this;
	}

	/**
	 * Set the SVG icon for the badge.
	 *
	 * @since 4.0.0
	 *
	 * @param string $icon   SVG icon name or markup.
	 * @param int    $width  Optional. Icon width.
	 * @param int    $height Optional. Icon height.
	 *
	 * @return $this
	 */
	public function icon( string $icon, int $width = 16, int $height = 16 ): self {
		$this->icon        = $icon;
		$this->icon_width  = $width;
		$this->icon_height = $height;
		return $this;
	}

	/**
	 * Get the final badge HTML.
	 *
	 * @since 4.0.0
	 *
	 * @return string HTML output.
	 */
	public function get(): string {
		$classes = ! empty( $this->variant )
		? sprintf( 'tutor-badge tutor-badge-%s', esc_attr( $this->variant ) )
		: 'tutor-badge';

		if ( $this->rounded ) {
			$classes .= ' tutor-badge-rounded';
		}

		// Merge with any custom class attribute.
		$this->attributes['class'] = trim( "{$classes} " . ( $this->attributes['class'] ?? '' ) );

		$attributes = $this->get_attributes_string();

		// Build icon HTML if exists.
		$icon_html = '';
		if ( ! empty( $this->icon ) ) {
			if ( false !== strpos( $this->icon, '<svg' ) ) {
				$icon_html = $this->icon;
			} elseif ( filter_var( $this->icon, FILTER_VALIDATE_URL ) !== false ) {
				$icon_html = sprintf( '<img src="%s" width="%s" height="%s" alt="%s" />', esc_url( $this->icon ), esc_attr( $this->icon_width ), esc_attr( $this->icon_height ), esc_attr__( 'Badge Icon', 'tutor' ) );
			} else {
				$icon_html = SvgIcon::make()->name( $this->icon )->width( $this->icon_width )->height( $this->icon_height )->get();
			}
		}

		// Build content.
		$content = sprintf(
			'%s%s',
			$icon_html,
			esc_html( $this->label )
		);

		$this->component_string = sprintf(
			'<span %s>%s</span>',
			$attributes,
			$content
		);

		return $this->component_string;
	}
}
