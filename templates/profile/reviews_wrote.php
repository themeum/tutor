<?php
$user_name = sanitize_text_field(get_query_var('dozent_student_username'));
$get_user = dozent_utils()->get_user_by_login($user_name);
$user_id = $get_user->ID;


$reviews = dozent_utils()->get_reviews_by_user($user_id);

if ( ! is_array($reviews) || ! count($reviews)){ ?>
    <div>
		<h2><?php _e("Not Found" , 'dozent'); ?></h2>
		<p><?php _e("Sorry, but you are looking for something that isn't here." , 'dozent'); ?></p>
    </div>
    <?php
	return;
}
?>

<div class=" dozent-course-reviews-wrap">
    <div class="course-target-reviews-title">
        <h4><?php _e(sprintf('Reviews wrote by %s ', $get_user->display_name), 'dozent'); ?></h4>
    </div>

    <div class="dozent-reviews-list">
		<?php
		foreach ($reviews as $review){
			$profile_url = dozent_utils()->profile_url($review->user_id);
			?>
            <div class="dozent-review-individual-item dozent-review-<?php echo $review->comment_ID; ?>">
                <div class="review-left">
                    <div class="review-avatar">
                        <a href="<?php echo $profile_url; ?>">
		                    <?php echo dozent_utils()->get_dozent_avatar($review->user_id); ?>
                        </a>
                    </div>

                    <div class="review-time-name">

                        <p> <a href="<?php echo $profile_url; ?>">  <?php echo $review->display_name; ?> </a> </p>
                        <p class="review-meta">
		                    <?php _e(sprintf('%s ago', human_time_diff(strtotime($review->comment_date))), 'dozent'); ?>
                        </p>
                    </div>
                </div>

                <div class="review-content review-right">

                    <div class="individual-review-course-name">
                        <?php _e('On', 'dozent'); ?>
                        <a href="<?php echo get_the_permalink($review->comment_post_ID); ?>"><?php echo get_the_title
                        ($review->comment_post_ID);
                        ?></a>
                    </div>

                    <div class="individual-review-rating-wrap">
						<?php dozent_utils()->star_rating_generator($review->rating); ?>
                    </div>
					<?php echo wpautop($review->comment_content); ?>
                </div>
            </div>
			<?php
		}
		?>
    </div>
</div>