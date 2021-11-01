<?php
/**
 * @package TutorLMS/Templates
 * @version 1.4.3
 * 
 * To be loaded in single course page at the bottom
 */

$isLoggedIn = is_user_logged_in();
$rating = $isLoggedIn ? tutor_utils()->get_reviews_by_user(0, 0, 150, false, get_the_ID()) : '';
?>

<div class="tutor-course-enrolled-review-wrap" id>
    <a href="javascript:;" class="write-course-review-link-btn">
		<?php
		if($isLoggedIn && $rating && (!empty($rating->rating) || !empty($rating->comment_content))){
			_e('Edit review', 'tutor');
		}else{
			_e('Write a review', 'tutor');
		}
		?>
    </a>
    <div class="tutor-write-review-form" style="display: none;">
		<?php
		if($isLoggedIn) {
			?>
            <form method="post">
                <div class="tutor-star-rating-container">
					<input type="hidden" name="course_id" value="<?php echo get_the_ID(); ?>"/>
					<input type="hidden" name="review_id" value="<?php echo $rating ? $rating->comment_ID : ''; ?>"/>
					<input type="hidden" name="action" value="tutor_place_rating"/>
                    <div class="tutor-form-group">
						<?php
							tutor_utils()->star_rating_generator(tutor_utils()->get_rating_value($rating ? $rating->rating : 0));
						?>
                    </div>
                    <div class="tutor-form-group">
                        <textarea name="review" placeholder="<?php _e('write a review', 'tutor'); ?>"><?php echo stripslashes($rating ? $rating->comment_content : ''); ?></textarea>
                    </div>
                    <div class="tutor-form-group">
                        <button type="submit" class="tutor_submit_review_btn tutor-btn"><?php _e('Submit Review', 'tutor'); ?></button>
                    </div>
                </div>
            </form>
			<?php
		} else {
			ob_start();
			tutor_load_template( 'single.course.login' );
			
			echo apply_filters( 'tutor_course/global/login', ob_get_clean());
		}
		?>
    </div>
</div>