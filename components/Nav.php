<?php
/**
 * Nav Component Class.
 *
 * @package Tutor\Components
 * @author Themeum
 * @link https://themeum.com
 * @since 4.0.0
 */

namespace Tutor\Components;

defined( 'ABSPATH' ) || exit;

use Tutor\Components\Constants\InputType;
use Tutor\Components\Constants\Size;
use Tutor\Components\Constants\Variant;
use TUTOR\Icon;

/**
 * Class Nav
 *
 * Responsible for rendering the nav component.
 *
 *
 * //Example Usage :
 *
 * ```
 * $items = array(
 *   array(
 *       'type'     => 'link',        // 'link' or 'dropdown'
 *       'label'    => 'Wishlist',
 *       'icon'     => Icon::WISHLIST,
 *       'url'      => '#',
 *       'active'   => false,
 *   ),
 *   array(
 *       'type'    => 'dropdown',
 *       'icon'    => Icon::ENROLLED,
 *       'active'  => true,
 *       'options' => array(
 *           array(
 *               'label'  => 'Active',
 *               'count   => 4,
 *               'icon'   => Icon::PLAY_LINE,
 *               'url'    => '#',
 *               'active' => false,
 *           ),
 *           array(
 *               'label'  => 'Enrolled',
 *               'count   => 4,
 *               'icon'   => Icon::ENROLLED,
 *               'url'    => '#',
 *               'active' => true,
 *           ),
 *       ),
 *   ),
 * );
 *
 *   echo Nav::make()
 *       ->items( array( $dropdown ) )
 *       ->size( Size::SMALL )
 *       ->variant( Variant::SECONDARY )
 *       ->render();
 * ```
 *
 * @since 4.0.0
 */
class Nav extends BaseComponent {

	/**
	 * The nav variant.
	 *
	 * @var string
	 */
	protected $nav_variant = Variant::PRIMARY;

	/**
	 * The nav size.
	 *
	 * @var string
	 */
	protected $nav_size = Size::MEDIUM;

	/**
	 * The nav items.
	 *
	 * @var array
	 */
	protected $nav_items = array();

	/**
	 * Nav attributes.
	 *
	 * @since 4.0.0
	 *
	 * @var array
	 */
	protected $attributes = array();

	/**
	 * Set the nav variant.
	 *
	 * @since 4.0.0
	 *
	 * @param string $variant the nav variant to set.
	 *
	 * @return self
	 */
	public function variant( $variant = Variant::PRIMARY ): self {
		$allowed_variants  = array( Variant::PRIMARY, Variant::SECONDARY );
		$this->nav_variant = in_array( $variant, $allowed_variants, true ) ? $variant : Variant::PRIMARY;
		return $this;
	}

	/**
	 * Set the nav size.
	 *
	 * @since 4.0.0
	 *
	 * @param string $size the nav size.
	 *
	 * @return self
	 */
	public function size( $size = Size::MEDIUM ): self {
		$allowed_sizes  = array( Size::MEDIUM, Size::SMALL, Size::LARGE );
		$this->nav_size = in_array( $size, $allowed_sizes, true ) ? $size : Size::MEDIUM;

		return $this;
	}

	/**
	 * Set the nav items.
	 *
	 * @since 4.0.0
	 *
	 * @param array $items the nav items.
	 *
	 * @return self
	 */
	public function items( $items = array() ): self {
		$this->nav_items = $items;
		return $this;
	}

	/**
	 * Get icon size for item size.
	 *
	 * @since 4.0.0
	 *
	 * @param string $size the item size.
	 *
	 * @return integer
	 */
	protected function get_icon_size( $size ): int {
		$icon_sizes = array(
			Size::SMALL  => 16,
			Size::MEDIUM => 20,
			Size::LARGE  => 24,
		);

		return $icon_sizes[ $size ];
	}

	/**
	 * Get the label of the active dropdown option.
	 *
	 * @since 4.0.0
	 *
	 * @param array $options Array of dropdown options.
	 *
	 * @return string The label of the active option, or the first option's label * if none are active.
	 */
	protected function get_active_dropdown_label( array $options ): string {
		if ( ! tutor_utils()->count( $options ) ) {
			return '';
		}

		$active_option = $options[0];
		foreach ( $options as $option ) {
			if ( ! empty( $option['active'] ) ) {
				$active_option = $option;
				break;
			}
		}

		$label = $active_option['label'] ?? '';
		if ( isset( $active_option['count'] ) ) {
			$label .= ' (' . $active_option['count'] . ')';
		}

		return $label;
	}


	/**
	 * Render dropdown nav if it is selected.
	 *
	 * @since 4.0.0
	 *
	 * @param array $item the dropdown nav item.
	 *
	 * @return string
	 */
	protected function render_dropdown_item( array $item ): string {
		$dropdown = '';

		if ( ! tutor_utils()->count( $item ) ) {
			return '';
		}

		$options       = $item['options'] ?? array();
		$active_label  = $this->get_active_dropdown_label( $options );
		$icon_size     = $this->get_icon_size( $this->nav_size );
		$active_item   = isset( $item['active'] ) && $item['active'] ? 'active' : '';
		$icon          = isset( $item['icon'] ) ? tutor_utils()->get_svg_icon( $item['icon'], $icon_size, $icon_size ) : '';
		$dropdown_icon = tutor_utils()->get_svg_icon(
			Icon::CHEVRON_DOWN_2,
			$icon_size,
			$icon_size,
			array( 'class' => 'tutor-icon-subdued' )
		);

		$dropdown_options = '';

		if ( count( $options ) ) {
			foreach ( $options as $option ) {
				$icon      = isset( $option['icon'] ) ? tutor_utils()->get_svg_icon( $option['icon'], $icon_size, $icon_size ) : '';
				$is_active = isset( $option['active'] ) && $option['active'] ? 'active' : '';
				$label     = esc_html( $option['label'] );
				$label     = isset( $option['count'] ) ? $label . ' (' . esc_html( $option['count'] ) . ')' : $label;
				$url       = isset( $option['url'] ) ? esc_url( $option['url'] ) : '#';

				$dropdown_options .= sprintf(
					'<a href="%s" class="tutor-nav-dropdown-item %s">
						%s
						%s
					</a>',
					$url,
					$is_active,
					$icon,
					$label
				);
			}
		}

		$dropdown = sprintf(
			'<div x-data="tutorPopover({ placement: \'bottom-start\', offset: 4 })">
				<button x-ref="trigger" @click="toggle()"
					class="tutor-nav-item %s">
				%s
				%s
				%s	
				</button>
				<div x-ref="content" x-show="open" x-cloak @click.outside="handleClickOutside()" class="tutor-popover tutor-nav-dropdown">
					%s
				</div>
			</div>',
			$active_item,
			$icon,
			esc_html( $active_label ),
			$dropdown_icon,
			$dropdown_options
		);

		return $dropdown;
	}

	/**
	 * Render link nav item if it is selected.
	 *
	 * @since 4.0.0
	 *
	 * @param array $item the link nav item.
	 *
	 * @return string
	 */
	protected function render_link_item( array $item ): string {
		$dropdown = '';

		if ( ! tutor_utils()->count( $item ) ) {
			return '';
		}

		$active_item = isset( $item['active'] ) && $item['active'] ? 'active' : '';
		$url         = isset( $item['url'] ) ? esc_url( $item['url'] ) : '#';
		$icon_size   = $this->get_icon_size( $this->nav_size );
		$label       = esc_html( $item['label'] ?? '' );
		$label       = isset( $item['count'] ) ? $label . ' (' . esc_html( $item['count'] ) . ')' : $label;
		$icon        = isset( $item['icon'] ) ? tutor_utils()->get_svg_icon( $item['icon'], $icon_size, $icon_size ) : '';

		$dropdown = sprintf(
			'<a href="%s" class="tutor-nav-item %s">
				%s
				%s
			</a>',
			$url,
			$active_item,
			$icon,
			$label
		);

		return $dropdown;
	}

	/**
	 * Get the HTML nav component.
	 *
	 * @since 4.0.0
	 *
	 * @return string
	 */
	public function get(): string {
		if ( ! count( $this->nav_items ) ) {
			return '';
		}

		$nav_items = '';

		foreach ( $this->nav_items as $nav_item ) {
			if ( InputType::DROPDOWN === $nav_item['type'] ) {
				$nav_items .= $this->render_dropdown_item( $nav_item );
			} else {
				$nav_items .= $this->render_link_item( $nav_item );
			}
		}

		$classes = sprintf(
			'tutor-nav tutor-nav-%s tutor-nav-%s',
			$this->nav_size,
			$this->nav_variant,
		);

		// Merge with any custom class attribute.
		$this->attributes['class'] = trim( "{$classes} " . ( $this->attributes['class'] ?? '' ) );

		$attributes = $this->render_attributes();

		return sprintf(
			'<div %s>
				%s
			</div>',
			$attributes,
			$nav_items
		);
	}
}
