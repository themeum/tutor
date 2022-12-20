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

?>
<form class="tutor-modal tutor-is-active tutor-course-review-popup-form">
	<div class="tutor-modal-overlay"></div>
	<div class="tutor-modal-window">
		<div class="tutor-modal-content tutor-modal-content-white">
			<button class="tutor-iconic-btn tutor-modal-close-o" data-tutor-modal-close>
				<span class="tutor-icon-times" area-hidden="true"></span>
			</button>

			<div class="tutor-modal-body tutor-text-center">
				<div class="tutor-fs-3 tutor-fw-medium tutor-color-black tutor-mt-48 tutor-mb-12"><?php esc_html_e( 'How would you rate this course?', 'tutor' ); ?></div>
				<div class="tutor-fs-6 tutor-color-muted"><?php esc_html_e( 'Select Rating', 'tutor' ); ?></div>

				<input type="hidden" name="course_id" value="<?php echo esc_attr( $course_id ); ?>"> 
				<input type="hidden" name="review_id" value="<?php echo esc_attr( isset( $review_id ) ? $review_id : '' ); ?>"/>
				<input type="hidden" name="action" value="tutor_place_rating" />

				<div class="tutor-ratings tutor-ratings-xl tutor-ratings-selectable tutor-justify-center tutor-mt-16" tutor-ratings-selectable>
					<?php
						tutor_utils()->star_rating_generator( tutor_utils()->get_rating_value() );
					?>
				</div>

				<textarea name="review" class="tutor-form-control tutor-mt-28" placeholder="<?php esc_attr_e( 'Tell us about your own personal experience taking this course. Was it a good match for you?', 'tutor' ); ?>"></textarea>

				<div class="tutor-d-flex tutor-justify-center tutor-my-48">
					<button type="button" class="tutor-btn tutor-btn-outline-primary" data-tutor-modal-close>
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
