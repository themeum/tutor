<?php
/**
 * Popover Component Class.
 *
 * @package Tutor\Components
 * @author Themeum
 * @link https://themeum.com
 * @since 4.0.0
 */

namespace Tutor\Components;

use TUTOR\Icon;

defined( 'ABSPATH' ) || exit;

/**
 * Class Popover
 *
 * Responsible for rendering popover component on button
 * at different placement positions with footer component and menu items
 *
 * @since 4.0.0
 */
class Popover extends BaseComponent {

	/**
	 * The popover title.
	 *
	 * @var string
	 */
	protected $popover_title;

	/**
	 * The popover body.
	 *
	 * @var string
	 */
	protected $popover_body;

	/**
	 * Callback function for popover body escaping.
	 *
	 * @var string
	 */
	protected $popover_body_esc = 'wp_kses_post';

	/**
	 * The popover placement location (left | right | top | bottom ).
	 *
	 * Default 'bottom-start'.
	 *
	 * @var string
	 */
	protected $popover_placement = 'bottom-start';

	/**
	 * Popover button element.
	 *
	 * @var string
	 */
	protected $popover_button;

	/**
	 * Popover menu items list.
	 *
	 * @var array
	 */
	protected $popover_menu_items;

	/**
	 * Whether to show or hide popover close button.
	 *
	 * Default true.
	 *
	 * @var bool
	 */
	protected $show_close_button = false;

	/**
	 * Popover footer buttons.
	 *
	 * @var array
	 */
	protected $popover_footer_buttons;

	/**
	 * Popover footer button alignment class ( left | center ).
	 *
	 * Default 'right'.
	 *
	 * @var array
	 */
	protected $popover_footer_alignment;

	/**
	 * Set Popover title
	 *
	 * @since 4.0.0
	 *
	 * @param string $popover_title the popover title.
	 *
	 * @return self
	 */
	public function title( string $popover_title ): self {
		$this->popover_title = $popover_title;
		return $this;
	}

	/**
	 * Set Popover body html.
	 *
	 * @since 4.0.0
	 *
	 * @param string $popover_body the popover body html.
	 *
	 * @return self
	 */
	public function body( string $popover_body ): self {
		$this->popover_body = $popover_body;
		return $this;
	}

	/**
	 * Set popover placement position.
	 *
	 * @since 4.0.0
	 *
	 * @param string $popover_placement the placement position.
	 *
	 * @return self
	 */
	public function placement( string $popover_placement = 'bottom-start' ): self {
		$placement_positions = array( 'top', 'left', 'right', 'bottom', 'bottom-start' );
		if ( ! in_array( $popover_placement, $placement_positions, true ) ) {
			$this->popover_placement = 'bottom-start';
		}

		$this->popover_placement = $popover_placement;
		return $this;
	}

	/**
	 * Popover button component.
	 *
	 * @since 4.0.0
	 *
	 * @param string $popover_button the popover button element html.
	 *
	 * @return self
	 */
	public function trigger( string $popover_button ): self {
		$this->popover_button = $popover_button;
		return $this;
	}

	/**
	 * Whether to hide close button on popover.
	 *
	 * @since 4.0.0
	 *
	 * @param boolean $show_close_button determines whether to hide close button.
	 *
	 * @return self
	 */
	public function closeable( bool $show_close_button = false ): self {
		$this->show_close_button = $show_close_button;
		return $this;
	}

	/**
	 * Set the popover footer alignment.
	 *
	 * @since 4.0.0
	 *
	 * @param string $popover_footer_alignment the popover footer alignment class.
	 *
	 * @return self
	 */
	public function footer_alignment( string $popover_footer_alignment ): self {
		$this->popover_footer_alignment = $popover_footer_alignment;
		return $this;
	}

	/**
	 * Set popover footer buttons.
	 *
	 * @since 4.0.0
	 *
	 * @param array $popover_footer_buttons the footer button list.
	 *
	 * @return self
	 */
	public function footer( array $popover_footer_buttons = array() ): self {
		$this->popover_footer_buttons = $popover_footer_buttons;
		return $this;
	}

	/**
	 * Menu items for creating the popover menu.
	 *
	 * @since 4.0.0
	 *
	 * @param array $popover_menu_items the popover menu items list.
	 *
	 * @return self
	 */
	public function menu_items( array $popover_menu_items ): self {
		$this->popover_menu_items = $popover_menu_items;
		return $this;
	}

	/**
	 * Render popover header element.
	 *
	 * @since 4.0.0
	 *
	 * @return string
	 */
	protected function render_header(): string {
		if ( empty( $this->popover_title ) ) {
			return '';
		}

		$title = sprintf( '<h3 class="tutor-popover-title">%s</h3>', esc_attr( $this->popover_title ) );

		$close_button = sprintf(
			'<button @click="hide()" class="tutor-popover-close">
				%s
			</button>',
			tutor_utils()->get_svg_icon( Icon::CROSS, 14, 14 )
		);

		if ( $this->show_close_button ) {
			return sprintf(
				'<div class="tutor-popover-header">
					%s
					%s
				</div>
			',
				$title,
				$close_button
			);
		}

		return sprintf(
			'<div class="tutor-popover-header">
					%s
				</div>
			',
			$title,
		);
	}

	/**
	 * Render popover body component.
	 *
	 * @since 4.0.0
	 *
	 * @return string
	 */
	protected function render_body(): string {
		if ( empty( $this->popover_body ) ) {
			return '';
		}

		$body = $this->esc( $this->popover_body, $this->popover_body_esc );

		return sprintf(
			'<div class="tutor-popover-body">
				%s
			</div>',
			$body
		);
	}

	/**
	 * Render footer component for popover.
	 *
	 * @since 4.0.0
	 *
	 * @return string
	 */
	protected function render_footer(): string {
		$footer_buttons   = '';
		$footer_alignment = in_array( $this->popover_footer_alignment, array( 'left', 'center' ), true ) ? $this->popover_footer_alignment : '';

		if ( ! tutor_utils()->count( $this->popover_footer_buttons ) ) {
			return $footer_buttons;
		}

		$alignment = 'left' === $footer_alignment ? 'tutor-justify-start' : 'tutor-justify-center';

		foreach ( $this->popover_footer_buttons as $button ) {
			$footer_buttons .= $button;
		}

		if ( ! empty( $footer_alignment ) ) {
			return sprintf(
				'<div class="tutor-popover-footer %s">
					%s
				</div>',
				$alignment,
				$footer_buttons
			);
		}

		return sprintf(
			'<div class="tutor-popover-footer">
				%s
			</div>',
			$footer_buttons
		);
	}

	/**
	 * Render Menu for popover.
	 *
	 * @since 4.0.0
	 *
	 * @return string
	 */
	protected function render_menu(): string {
		$menu_items = '';
		if ( ! tutor_utils()->count( $this->popover_menu_items ) ) {
			return $menu_items;
		}

		return $menu_items;
	}

	/**
	 * Render Popover Component.
	 *
	 * @since 4.0.0
	 *
	 * @return string HTML content.
	 */
	public function render(): string {

		$header             = $this->render_header();
		$body               = $this->render_body();
		$placement_position = $this->popover_placement;
		$button             = $this->popover_button ?? '';
		$footer             = $this->render_footer();

		$placement_class = 'bottom-start' !== $placement_position ? "tutor-popover-$placement_position" : 'tutor-popover-top';
		$class           = 'tutor-popover ' . $placement_class;

		if ( isset( $this->attributes['class'] ) ) {
			$custom_class = $this->attributes['class'];
			$class       .= $custom_class;
			unset( $this->attributes['class'] );
		}

		return sprintf(
			'<div x-data="tutorPopover({ placement: \'%s\' })">
				%s
				<div 
					x-ref="content"
					x-show="open"
					x-cloak
					class=%s
				>	
				%s
				%s
				%s
				</div>
			</div>',
			$placement_position,
			$button,
			$class,
			$header,
			$body,
			$footer
		);
	}
}
