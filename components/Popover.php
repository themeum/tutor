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
 * at different placement positions with footer component and menu items.
 *
 * Example Usage: Basic Popover
 * ```
 * echo Popover::make()
 *      ->title( 'Basic' )
 *       ->body( '<p>This is a popover component</p>' )
 *       ->closeable( true )
 *       ->trigger(
 *           Button::make()
 *           ->label( 'Show Popover' )
 *           ->attr( 'x-ref', 'trigger' )
 *           ->attr( '@click', 'toggle()' )
 *           ->size( 'medium' )
 *           ->variant( 'primary' )
 *           ->render()
 *       )
 *       ->render();
 * ```
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
	 * Popover menu item icon svg.
	 *
	 * @var string
	 */
	protected $popover_menu_item_icon;

	/**
	 * Popover menu item icon alignment.
	 *
	 * @var string
	 */
	protected $popover_menu_item_icon_alignment;

	/**
	 * Popover menu item custom class.
	 *
	 * @var string
	 */
	protected $popover_menu_item_class;

	/**
	 * Attributes of Popover menu items.
	 *
	 * @var array
	 */
	protected $attributes;

	/**
	 * Prevent close from outside
	 *
	 * @var bool
	 */
	protected $popover_close_outside = true;

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
	 * Menu items for creating popover menu.
	 *
	 * @since 4.0.0
	 *
	 * @param string $tag the menu item html tag.
	 * @param string $popover_menu_item the popover menu item.
	 * @param string $class the menu item class.
	 * @param string $icon the menu item icon.
	 * @param string $icon_alignment menu item icon alignment.
	 * @param array  $attr the menu item attributes.
	 *
	 * @return self
	 */
	public function menu_item( string $tag, string $popover_menu_item, string $class = '', string $icon = '', string $icon_alignment = '', array $attr = array() ): self {
		$menu_item_tag                          = ! empty( $tag ) ? $this->esc( $tag, $this->popover_body_esc ) : '';
		$this->popover_menu_item_class          = $class ?? '';
		$this->popover_menu_item_icon           = $icon ?? '';
		$this->popover_menu_item_icon_alignment = ! in_array( $icon_alignment, array( 'left', 'right' ), true ) ? 'left' : $icon_alignment;

		$this->popover_menu_items[] = array(
			'tag'            => $menu_item_tag,
			'content'        => $popover_menu_item,
			'class'          => $this->popover_menu_item_class,
			'icon'           => $this->popover_menu_item_icon,
			'icon_alignment' => $this->popover_menu_item_icon_alignment,
			'attr'           => $attr,
		);

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
	 * Handle closing popover on outside click.
	 *
	 * @since 4.0.0
	 *
	 * @param boolean $is_closeable whether allow to close from outside click.
	 *
	 * @return self
	 */
	public function dismissible( bool $is_closeable ): self {
		$this->popover_close_outside = $is_closeable;
		return $this;
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

		foreach ( $this->popover_menu_items as $item ) {
			$tag            = isset( $item['tag'] ) ? $item['tag'] : 'div';
			$content        = isset( $item['content'] ) ? $this->esc( $item['content'], $this->popover_body_esc ) : '';
			$class          = isset( $item['class'] ) ? esc_attr( $item['class'] ) : '';
			$icon           = isset( $item['icon'] ) ? $item['icon'] : '';
			$icon_alignment = isset( $item['icon_alignment'] ) ? $item['icon_alignment'] : 'left';

			$this->attributes = tutor_utils()->count( $item['attr'] ) ? $item['attr'] : array();

			$menu_item_attr = $this->render_attributes();

			if ( empty( $icon ) ) {
				$menu_items .= sprintf(
					'<%1$s class="tutor-popover-menu-item %2$s" %4$s>%3$s</%1$s>',
					$tag,
					$class,
					$content,
					$menu_item_attr
				);
			}

			if ( 'right' === $icon_alignment ) {
				$menu_items .= sprintf(
					'<%1$s class="tutor-popover-menu-item %2$s" %5$s>%3$s%4$s</%1$s>',
					$tag,
					$class,
					$content,
					$icon,
					$menu_item_attr
				);
			} else {
				$menu_items .= sprintf(
					'<%1$s class="tutor-popover-menu-item %2$s" %5$s>%4$s%3$s</%1$s>',
					$tag,
					$class,
					$content,
					$icon,
					$menu_item_attr
				);
			}
		}

		return sprintf( '<div class="tutor-popover-menu">%s</div>', $menu_items );
	}

	/**
	 * Get the final Popover HTML Component.
	 *
	 * @since 4.0.0
	 *
	 * @return string HTML content.
	 */
	public function get(): string {

		$header             = $this->render_header();
		$body               = $this->render_body();
		$placement_position = $this->popover_placement;
		$button             = $this->popover_button ?? '';
		$footer             = $this->render_footer();
		$menu               = $this->render_menu();

		$placement_class = 'bottom-start' !== $placement_position ? "tutor-popover-$placement_position" : 'tutor-popover-top';
		$class           = 'tutor-popover ' . $placement_class;

		$closeable_attr = $this->popover_close_outside ? '@click.outside="handleClickOutside()' : '';

		return sprintf(
			'<div x-data="tutorPopover({ placement: \'%s\' })">
				%s
				<div 
					x-ref="content"
					x-show="open"
					x-cloak
					class=%s
					%s"
				>	
				%s
				%s
				%s
				%s
				</div>
			</div>',
			$placement_position,
			$button,
			$class,
			$closeable_attr,
			$header,
			$body,
			$footer,
			$menu
		);
	}
}
