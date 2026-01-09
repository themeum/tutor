<?php
/**
 * StarRating Component Class.
 *
 * Renders a static star rating display.
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
 * Class StarRating
 *
 * Example Usage:
 * ```
 * StarRating::make()
 *     ->rating(4.5)
 *     ->showAverage(true)
 *     ->render();
 * ```
 *
 * @since 4.0.0
 */
class StarRating extends BaseComponent {

	/**
	 * Rating value
	 *
	 * @var float
	 */
	protected $rating = 0;

	/**
	 * Whether to show numerical rating
	 *
	 * @var bool
	 */
	protected $show_average = false;

	/**
	 * Star icon size
	 *
	 * @var int
	 */
	protected $icon_size = 16;

	/**
	 * Review count
	 *
	 * @var int|null
	 */
	protected $count = null;

	/**
	 * Set rating value
	 *
	 * @param float $rating rating.
	 *
	 * @return self
	 */
	public function rating( float $rating ): self {
		$this->rating = $rating;
		return $this;
	}

	/**
	 * Set whether to show average rating text
	 *
	 * @param bool $show show average.
	 *
	 * @return self
	 */
	public function show_average( bool $show = true ): self {
		$this->show_average = $show;
		return $this;
	}

	/**
	 * Set icon size
	 *
	 * @param int $size size.
	 *
	 * @return self
	 */
	public function icon_size( int $size ): self {
		$this->icon_size = $size;
		return $this;
	}

	/**
	 * Set review count
	 *
	 * @param int $count count.
	 *
	 * @return self
	 */
	public function count( int $count ): self {
		$this->count = $count;
		return $this;
	}

	/**
	 * Get component content
	 *
	 * @return string
	 */
	public function get(): string {
		$rating    = $this->rating;
		$icon_size = $this->icon_size;

		$star_fill = tutor_utils()->get_svg_icon( Icon::STAR_FILL, $icon_size, $icon_size );
		$star_half = tutor_utils()->get_svg_icon( Icon::STAR_HALF, $icon_size, $icon_size );
		$star      = tutor_utils()->get_svg_icon( Icon::STAR_LINE, $icon_size, $icon_size );

		ob_start();
		?>
		<div class="tutor-ratings-stars tutor-flex tutor-items-center tutor-gap-1" data-rating-value="<?php echo esc_attr( $rating ); ?>">
			<div class="tutor-flex tutor-items-center tutor-gap-1">
				<?php for ( $i = 1; $i <= 5; $i++ ) : ?>
					<span class="tutor-icon-exception4 tutor-flex-center">
						<?php
						if ( (int) $rating >= $i ) {
							echo $star_fill; // phpcs:ignore
						} elseif ( ( $rating - $i ) >= -0.5 ) {
							echo $star_half; // phpcs:ignore
						} else {
							echo $star; // phpcs:ignore
						}
						?>
					</span>
				<?php endfor; ?>
			</div>

			<?php if ( $this->show_average || $this->count ) : ?>
				<div class="tutor-ratings-meta tutor-flex tutor-items-center tutor-gap-1 tutor-ml-1">
					<?php if ( $this->show_average ) : ?>
						<span class="tutor-small tutor-font-medium tutor-text-primary">
							<?php echo esc_html( number_format( $rating, 1 ) ); ?>
						</span>
					<?php endif; ?>

					<?php if ( $this->count ) : ?>
						<span class="tutor-small tutor-text-subdued">
							<?php
							/* translators: %s: review count */
							echo esc_html( sprintf( _n( '(%s Review)', '(%s Reviews)', $this->count, 'tutor' ), number_format_i18n( $this->count ) ) );
							?>
						</span>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
		<?php

		$this->component_string = ob_get_clean();
		return $this->component_string;
	}
}
