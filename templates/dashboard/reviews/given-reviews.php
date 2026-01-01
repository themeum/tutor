<?php
/**
 *  Tutor dashboard reviews.
 *
 *  @package Tutor\Templates
 *  @author Themeum <support@themeum.com>
 *  @link https://themeum.com
 *  @since 4.0.0
 */

use TUTOR\Icon;
use TUTOR\Input;
use Tutor\Components\ConfirmationModal;
use Tutor\Components\EmptyState;
use Tutor\Components\Modal;
use Tutor\Components\Pagination;
use Tutor\Components\Button;
use Tutor\Components\Constants\Size;
use Tutor\Components\Constants\Variant;

// Pagination Variable.
$pagination_per_page = tutor_utils()->get_option( 'pagination_per_page', 20 );
$current_page        = max( 1, Input::get( 'current_page', 0, Input::TYPE_INT ) );
$offset              = ( $current_page - 1 ) * $pagination_per_page;


$all_reviews    = tutor_utils()->get_reviews_by_user( 0, $offset, $pagination_per_page, true );
$review_count   = $all_reviews->count;
$reviews        = $all_reviews->results;
$received_count = tutor_utils()->get_reviews_by_instructor( 0, 0, 0 )->count;

$converted_reviews = array_map(
	function ( $review ) {
		return array(
			'id'             => $review->comment_ID,
			'post_id'        => $review->comment_post_ID, // course or bundle id.
			'title'          => $review->course_title, // course or bundle title.
			'review_date'    => $review->comment_date,
			'rating'         => $review->rating,
			'is_bundle'      => false, // Currently only course reviews are supported.
			'review_content' => $review->comment_content,
		);
	},
	$reviews ?? array()
);

$bin_icon = tutor_utils()->get_svg_icon( Icon::BIN );
?>

<?php if ( $all_reviews->count > 0 ) : ?>
	<div class="tutor-user-reviews">
		<div class="tutor-profile-container">
			<div class="tutor-flex tutor-flex-column tutor-gap-5 tutor-mt-9">
				<?php foreach ( $converted_reviews as $review ) : ?>
					<?php tutor_load_template( 'dashboard.reviews.review-card', array( 'review' => $review ) ); ?>
				<?php endforeach; ?>
			</div>

			<?php if ( $all_reviews->count > $pagination_per_page ) : ?>
				<div class="tutor-mt-6">
					<?php
						Pagination::make()
						->current( $current_page )
						->total( $all_reviews->count )
						->limit( $pagination_per_page )
						->render();
					?>
				</div>
			<?php endif; ?>
		</div>

		<div x-data="tutorReviewDeleteModal()" x-cloak>
			<?php
			ConfirmationModal::make()
				->id( 'review-delete-modal' )
				->title( __( 'Delete This Review?', 'tutor' ) )
				->message( __( 'Are you sure you want to delete this review? Please confirm your choice.', 'tutor' ) )
				->confirm_handler( 'handleDeleteReview(payload?.id)' )
				->mutation_state( 'deleteReviewMutation' )
				->confirm_button(
					Button::make()
						->attr( '@click', 'handleDeleteReview(payload?.id)' )
						->label( __( 'Yes, Delete This', 'tutor' ) )
						->variant( Variant::DESTRUCTIVE )
						->size( Size::X_SMALL )
						->get()
				)
				->cancel_button(
					Button::make()
						->attr( '@click', 'TutorCore.modal.closeModal("review-delete-modal")' )
						->label( __( 'Cancel', 'tutor' ) )
						->variant( Variant::SECONDARY )
						->size( Size::X_SMALL )
						->get()
				)
				->render();
			?>
		</div>
	</div>
<?php else : ?>
	<?php
		EmptyState::make()
			->title( 'No Reviews Found' )
			->render();
	?>
<?php endif; ?>

<!-- @TODO: Need to use new DeleteConfirmationModal component -->
