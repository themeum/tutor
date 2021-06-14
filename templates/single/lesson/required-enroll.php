<?php
/**
 * Display single login
 *
 * @since v.1.0.0
 * @author themeum
 * @url https://themeum.com
 *
 * @package TutorLMS/Templates
 * @version 1.4.3
 */

if ( ! defined( 'ABSPATH' ) )
	exit;

get_header();

?>

<?php do_action('tutor_lesson/single/before/wrap'); ?>
    <div <?php tutor_post_class(); ?>>

        <div class="tutor-single-lesson-segment tutor-lessonrequired-enroll-wrap">
            <div class="tutor-notice-warning">
				<?php
				$course_id = tutor_utils()->get_course_id_by('lesson', get_the_ID());
				?>

                <h2><?php _e('Please enroll in this course first', 'tutor'); ?></h2>
                <h3> <?php echo sprintf(__('Course name : %s', 'tutor'), get_the_title($course_id)); ?> </h3>
                <a href="<?php echo get_permalink($course_id); ?>" class="tutor-button"><?php _e('View Course', 'tutor'); ?></a>
            </div>
        </div>
    </div><!-- .wrap -->

<?php do_action('tutor_lesson/single/after/wrap'); ?>

<?php
get_footer();
