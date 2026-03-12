<?php
/**
 * Tutor learning area reviews.
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use TUTOR\Icon;
use TUTOR\Input;
use Tutor\Components\Avatar;
use Tutor\Components\Constants\Size;
use Tutor\Components\EmptyState;
use Tutor\Components\Pagination;
use Tutor\Components\StarRating;
use Tutor\Helpers\DateTimeHelper;

// Get course ID from global variable set in learning-area/index.php .
global $tutor_course_id;

$disable = ! get_tutor_option( 'enable_course_review' );
if ( $disable ) {
	EmptyState::make()->title( __( 'Reviews are disabled', 'tutor' ) )->render();
	return;
}

// Pagination setup.
$review_per_page = tutor_utils()->get_option( 'pagination_per_page', 10 );
$current_page    = max( 1, Input::get( 'current_page', 1, Input::TYPE_INT ) );
$offset          = ( $current_page - 1 ) * $review_per_page;

$course_rating = tutor_utils()->get_course_rating( $tutor_course_id );
$total_items   = (int) tutor_utils()->get_course_reviews( $tutor_course_id, null, null, true, array( 'approved' ) );
$reviews       = tutor_utils()->get_course_reviews( $tutor_course_id, $offset, $review_per_page, false, array( 'approved' ) );

?>

<div class="tutor-py-7 tutor-learning-area-reviews">
	<h4 class="tutor-h4 tutor-mb-5 tutor-flex tutor-items-center tutor-gap-4">
		<?php tutor_utils()->render_svg_icon( Icon::RATINGS, 24, 24 ); ?>
		<?php esc_html_e( 'Reviews', 'tutor' ); ?>
	</h4>

	<?php if ( empty( $reviews ) ) : ?>
		<?php EmptyState::make()->title( __( 'No Reviews Found', 'tutor' ) )->render(); ?>
	<?php else : ?>
		<div class="tutor-card tutor-p-none">
			<div class="tutor-review-summary tutor-flex tutor-items-start tutor-justify-between tutor-gap-6 tutor-sm-flex-column tutor-sm-items-stretch tutor-p-6 tutor-sm-p-5 tutor-mb-6">
				<div>
					<h4 class="tutor-h4 tutor-mb-8">
						<?php esc_html_e( 'Student Ratings & Reviews', 'tutor' ); ?>
					</h4>
					<div class="tutor-flex tutor-items-center tutor-gap-5">
						<div class="tutor-h2 tutor-font-bold">
							<?php echo esc_html( number_format_i18n( (float) $course_rating->rating_avg, 1 ) ); ?>
						</div>
						<div>
							<?php
							StarRating::make()
								->rating( $course_rating->rating_avg )
								->count( $course_rating->rating_count )
								->show_average( false )
								->render();
							?>
						</div>
					</div>
					<div class="tutor-small tutor-text-secondary">
						<?php
						printf(
							/* translators: %s is total rating count */
							esc_html( _n( 'Based on %s rating', 'Based on %s ratings', $total_items, 'tutor' ) ),
							esc_html( number_format_i18n( $total_items ) )
						);
						?>
					</div>
				</div>
				<div class="tutor-flex tutor-flex-column tutor-gap-3 tutor-w-full" style="max-width: 520px;">
					<?php for ( $i = 5; $i >= 1; $i-- ) : ?>
						<?php
						$count   = (int) ( $course_rating->count_by_value[ $i ] ?? 0 );
						$percent = $course_rating->rating_count > 0 ? ( $count * 100 ) / $course_rating->rating_count : 0;
						?>
						<div class="tutor-flex tutor-items-center tutor-gap-3">
							<div class="tutor-flex tutor-items-center tutor-gap-2 tutor-tiny tutor-text-secondary" style="width: 44px;">
							<?php tutor_utils()->render_svg_icon( Icon::STAR_FILL, 14, 14, array( 'color' => '#fdb022' ) ); ?>
							<span class="tutor-text-primary tutor-font-medium"><?php echo esc_html( $i ); ?></span>
							</div>
							<div class="tutor-progress-bar tutor-w-full" data-tutor-animated>
								<div class="tutor-progress-bar-fill" style="--tutor-progress-width: <?php echo esc_attr( $percent ); ?>%; background: var(--tutor-icon-exception4);"></div>
							</div>
							<div class="tutor-tiny tutor-text-secondary" style="width: 88px; text-align: right;">
								<?php
								printf(
									/* translators: %s is rating count */
									esc_html( _n( '%s rating', '%s ratings', $count, 'tutor' ) ),
									esc_html( number_format_i18n( $count ) )
								);
								?>
							</div>
						</div>
					<?php endfor; ?>
				</div>
			</div>

			<div class="tutor-flex tutor-flex-column tutor-gap-5 tutor-mb-6">
			<?php foreach ( $reviews as $review ) : ?>
				<?php
				$review->comment_content = wp_kses_post( htmlspecialchars( stripslashes( $review->comment_content ?? '' ) ) );
				?>
				<div class="tutor-single-review">
					<div class="tutor-flex tutor-items-start tutor-justify-between tutor-gap-4 tutor-sm-flex-column">
						<div class="tutor-flex tutor-items-center tutor-gap-3">
							<?php Avatar::make()->user( (int) $review->user_id )->size( Size::SIZE_40 )->render(); ?>
							<div class="tutor-flex tutor-flex-column tutor-gap-1">
								<div class="tutor-medium tutor-font-medium">
									<?php echo esc_html( $review->display_name ?? '' ); ?>
								</div>
								<div class="tutor-tiny tutor-text-secondary">
								<?php
								/* translators: %s: time difference */
								echo esc_html( sprintf( __( '%s ago', 'tutor' ), human_time_diff( strtotime( $review->comment_date ) ) ) );
								?>
								</div>
							</div>
						</div>

						<div class="tutor-mt-1 tutor-sm-mt-0">
							<?php StarRating::make()->rating( (float) ( $review->rating ?? 0 ) )->render(); ?>
						</div>
					</div>

					<div class="tutor-mt-4 tutor-text-secondary tutor-small">
						<?php echo esc_textarea( html_entity_decode( $review->comment_content ?? '' ) ); ?>
					</div>
				</div>
			<?php endforeach; ?>
			</div>
		</div>


	<?php endif; ?>

	<?php
	Pagination::make()
		->current( $current_page )
		->total( $total_items )
		->limit( $review_per_page )
		->attr( 'class', 'tutor-mt-6' )
		->render();
	?>
</div>

