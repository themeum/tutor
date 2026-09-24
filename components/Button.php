<?php
/**
 * Tutor Component: Button
 *
 * Provides a fluent builder for rendering buttons with
 * different sizes, variants, and styles.
 *
 * @package Tutor\Components
 * @author Themeum
 * @link https://themeum.com
 * @since 4.0.0
 */

namespace Tutor\Components;

use Tutor\Components\Constants\Size;
use Tutor\Components\Constants\Variant;

defined( 'ABSPATH' ) || exit;

/**
 * Button Component Class.
 *
 * Example usage:
 * ```
 * // Primary button (default)
 * Button::make()
 *     ->label( 'Enroll Now' )
 *     ->render();
 *
 * // Button with size and variant
 * Button::make()
 *     ->label( 'Save Changes' )
 *     ->size( Size::LARGE )
 *     ->variant( Variant::PRIMARY )
 *     ->render();
 *
 * // Button with left icon
 * Button::make()
 *     ->label( 'Add Course' )
 *     ->icon( Icon::PLUS )
 *     ->size( Size::MEDIUM )
 *     ->variant( Variant::PRIMARY )
 *     ->render();
 *
 * // Button with right icon and custom icon size/color
 * Button::make()
 *     ->label( 'Download' )
 *     ->icon( Icon::DOWNLOAD_2, 'right', 18, Color::BRAND )
 *     ->variant( Variant::OUTLINE )
 *     ->render();
 *
 * // Icon-only button (no visible label, uses aria-label)
 * Button::make()
 *     ->label( 'Delete' )
 *     ->icon( Icon::DELETE_2 )
 *     ->icon_only()
 *     ->variant( Variant::GHOST )
 *     ->size( Size::SMALL )
 *     ->render();
 *
 * // Disabled button
 * Button::make()
 *     ->label( 'Submit' )
 *     ->variant( Variant::PRIMARY )
 *     ->disabled()
 *     ->render();
 *
 * // Block (full-width) button
 * Button::make()
 *     ->label( 'Continue' )
 *     ->variant( Variant::PRIMARY )
 *     ->block()
 *     ->render();
 *
 * // Link-styled button rendered as <a> tag
 * Button::make()
 *     ->label( 'View Course' )
 *     ->variant( Variant::LINK )
 *     ->tag( 'a' )
 *     ->attr( 'href', get_permalink( $course_id ) )
 *     ->render();
 *
 * // Destructive button
 * Button::make()
 *     ->label( 'Delete Course' )
 *     ->variant( Variant::DESTRUCTIVE )
 *     ->size( Size::SMALL )
 *     ->render();
 *
 * // Secondary/outline button with data attribute
 * Button::make()
 *     ->label( 'Cancel' )
 *     ->variant( Variant::SECONDARY )
 *     ->attr( 'data-id', $course_id )
 *     ->render();
 *
 * // Button with RTL-flippable directional icon
 * Button::make()
 *     ->label( 'Next' )
 *     ->icon( Icon::CHEVRON_RIGHT, 'right' )
 *     ->flip_rtl()
 *     ->render();
 *
 * // Retrieve HTML string without echoing
 * $html = Button::make()->label( 'Get Started' )->variant( Variant::PRIMARY )->get();
 * ```
 *
 * @since 4.0.0
 */
class Button extends BaseComponent {

	/**
	 * Button label text.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	protected $label = '';

	/**
	 * Button size (small|medium|large).
	 *
	 * @since 4.0.0
	 *
	 * @see Size constants
	 *
	 * @var string
	 */
	protected $size = Size::MEDIUM;

	/**
	 * Button variant style (primary|secondary, etc).
	 *
	 * @since 4.0.0
	 *
	 * @see Size constants
	 *
	 * @var string
	 */
	protected $variant = Variant::PRIMARY;

	/**
	 * Button HTML tag (button|a).
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	protected $tag = 'button';

	/**
	 * Button attributes.
	 *
	 * @since 4.0.0
	 *
	 * @var array
	 */
	protected $attributes = array();

	/**
	 * The SVG icon name or markup.
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
	protected $icon_size = 16;

	/**
	 * Icon color.
	 *
	 * @var string
	 */
	protected $icon_color = '';

	/**
	 * Button icon position
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	private const POSITION_LEFT  = 'left';
	private const POSITION_RIGHT = 'right';

	/**
	 * The icon position relative to label. Accepts 'left' or 'right'.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	protected $icon_position = 'left';

	/**
	 * Icon attributes.
	 *
	 * @var array
	 */
	protected $icon_attributes = array();

	/**
	 * Whether to flip the button icon in RTL.
	 *
	 * @since 4.0.0
	 *
	 * @var bool
	 */
	protected $flip_rtl = false;

	/**
	 * Whether button is disabled.
	 *
	 * @since 4.0.0
	 *
	 * @var bool
	 */
	protected $disabled = false;

	/**
	 * Whether button is an icon-only button.
	 *
	 * @since 4.0.0
	 *
	 * @var bool
	 */
	protected $icon_only = false;

	/**
	 * Whether button is a block button.
	 *
	 * @since 4.0.0
	 *
	 * @var bool
	 */
	protected $block = false;

	/**
	 * Set button label text.
	 *
	 * @since 4.0.0
	 *
	 * @param string $label Button label text.
	 *
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
	 *
	 * @return $this
	 */
	public function size( $size ) {
		$allowed = $this->get_allowed_sizes();
		if ( in_array( $size, $allowed, true ) ) {
			$this->size = $size;
		}
		return $this;
	}

	/**
	 * Set button block state.
	 *
	 * @since 4.0.0
	 *
	 * @param bool $block Whether the button is a block button.
	 *
	 * @return $this
	 */
	public function block( bool $block = true ): self {
		$this->block = $block;
		return $this;
	}

	/**
	 * Set button variant style.
	 *
	 * @since 4.0.0
	 *
	 * @param string $variant Button variant (primary|secondary|success|danger|link).
	 *
	 * @return $this
	 */
	public function variant( $variant ) {
		$this->variant = sanitize_html_class( $variant );
		return $this;
	}

	/**
	 * Set the SVG icon for the button.
	 *
	 * @since 4.0.0
	 *
	 * @param string $icon       SVG icon name or markup.
	 * @param string $position   Optional. Icon position: 'left' or 'right'.
	 * @param int    $size       Optional. Icon size (width and height).
	 * @param string $color      Optional. Icon color (use \Tutor\Components\Constants\Color).
	 * @param array  $attributes Optional. Icon attributes.
	 *
	 * @return $this
	 */
	public function icon( string $icon, string $position = 'left', int $size = 16, string $color = '', array $attributes = array() ): self {
		$this->icon            = $icon;
		$this->icon_position   = in_array( $position, array( self::POSITION_LEFT, self::POSITION_RIGHT ), true ) ? $position : 'left';
		$this->icon_size       = $size;
		$this->icon_color      = $color;
		$this->icon_attributes = $attributes;
		return $this;
	}

	/**
	 * Set the HTML tag for rendering.
	 *
	 * @since 4.0.0
	 *
	 * @param bool $icon_only Whether the button is icon-only.
	 *
	 * @return $this
	 */
	public function icon_only( bool $icon_only = true ): self {
		$this->icon_only = $icon_only;
		return $this;
	}

	/**
	 * Flip the button icon in RTL.
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
	 * Allowed sizes
	 *
	 * @since 4.0.0
	 *
	 * @return array
	 */
	public function get_allowed_sizes() {
		return array(
			Size::SMALL,
			Size::MEDIUM,
			Size::LARGE,
			Size::X_SMALL,
		);
	}

	/**
	 * Get the final button HTML.
	 *
	 * @since 4.0.0
	 *
	 * @return string HTML output.
	 */
	public function get(): string {
		$classes = sprintf(
			'tutor-btn tutor-btn-%1$s tutor-btn-%2$s',
			esc_attr( $this->variant ),
			esc_attr( $this->size )
		);

		if ( $this->disabled ) {
			$this->attributes['disabled'] = 'disabled';
			$classes                     .= ' disabled';
		}

		if ( $this->block ) {
			$classes .= ' tutor-btn-block';
		}

		if ( $this->icon_only ) {
			$classes .= ' tutor-btn-icon';
			if ( ! empty( $this->label ) && empty( $this->attributes['aria-label'] ) ) {
				$this->attributes['aria-label'] = $this->label;
			}
		}

		$this->attributes['class'] = trim( "{$classes} " . ( $this->attributes['class'] ?? '' ) );

		$attributes = $this->get_attributes_string();

		// Prepare icon HTML if exists.
		$icon_html = '';
		if ( ! empty( $this->icon ) ) {
			if ( false !== strpos( $this->icon, '<svg' ) ) {
				$icon_html = $this->icon;
			} else {
				ob_start();
				SvgIcon::make()
					->name( $this->icon )
					->size( $this->icon_size )
					->color( $this->icon_color )
					->flip_rtl( $this->flip_rtl )
					->attrs( $this->icon_attributes )
					->render();
				$icon_html = ob_get_clean();
			}
		}

		if ( ! empty( $icon_html ) && empty( $this->label ) ) {
			$this->attributes['class'] .= ' tutor-btn-icon';
			// Re-render attributes to include updated class.
			$attributes = $this->get_attributes_string();
		}

		// Build button inner HTML depending on icon position.
		if ( $this->icon_only ) {
			$content = $icon_html;
		} else {
			$content = self::POSITION_RIGHT === ( $this->icon_position ? $this->icon_position : self::POSITION_LEFT )
				? sprintf( '%1$s%2$s', esc_html( $this->label ), $icon_html )
				: sprintf( '%1$s%2$s', $icon_html, esc_html( $this->label ) );
		}

		$this->component_string = sprintf(
			'<%1$s %2$s>%3$s</%1$s>',
			esc_attr( $this->tag ),
			$attributes,
			$content
		);

		return $this->component_string;
	}
}
