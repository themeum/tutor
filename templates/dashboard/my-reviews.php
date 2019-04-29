<?php
/**
 * My Own reviews
 *
 * @since v.1.1.2
 *
 * @author Themeum
 * @url https://themeum.com
 * @package Tutor
 */

$reviews = tutor_utils()->get_reviews_by_user();

if ( ! is_array($reviews) || ! count($reviews)){ ?>
    <div>
		<h3><?php _e("Not Found" , 'tutor'); ?></h3>
		<p><?php _e("Sorry, but you are looking for something that isn't here." , 'tutor'); ?></p>
    </div>
    <?php
	return;
}
?>

<div class=" tutor-course-reviews-wrap">
    <div class="course-target-reviews-title">
        <h3><?php _e(sprintf("My Reviews"), 'tutor'); ?></h3>
    </div>

    <div class="tutor-reviews-list">
		<?php
		foreach ($reviews as $review){
			$profile_url = tutor_utils()->profile_url($review->user_id);
			?>
            <div class="tutor-review-individual-item tutor-review-<?php echo $review->comment_ID; ?>">

                <div class="individual-review-course-name">
		            <?php _e('On', 'tutor'); ?>
                    <a href="<?php echo get_the_permalink($review->comment_post_ID); ?>"><?php echo get_the_title($review->comment_post_ID); ?></a>
                    <p class="review-meta"><?php _e(sprintf('%s ago', human_time_diff(strtotime($review->comment_date))), 'tutor'); ?></p>
                </div>

                <div class="individual-review-rating-wrap">
		            <?php tutor_utils()->star_rating_generator($review->rating); ?>
                </div>
	            <?php echo wpautop($review->comment_content); ?>

            </div>
			<?php
		}
		?>
    </div>
</div>