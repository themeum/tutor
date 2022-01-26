<?php
/**
 * My Own reviews
 *
 * @since v.1.1.2
 *
 * @author Themeum
 * @url https://themeum.com
 *
 * @package TutorLMS/Templates
 * @version 1.4.3
 *
 * To be loaded for given review list in frontend dashboard
 */

$reviews        = tutor_utils()->get_reviews_by_user( 0, 0, 150, true );
$review_count   = $reviews->count;
$reviews        = $reviews->results;
$received_count = tutor_utils()->get_reviews_by_instructor( 0, 0, 0 )->count;
?>

<div class="tutor-dashboard-content-inner">
	<div class="tutor-text-medium-h5 tutor-color-text-primary tutor-mb-25"><?php _e( 'Reviews', 'tutor' ); ?></div>
	<?php
	if ( current_user_can( tutor()->instructor_role ) ) {
		?>
		<div class="tutor-dashboard-inline-links">
			<ul>
				<li><a href="<?php echo esc_url( tutor_utils()->get_tutor_dashboard_page_permalink( 'reviews' ) ); ?>"> <?php esc_html_e( 'Received', 'tutor' ); ?> (<?php echo $received_count; ?>)</a> </li>
				<li class="active"> <a href="<?php echo esc_url( tutor_utils()->get_tutor_dashboard_page_permalink( 'reviews/given-reviews' ) ); ?>"> <?php esc_html_e( 'Given', 'tutor' ); ?> (<?php echo $review_count; ?>)</a> </li>
			</ul>
		</div>
	<?php } ?>

	<div class="tutor-dashboard-reviews-wrap">
		<?php
		if ( ! is_array( $reviews ) || ! count( $reviews ) ) {
			?>
			<div class="tutor-dashboard-content-inner">
				<?php tutor_utils()->tutor_empty_state( tutor_utils()->not_found_text() ); ?>
			</div>
			<?php
		}
		?>

		<div class="tutor-dashboard-reviews">
			<?php
			foreach ( $reviews as $review ) {
				$profile_url = tutor_utils()->profile_url( $review->user_id, false );
				$update_id   = 'tutor_review_update_' . $review->comment_ID;
				$delete_id   = 'tutor_review_delete_' . $review->comment_ID;
				$row_id      = 'tutor_review_row_' . $review->comment_ID;
				?>
				<div class="tutor-dashboard-single-review tutor-review-<?php echo esc_html( $review->comment_ID ); ?>" id="<?php echo esc_html( $row_id ); ?>">
					<div class="tutor-dashboard-review-header">

						<div class="tutor-dashboard-review-heading">
							<div class="tutor-dashboard-review-title tutor-text-regular-h6 tutor-color-text-primary">
								<?php esc_html_e( 'Course: ', 'tutor' ); ?>
								<span class="tutor-text-medium-h6" data-href="<?php echo esc_url( get_the_permalink( $review->comment_post_ID ) ); ?>">
									<?php esc_html_e( get_the_title( $review->comment_post_ID ) ); ?>
								</span>
							</div>
						</div>
					</div>

					<div class="individual-dashboard-review-body">
						<div class="tutor-bs-d-sm-flex tutor-bs-justify-content-between ">
							<div class="individual-star-rating-wrap tutor-mt-sm-0 tutor-mb-10">
								<?php tutor_utils()->star_rating_generator_v2( $review->rating, null, true ); ?> 
							</div>
							<div class="tutor-given-review-action tutor-text-regular-body tutor-color-text-subsued tutor-bs-d-flex tutor-bs-align-items-center">
								<span data-tutor-modal-target="<?php echo esc_html( $update_id ); ?>" class="tutor-bs-d-flex tutor-bs-align-items-center">
									<i class="ttr-edit-filled tutor-icon-24 tutor-mr-3"></i>
									<span><?php esc_html_e( 'Edit', 'tutor' ); ?></span>
								</span>
								<span data-tutor-modal-target="<?php echo esc_html( $delete_id ); ?>" class="tutor-bs-d-flex tutor-bs-align-items-center">
									<i class="ttr-delete-stroke-filled tutor-icon-24 tutor-mr-3"></i>
									<span><?php esc_html_e( 'Delete', 'tutor' ); ?></span>
								</span>
							</div>
						</div>
						<div class="tutor-mt-24 tutor-text-regular-body tutor-color-text-hints">
							<?php echo htmlspecialchars( stripslashes( $review->comment_content ) ); ?>
						</div>
					</div>

					<!-- Edit Modal -->
					<form class="tutor-modal tutor-modal-is-close-inside-inner modal-sticky-header-footer" id="<?php echo esc_html( $update_id ); ?>">
						<!-- <span class="tutor-modal-overlay"></span>
						<div class="tutor-modal-root">
							<div class="tutor-modal-inner">
								<div class="tutor-modal-header">
									<h3 class="tutor-modal-title tutor-text-bold-h6 tutor-color-text-title">
										<?php esc_html_e( 'Update Review' ); ?>
									</h3>
									<button data-tutor-modal-close class="tutor-modal-close">
										<span class="ttr-line-cross-line"></span>
									</button>
								</div>
								
								<div class="tutor-modal-body-alt modal-container">
									<input type="hidden" name="course_id" value="<?php echo esc_html( $review->comment_post_ID ); ?>"/>
									<input type="hidden" name="review_id" value="<?php echo esc_html( $review->comment_ID ); ?>"/>
									<input type="hidden" name="action" value="tutor_place_rating"/>

									<div class="tutor-star-rating-container">
										<?php
											tutor_utils()->star_rating_generator( tutor_utils()->get_rating_value( $review->rating ) );
										?>
									</div>
									<textarea class="tutor-form-control tutor-mt-10" name="review" placeholder="<?php _e( 'write a review', 'tutor' ); ?>"><?php
										esc_html_e( stripslashes( $review->comment_content ) );
									?></textarea>
								</div>

								<div class="tutor-modal-footer">
									<div class="tutor-bs-row">
										<div class="tutor-bs-col">
											<div class="tutor-btn-group">
												<button data-tutor-modal-close type="button" data-action="back" class="tutor-btn tutor-is-default">
													<?php esc_html_e( 'Cancel', 'tutor' ); ?>
												</button>
											</div>
										</div>
										<div class="tutor-bs-col-auto">
											<button type="submit" data-action="next" class="tutor-btn tutor-is-primary tutor_submit_review_btn">
												<?php esc_html_e( 'Update Review', 'tutor' ); ?>
											</button>
										</div>
									</div>
								</div>
							</div>
						</div> -->
						<!-- <div id="tutor_review_edit" class="tutor-modal tutor-is-active"> -->
							<span class="tutor-modal-overlay"></span>
							<div class="tutor-modal-root">
								<div class="tutor-modal-inner">
									<button data-tutor-modal-close="" class="tutor-modal-close">
										<span class="ttr-cross-filled"></span>
									</button>
									<div class="tutor-modal-body tutor-text-center">
										<div class="tutor-rating-modal-title tutor-text-regular-h4 tutor-color-text-title tutor-mb-16">
											How would you rate this course?
										</div>
										<div class="tutor-modal-text-rating tuor-text-medium-body tutor-color-text-primary tutor-mb-12">
											Average
										</div>
										<input type="hidden" name="course_id" value="<?php echo esc_html( $review->comment_post_ID ); ?>"/>
										<input type="hidden" name="review_id" value="<?php echo esc_html( $review->comment_ID ); ?>"/>
										<input type="hidden" name="action" value="tutor_place_rating"/>

										<div class="tutor-star-rating-container">
											<div class="tutor-ratings tutor-ratings-modal">
												<div class="tutor-rating-stars">
													<?php
														tutor_utils()->star_rating_generator( tutor_utils()->get_rating_value( $review->rating ) );
													?>
												</div>
											</div>
										</div>
										<textarea class="tutor-form-control tutor-mt-28" name="review" placeholder="<?php _e( 'write a review', 'tutor' ); ?>"><?php esc_html_e( stripslashes( $review->comment_content ) ); ?></textarea>
										<div class="tutor-modal-delete-footer tutor-modal-btns tutor-btn-group">
												<button data-tutor-modal-close type="button" data-action="back" class="tutor-btn tutor-is-default">
													<?php esc_html_e( 'Cancel', 'tutor' ); ?>
												</button>
												<button type="submit" data-action="next" class="tutor-btn tutor-is-primary tutor_submit_review_btn">
													<?php esc_html_e( 'Update Review', 'tutor' ); ?>
												</button>
										</div>
									</div>
								</div>
							</div>
						<!-- </div> -->
					</form>

					<!-- Delete Modal -->
					<div id="<?php echo $delete_id; ?>" class="tutor-modal tutor-modal-is-close-inside-inner">
						<span class="tutor-modal-overlay"></span>
						<button data-tutor-modal-close class="tutor-modal-close">
							<span class="ttr-line-cross-line"></span>
						</button>
						<div class="tutor-modal-root">
							<div class="tutor-modal-inner">
								<button data-tutor-modal-close class="tutor-modal-close">
									<span class="ttr-cross-filled"></span>
								</button>
								<div class="tutor-modal-body tutor-text-center">
									<div class="tutor-modal-icon">
										<img src="<?php echo tutor()->url; ?>assets/images/icon-trash.svg" />
									</div>
									<div class="tutor-modal-text-wrap">
										<h3 class="tutor-modal-title">
											<?php esc_html_e( 'Delete This Review?', 'tutor' ); ?>
										</h3>
										<p>
											<?php esc_html_e( 'Are you sure you want to delete this review permanently from the site? Please confirm your choice.', 'tutor' ); ?>
										</p>
									</div>
									<div class="tutor-modal-delete-footer tutor-modal-btns tutor-btn-group">
										<button data-tutor-modal-close class="tutor-btn tutor-is-outline tutor-is-default">
											<?php esc_html_e( 'Cancel', 'tutor' ); ?>
										</button>
										<button class="tutor-btn tutor-list-ajax-action" data-request_data='{"review_id":<?php echo $review->comment_ID; ?>,"action":"delete_tutor_review"}' data-delete_element_id="<?php echo $row_id; ?>">
											<?php esc_html_e( 'Yes, Delete This', 'tutor' ); ?>
										</button>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<?php
			}
			?>
		</div>
	</div>
</div>
