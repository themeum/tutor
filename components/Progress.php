<?php
/**
 * Tutor Component: Progress
 *
 * Provides a fluent builder for rendering progress bars and progress circles
 * with different states and animations.
 *
 * @package Tutor\Components
 * @author Themeum
 * @link https://themeum.com
 * @since 4.0.0
 */

namespace Tutor\Components;

defined( 'ABSPATH' ) || exit;

/**
 * Progress Component Class.
 *
 * Example usage:
 * ```
 * // Progress bar
 * Progress::make()
 *     ->type( 'bar' )
 *     ->value( 75 )
 *     ->animated()
 *     ->render();
 *
 * // Progress circle
 * Progress::make()
 *     ->type( 'circle' )
 *     ->value( 75 )
 *     ->render();
 *
 * ```
 *
 * @since 4.0.0
 */
class Progress extends BaseComponent {

	/**
	 * Progress type (bar|circle).
	 *
	 * @since 4.0.0
	 * @var string
	 */
	protected $type = 'bar';

	/**
	 * Progress value (0-100).
	 *
	 * @since 4.0.0
	 * @var int
	 */
	protected $value = 0;

	/**
	 * Whether progress bar is animated.
	 *
	 * @since 4.0.0
	 * @var bool
	 */
	protected $animated = true;

	/**
	 * Progress circle state (progress|complete).
	 * Only applicable for circle type.
	 *
	 * @since 4.0.0
	 * @var string
	 */
	protected $state = 'progress';

	/**
	 * Set progress type.
	 *
	 * @since 4.0.0
	 *
	 * @param string $type Progress type (bar|circle).
	 * @return $this
	 */
	public function type( $type ) {
		$allowed = array( 'bar', 'circle' );
		if ( in_array( $type, $allowed, true ) ) {
			$this->type = $type;
		}
		return $this;
	}

	/**
	 * Set progress value (0-100).
	 *
	 * @since 4.0.0
	 *
	 * @param int $value Progress value.
	 * @return $this
	 */
	public function value( $value ) {
		$this->value = max( 0, min( 100, (int) $value ) );
		return $this;
	}

	/**
	 * Enable animated progress bar.
	 *
	 * @since 4.0.0
	 *
	 * @param bool $animated Whether to animate the progress bar.
	 * @return $this
	 */
	public function animated( $animated = true ) {
		$this->animated = (bool) $animated;
		return $this;
	}

	/**
	 * Render progress bar HTML.
	 *
	 * @since 4.0.0
	 *
	 * @return string HTML output.
	 */
	protected function render_bar() {
		$classes = 'tutor-progress-bar';

		// Add animated attribute if enabled.
		if ( $this->animated ) {
			$this->attributes['data-tutor-animated'] = '';
		}

		// Merge classes.
		$this->attributes['class'] = trim( "{$classes} " . ( $this->attributes['class'] ?? '' ) );

		$attributes = $this->render_attributes();

		return sprintf(
			'<div %s><div class="tutor-progress-bar-fill" style="--tutor-progress-width: %d%%;"></div></div>',
			$attributes,
			$this->value
		);
	}

	/**
	 * Render progress circle HTML.
	 *
	 * @since 4.0.0
	 *
	 * @return string HTML output.
	 */
	protected function render_circle() {
		// Build Alpine.js x-data attribute.
		$alpine_data = array();

		if ( 'progress' === $this->state ) {
			$alpine_data['value'] = $this->value;
			$alpine_data['type']  = 'progress';
		} else {
			$alpine_data['type'] = $this->state;
		}

		// Convert to JSON for x-data.
		$alpine_json = wp_json_encode( $alpine_data );

		// Merge attributes.
		$this->attributes['x-data'] = sprintf( 'tutorStatics(%s)', $alpine_json );

		$attributes = $this->render_attributes();

		return sprintf(
			'<div %s><div x-html="render()"></div></div>',
			$attributes
		);
	}

	/**
	 * Get the final progress HTML.
	 *
	 * @since 4.0.0
	 *
	 * @return string HTML output.
	 */
	public function get(): string {
		if ( 'circle' === $this->type ) {
			$this->component_string = $this->render_circle();
		} else {
			$this->component_string = $this->render_bar();
		}

		return $this->component_string;
	}

}
