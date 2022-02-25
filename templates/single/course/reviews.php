<?php
/**
 * Template for displaying course reviews
 *
 * @since v.1.0.0
 *
 * @author Themeum
 * @url https://themeum.com
 *
 * @package TutorLMS/Templates
 * @version 1.4.5
 */

$disable = ! get_tutor_option( 'enable_course_review' );
if ( $disable ) {
	return;
}

$per_page = tutor_utils()->get_option( 'pagination_per_page', 10 );
$current_page = max(1, (int)tutor_utils()->avalue_dot('current_page', $_POST));
$offset = ($current_page - 1) * $per_page;

$course_id = isset($_POST['course_id']) ? (int)$_POST['course_id'] : get_the_ID();
$is_enrolled = tutor_utils()->is_enrolled($course_id, get_current_user_id());

$reviews = tutor_utils()->get_course_reviews($course_id, $offset, $per_page);
$reviews_total = tutor_utils()->get_course_reviews($course_id, null, null, true);
$rating = tutor_utils()->get_course_rating($course_id);
$my_rating = tutor_utils()->get_reviews_by_user(0, 0, 150, false, $course_id);

if(isset($_POST['course_id'])) {
	// It's load more
	tutor_load_template('single.course.reviews-loop', array('reviews' => $reviews));
	return;
}

do_action( 'tutor_course/single/enrolled/before/reviews' );
?>

<div class="tutor-pagination-wrapper-replacable">
	<div class="tutor-course-topics-header">
		<div class="tutor-course-topics-header-left tutor-mb-20">
			<div class="text-primary tutor-text-medium-h6">
				<span>
					<?php
						$review_title = apply_filters( 'tutor_course_reviews_section_title', 'Student Ratings & Reviews' );
						echo esc_html( $review_title, 'tutor' );
					?>
				</span>
			</div>
		</div>
	</div>

	<?php if(! is_array( $reviews ) || ! count( $reviews )): ?>
		<?php tutor_utils()->tutor_empty_state(__('No Review Yet', 'tutor')); ?>
	<?php else: ?>
		<div class="tutor-ratingsreviews">
			<div class="tutor-ratingsreviews-ratings">
				<div class="tutor-ratingsreviews-ratings-avg tutor-text-center">
					<div class="text-medium-h1 tutor-color-text-primary tutor-mb-18">
						<?php echo number_format( $rating->rating_avg, 1 ); ?>
					</div>
					<?php tutor_utils()->star_rating_generator_v2( $rating->rating_avg, null, false, 'tutor-bs-d-block', 'lg' ); ?>
					<div class="tutor-total-ratings-text tutor-text-regular-body text-subsued tutor-mt-10">
						<span class="tutor-rating-text-part">
							<?php esc_html_e( 'Total ', 'tutor' ); ?>
						</span>
						<span class="tutor-rating-count-part">
							<?php echo esc_html( count( $reviews ) ); ?>
						</span>
						<span class="tutor-rating-text-part">
							<?php echo esc_html( _n( ' Rating', ' Ratings', count( $reviews ), 'tutor' ) ); ?>
						</span>
					</div>
				</div>
				
				<div class="tutor-ratingsreviews-ratings-all">
					<?php foreach ( $rating->count_by_value as $key => $value ) : ?>
						<?php $rating_count_percent = ( $value > 0 ) ? ( $value * 100 ) / $rating->rating_count : 0; ?>
						<div class="rating-numbers">
							<div class="rating-progress">
								<div class="tutor-ratings tutor-is-sm">
									<div class="tutor-rating-stars">
										<span class="tutor-icon-star-line-filled"></span>
									</div>
									<div class="tutor-rating-text  tutor-text-medium-body  tutor-color-text-primary">
										<?php echo $key; ?>
									</div>
								</div>
								<div class="progress-bar tutor-mt-10" style="--progress-value: <?php echo $rating_count_percent; ?>%">
									<span class="progress-value"></span>
								</div>
							</div>
							<div class="rating-num tutor-text-regular-caption tutor-color-text-subsued">
								<?php
									echo $value . ' ';
									echo $value > 1 ? __( 'ratings', 'tutor' ) : __( 'rating', 'tutor' );
								?>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
			
			<div class="tutor-ratingsreviews-reviews">
				<ul class="review-list tutor-m-0 tutor-pagination-content-appendable">
					<?php
					foreach ( $reviews as $review ) {
						$profile_url = tutor_utils()->profile_url( $review->user_id, false );
						?>
							<li>
								<div>
									<div class="">
										<img class="tutor-avatar-circle tutor-50" src="<?php echo get_avatar_url( $review->user_id ); ?>" alt="student avatar" />
									</div>
									<div class="tutor-text-regular-body tutor-color-text-primary tutor-mt-16">
										<a href="<?php echo esc_url( $profile_url ); ?>" class="tutor-reviewer-name">
										<?php echo esc_html( $review->display_name ); ?>
										</a>
									</div>
									<div class="tutor-text-regular-small tutor-color-text-hints">
										<span class="tutor-review-time">
											<?php echo sprintf( __( '%s ago', 'tutor' ), human_time_diff( strtotime( $review->comment_date ) ) ); ?>
										</span>
									</div>
								</div>
								<div>
								<?php tutor_utils()->star_rating_generator_v2( $review->rating, null, true, 'tutor-is-sm' ); ?>
									<div class="tutor-text-regular-caption tutor-color-text-subsued tutor-mt-10 tutor-review-comment">
									<?php echo htmlspecialchars( $review->comment_content ); ?>
									</div>
								</div>
							</li>
						<?php
					}
					?>
				</ul>
			</div>
		</div>
	<?php endif; ?>
	
	<div class="tutor-bs-row tutor-mt-40 tutor-mb-20">
		<div class="tutor-bs-col">
			<?php if($is_enrolled): ?>
				<button class="tutor-btn write-course-review-link-btn">
					<i class="tutor-icon-star-line-filled tutor-icon-24 tutor-mr-5"></i>
					<?php
						$is_new = !$my_rating || empty($my_rating->rating) || empty($my_rating->comment_content);
						$is_new ? _e('Write a review', 'tutor') : _e('Edit review', 'tutor');
					?>
				</button>
			<?php endif; ?>
		</div>
		<div class="tutor-bs-col-auto">
			<?php 
				$pagination_data = array(
					'total_items' => $reviews_total,
					'per_page'    => $per_page,
					'paged'       => $current_page,
					'layout'	  => array(
						'type' => 'load_more',
						'load_more_text' => __('Load More', 'tutor')
					),
					'ajax'		  => array(
						'action' => 'tutor_single_course_reviews_load_more',
						'course_id' => $course_id,
					)
				);

				$pagination_template_frontend = tutor()->path . 'templates/dashboard/elements/pagination.php';
				tutor_load_template_from_custom_path( $pagination_template_frontend, $pagination_data );
			?>
		</div>
	</div>
</div>

<?php if($is_enrolled): ?>
	<div class="tutor-course-enrolled-review-wrap tutor-mt-15">
		<div class="tutor-write-review-form" style="display: none;">
			<form method="post">
				<div class="tutor-star-rating-container">
					<input type="hidden" name="course_id" value="<?php echo $course_id; ?>"/>
					<input type="hidden" name="review_id" value="<?php echo $my_rating ? $my_rating->comment_ID : ''; ?>"/>
					<input type="hidden" name="action" value="tutor_place_rating"/>
					<div class="tutor-form-group">
						<?php
							tutor_utils()->star_rating_generator(tutor_utils()->get_rating_value($my_rating ? $my_rating->rating : 0));
						?>
					</div>
					<div class="tutor-form-group">
						<textarea name="review" placeholder="<?php _e('write a review', 'tutor'); ?>"><?php echo stripslashes($my_rating ? $my_rating->comment_content : ''); ?></textarea>
					</div>
					<div class="tutor-form-group">
						<button type="submit" class="tutor_submit_review_btn tutor-btn">
							<?php _e('Submit Review', 'tutor'); ?>
						</button>
					</div>
				</div>
			</form>
		</div>
	</div>
<?php endif; ?>

<?php do_action( 'tutor_course/single/enrolled/after/reviews' ); ?>