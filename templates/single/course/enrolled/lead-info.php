<?php
/**
 * Template for displaying lead info
 *
 * @since v.1.0.0
 *
 * @author Themeum
 * @url https://themeum.com
 */

if ( ! defined( 'ABSPATH' ) )
	exit;

global $wp_query;
?>

<div class="tutor-full-width-course-top tutor-course-top-info">
    <div <?php tutor_post_class(); ?>>

	    <?php do_action('tutor_course/single/enrolled/before/lead_info/course_title'); ?>
        <h1 class="tutor-course-header-h1"><?php the_title(); ?></h1>
	    <?php do_action('tutor_course/single/enrolled/after/lead_info/course_title'); ?>


	    <?php do_action('tutor_course/single/enrolled/before/lead_info/excerpt'); ?>
        <div class="tutor-course-summery">
			<?php tutor_the_excerpt(); ?>
        </div>
	    <?php do_action('tutor_course/single/enrolled/after/lead_info/excerpt'); ?>


        <div class="tutor-course-enrolled-info">

	        <?php do_action('tutor_course/single/enrolled/before/lead_info/enrolled_date'); ?>
            <p>
				<?php
				$enrolled = tutor_utils()->is_enrolled();
				_e(sprintf("Enrolled at : %s", date(get_option('date_format'), strtotime($enrolled->post_date)) ), 'tutor');
				?>
            </p>
	        <?php do_action('tutor_course/single/enrolled/after/lead_info/enrolled_date'); ?>


	        <?php do_action('tutor_course/single/enrolled/before/lead_info/review'); ?>
            <div class="tutor-course-enrolled-review-wrap">
                <form method="post" class="tutor-write-review-form">
                    <input type="hidden" name="tutor_course_id" value="<?php echo get_the_ID(); ?>">
                    <span class="tutor-ratings-wrap">
                        <?php
                        $rating = tutor_utils()->get_course_rating_by_user();
                        tutor_utils()->star_rating_generator(tutor_utils()->get_rating_value($rating->rating));
                        ?>
                    </span>

                    <a href="javascript:;" class="write-course-review-link-btn"><?php _e('Write a review', 'tutor'); ?></a>

                    <div class="tutor-write-review-box" style="display: none;">

                        <div class="tutor-form-row">
                            <div class="tutor-form-col-6">
                                <div class="tutor-form-group">
                                    <textarea name="review" placeholder="<?php _e('write a review', 'tutor'); ?>"><?php echo $rating->review; ?></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="tutor-form-row">
                            <div class="tutor-form-col-6">
                                <div class="tutor-form-group">
                                    <button type="submit" class="tutor_submit_review_btn"><?php _e('Submit Review', 'tutor'); ?></button>
                                </div>
                            </div>
                        </div>

                    </div>

                </form>
            </div>

	        <?php do_action('tutor_course/single/enrolled/after/lead_info/review'); ?>


            <?php
            do_action('tutor_course/single/enrolled/before/lead_info/progress_bar');
            $count_completed_lesson = tutor_course_completing_progress_bar();
            do_action('tutor_course/single/enrolled/after/lead_info/progress_bar');
            ?>

            <div class="tutor-lead-info-btn-group">
				<?php
				if ( $wp_query->query['post_type'] !== 'lesson') {
					$lesson_url = tutor_utils()->get_course_first_lesson();
					if ( $lesson_url ) {
						?>
                        <a href="<?php echo $lesson_url; ?>" class="tutor-button"><?php _e( 'Continue to lesson', 'tutor' ); ?></a>
					<?php }
				}
				?>

                <a href="<?php echo get_the_permalink(); ?>" class="tutor-button"><?php _e('Course Home', 'tutor'); ?></a>

                <?php tutor_course_mark_complete_html(); ?>
            </div>
        </div>

	    <?php do_action('tutor_course/single/enrolled/before/lead_info/meta'); ?>
        <div class="tutor-course-lead-meta">
			<span class="tutor-author">
				<?php _e(sprintf("Created by : %s", get_tutor_course_author()) , 'tutor'); ?>,
			</span>

            <span class="tutor-course-lead-updated">
				<?php _e(sprintf("Last updated : %s", get_the_modified_date()) , 'tutor'); ?>
			</span>
        </div>
	    <?php do_action('tutor_course/single/enrolled/after/lead_info/meta'); ?>

    </div><!-- .wrap -->
</div>