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

defined( 'ABSPATH' ) || exit;

use TUTOR\Icon;

/**
 * Class StarRating
 *
 * Example Usage:
 * ```php
 * // Display stars only (no numbers)
 * StarRating::make()
 *     ->rating( 4.5 )
 *     ->render();
 *
 * // Stars with average numeric rating shown
 * StarRating::make()
 *     ->rating( 4.5 )
 *     ->show_average( true )
 *     ->render();
 *
 * // Stars with review count
 * StarRating::make()
 *     ->rating( 3.8 )
 *     ->count( 120 )
 *     ->render();
 *
 * // Stars with average and review count
 * StarRating::make()
 *     ->rating( 4.2 )
 *     ->show_average( true )
 *     ->count( 58 )
 *     ->render();
 *
 * // Larger icon size
 * StarRating::make()
 *     ->rating( 5.0 )
 *     ->icon_size( 24 )
 *     ->show_average( true )
 *     ->render();
 *
 * // Zero rating (no stars filled)
 * StarRating::make()
 *     ->rating( 0 )
 *     ->show_average( true )
 *     ->count( 0 )
 *     ->render();
 *
 * // Retrieve HTML without echoing
 * $html = StarRating::make()->rating( 4.5 )->show_average( true )->count( 30 )->get();
 * ```
 *
 * @since 4.0.0
 */
class StarRating extends BaseComponent {

	/**
	 * Rating value
	 *
	 * @since 4.0.0
	 *
	 * @var float
	 */
	protected $rating = 0;

	/**
	 * Whether to show numerical rating
	 *
	 * @since 4.0.0
	 *
	 * @var bool
	 */
	protected $show_average = false;

	/**
	 * Star icon size
	 *
	 * @since 4.0.0
	 *
	 * @var int
	 */
	protected $icon_size = 16;

	/**
	 * Review count
	 *
	 * @since 4.0.0
	 *
	 * @var int|null
	 */
	protected $count = null;

	/**
	 * Set rating value
	 *
	 * @since 4.0.0
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
	 * @since 4.0.0
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
	 * @since 4.0.0
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
	 * @since 4.0.0
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
	 * @since 4.0.0
	 *
	 * @return string
	 */
	public function get(): string {
		$rating    = $this->rating;
		$icon_size = $this->icon_size;

		$star_fill = SvgIcon::make()->name( Icon::STAR_FILL )->size( $icon_size )->ignore_kids()->get();
		$star_half = SvgIcon::make()->name( Icon::STAR_HALF )->size( $icon_size )->ignore_kids()->get();
		$star      = SvgIcon::make()->name( Icon::STAR_LINE )->size( $icon_size )->ignore_kids()->get();

		ob_start();
		?>
		<div class="tutor-ratings-stars tutor-flex tutor-items-center tutor-gap-1" data-rating-value="<?php echo esc_attr( $rating ); ?>">
			<div class="tutor-flex tutor-items-center tutor-gap-1">
				<?php for ( $i = 1; $i <= 5; $i++ ) : ?>
					<span class="tutor-icon-exception4 tutor-flex-center">
						<?php
						if ( (int) $rating >= $i ) {
							echo $star_fill; // phpcs:ignore -- get_svg_icon returns escaped html
						} elseif ( ( $rating - $i ) >= -0.5 ) {
							echo $star_half; // phpcs:ignore -- get_svg_icon returns escaped html
						} else {
							echo $star; // phpcs:ignore -- get_svg_icon returns escaped html
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
