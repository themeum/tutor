<?php
/**
 * Template for displaying course reviews
 *
 * @since v.1.0.0
 *
 * @author Themeum
 * @url https://themeum.com
 */


do_action('tutor_course/single/enrolled/before/reviews');


$reviews = tutor_utils()->get_course_reviews();
if ( ! is_array($reviews) || ! count($reviews)){
	return;
}
?>

<div class="tutor-single-course-segment">

    <div class="course-student-rating-title">
        <h4 class="tutor-segment-title"><?php _e('Student Feedback', 'tutor'); ?></h4>
    </div>

    <div class="tutor-course-reviews-wrap">
        <div class="tutor-course-student-rating-wrap">
            <div class="course-avg-rating-wrap">
                <p class="course-avg-rating">
					<?php
					$rating = tutor_utils()->get_course_rating();
					echo number_format($rating->rating_avg, 1);
					?>
                </p>
                <p class="course-avg-rating-html">
					<?php tutor_utils()->star_rating_generator($rating->rating_avg);?>
                </p>
                <p class="tutor-course-avg-rating-total">Total <span><?php echo $rating->rating_count;?></span> Ratings</p>

            </div>
        </div>


        <div class="tutor-course-reviews-list">
			<?php
			foreach ($reviews as $review){
				$profile_url = tutor_utils()->profile_url($review->user_id);
				?>
                <div class="tutor-review-individual-item tutor-review-<?php echo $review->comment_ID; ?>">
                    <div class="review-left">
                        <div class="review-avatar">
                            <a href="<?php echo $profile_url; ?>"> <?php echo tutor_utils()->get_tutor_avatar($review->user_id); ?> </a>
                        </div>
                        <div class="tutor-review-user-info">
                            <div class="review-time-name">
                                <p> <a href="<?php echo $profile_url; ?>">  <?php echo $review->display_name; ?> </a> </p>
                                <p class="review-meta">
									<?php _e(sprintf('%s ago', human_time_diff(strtotime($review->comment_date))), 'tutor'); ?>
                                </p>
                            </div>
                            <div class="individual-review-rating-wrap">
								<?php tutor_utils()->star_rating_generator($review->rating); ?>
                            </div>
                        </div>

                    </div>

                    <div class="review-content review-right">
						<?php echo wpautop(stripslashes($review->comment_content)); ?>
                    </div>
                </div>
				<?php
			}
			?>
        </div>
    </div>
</div>

<?php do_action('tutor_course/single/enrolled/after/reviews'); ?>
