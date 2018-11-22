<?php
    $isLoggedIn = is_user_logged_in();
    $rating = $isLoggedIn ? dozent_utils()->get_course_rating_by_user() : '';
?>

<div class="dozent-course-enrolled-review-wrap">
    <a href="javascript:;" class="write-course-review-link-btn">
        <?php
            if($isLoggedIn && (!empty($rating->rating) || !empty($rating->review))){
                _e('Edit review', 'dozent');
            }else{
                _e('Write a review', 'dozent');
            }
        ?>
    </a>
    <div class="dozent-write-review-form" style="display: none;">
    <?php
        if($isLoggedIn) {
    ?>
        <form method="post">
            <input type="hidden" name="dozent_course_id" value="<?php echo get_the_ID(); ?>">
            <div class="dozent-write-review-box">
                <div class="dozent-form-group">
                    <span class="dozent-ratings-wrap">
                        <?php
                            dozent_utils()->star_rating_generator(dozent_utils()->get_rating_value($rating->rating));
                        ?>
                    </span>
                </div>
                <div class="dozent-form-group">
                    <textarea name="review" placeholder="<?php _e('write a review', 'dozent'); ?>"><?php echo stripslashes($rating->review); ?></textarea>
                </div>
                <div class="dozent-form-group">
                    <button type="submit" class="dozent_submit_review_btn"><?php _e('Submit Review', 'dozent'); ?></button>
                </div>
            </div>
        </form>
    <?php
        }else{
            ob_start();
            dozent_load_template( 'single.course.login' );
            $output = apply_filters( 'dozent_course/global/login', ob_get_clean());
            echo $output;
        }
    ?>
    </div>
</div>


