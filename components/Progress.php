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

use Tutor\Components\Constants\Size;
use Tutor\Components\Constants\Variant;

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
	 * Progress size (x-small|small|medium|large).
	 *
	 * @since 4.0.0
	 * @var string
	 */
	protected $size = Size::SMALL;

	/**
	 * Progress variant style (brand|warning, etc).
	 *
	 * @since 4.0.0
	 *
	 * @see Variant constants
	 *
	 * @var string
	 */
	protected $variant = '';

	/**
	 * Background color.
	 *
	 * @since 4.0.0
	 * @var string
	 */
	protected $background = 'none';

	/**
	 * Stroke color (background circle).
	 *
	 * @since 4.0.0
	 * @var string
	 */
	protected $stroke_color = 'var(--tutor-actions-gray-secondary)';

	/**
	 * Progress stroke color (progress arc).
	 *
	 * @since 4.0.0
	 * @var string
	 */
	protected $progress_stroke_color = 'var(--tutor-actions-brand-primary)';

	/**
	 * Whether to show label.
	 *
	 * @since 4.0.0
	 * @var bool
	 */
	protected $show_label = true;

	/**
	 * Custom label text.
	 *
	 * @since 4.0.0
	 * @var string
	 */
	protected $label = '';

	/**
	 * Animation duration in milliseconds.
	 *
	 * @since 4.0.0
	 * @var int
	 */
	protected $duration = 1000;

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
	 * Set progress size.
	 *
	 * @since 4.0.0
	 *
	 * @param string $size Progress size (x-small|small|medium|large).
	 * @return $this
	 */
	public function size( $size ) {
		$allowed = array( Size::X_SMALL, Size::SMALL, Size::MEDIUM, Size::LARGE );
		if ( in_array( $size, $allowed, true ) ) {
			$this->size = $size;
		}
		return $this;
	}

	/**
	 * Set progress variant style.
	 *
	 * @since 4.0.0
	 *
	 * @param string $variant Progress variant (brand|warning, etc).
	 * @return $this
	 */
	public function variant( $variant ) {
		$this->variant = sanitize_html_class( $variant );
		return $this;
	}

	/**
	 * Set progress state.
	 *
	 * @since 4.0.0
	 *
	 * @param string $state Progress state (progress|complete|locked).
	 * @return $this
	 */
	public function state( $state ) {
		$allowed = array( 'progress', 'complete', 'locked' );
		if ( in_array( $state, $allowed, true ) ) {
			$this->state = $state;
		}
		return $this;
	}

	/**
	 * Set progress as complete.
	 *
	 * @since 4.0.0
	 * @return $this
	 */
	public function complete() {
		return $this->state( 'complete' );
	}

	/**
	 * Set progress as locked.
	 *
	 * @since 4.0.0
	 * @return $this
	 */
	public function locked() {
		return $this->state( 'locked' );
	}

	/**
	 * Set background color.
	 *
	 * @since 4.0.0
	 *
	 * @param string $background Background color.
	 * @return $this
	 */
	public function background( $background ) {
		$this->background = $background;
		return $this;
	}

	/**
	 * Set stroke color (background circle).
	 *
	 * @since 4.0.0
	 *
	 * @param string $stroke_color Stroke color.
	 * @return $this
	 */
	public function stroke_color( $stroke_color ) {
		$this->stroke_color = $stroke_color;
		return $this;
	}

	/**
	 * Set progress stroke color (progress arc).
	 *
	 * @since 4.0.0
	 *
	 * @param string $progress_stroke_color Progress stroke color.
	 * @return $this
	 */
	public function progress_stroke_color( $progress_stroke_color ) {
		$this->progress_stroke_color = $progress_stroke_color;
		return $this;
	}

	/**
	 * Set whether to show label.
	 *
	 * @since 4.0.0
	 *
	 * @param bool $show_label Whether to show label.
	 * @return $this
	 */
	public function show_label( $show_label = true ) {
		$this->show_label = (bool) $show_label;
		return $this;
	}

	/**
	 * Set custom label text.
	 *
	 * @since 4.0.0
	 *
	 * @param string $label Custom label text.
	 * @return $this
	 */
	public function label( $label ) {
		$this->label = $label;
		return $this;
	}

	/**
	 * Set animation duration.
	 *
	 * @since 4.0.0
	 *
	 * @param int $duration Animation duration in milliseconds.
	 * @return $this
	 */
	public function duration( $duration ) {
		$this->duration = (int) $duration;
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

		if ( ! empty( $this->variant ) ) {
			$classes .= " tutor-progress-bar-{$this->variant}";
		}

		// Add animated attribute if enabled.
		if ( $this->animated ) {
			$this->attributes['data-tutor-animated'] = '';
		}

		// Merge classes.
		$this->attributes['class'] = trim( "{$classes} " . ( $this->attributes['class'] ?? '' ) );

		$attributes = $this->get_attributes_string();

		$fixed_offset   = $this->value - 10;
		$proportional   = (int) round( $this->value * 0.85 );
		$animation_from = max( 0, min( $fixed_offset, $proportional ) );

		return sprintf(
			'<div %s><div class="tutor-progress-bar-fill" style="--tutor-progress-start: %d%%; --tutor-progress-width: %d%%;"></div></div>',
			$attributes,
			$animation_from,
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

		$alpine_data['size']                = $this->size;
		$alpine_data['background']          = $this->background;
		$alpine_data['strokeColor']         = $this->stroke_color;
		$alpine_data['progressStrokeColor'] = $this->progress_stroke_color;
		$alpine_data['showLabel']           = $this->show_label;
		$alpine_data['label']               = $this->label;
		$alpine_data['animated']            = $this->animated;
		$alpine_data['duration']            = $this->duration;

		// Convert to JSON for x-data.
		$alpine_json = wp_json_encode( $alpine_data );

		// Merge attributes.
		$this->attributes['x-data'] = sprintf( 'tutorStatics(%s)', $alpine_json );

		$attributes = $this->get_attributes_string();

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
