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

        <h1 class="tutor-course-header-h1"><?php the_title(); ?></h1>

        <div class="tutor-course-summery">
			<?php tutor_the_excerpt(); ?>
        </div>

        <div class="tutor-course-enrolled-info">
            <p>
				<?php
				$enrolled = tutor_utils()->is_enrolled();
				_e(sprintf("Enrolled at : %s", date(get_option('date_format'), strtotime($enrolled->post_date)) ), 'tutor');
				?>
            </p>

            <?php
            $count_completed_lesson = tutor_course_completing_progress_bar();
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

        <div class="tutor-course-lead-meta">
			<span class="tutor-author">
				<?php _e(sprintf("Created by : %s", get_tutor_course_author()) , 'tutor'); ?>,
			</span>

            <span class="tutor-course-lead-updated">
				<?php _e(sprintf("Last updated : %s", get_the_modified_date()) , 'tutor'); ?>
			</span>
        </div>
    </div><!-- .wrap -->
</div>