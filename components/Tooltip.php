<?php
/**
 * Tutor Component: Tooltip
 *
 * Provides a fluent builder for rendering tooltips with
 * different placements, sizes, and triggers.
 *
 * @package Tutor\Components
 * @author Themeum
 * @link https://themeum.com
 * @since 4.0.0
 */

namespace Tutor\Components;

defined( 'ABSPATH' ) || exit;

use Tutor\Components\Constants\Size;

/**
 * Tooltip Component Class.
 *
 * Example usage:
 * ```
 * Tooltip::make()
 *     ->content( 'Helpful information' )
 *     ->placement( 'top' )
 *     ->size( Size::SMALL )
 *     ->trigger_element( '<button class="tutor-btn">Hover me</button>' )
 *     ->render();
 * ```
 *
 * @since 4.0.0
 */
class Tooltip extends BaseComponent {

	/**
	 * Placement constants.
	 */
	public const PLACEMENT_TOP    = 'top';
	public const PLACEMENT_BOTTOM = 'bottom';
	public const PLACEMENT_START  = 'start';
	public const PLACEMENT_END    = 'end';

	/**
	 * Arrow alignment constants.
	 */
	public const ARROW_START  = 'start';
	public const ARROW_CENTER = 'center';
	public const ARROW_END    = 'end';

	/**
	 * Trigger constants.
	 */
	public const HOVER = 'hover';
	public const CLICK = 'click';
	public const FOCUS = 'focus';

	/**
	 * Tooltip content (HTML or text).
	 *
	 * @var string
	 */
	protected $content = '';

	/**
	 * Tooltip placement (top|bottom|start|end).
	 *
	 * @var string
	 */
	protected $placement = self::PLACEMENT_TOP;

	/**
	 * Tooltip size (small|large).
	 *
	 * @var string
	 */
	protected $size = Size::SMALL;

	/**
	 * Arrow alignment (start|center|end).
	 *
	 * @var string
	 */
	protected $arrow = self::ARROW_START;

	/**
	 * Tooltip trigger type (hover|focus|click).
	 *
	 * @var string
	 */
	protected $trigger = self::HOVER;

	/**
	 * The element that triggers the tooltip.
	 *
	 * @var string
	 */
	protected $trigger_element = '';

	/**
	 * Distance from the trigger in pixels.
	 *
	 * @var int
	 */
	protected $offset = 8;

	/**
	 * Delay in milliseconds for showing/hiding.
	 *
	 * @var array
	 */
	protected $delay = array(
		'show' => 0,
		'hide' => 0,
	);

	/**
	 * Allowed HTML tags and Alpine.js attributes for wp_kses sanitization.
	 *
	 * @since 4.0.0
	 *
	 * @var array
	 */
	protected $allow_alpine_attributes = array();

	/**
	 * Allowed Alpine.js HTML attributes.
	 *
	 * @since 4.0.0
	 *
	 * @var array
	 */
	private const ALLOWED_ALPINE_ATTRS = array(
		'x-data',
		'x-text',
		'x-show',
		'x-cloak',
		'x-ref',
		'x-transition',
	);

	/**
	 * Set the tooltip content.
	 *
	 * @param string $content HTML or text content.
	 *
	 * @return $this
	 */
	public function content( string $content ): self {
		$this->content = $content;
		return $this;
	}

	/**
	 * Set the tooltip placement.
	 *
	 * @param string $placement top|bottom|start|end.
	 *
	 * @return $this
	 */
	public function placement( string $placement ): self {
		$allowed = array(
			self::PLACEMENT_TOP,
			self::PLACEMENT_BOTTOM,
			self::PLACEMENT_START,
			self::PLACEMENT_END,
		);
		if ( in_array( $placement, $allowed, true ) ) {
			$this->placement = $placement;
		}
		return $this;
	}

	/**
	 * Set the tooltip size.
	 *
	 * @param string $size small|large.
	 *
	 * @return $this
	 */
	public function size( string $size ): self {
		$allowed = array( Size::SMALL, Size::LARGE );
		if ( in_array( $size, $allowed, true ) ) {
			$this->size = $size;
		}
		return $this;
	}

	/**
	 * Set the arrow alignment.
	 *
	 * @param string $arrow start|center|end.
	 *
	 * @return $this
	 */
	public function arrow( string $arrow ): self {
		$allowed = array(
			self::ARROW_START,
			self::ARROW_CENTER,
			self::ARROW_END,
		);
		if ( in_array( $arrow, $allowed, true ) ) {
			$this->arrow = $arrow;
		}
		return $this;
	}

	/**
	 * Set the trigger type.
	 *
	 * @param string $trigger hover|focus|click.
	 *
	 * @return $this
	 */
	public function trigger_on( string $trigger ): self {
		$allowed = array(
			self::HOVER,
			self::FOCUS,
			self::CLICK,
		);
		if ( in_array( $trigger, $allowed, true ) ) {
			$this->trigger = $trigger;
		}
		return $this;
	}

	/**
	 * Set the element that triggers the tooltip.
	 *
	 * @param string $trigger_element HTML for the trigger element.
	 *
	 * @return $this
	 */
	public function trigger_element( string $trigger_element ): self {
		$this->trigger_element = $trigger_element;
		return $this;
	}

	/**
	 * Set the offset.
	 *
	 * @param int $offset Distance in pixels.
	 *
	 * @return $this
	 */
	public function offset( int $offset ): self {
		$this->offset = $offset;
		return $this;
	}

	/**
	 * Set the show and hide delay.
	 *
	 * @param int $show Show delay in ms.
	 * @param int $hide Hide delay in ms.
	 *
	 * @return $this
	 */
	public function delay( int $show, int $hide = 0 ): self {
		$this->delay = array(
			'show' => $show,
			'hide' => $hide,
		);
		return $this;
	}

	/**
	 * Adds Alpine.js attributes to the wp_kses allow-list.
	 *
	 * @since 4.0.0
	 *
	 * @param array $attributes attribute map.
	 *
	 * @return $this
	 */
	public function allow_alpine_attributes( array $attributes ) {

		$this->allow_alpine_attributes = wp_kses_allowed_html( 'post' );

		foreach ( $attributes as $tag => $attr ) {
			$tag = sanitize_key( $tag );

			if ( ! isset( $this->allow_alpine_attributes[ $tag ][ $attr ] ) && in_array( $attr, self::ALLOWED_ALPINE_ATTRS, true ) ) {
				$this->allow_alpine_attributes[ $tag ][ $attr ] = true;
			}
		}

		return $this;
	}

	/**
	 * Returns the allowed HTML configuration for wp_kses().
	 *
	 * @since 4.0.0
	 *
	 * @return array
	 */
	private function allowed_attributes() {

		if ( empty( $this->allow_alpine_attributes ) ) {
			return wp_kses_allowed_html( 'post' );
		}

		return $this->allow_alpine_attributes;
	}

	/**
	 * Get the final tooltip HTML.
	 *
	 * @return string HTML output.
	 */
	public function get(): string {
		$config = array(
			'placement' => $this->placement,
			'trigger'   => $this->trigger,
			'size'      => $this->size,
			'arrow'     => $this->arrow,
			'offset'    => $this->offset,
			'delay'     => $this->delay,
		);

		// Prepare x-data dynamic config.
		$x_data = sprintf( 'tutorTooltip(%s)', wp_json_encode( $config ) );

		// Wrap the trigger element.
		// We use str_replace to inject x-ref="trigger" into the trigger element if possible,
		// or we can expect the user to provide it. But to be helpful, let's inject it.
		$trigger_html = $this->trigger_element;
		if ( ! empty( $trigger_html ) && false === strpos( $trigger_html, 'x-ref="trigger"' ) ) {
			// Find the first tag and add x-ref="trigger".
			$trigger_html = preg_replace( '/<([a-z0-9]+)/i', '<$1 x-ref="trigger"', $trigger_html, 1 );
		}

		$this->component_string = sprintf(
			'<div x-data="%1$s" class="tutor-tooltip-wrap %2$s" %3$s>
				%4$s
				<div x-ref="content" x-show="open" x-cloak x-transition class="tutor-tooltip">
					%5$s
				</div>
			</div>',
			esc_attr( $x_data ),
			esc_attr( $this->attributes['class'] ?? '' ),
			$this->get_attributes_string(),
			$trigger_html,
			wp_kses( $this->content, $this->allowed_attributes() )
		);

		return $this->component_string;
	}
}
