<?php
/**
 * My Own reviews
 *
 * @package Tutor\Templates
 * @subpackage Dashboard\Reviews
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.1.2
 */

use TUTOR\Input;

// Pagination Variable.
$per_page     = tutor_utils()->get_option( 'pagination_per_page', 20 );
$current_page = max( 1, Input::get( 'current_page', 0, Input::TYPE_INT ) );
$offset       = ( $current_page - 1 ) * $per_page;


$all_reviews    = tutor_utils()->get_reviews_by_user( 0, $offset, $per_page, true );
$review_count   = $all_reviews->count;
$reviews        = $all_reviews->results;
$received_count = tutor_utils()->get_reviews_by_instructor( 0, 0, 0 )->count;
?>

<div class="tutor-dashboard-content-inner">
	<div class="tutor-fs-5 tutor-fw-medium tutor-color-black tutor-mb-24"><?php esc_html_e( 'Reviews', 'tutor' ); ?></div>
	<?php if ( current_user_can( tutor()->instructor_role ) ) : ?>
		<div class="tutor-mb-32">
			<ul class="tutor-nav">
				<li class="tutor-nav-item">
					<a class="tutor-nav-link" href="<?php echo esc_url( tutor_utils()->get_tutor_dashboard_page_permalink( 'reviews' ) ); ?>"> <?php esc_html_e( 'Received', 'tutor' ); ?> (<?php echo esc_html( $received_count ); ?>)</a>
				</li>
				<li class="tutor-nav-item">
					<a class="tutor-nav-link is-active" href="<?php echo esc_url( tutor_utils()->get_tutor_dashboard_page_permalink( 'reviews/given-reviews' ) ); ?>"> <?php esc_html_e( 'Given', 'tutor' ); ?> (<?php echo esc_html( $review_count ); ?>)</a>
				</li>
			</ul>
		</div>
	<?php endif; ?>

	<div class="tutor-dashboard-reviews-wrap">
		<?php if ( ! is_array( $reviews ) || ! count( $reviews ) ) : ?>
			<div class="tutor-dashboard-content-inner">
				<?php tutor_utils()->tutor_empty_state( tutor_utils()->not_found_text() ); ?>
			</div>
		<?php endif; ?>

		<div class="tutor-dashboard-reviews">
			<?php
			foreach ( $reviews as $review ) :
				$profile_url = tutor_utils()->profile_url( $review->user_id, false );
				$update_id   = 'tutor_review_update_' . $review->comment_ID;
				$delete_id   = 'tutor_review_delete_' . $review->comment_ID;
				$row_id      = 'tutor_review_row_' . $review->comment_ID;
				?>
				<div id="<?php echo esc_html( $row_id ); ?>" class="tutor-card tutor-dashboard-single-review tutor-review-<?php echo esc_html( $review->comment_ID ); ?> tutor-mb-32">
					<div class="tutor-card-header">
						<h4 class="tutor-card-title">
						<?php esc_html_e( 'Course: ', 'tutor' ); ?>
							<span class="tutor-fs-6 tutor-fw-medium" data-href="<?php echo esc_url( get_the_permalink( $review->comment_post_ID ) ); ?>">
							<?php echo esc_html( get_the_title( $review->comment_post_ID ) ); ?>
							</span>
						</h4>
					</div>

					<div class="tutor-card-body">
						<div class="tutor-row tutor-align-center tutor-mb-24">
							<div class="tutor-col">
							<?php tutor_utils()->star_rating_generator_v2( $review->rating, null, true ); ?> 
							</div>

							<div class="tutor-col-auto">
								<div class="tutor-given-review-actions tutor-d-flex">
									<span class="tutor-btn tutor-btn-ghost" data-tutor-modal-target="<?php echo esc_html( $update_id ); ?>" role="button">
										<i class="tutor-icon-edit tutor-mr-8" area-hidden="true"></i>
										<span><?php esc_html_e( 'Edit', 'tutor' ); ?></span>
									</span>

									<span class="tutor-btn tutor-btn-ghost tutor-ml-16" data-tutor-modal-target="<?php echo esc_html( $delete_id ); ?>" role="button">
										<i class="tutor-icon-trash-can-line tutor-mr-8"  area-hidden="true"></i>
										<span><?php esc_html_e( 'Delete', 'tutor' ); ?></span>
									</span>
								</div>
							</div>
						</div>

						<div class="tutor-fs-6 tutor-color-muted">
						<?php echo esc_textarea( htmlspecialchars( stripslashes( $review->comment_content ) ) ); ?>
						</div>
					</div>

					<!-- Edit Review Modal -->
					<form class="tutor-modal" id="<?php echo esc_html( $update_id ); ?>">
						<div class="tutor-modal-overlay"></div>
						<div class="tutor-modal-window">
							<div class="tutor-modal-content tutor-modal-content-white">
								<button class="tutor-iconic-btn tutor-modal-close-o" data-tutor-modal-close>
									<span class="tutor-icon-times" area-hidden="true"></span>
								</button>

								<div class="tutor-modal-body tutor-text-center">
									<div class="tutor-fs-3 tutor-fw-medium tutor-color-black tutor-mt-48 tutor-mb-12"><?php esc_html_e( 'How would you rate this course?', 'tutor' ); ?></div>
									<div class="tutor-fs-6 tutor-color-muted"><?php esc_html_e( 'Select Rating', 'tutor' ); ?></div>

									<input type="hidden" name="course_id" value="<?php echo esc_html( $review->comment_post_ID ); ?>"/>
									<input type="hidden" name="review_id" value="<?php echo esc_html( $review->comment_ID ); ?>"/>
									<input type="hidden" name="action" value="tutor_place_rating" />

									<div class="tutor-ratings tutor-ratings-xl tutor-ratings-selectable tutor-justify-center tutor-mt-16" tutor-ratings-selectable>
									<?php
										tutor_utils()->star_rating_generator( tutor_utils()->get_rating_value( $review->rating ) );
									?>
									</div>

									<textarea class="tutor-form-control tutor-mt-28" name="review" placeholder="<?php esc_html_e( 'write a review', 'tutor' ); ?>"><?php echo esc_html( stripslashes( $review->comment_content ) ); ?></textarea>

									<div class="tutor-d-flex tutor-justify-center tutor-my-48">
										<button type="button" class="tutor-btn tutor-btn-outline-primary" data-tutor-modal-close data-action="back">
										<?php esc_html_e( 'Cancel', 'tutor' ); ?>
										</button>
										<button type="submit" class="tutor_submit_review_btn tutor-btn tutor-btn-primary tutor-ml-20" data-action="next">
										<?php esc_html_e( 'Update Review', 'tutor' ); ?>
										</button>
									</div>
								</div>
							</div>
						</div>
					</form>

					<!-- Delete Modal -->
					<?php
					tutor_load_template(
						'modal.confirm',
						array(
							'id'      => $delete_id,
							'image'   => 'icon-trash.svg',
							'title'   => __( 'Do You Want to Delete This Review?', 'tutor' ),
							'content' => __( 'Are you sure you want to delete this review permanently from the site? Please confirm your choice.', 'tutor' ),
							'yes'     => array(
								'text'  => __( 'Yes, Delete This', 'tutor' ),
								'class' => 'tutor-list-ajax-action',
								'attr'  => array( 'data-request_data=\'{"action":"delete_tutor_review", "review_id":"' . $review->comment_ID . '"}\'', 'data-delete_element_id="' . $row_id . '"' ),
							),
						)
					);
					?>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</div>
<?php
if ( $all_reviews->count > $per_page ) {
	$pagination_data              = array(
		'total_items' => $all_reviews->count,
		'per_page'    => $per_page,
		'paged'       => $current_page,
	);
	$pagination_template_frontend = tutor()->path . 'templates/dashboard/elements/pagination.php';
	tutor_load_template_from_custom_path( $pagination_template_frontend, $pagination_data );
}
?>
