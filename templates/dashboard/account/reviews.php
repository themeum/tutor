<?php
/**
 * Tutor dashboard reviews.
 *
 * @package Tutor\Templates
 * @subpackage Dashboard
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use TUTOR\Input;
use TUTOR\User;
use Tutor\Components\ConfirmationModal;
use Tutor\Components\EmptyState;
use Tutor\Components\Pagination;

$item_per_page = tutor_utils()->get_option( 'pagination_per_page', 20 );
$current_page  = max( 1, Input::get( 'current_page', 0, Input::TYPE_INT ) );
$offset        = ( $current_page - 1 ) * $item_per_page;

$all_reviews  = User::is_student_view() ?
	tutor_utils()->get_reviews_by_user( 0, $offset, $item_per_page, true, null, array( 'approved', 'hold' ) ) :
	tutor_utils()->get_reviews_by_instructor( 0, $offset, $item_per_page );
$review_count = $all_reviews->count;
$reviews      = $all_reviews->results;
$is_editable  = User::is_student_view();
?>

<?php require_once tutor_get_template( 'account-header' ); ?>

<div class="tutor-user-reviews" role="main">
	<div class="tutor-account-container">
	<?php if ( is_array( $reviews ) && count( $reviews ) ) : ?>
		<div class="tutor-flex tutor-flex-column tutor-gap-5">
			<?php foreach ( $reviews as $review ) : ?>
				<?php
					$review->is_editable = $is_editable;
					$review->user_avatar = tutor_utils()->get_user_avatar_url( $review->user_id );
					tutor_load_template( 'dashboard.account.reviews.review-card', array( 'review' => $review ) );
				?>
			<?php endforeach; ?>
		</div>

		<?php
		Pagination::make()
			->current( $current_page )
			->total( $review_count )
			->limit( $item_per_page )
			->attr( 'class', 'tutor-mt-6' )
			->render();
		?>

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

		<?php else : ?>
			<div class="tutor-card">
				<?php
					EmptyState::make()
						->title( __( 'No Reviews Found', 'tutor' ) )
						->icon( tutor_utils()->get_themed_svg( 'images/illustrations/reviews-empty.svg' ) )
						->render();
				?>
			</div>
		<?php endif; ?>
	</div>
</div>
