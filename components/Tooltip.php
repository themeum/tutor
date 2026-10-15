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
 * ```php
 * // Basic tooltip on hover (top placement, default)
 * Tooltip::make()
 *     ->content( 'This is a helpful tip.' )
 *     ->trigger_element( '<button class="tutor-btn tutor-btn-ghost">Hover me</button>' )
 *     ->render();
 *
 * // Tooltip with specific placement and size
 * Tooltip::make()
 *     ->content( 'Detailed information here.' )
 *     ->placement( Tooltip::PLACEMENT_BOTTOM )
 *     ->size( Size::LARGE )
 *     ->trigger_element( '<span class="tutor-icon-wrapper">' . SvgIcon::make()->name( Icon::INFO )->size( 16 )->get() . '</span>' )
 *     ->render();
 *
 * // Tooltip triggered on focus (keyboard accessible)
 * Tooltip::make()
 *     ->content( 'This field requires a valid email.' )
 *     ->trigger_on( Tooltip::FOCUS )
 *     ->placement( Tooltip::PLACEMENT_TOP )
 *     ->trigger_element( '<input type="email" class="tutor-input" name="email">' )
 *     ->render();
 *
 * // Tooltip triggered on click
 * Tooltip::make()
 *     ->content( 'Copied to clipboard!' )
 *     ->trigger_on( Tooltip::CLICK )
 *     ->placement( Tooltip::PLACEMENT_END )
 *     ->trigger_element( '<button class="tutor-btn tutor-btn-outline">Copy</button>' )
 *     ->render();
 *
 * // Tooltip with arrow alignment (centered)
 * Tooltip::make()
 *     ->content( 'Centered arrow tip.' )
 *     ->arrow( Tooltip::ARROW_CENTER )
 *     ->placement( Tooltip::PLACEMENT_TOP )
 *     ->trigger_element( '<span>Hover</span>' )
 *     ->render();
 *
 * // Tooltip with custom offset distance
 * Tooltip::make()
 *     ->content( 'Offset from trigger by 16px.' )
 *     ->offset( 16 )
 *     ->trigger_element( '<button class="tutor-btn">Info</button>' )
 *     ->render();
 *
 * // Tooltip with show/hide delay
 * Tooltip::make()
 *     ->content( 'Appears after 300ms, hides after 200ms.' )
 *     ->delay( 300, 200 )
 *     ->trigger_element( '<span>Hover me</span>' )
 *     ->render();
 *
 * // Tooltip with rich HTML content
 * Tooltip::make()
 *     ->content( '<strong>Tip:</strong> Use <em>keyboard shortcuts</em> to save time.', array( 'strong' => array(), 'em' => array() ) )
 *     ->placement( Tooltip::PLACEMENT_BOTTOM )
 *     ->trigger_element( '<span>' . SvgIcon::make()->name( Icon::QUESTION_CIRCLE )->size( 16 )->get() . '</span>' )
 *     ->render();
 *
 * // Tooltip placed at start (left in LTR)
 * Tooltip::make()
 *     ->content( 'Left-side tooltip.' )
 *     ->placement( Tooltip::PLACEMENT_START )
 *     ->trigger_element( '<button class="tutor-btn">Start</button>' )
 *     ->render();
 *
 * // Retrieve HTML without echoing
 * $html = Tooltip::make()->content( 'Save your work' )->trigger_element( $btn_html )->get();
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
	 * Tooltip size (small|medium|large).
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
	 * Set and sanitize the tooltip content.
	 *
	 * @param string                             $content HTML or text content.
	 * @param array<string, array<string, bool>> $extra_tags Optional.
	 *        Additional HTML tags and attributes in KSES-compatible format.
	 *
	 * @return $this
	 */
	public function content( string $content, $extra_tags = array() ): self {

		$this->content = wp_kses( $content, $this->get_allowed_html_tags( $extra_tags ) );
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
		$allowed = array( Size::SMALL, Size::MEDIUM, Size::LARGE );
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

		$trigger_html = $this->trigger_element;

		$this->component_string = sprintf(
			'<div x-data="%1$s" x-ref="trigger" class="tutor-tooltip-wrap %2$s" %3$s>
				%4$s
				<template x-teleport="body">
					<div x-ref="content" x-show="open" x-cloak x-transition class="tutor-tooltip">
						%5$s
					</div>
				</template>
			</div>',
			esc_attr( $x_data ),
			esc_attr( $this->attributes['class'] ?? '' ),
			$this->get_attributes_string(),
			$trigger_html,
			$this->content
		);

		return $this->component_string;
	}
}
