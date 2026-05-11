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
use Tutor\Components\Constants\Size;

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
	 * View type (star|emoji)
	 *
	 * @var string
	 */
	protected $view = 'star';

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
	 * Set view type
	 *
	 * @since 4.0.0
	 *
	 * @param string $view view type.
	 *
	 * @return self
	 */
	public function view( string $view ): self {
		$this->view = $view;
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

		$is_emoji     = 'emoji' === $this->view;
		$emoji_images = array(
			1 => 'poor.png',
			2 => 'fair.png',
			3 => 'okay.png',
			4 => 'good.png',
			5 => 'amazing.png',
		);
		$emoji_url    = tutor()->url . 'assets/images/emojis/';

		$labels = array(
			1 => __( 'Poor', 'tutor' ),
			2 => __( 'Fair', 'tutor' ),
			3 => __( 'Okay', 'tutor' ),
			4 => __( 'Good', 'tutor' ),
			5 => __( 'Amazing', 'tutor' ),
		);

		$star_fill = SvgIcon::make()->name( Icon::STAR_FILL )->size( $is_emoji ? Size::SIZE_32 : Size::SIZE_24 )->ignore_kids()->get();
		$star_half = SvgIcon::make()->name( Icon::STAR_HALF )->size( $is_emoji ? Size::SIZE_32 : Size::SIZE_24 )->ignore_kids()->get();
		$star      = SvgIcon::make()->name( Icon::STAR_LINE )->size( $is_emoji ? Size::SIZE_32 : Size::SIZE_24 )->ignore_kids()->get();

		ob_start();
		?>
		<div 
			class="tutor-star-rating-container <?php echo $is_emoji ? 'is-emoji-view' : ''; ?>"
			x-data="tutorStarRatingInput({
				initialRating: <?php echo esc_attr( $current_rating ); ?>,
				fieldName: '<?php echo esc_attr( $this->field_name ); ?>'
			})"
		>
			<input 
				type="hidden" 
				name="<?php echo esc_attr( $this->field_name ); ?>" 
				x-bind="register('<?php echo esc_attr( $this->field_name ); ?>' )"
			>

			<?php if ( $is_emoji ) : ?>
				<div class="tutor-flex tutor-items-center tutor-gap-4 tutor-justify-center">
					<?php
					$rating_classes = array(
						1 => 'is-poor',
						2 => 'is-fair',
						3 => 'is-okay',
						4 => 'is-good',
						5 => 'is-amazing',
					);
					?>
					<?php foreach ( $emoji_images as $i => $image ) : ?>
						<button 
							type="button"
							class="tutor-star-rating-emoji-btn <?php echo esc_attr( $rating_classes[ $i ] ); ?>"
							:class="{'is-active': rating == <?php echo (int) $i; ?>}"
							@click="setRating(<?php echo esc_attr( $i ); ?>, (rating) => setValue('<?php echo esc_attr( $this->field_name ); ?>', rating))"
						>
							<img src="<?php echo esc_url( $emoji_url . $image ); ?>" alt="<?php echo esc_attr( $labels[ $i ] ); ?>" class="tutor-rating-emoji-img" width="32" height="32">
							<span class="tutor-rating-label">
								<?php echo esc_html( $labels[ $i ] ); ?>
							</span>
						</button>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<div class="tutor-flex tutor-items-center tutor-gap-2 tutor-justify-center" @mouseleave="hoverRating = 0">
				<?php for ( $i = 1; $i <= 5; $i++ ) : ?>
					<button 
						type="button"
						class="tutor-star-rating-icon-btn tutor-btn tutor-p-none tutor-min-h-0 tutor-bg-transparent"
						@click="setRating(<?php echo esc_attr( $i ); ?>, (rating) => setValue('<?php echo esc_attr( $this->field_name ); ?>', rating))"
						@mouseenter="hoverRating = <?php echo esc_attr( $i ); ?>"
					>
						<template x-if="effectiveRating >= <?php echo esc_attr( $i ); ?>">
							<span class="tutor-icon-exception4 tutor-flex">
								<?php echo $star_fill; // phpcs:ignore ?>
							</span>
						</template>
						<template x-if="effectiveRating > <?php echo esc_attr( $i - 1 ); ?> && effectiveRating < <?php echo esc_attr( $i ); ?>">
							<span class="tutor-icon-exception4 tutor-flex">
								<?php echo $star_half; // phpcs:ignore ?>
							</span>
						</template>
						<template x-if="effectiveRating <= <?php echo esc_attr( $i - 1 ); ?>">
							<span class="tutor-icon-exception4 tutor-flex">
								<?php echo $star; // phpcs:ignore ?>
							</span>
						</template>
					</button>
				<?php endfor; ?>
			</div>

			<?php if ( ! $is_emoji ) : ?>
				<span class="tutor-small tutor-font-medium" x-text="feedback"></span>
			<?php endif; ?>
		</div>
		<?php

		$this->component_string = ob_get_clean();
		return $this->component_string;
	}
}
