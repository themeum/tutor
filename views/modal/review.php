<?php
/**
 * Review Form: To be loaded after course completion button click
 *
 * @package Tutor\Views
 * @subpackage Tutor\Modal
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.0.0
 */

use Tutor\Components\Modal;

$is_course_details_page = tutor_utils()->is_course_details_page();

$modal_id = 'tutor-review-modal-' . $course_id;
?>
<?php if ( $is_course_details_page ) : ?> 
<form class="tutor-modal tutor-is-active tutor-course-review-popup-form">
	<div class="tutor-modal-overlay"></div>
	<div class="tutor-modal-window">
		<div class="tutor-modal-content tutor-modal-content-white">
			<button type="button" class="tutor-iconic-btn tutor-modal-close-o" data-tutor-modal-close aria-label="<?php esc_attr_e( 'Close', 'tutor' ); ?>">
				<span class="tutor-icon-times" aria-hidden="true"></span>
			</button>

			<div class="tutor-modal-body tutor-text-center">
				<div id="<?php echo esc_attr( $modal_id ); ?>-title" class="tutor-fs-3 tutor-fw-medium tutor-color-black tutor-mt-48 tutor-mb-12"><?php esc_html_e( 'How would you rate this course?', 'tutor' ); ?></div>
				<div class="tutor-fs-6 tutor-color-muted"><?php esc_html_e( 'Select Rating', 'tutor' ); ?></div>

				<input type="hidden" name="course_id" value="<?php echo esc_attr( $course_id ); ?>"> 
				<input type="hidden" name="review_id" value="<?php echo esc_attr( isset( $review_id ) ? $review_id : '' ); ?>"/>
				<input type="hidden" name="action" value="tutor_place_rating" />

				<div class="tutor-ratings tutor-ratings-xl tutor-ratings-selectable tutor-justify-center tutor-mt-16" tutor-ratings-selectable>
					<?php
						tutor_utils()->star_rating_generator( tutor_utils()->get_rating_value() );
					?>
				</div>

				<textarea name="review" class="tutor-form-control tutor-mt-28" aria-label="<?php esc_attr_e( 'Tell us about your own personal experience taking this course', 'tutor' ); ?>" placeholder="<?php esc_attr_e( 'Tell us about your own personal experience taking this course. Was it a good match for you?', 'tutor' ); ?>"></textarea>

				<div class="tutor-d-flex tutor-justify-center tutor-my-48">
					<button type="button" class="tutor-review-popup-cancel tutor-btn tutor-btn-outline-primary" data-tutor-modal-close>
						<?php esc_html_e( 'Cancel', 'tutor' ); ?>
					</button>
					<button type="submit" class="tutor_submit_review_btn tutor-btn tutor-btn-primary tutor-ml-20">
						<?php esc_html_e( 'Update Review', 'tutor' ); ?>
					</button>
				</div>
			</div>
		</div>
	</div>
</form>
<?php else : ?>

	<?php
	$modal_template = tutor_get_template( 'learning-area.subpages.reviews.create-review-modal' );
	Modal::make()
		->id( 'create-review-modal' )
		->title( __( 'How Was Your Experience?', 'tutor' ) )
		->subtitle( __( 'Your feedback helps others find the right course.', 'tutor' ) )
		->template( $modal_template, array( 'clear_review_popup_data' => true ) )
		->width( '452px' )
		->state( 'open' )
		->closeable( false )
		->render();
	?>

<?php endif; ?>
