<?php

/**
 * Display single login
 *
 * @since v.1.0.0
 * @author themeum
 * @url https://themeum.com
 */

if ( ! defined( 'ABSPATH' ) )
	exit;

get_header();

?>

<?php do_action('lms_lesson/single/before/wrap'); ?>
    <div <?php lms_post_class(); ?>>

        <div class="lms-single-lesson-segment lms-lessonrequired-enroll-wrap">
            <div class="lms-notice-warning">
				<?php
				$course_id = lms_utils()->get_course_id_by_lesson();
				?>

                <h2><?php _e('Please enroll This course first', 'lms'); ?></h2>
                <h3> <?php _e(sprintf('Course name : %s', get_the_title($course_id)), 'lms'); ?> </h3>
                <a href="<?php echo get_permalink($course_id); ?>" class="lms-button"><?php _e('View Course', 'lms'); ?></a>
            </div>
        </div>
    </div><!-- .wrap -->

<?php do_action('lms_lesson/single/after/wrap'); ?>

<?php
get_footer();
