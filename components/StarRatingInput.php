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

use TUTOR\Icon;

defined( 'ABSPATH' ) || exit;

/**
 * Class StarRatingInput
 *
 * Example Usage:
 * ```
 * StarRatingInput::make()
 *     ->fieldName('rating')
 *     ->currentRating(4.5)
 *     ->render();
 * ```
 *
 * @since 4.0.0
 */
class StarRatingInput extends BaseComponent {

	/**
	 * Form field name
	 *
	 * @var string
	 */
	protected $field_name = '';

	/**
	 * Current rating value
	 *
	 * @var float
	 */
	protected $current_rating = 0;

	/**
	 * On change callback function name or js snippet
	 *
	 * @var string
	 */
	protected $on_change = '';

	/**
	 * Alpine register function binding
	 *
	 * @var string
	 */
	protected $register = '';

	/**
	 * Icon size
	 *
	 * @var int
	 */
	protected $icon_size = 20;

	/**
	 * Set field name
	 *
	 * @param string $name field name.
	 *
	 * @return self
	 */
	public function fieldName( string $name ): self {
		$this->field_name = $name;
		return $this;
	}

	/**
	 * Set current rating
	 *
	 * @param float $rating rating.
	 *
	 * @return self
	 */
	public function currentRating( float $rating ): self {
		$this->current_rating = $rating;
		return $this;
	}

	/**
	 * Set on change callback
	 *
	 * @param string $callback callback.
	 *
	 * @return self
	 */
	public function onChange( string $callback ): self {
		$this->on_change = $callback;
		return $this;
	}

	/**
	 * Set register binding
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
	 * @return string
	 */
	public function get(): string {
		$current_rating = $this->current_rating;
		$star_fill      = tutor_utils()->get_svg_icon( Icon::STAR_FILL, $this->icon_size, $this->icon_size );
		$star_half      = tutor_utils()->get_svg_icon( Icon::STAR_HALF, $this->icon_size, $this->icon_size );
		$star           = tutor_utils()->get_svg_icon( Icon::STAR_2, $this->icon_size, $this->icon_size );

		ob_start();
		?>
		<div 
			class="tutor-flex tutor-gap-4 tutor-justify-between tutor-items-center"
			x-data="tutorStarRatingInput({
				initialRating: <?php echo esc_js( $current_rating ); ?>,
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
						@click="setRating(<?php echo (int) $i; ?>, (rating) => setValue('<?php echo esc_attr( $this->field_name ); ?>', rating))"
						@mouseenter="hoverRating = <?php echo (int) $i; ?>"
					>
						<template x-if="effectiveRating >= <?php echo (int) $i; ?>">
							<span class="tutor-icon-exception4 tutor-flex-center">
								<?php echo $star_fill; // phpcs:ignore ?>
							</span>
						</template>
						<template x-if="effectiveRating > <?php echo (int) ( $i - 1 ); ?> && effectiveRating < <?php echo (int) $i; ?>">
							<span class="tutor-icon-exception4 tutor-flex-center">
								<?php echo $star_half; // phpcs:ignore ?>
							</span>
						</template>
						<template x-if="effectiveRating <= <?php echo (int) ( $i - 1 ); ?>">
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
		return ob_get_clean();
	}
}
