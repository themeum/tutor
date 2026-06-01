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
use Tutor\Components\Button;
use Tutor\Components\ConfirmationModal;
use Tutor\Components\Constants\Size;
use Tutor\Components\Constants\Variant;
use Tutor\Components\EmptyState;
use Tutor\Components\Modal;
use Tutor\Components\Pagination;
use Tutor\Components\Progress;
use Tutor\Components\StarRating;
use Tutor\Components\SvgIcon;

// Get course ID from global variable set in learning-area/index.php .
global $tutor_course_id,
$current_user_id,
$tutor_is_enrolled;

// Pagination setup.
$review_per_page = tutor_utils()->get_option( 'pagination_per_page', 10 );
$current_page    = max( 1, Input::get( 'current_page', 1, Input::TYPE_INT ) );
$offset          = ( $current_page - 1 ) * $review_per_page;

$course_rating = tutor_utils()->get_course_rating( $tutor_course_id );
$total_items   = (int) tutor_utils()->get_course_reviews( $tutor_course_id, null, null, true, array( 'approved' ), $current_user_id );
$reviews       = tutor_utils()->get_course_reviews( $tutor_course_id, $offset, $review_per_page, false, array( 'approved' ), $current_user_id );
$my_rating     = tutor_utils()->get_reviews_by_user( 0, 0, null, false, $tutor_course_id, array( 'approved', 'hold' ) );

if ( ! empty( $my_rating ) ) {
	$my_rating    = is_array( $my_rating ) ? $my_rating[0] : $my_rating;
	$my_rating_id = $my_rating->comment_ID;

	$reviews = array_filter(
		$reviews,
		function ( $review ) use ( $my_rating_id ) {
			return $review->comment_ID !== $my_rating_id;
		}
	);

	$reviews = array_values( $reviews );
	array_unshift( $reviews, $my_rating );
}

?>

<div class="tutor-py-8 tutor-learning-area-reviews">
	<div class="tutor-flex tutor-items-center tutor-justify-between tutor-mb-5">
		<h4 class="tutor-h4 tutor-flex tutor-items-center tutor-gap-4">
			<?php SvgIcon::make()->name( Icon::RATINGS )->size( 24 )->render(); ?>
			<?php esc_html_e( 'Reviews', 'tutor' ); ?>
		</h4>
		<?php
		if ( $tutor_is_enrolled && empty( $my_rating ) ) {
			?>
			<div>
				<?php
				Button::make()
					->label( __( 'Write a Review', 'tutor' ) )
					->size( Size::SMALL )
					->attr( 'x-on:click', 'TutorCore.modal.showModal("create-review-modal")' )
					->render();

				$modal_template = tutor_get_template( 'learning-area.subpages.reviews.create-review-modal' );
				Modal::make()
					->id( 'create-review-modal' )
					->title( __( 'How Was Your Experience?', 'tutor' ) )
					->subtitle( __( 'Your feedback helps others find the right course.', 'tutor' ) )
					->width( '452px' )
					->template( $modal_template )
					->render();
				?>
			</div>
			<?php
		}
		?>
	</div>

	<div class="tutor-card tutor-card-rounded-2xl tutor-p-none">
	<?php if ( empty( $reviews ) ) : ?>
		<?php
			EmptyState::make()
				->title( __( 'No Reviews Found!', 'tutor' ) )
				->icon( tutor_utils()->get_themed_svg( 'images/illustrations/reviews-empty.svg' ) )
				->render();
		?>
	<?php else : ?>
		<div class="tutor-grid tutor-grid-cols-2 tutor-sm-grid-cols-1 tutor-gap-4 tutor-sm-gap-6 tutor-p-6">
			<div class="tutor-flex tutor-flex-column tutor-gap-4 tutor-justify-between">
				<div class="tutor-medium tutor-font-medium" style="max-width: 172px;">
					<?php esc_html_e( 'Student Ratings & Reviews', 'tutor' ); ?>
				</div>
				<div class="tutor-average-rating tutor-surface-l1-hover tutor-rounded-sm tutor-py-3 tutor-px-5 tutor-flex tutor-flex-column tutor-gap-1 tutor-w-fit">
					<div class="tutor-flex tutor-items-center tutor-gap-5">
						<div class="tutor-h4 tutor-font-bold">
							<?php echo esc_html( number_format_i18n( (float) $course_rating->rating_avg, 1 ) ); ?>
						</div>
						<?php
						StarRating::make()
							->rating( $course_rating->rating_avg )
							->show_average( false )
							->render();
						?>
					</div>
					<div class="tutor-tiny tutor-text-secondary">
						<?php
						printf(
							/* translators: %s is total rating count */
							esc_html( _n( 'Based on %s rating', 'Based on %s ratings', $total_items, 'tutor' ) ),
							esc_html( number_format_i18n( $total_items ) )
						);
						?>
					</div>
				</div>
			</div>
			<div class="tutor-flex tutor-flex-column tutor-gap-3">
				<?php for ( $i = 5; $i >= 1; $i-- ) : ?>
					<?php
					$count   = (int) ( $course_rating->count_by_value[ $i ] ?? 0 );
					$percent = $course_rating->rating_count > 0 ? ( $count * 100 ) / $course_rating->rating_count : 0;
					?>
					<div class="tutor-flex tutor-items-center tutor-gap-5">
						<div class="tutor-flex tutor-items-center tutor-gap-3">
							<?php SvgIcon::make()->name( Icon::STAR_FILL )->size( 12 )->attr( 'class', 'tutor-icon-exception4' )->render(); ?>
							<span class="tutor-small" style="font-variant-numeric: tabular-nums;"><?php echo esc_html( $i ); ?></span>
						</div>
						<?php Progress::make()->variant( Variant::WARNING )->value( $percent )->animated()->render(); ?>
						<div class="tutor-small tutor-flex-shrink-0" style="min-width: 80px;">
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

		<div>
		<?php foreach ( $reviews as $review ) : ?>
			<?php tutor_load_template( 'learning-area.subpages.reviews.review-card', array( 'review' => $review ) ); ?>
		<?php endforeach; ?>
		</div>

		<div x-data="tutorReviewDeleteModal()" x-cloak>
			<?php
				ConfirmationModal::make()
					->id( 'review-delete-modal' )
					->title( __( 'Delete your Review?', 'tutor' ) )
					->message( __( 'Are you sure you want to delete this review? This action cannot be undone.', 'tutor' ) )
					->icon( tutor_utils()->get_themed_svg( 'images/illustrations/delete-reviews.svg' ), 80, 80, ConfirmationModal::ICON_TYPE_HTML )
					->confirm_handler( 'handleDeleteReview(payload?.id)' )
					->mutation_state( 'deleteReviewMutation' )
					->confirm_text( __( 'Yes, Delete This', 'tutor' ) )
					->cancel_text( __( 'Cancel', 'tutor' ) )
					->render();
			?>
		</div>
	<?php endif; ?>
	</div>

	<?php
	Pagination::make()
		->current( $current_page )
		->total( $total_items )
		->limit( $review_per_page )
		->attr( 'class', 'tutor-mt-6' )
		->render();
	?>
</div>
