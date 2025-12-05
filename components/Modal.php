<?php
/**
 * Tutor Component: Modal
 *
 * Provides a fluent builder for rendering modals with Alpine.js integration.
 *
 * @package Tutor\Components
 * @author Themeum
 * @link https://themeum.com
 * @since 4.0.0
 */

namespace Tutor\Components;

defined( 'ABSPATH' ) || exit;

/**
 * Modal Component Class.
 *
 * ```
 * // Example usage:
 *
 * Modal::make()
 *     ->id( 'confirm-modal' )
 *     ->title( 'Confirm Submission' )
 *     ->subtitle( 'Are you sure you want to submit?' )
 *     ->body( 'This action cannot be undone.' )
 *     ->footer_buttons( '<button class="tutor-btn">Cancel</button>' )
 *     ->footer_alignment( 'right' )
 *     ->render();
 *
 * // With template path
 * Modal::make()
 *     ->id( 'course-modal' )
 *     ->title( 'Course Details' )
 *     ->template( 'path/to/template.php' )
 *     ->render();
 *
 * // Headless modal (no close button)
 * Modal::make()
 *     ->id( 'headless-modal' )
 *     ->closeable( false )
 *     ->body( '<h3>Success!</h3>' )
 *     ->render();
 * ```
 *
 * @since 4.0.0
 */
class Modal extends BaseComponent {

	/**
	 * Modal unique ID.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	protected $id = '';

	/**
	 * Modal title.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	protected $title = '';

	/**
	 * Modal title esc func.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	protected $title_esc_cb = 'esc_html';

	/**
	 * Modal subtitle.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	protected $subtitle = '';

	/**
	 * Modal subtitle esc func.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	protected $subtitle_esc_cb = 'esc_html';

	/**
	 * Modal body content or template path.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	protected $body = '';

	/**
	 * Modal body content esc callback.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	protected $body_esc_cb = 'wp_kses_post';

	/**
	 * Whether body is a template path.
	 *
	 * @since 4.0.0
	 *
	 * @var bool
	 */
	protected $is_template = false;

	/**
	 * Footer buttons HTML.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	protected $footer_buttons = '';

	/**
	 * Footer alignment (left|center|right).
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	protected $footer_alignment = 'right';

	/**
	 * Whether modal is closeable.
	 *
	 * @since 4.0.0
	 *
	 * @var bool
	 */
	protected $closeable = true;

	/**
	 * Custom width for modal content.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	protected $width = '';

	/**
	 * Set modal ID.
	 *
	 * @since 4.0.0
	 *
	 * @param string $id Modal unique ID.
	 *
	 * @return $this
	 */
	public function id( $id ) {
		$this->id = sanitize_key( $id );
		return $this;
	}

	/**
	 * Set modal title.
	 *
	 * @since 4.0.0
	 *
	 * @param string $title Modal title.
	 * @param string $esc_cb Callable escaping function.
	 *
	 * @return $this
	 */
	public function title( string $title, $esc_cb = 'esc_html' ) {
		$this->title        = $title;
		$this->title_esc_cb = $esc_cb;
		return $this;
	}

	/**
	 * Set modal subtitle.
	 *
	 * @since 4.0.0
	 *
	 * @param string $subtitle Modal subtitle.
	 * @param string $esc_cb Callable escaping function.
	 *
	 * @return $this
	 */
	public function subtitle( string $subtitle, $esc_cb = 'esc_html' ) {
		$this->subtitle        = $subtitle;
		$this->subtitle_esc_cb = $esc_cb;
		return $this;
	}

	/**
	 * Set modal body content.
	 *
	 * @since 4.0.0
	 *
	 * @param string $body Body content HTML.
	 * @param string $esc_cb Body content HTML.
	 *
	 * @return $this
	 */
	public function body( $body, $esc_cb = 'wp_kses_post' ) {
		$this->body        = $body;
		$this->body_esc_cb = $esc_cb;
		$this->is_template = false;
		return $this;
	}

	/**
	 * Set modal body from template path.
	 *
	 * @since 4.0.0
	 *
	 * @param string $path Template file path.
	 *
	 * @return $this
	 */
	public function template( $path ) {
		$this->body        = $path;
		$this->is_template = true;
		return $this;
	}

	/**
	 * Set footer buttons HTML.
	 *
	 * @since 4.0.0
	 *
	 * @param string $buttons Footer buttons HTML.
	 *
	 * @return $this
	 */
	public function footer_buttons( $buttons ) {
		$this->footer_buttons = $buttons;
		return $this;
	}

	/**
	 * Set footer button alignment.
	 *
	 * @since 4.0.0
	 *
	 * @param string $alignment Alignment (left|center|right).
	 *
	 * @return $this
	 */
	public function footer_alignment( $alignment ) {
		$allowed = array( 'left', 'center', 'right' );
		if ( in_array( $alignment, $allowed, true ) ) {
			$this->footer_alignment = $alignment;
		}
		return $this;
	}

	/**
	 * Set whether modal is closeable.
	 *
	 * @since 4.0.0
	 *
	 * @param bool $closeable Whether modal can be closed.
	 *
	 * @return $this
	 */
	public function closeable( $closeable = true ) {
		$this->closeable = (bool) $closeable;
		return $this;
	}

	/**
	 * Set custom width for modal content.
	 *
	 * @since 4.0.0
	 *
	 * @param string $width Width value (e.g., '354px', '50%').
	 *
	 * @return $this
	 */
	public function width( $width ) {
		$this->width = $width;
		return $this;
	}

	/**
	 * Render modal header.
	 *
	 * @since 4.0.0
	 *
	 * @return string Header HTML.
	 */
	protected function render_header() {
		if ( empty( $this->title ) && empty( $this->subtitle ) ) {
			return '';
		}

		$title_html = $this->title
			? sprintf( '<div class="tutor-modal-title">%s</div>', $this->esc( $this->title, $this->title_esc_cb ) )
			: '';

		$subtitle_html = $this->subtitle
			? sprintf( '<div class="tutor-modal-subtitle">%s</div>', $this->esc( $this->subtitle, $this->subtitle_esc_cb ) )
			: '';

		return sprintf(
			'<div class="tutor-modal-header">%s%s</div>',
			$title_html,
			$subtitle_html
		);
	}

	/**
	 * Render modal body.
	 *
	 * @since 4.0.0
	 *
	 * @return string Body HTML.
	 */
	protected function render_body() {
		if ( empty( $this->body ) ) {
			return '';
		}

		$content = '';

		if ( $this->is_template ) {
			if ( file_exists( $this->body ) ) {
				ob_start();
				include $this->body;
				$content = ob_get_clean();
				return $content ? $content : '';
			}
		} else {
			$content = $this->esc( $this->body, $this->body_esc_cb );
		}

		return sprintf( '<div class="tutor-modal-body">%s</div>', $content );
	}

	/**
	 * Render modal footer.
	 *
	 * @since 4.0.0
	 *
	 * @return string Footer HTML.
	 */
	protected function render_footer() {
		if ( empty( $this->footer_buttons ) ) {
			return '';
		}

		$alignment_class = '';
		if ( 'left' === $this->footer_alignment ) {
			$alignment_class = ' tutor-justify-start';
		} elseif ( 'center' === $this->footer_alignment ) {
			$alignment_class = ' tutor-justify-center';
		}

		return sprintf(
			'<div class="tutor-modal-footer%s">%s</div>',
			esc_attr( $alignment_class ),
			$this->footer_buttons
		);
	}

	/**
	 * Get the modal HTML.
	 *
	 * @since 4.0.0
	 *
	 * @return string HTML output.
	 */
	public function get(): string {
		if ( empty( $this->id ) ) {
			return '';
		}

		// Build Alpine.js x-data.
		$alpine_data = array( 'id' => $this->id );
		if ( ! $this->closeable ) {
			$alpine_data['isCloseable'] = false;
		}

		$alpine_json = wp_json_encode( $alpine_data );

		// Build close button.
		$close_button = $this->closeable
			? '<button x-data="tutorIcon({ name: \'cross\', width: 16, height: 16})" x-bind="getCloseButtonBindings()"></button>'
			: '';

		// Build style attribute for custom width.
		$style_attr = $this->width
			? sprintf( ' style="width: %s;"', esc_attr( $this->width ) )
			: '';

		// Build modal content.
		$header = $this->render_header();
		$body   = $this->render_body();
		$footer = $this->render_footer();

		$this->component_string = sprintf(
			'<div x-data="tutorModal(%s)" x-cloak>
				<template x-teleport="body">
					<div x-bind="getModalBindings()">
						<div x-bind="getBackdropBindings()"></div>
						<div x-bind="getModalContentBindings()"%s>
							%s
							%s
							%s
							%s
						</div>
					</div>
				</template>
			</div>',
			esc_attr( $alpine_json ),
			$style_attr,
			$close_button,
			$header,
			$body,
			$footer
		);

		return $this->component_string;
	}
}
