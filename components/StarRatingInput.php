<?php
/**
 * StarRatingInput Component Class.
 *
 * Renders an interactive star rating input using Alpine.js.
 *
 * @package Tutor\Components
 * @author Themeum
 * @link https://themeum.com
 * @since 4.0.0
 */

namespace Tutor\Components;

defined( 'ABSPATH' ) || exit;

use TUTOR\Icon;
use Tutor\components\Constants\Size;

/**
 * Class StarRatingInput
 *
 * Example Usage:
 * ```
 * StarRatingInput::make()
 *     ->field_name('rating')
 *     ->current_rating(4.5)
 *     ->render();
 * ```
 *
 * @since 4.0.0
 */
class StarRatingInput extends BaseComponent {

	/**
	 * Form field name
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	protected $field_name = 'rating';

	/**
	 * Current rating value
	 *
	 * @since 4.0.0
	 *
	 * @var float
	 */
	protected $current_rating = 0;

	/**
	 * On change callback function name or js snippet
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	protected $on_change = '';

	/**
	 * Alpine register function binding
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	protected $register = '';

	/**
	 * Icon size
	 *
	 * @var int
	 */
	protected $icon_size = Size::SIZE_20;

	/**
	 * Set field name
	 *
	 * @since 4.0.0
	 *
	 * @param string $name field name.
	 *
	 * @return self
	 */
	public function field_name( string $name ): self {
		$this->field_name = $name;
		return $this;
	}

	/**
	 * Set current rating
	 *
	 * @since 4.0.0
	 *
	 * @param float $rating rating.
	 *
	 * @return self
	 */
	public function current_rating( float $rating ): self {
		$this->current_rating = $rating;
		return $this;
	}

	/**
	 * Set on change callback
	 *
	 * @since 4.0.0
	 *
	 * @param string $callback callback.
	 *
	 * @return self
	 */
	public function on_change( string $callback ): self {
		$this->on_change = $callback;
		return $this;
	}

	/**
	 * Set register binding
	 *
	 * @since 4.0.0
	 *
	 * @param string $register register.
	 *
	 * @return self
	 */
	public function register( string $register ): self {
		$this->register = $register;
		return $this;
	}

	/**
	 * Get component content
	 *
	 * @since 4.0.0
	 *
	 * @return string
	 */
	public function get(): string {
		$current_rating = $this->current_rating;
		$star_fill      = tutor_utils()->get_svg_icon( Icon::STAR_FILL, $this->icon_size, $this->icon_size );
		$star_half      = tutor_utils()->get_svg_icon( Icon::STAR_HALF, $this->icon_size, $this->icon_size );
		$star           = tutor_utils()->get_svg_icon( Icon::STAR_LINE, $this->icon_size, $this->icon_size );

		ob_start();
		?>
		<div 
			class="tutor-flex tutor-gap-4 tutor-justify-between tutor-items-center"
			x-data="tutorStarRatingInput({
				initialRating: <?php echo esc_attr( $current_rating ); ?>,
				fieldName: '<?php echo esc_attr( $this->field_name ); ?>'
			})"
			@mouseleave="hoverRating = 0"
		>
			<input 
				type="hidden" 
				name="<?php echo esc_attr( $this->field_name ); ?>" 
				x-bind="register('<?php echo esc_attr( $this->field_name ); ?>' )"
			>
			
			<div class="tutor-flex tutor-items-center tutor-gap-2">
				<?php for ( $i = 1; $i <= 5; $i++ ) : ?>
					<button 
						type="button"
						class="tutor-btn tutor-btn-link tutor-p-none tutor-min-h-0"
						@click="setRating(<?php echo esc_attr( $i ); ?>, (rating) => setValue('<?php echo esc_attr( $this->field_name ); ?>', rating))"
						@mouseenter="hoverRating = <?php echo esc_attr( $i ); ?>"
					>
						<template x-if="effectiveRating >= <?php echo esc_attr( $i ); ?>">
							<span class="tutor-icon-exception4 tutor-flex-center">
												<?php echo $star_fill; // phpcs:ignore ?>
							</span>
						</template>
						<template x-if="effectiveRating > <?php echo esc_attr( $i - 1 ); ?> && effectiveRating < <?php echo esc_attr( $i ); ?>">
							<span class="tutor-icon-exception4 tutor-flex-center">
												<?php echo $star_half; // phpcs:ignore ?>
							</span>
						</template>
						<template x-if="effectiveRating <= <?php echo esc_attr( $i - 1 ); ?>">
							<span class="tutor-icon-exception4 tutor-flex-center">
												<?php echo $star; // phpcs:ignore ?>
							</span>
						</template>
					</button>
				<?php endfor; ?>
			</div>

			<span class="tutor-small tutor-font-medium" x-text="feedback"></span>
		</div>
		<?php

		$this->component_string = ob_get_clean();
		return $this->component_string;
	}
}
