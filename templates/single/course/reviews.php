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


do_action('tutor_course/single/enrolled/before/reviews');

$disable = !get_tutor_option('enable_course_review');
if ($disable){
    return;
}

$reviews = tutor_utils()->get_course_reviews();
if ( ! is_array($reviews) || ! count($reviews)){
	return;
}

$rating = tutor_utils()->get_course_rating();
?>
<div class="tutor-ratingsreviews">
    <div class="tutor-ratingsreviews-ratings">
        <div class="tutor-ratingsreviews-ratings-avg">
            <div class="text-medium-h1 color-text-primary">
                <?php echo number_format($rating->rating_avg, 1); ?>
            </div>
            <?php tutor_utils()->star_rating_generator_v2($rating->rating_avg, null, false, 'tutor-bs-d-block'); ?>
        </div>
        
        <div class="tutor-ratingsreviews-ratings-all">
            <?php foreach($rating->count_by_value as $key => $value): ?>
                <?php $rating_count_percent = ($value > 0) ? ($value  * 100 ) / $rating->rating_count : 0; ?>
                <div class="rating-numbers">
                    <div class="rating-progress">
                        <div class="tutor-ratings tutor-is-sm">
                            <div class="tutor-rating-stars">
                                <span class="ttr-star-line-filled"></span>
                            </div>
                            <div class="tutor-rating-text text-medium-body color-text-primary">
                                <?php echo $key; ?>
                            </div>
                        </div>
                        <div class="progress-bar tutor-mt-10" style="--progress-value: <?php echo $rating_count_percent; ?>%">
                            <span class="progress-value"></span>
                        </div>
                    </div>
                    <div class="rating-num text-regular-caption color-text-subsued">
                        <?php
                            echo $value.' ';
                            echo $value > 1 ? __('ratings', 'tutor') : __('rating', 'tutor'); 
                        ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <div class="tutor-ratingsreviews-reviews">
        <ul class="review-list tutor-m-0">
            <?php 
                foreach ($reviews as $review){
                    $profile_url = tutor_utils()->profile_url($review->user_id);
                    ?>
                    <li>
                        <div>
                            <div class="tutor-avatar-circle tutor-50">
                                <img src="<?php echo get_avatar_url( $review->user_id ) ?>" alt="student avatar" />
                            </div>
                            <div class="text-regular-body color-text-primary tutor-mt-16">
                                <a href="<?php echo $profile_url; ?>">
                                    <?php echo $review->display_name; ?>
                                </a>
                            </div>
                            <div class="text-regular-small color-text-hints">
                                <?php echo sprintf(__('%s ago', 'tutor'), human_time_diff(strtotime($review->comment_date))); ?>
                            </div>
                        </div>
                        <div>
                            <?php tutor_utils()->star_rating_generator_v2($review->rating, null, true, 'tutor-is-sm'); ?>
                            <div class="text-regular-caption color-text-subsued tutor-mt-10">
                                <?php echo htmlspecialchars($review->comment_content); ?>
                            </div>
                        </div>
                    </li>
                    <?php
                }
            ?>
        </ul>
    </div>
</div>

<?php do_action('tutor_course/single/enrolled/after/reviews'); ?>
