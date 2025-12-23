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

use Tutor\Components\Constants\Variant;

defined( 'ABSPATH' ) || exit;

/**
 * Badge Component Class.
 *
 * Example usage:
 * ```
 * Badge::make()
 *     ->label( 'Primary' )
 *     ->variant( Variant::PRIMARY )
 *     ->icon( '<svg>...</svg>' )
 *     ->render();
 *
 * Badge::make()
 *     ->label( 'Points: 20' )
 *     ->variant( Variant::SECONDARY )
 *     ->circle()
 *     ->render();
 * ```
 *
 * @since 4.0.0
 */
class Badge extends BaseComponent {

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
	protected $variant = Variant::PRIMARY;

	/**
	 * Whether badge has circle style.
	 *
	 * @since 4.0.0
	 *
	 * @var bool
	 */
	protected $circle = false;

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
	 * @param string $variant Badge variant (primary|pending|completed|cancelled|secondary|exception).
	 *
	 * @return $this
	 */
	public function variant( $variant ) {
		$this->variant = esc_attr( $variant );
		return $this;
	}

	/**
	 * Enable circle style for badge.
	 *
	 * @since 4.0.0
	 *
	 * @return $this
	 */
	public function circle() {
		$this->circle = true;
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
		$classes = sprintf(
			'tutor-badge tutor-badge-%s',
			esc_attr( $this->variant )
		);

		if ( $this->circle ) {
			$classes .= ' tutor-badge-circle';
		}

		// Merge with any custom class attribute.
		$this->attributes['class'] = trim( "{$classes} " . ( $this->attributes['class'] ?? '' ) );

		$attributes = $this->render_attributes();

		// Build icon HTML if exists.
		$icon_html = '';
		if ( ! empty( $this->icon ) ) {
			if ( false !== strpos( $this->icon, '<svg' ) ) {
				$icon_html = $this->icon;
			} else {
				ob_start();
				tutor_utils()->render_svg_icon( $this->icon, $this->icon_width, $this->icon_height );
				$icon_html = ob_get_clean();
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
