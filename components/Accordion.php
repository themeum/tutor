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

defined( 'ABSPATH' ) || exit;

/**
 * Accordion Component Class.
 *
 * Example usage:
 * ```
 * Accordion::make()
 *     ->id( 'about-course' )
 *     ->title( 'About this Course' )
 *     ->content( '<p>Course description here...</p>' )
 *     ->open()
 *     ->render();
 *
 * // With template path
 * Accordion::make()
 *     ->id( 'course-details' )
 *     ->title( 'Course Details' )
 *     ->template( 'path/to/template.php' )
 *     ->render();
 * ```
 *
 * @since 4.0.0
 */
class Accordion extends BaseComponent {

	/**
	 * Accordion item unique ID.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	protected $id = '';

	/**
	 * Accordion title text.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	protected $title = '';

	/**
	 * Accordion title esc fn.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	protected $title_esc_cb = 'esc_html';

	/**
	 * Accordion content or template path.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	protected $content = '';

	/**
	 * Accordion content esc fn.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	protected $content_esc_cb = 'esc_html';

	/**
	 * Whether content is a template path.
	 *
	 * @since 4.0.0
	 *
	 * @var bool
	 */
	protected $is_template = false;

	/**
	 * Whether accordion is open by default.
	 *
	 * @since 4.0.0
	 *
	 * @var bool
	 */
	protected $is_open = false;

	/**
	 * Custom icon SVG.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	protected $icon = '';

	/**
	 * Set accordion ID.
	 *
	 * @since 4.0.0
	 *
	 * @param string $id Accordion unique ID.
	 *
	 * @return $this
	 */
	public function id( $id ) {
		$this->id = sanitize_key( $id );
		return $this;
	}

	/**
	 * Set accordion title.
	 *
	 * @since 4.0.0
	 *
	 * @param string $title Accordion title text.
	 * @param string $title_esc_cb Title esc callback.
	 *
	 * @return $this
	 */
	public function title( $title, $title_esc_cb = 'esc_html' ) {
		$this->title        = $title;
		$this->title_esc_cb = $title_esc_cb;
		return $this;
	}

	/**
	 * Set accordion content.
	 *
	 * @since 4.0.0
	 *
	 * @param string $content Content HTML.
	 * @param string $content_esc_cb Callback for esc content.
	 *
	 * @return $this
	 */
	public function content( $content, $content_esc_cb = 'esc_html' ) {
		$this->content        = $content;
		$this->content_esc_cb = $content_esc_cb;
		$this->is_template    = false;
		return $this;
	}

	/**
	 * Set accordion content from template path.
	 *
	 * @since 4.0.0
	 *
	 * @param string $path Template file path.
	 *
	 * @return $this
	 */
	public function template( $path ) {
		$this->content     = $path;
		$this->is_template = true;
		return $this;
	}

	/**
	 * Set accordion open by default.
	 *
	 * @since 4.0.0
	 *
	 * @param bool $open Whether accordion is open.
	 *
	 * @return $this
	 */
	public function open( $open = true ) {
		$this->is_open = (bool) $open;
		return $this;
	}

	/**
	 * Set custom icon.
	 *
	 * @since 4.0.0
	 *
	 * @param string $icon SVG icon markup.
	 *
	 * @return $this
	 */
	public function icon( $icon ) {
		$this->icon = $icon;
		return $this;
	}

	/**
	 * Render accordion content.
	 *
	 * @since 4.0.0
	 *
	 * @return string Content HTML.
	 */
	protected function render_content() {
		if ( empty( $this->content ) ) {
			return '';
		}

		if ( $this->is_template ) {
			if ( file_exists( $this->content ) ) {
				ob_start();
				include $this->content;
				$content = ob_get_clean();
				return $content ? $content : '';
			}
			return '';
		}

		return $this->esc( $this->content, $this->content_esc_cb );
	}

	/**
	 * Render default chevron icon.
	 *
	 * @since 4.0.0
	 *
	 * @return string Icon SVG.
	 */
	protected function render_default_icon() {
		return '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" role="presentation" aria-hidden="true"><path d="M19.5 8.25L12 15.75L4.5 8.25" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"></path></svg>';
	}

	/**
	 * Get the accordion item HTML.
	 *
	 * @since 4.0.0
	 *
	 * @return string HTML output.
	 */
	public function get(): string {
		if ( empty( $this->id ) || empty( $this->title ) ) {
			return '';
		}

		$accordion_id = esc_attr( $this->id );
		$trigger_id   = sprintf( 'tutor-acc-trigger-%s', $accordion_id );
		$panel_id     = sprintf( 'tutor-acc-panel-%s', $accordion_id );

		$icon_html = ! empty( $this->icon ) ? $this->icon : $this->render_default_icon();
		$content   = $this->render_content();

		$aria_expanded = $this->is_open ? 'true' : 'false';
		$content_class = $this->is_open ? 'tutor-accordion-content tutor-accordion-content-expanded' : 'tutor-accordion-content tutor-accordion-content-collapsed';

		$content_style = $this->is_open ? 'height: auto;' : 'height: 0px;';

		// Merge custom classes.
		$item_classes = 'tutor-accordion-item';
		if ( ! empty( $this->attributes['class'] ) ) {
			$item_classes .= ' ' . $this->attributes['class'];
			unset( $this->attributes['class'] );
		}

		// Build Alpine.js x-data using tutorAccordion component.
		$alpine_config = array(
			'id'     => $this->id,
			'isOpen' => $this->is_open,
		);
		$alpine_json   = wp_json_encode( $alpine_config );

		$this->attributes['x-data'] = sprintf( 'tutorAccordion(%s)', $alpine_json );

		$item_attrs = $this->render_attributes();

		$this->component_string = sprintf(
			'<div class="%s" %s>
				<button 
					@click="toggle(\'%s\')" 
					:aria-expanded="isOpen(\'%s\')" 
					class="tutor-accordion-header tutor-accordion-trigger" 
					aria-controls="%s" 
					id="%s" 
					aria-expanded="%s"
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
					class="%s"
					style="%s"
				>
					<div class="tutor-accordion-body">
						%s
					</div>
				</div>
			</div>',
			esc_attr( $item_classes ),
			$item_attrs,
			$accordion_id,
			$accordion_id,
			esc_attr( $panel_id ),
			esc_attr( $trigger_id ),
			esc_attr( $aria_expanded ),
			$this->esc( $this->title, $this->title_esc_cb ),
			$icon_html,
			esc_attr( $panel_id ),
			esc_attr( $trigger_id ),
			esc_attr( $content_class ),
			esc_attr( $content_style ),
			$content
		);

		return $this->component_string;
	}

}
