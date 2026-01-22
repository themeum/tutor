<?php
/**
 * Alert Component Class.
 *
 * Provides a fluent builder for rendering alerts with
 * different variants and actions.
 *
 * @package Tutor\Components
 * @author Themeum
 * @link https://themeum.com
 * @since 4.0.0
 */

namespace Tutor\Components;

defined( 'ABSPATH' ) || exit;

/**
 * Alert Component Class.
 *
 * Example usage:
 * ```
 * Alert::make()
 *     ->text( 'This is an alert' )
 *     ->variant( Alert::SUCCESS )
 *     ->icon( Icon::PRIME_CHECK_CIRCLE )
 *     ->action( '<button class="...">Resume</button>' )
 *     ->render();
 * ```
 *
 * @since 4.0.0
 */
class Alert extends BaseComponent {

	/**
	 * Variant constants.
	 *
	 * @since 4.0.0
	 */
	public const DEFAULT = 'default';
	public const INFO    = 'info';
	public const SUCCESS = 'success';
	public const WARNING = 'warning';
	public const ERROR   = 'error';

	/**
	 * Alert text content.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	protected $text = '';

	/**
	 * Alert variant style.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	protected $variant = self::DEFAULT;

	/**
	 * The SVG icon markup or name.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	protected $icon = '';

	/**
	 * Icon width.
	 *
	 * @var int
	 */
	protected $icon_width = 20;

	/**
	 * Icon height.
	 *
	 * @var int
	 */
	protected $icon_height = 20;

	/**
	 * Alert action HTML.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	protected $action = '';

	/**
	 * Set alert text content.
	 *
	 * @since 4.0.0
	 *
	 * @param string $text Alert text content.
	 *
	 * @return $this
	 */
	public function text( string $text ): self {
		$this->text = $text;
		return $this;
	}

	/**
	 * Set alert variant style.
	 *
	 * @since 4.0.0
	 *
	 * @param string $variant Alert variant (default|info|success|warning|error).
	 *
	 * @return $this
	 */
	public function variant( string $variant ): self {
		$this->variant = $variant;
		return $this;
	}

	/**
	 * Set the SVG icon for the alert.
	 *
	 * @since 4.0.0
	 *
	 * @param string $icon   SVG icon name or markup.
	 * @param int    $width  Optional. Icon width.
	 * @param int    $height Optional. Icon height.
	 *
	 * @return $this
	 */
	public function icon( string $icon, int $width = 20, int $height = 20 ): self {
		$this->icon        = $icon;
		$this->icon_width  = $width;
		$this->icon_height = $height;
		return $this;
	}

	/**
	 * Set alert action HTML.
	 *
	 * @since 4.0.0
	 *
	 * @param string $action Alert action HTML.
	 *
	 * @return $this
	 */
	public function action( string $action ): self {
		$this->action = $action;
		return $this;
	}

	/**
	 * Get the final alert HTML.
	 *
	 * @since 4.0.0
	 *
	 * @return string HTML output.
	 */
	public function get(): string {
		$classes = sprintf( 'tutor-alert tutor-alert-%s', esc_attr( $this->variant ) );

		// Merge with any custom class attribute.
		$this->attributes['class'] = trim( "{$classes} " . ( $this->attributes['class'] ?? '' ) );

		$attributes = $this->get_attributes_string();

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

		// Build content wrapper.
		$content_html = sprintf(
			'<div class="tutor-alert-content">
				%s
				<div class="tutor-alert-text">%s</div>
			</div>',
			! empty( $icon_html ) ? '<div class="tutor-alert-icon">' . $icon_html . '</div>' : '',
			$this->text // Allow HTML in text if needed, or we could esc_html here. Usually components like this might need small HTML in text.
		);

		// Build action wrapper.
		$action_html = ! empty( $this->action )
			? sprintf( '<div class="tutor-alert-action">%s</div>', $this->action )
			: '';

		$this->component_string = sprintf(
			'<div %s>%s%s</div>',
			$attributes,
			$content_html,
			$action_html
		);

		return $this->component_string;
	}
}
