<?php
/**
 * Tutor Component: Accordion
 *
 * Provides a fluent builder for rendering accordion items with
 * Alpine.js integration for expand/collapse functionality.
 *
 * @package Tutor\Components
 * @author Themeum
 * @link https://themeum.com
 * @since 4.0.0
 */

namespace Tutor\Components;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Accordion Component Class.
 *
 * Example usage:
 * ```
 * // Single item accordion
 * Accordion::make()
 *     ->add_item( 'About Course', '<p>Description...</p>' )
 *     ->render();
 *
 * // Multiple items
 * Accordion::make()
 *     ->add_item( 'About Course', '<p>Description...</p>' )
 *     ->add_item( 'Requirements', '<p>Prerequisites...</p>' )
 *     ->add_item( 'Instructor', '<p>Meet your instructor...</p>' )
 *     ->default_open( array( 0 ) )
 *     ->render();
 *
 * // With custom icon and template
 * Accordion::make()
 *     ->add_item( 'Details', '', 'path/to/template.php', 'custom-icon' )
 *     ->allow_multiple( false )
 *     ->render();
 * ```
 *
 * @since 4.0.0
 */
class Accordion extends BaseComponent {

	/**
	 * Accordion items array.
	 *
	 * @since 4.0.0
	 *
	 * @var array
	 */
	protected $items = array();

	/**
	 * Whether multiple items can be open.
	 *
	 * @since 4.0.0
	 *
	 * @var bool
	 */
	protected $multiple = true;

	/**
	 * Default open item indices.
	 *
	 * @since 4.0.0
	 *
	 * @var array
	 */
	protected $default_open = array();

	/**
	 * Add accordion item.
	 *
	 * @since 4.0.0
	 *
	 * @param string $title Item title.
	 * @param string $content Item content HTML.
	 * @param string $template Optional. Template path instead of content.
	 * @param string $icon Optional. Custom icon name.
	 *
	 * @return $this
	 */
	public function add_item( $title, $content = '', $template = '', $icon = '' ) {
		$this->items[] = array(
			'title'    => $title,
			'content'  => $content,
			'template' => $template,
			'icon'     => $icon,
		);
		return $this;
	}

	/**
	 * Set whether multiple items can be open.
	 *
	 * @since 4.0.0
	 *
	 * @param bool $multiple Whether to allow multiple open items.
	 *
	 * @return $this
	 */
	public function allow_multiple( $multiple = true ) {
		$this->multiple = (bool) $multiple;
		return $this;
	}

	/**
	 * Set default open item indices.
	 *
	 * @since 4.0.0
	 *
	 * @param array $indices Array of item indices to open by default.
	 *
	 * @return $this
	 */
	public function default_open( $indices ) {
		$this->default_open = is_array( $indices ) ? $indices : array( $indices );
		return $this;
	}

	/**
	 * Render accordion item content.
	 *
	 * @since 4.0.0
	 *
	 * @param array $item Item data.
	 *
	 * @return string Content HTML.
	 */
	protected function render_item_content( $item ) {
		// If template is provided, use it.
		if ( ! empty( $item['template'] ) && file_exists( $item['template'] ) ) {
			ob_start();
			include $item['template'];
			return ob_get_clean();
		}

		// Otherwise return content.
		return ! empty( $item['content'] ) ? $item['content'] : '';
	}

	/**
	 * Render default chevron icon.
	 *
	 * @since 4.0.0
	 *
	 * @return string Icon SVG.
	 */
	protected function render_default_icon() {
		if ( function_exists( 'tutor_utils' ) ) {
			ob_start();
			tutor_utils()->render_svg_icon( 'chevron-down', 24, 24 );
			return ob_get_clean();
		}
		return '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" role="presentation" aria-hidden="true"><path d="M19.5 8.25L12 15.75L4.5 8.25" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"></path></svg>';
	}

	/**
	 * Render custom icon.
	 *
	 * @since 4.0.0
	 *
	 * @param string $icon Icon name.
	 *
	 * @return string Icon SVG.
	 */
	protected function render_icon( $icon ) {
		if ( empty( $icon ) ) {
			return $this->render_default_icon();
		}

		if ( function_exists( 'tutor_utils' ) ) {
			ob_start();
			tutor_utils()->render_svg_icon( $icon, 24, 24 );
			return ob_get_clean();
		}

		return $icon;
	}

	/**
	 * Get the accordion HTML.
	 *
	 * @since 4.0.0
	 *
	 * @return string HTML output.
	 */
	public function get(): string {
		if ( empty( $this->items ) ) {
			return '';
		}

		// Build Alpine.js config.
		$alpine_config = array(
			'multiple'    => $this->multiple,
			'defaultOpen' => $this->default_open,
		);
		$alpine_json = wp_json_encode( $alpine_config );

		// Merge custom classes.
		$wrapper_classes = 'tutor-accordion';
		if ( ! empty( $this->attributes['class'] ) ) {
			$wrapper_classes .= ' ' . $this->attributes['class'];
			unset( $this->attributes['class'] );
		}

		$this->attributes['x-data'] = sprintf( 'tutorAccordion(%s)', $alpine_json );
		$this->attributes['class']  = $wrapper_classes;

		$wrapper_attrs = $this->render_attributes();

		// Build items HTML.
		$items_html = '';
		foreach ( $this->items as $index => $item ) {
			$title     = isset( $item['title'] ) ? $item['title'] : '';
			$icon      = isset( $item['icon'] ) ? $item['icon'] : '';
			$panel_id  = 'tutor-acc-panel-' . $index;
			$trigger_id = 'tutor-acc-trigger-' . $index;

			$icon_html = $this->render_icon( $icon );
			$content   = $this->render_item_content( $item );

			$items_html .= sprintf(
				'<div class="tutor-accordion-item">
					<button
						@click="toggle(%d)"
						@keydown="handleKeydown($event, %d)"
						:aria-expanded="isOpen(%d)"
						class="tutor-accordion-header tutor-accordion-trigger"
						aria-controls="%s"
						id="%s"
					>
						<span class="tutor-accordion-title">%s</span>
						<span class="tutor-accordion-icon" aria-hidden="true">
							%s
						</span>
					</button>
					<div
						id="%s"
						role="region"
						aria-labelledby="%s"
						class="tutor-accordion-content"
						x-show="isOpen(%d)"
						x-collapse.duration.350ms
					>
						<div class="tutor-accordion-body">
							%s
						</div>
					</div>
				</div>',
				$index,
				$index,
				$index,
				esc_attr( $panel_id ),
				esc_attr( $trigger_id ),
				esc_html( $title ),
				$icon_html,
				esc_attr( $panel_id ),
				esc_attr( $trigger_id ),
				$index,
				wp_kses_post( $content )
			);
		}

		return sprintf(
			'<div %s>%s</div>',
			$wrapper_attrs,
			$items_html
		);
	}

}