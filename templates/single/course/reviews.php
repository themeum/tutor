<?php
/**
 * Template for displaying course reviews
 *
 * @since v.1.0.0
 *
 * @author Themeum
 * @url https://themeum.com
 */

$reviews = tutor_utils()->get_course_reviews();
if ( ! is_array($reviews) || ! count($reviews)){
	return;
}
?>

<div class="tutor-single-course-segment tutor-course-student-rating-wrap">
    <div class="course-student-rating-title">
        <h4><?php _e('Student Feedback', 'tutor'); ?></h4>
    </div>

    <div class="course-avg-rating-wrap">
        <p class="course-avg-rating">
            <?php
            $rating = tutor_utils()->get_course_rating();
            echo $rating->rating_avg;
            ?>
        </p>
        <p class="course-avg-rating-html">
            <?php tutor_utils()->star_rating_generator($rating->rating_avg); ?>
        </p>

        <p><?php _e('Course Rating', 'tutor'); ?></p>
    </div>
</div>

<div class="tutor-single-course-segment  tutor-course-reviews-wrap">
    <div class="course-target-reviews-title">
        <h4><?php _e('Reviews', 'tutor'); ?></h4>
    </div>

    <div class="tutor-course-reviews-list">
		<?php
		foreach ($reviews as $review){
			?>
            <div class="tutor-review-individual-item tutor-review-<?php echo $review->comment_ID; ?>">
                <div class="review-left">
                    <div class="review-avatar">
                        <span class="text-avatar">
                            <?php echo tutor_utils()->text_avatar_generator($review->display_name); ?>
                        </span>
                    </div>

                    <div class="review-time-name">
                        <p class="review-meta">
                            <?php _e(sprintf('%s ago', human_time_diff(strtotime($review->comment_date))), 'lms'); ?>
                        </p>
                        <p><?php echo $review->display_name; ?> </p>
                    </div>
                </div>

                <div class="review-content review-right">
                    <div class="individual-review-rating-wrap">
	                    <?php tutor_utils()->star_rating_generator($review->rating); ?>
                    </div>
                    <?php echo wpautop($review->comment_content); ?>
                </div>
            </div>
			<?php
		}
		?>
    </div>
</div>
