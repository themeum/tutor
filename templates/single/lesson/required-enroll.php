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

<?php do_action('dozent_lesson/single/before/wrap'); ?>
    <div <?php dozent_post_class(); ?>>

        <div class="dozent-single-lesson-segment dozent-lessonrequired-enroll-wrap">
            <div class="dozent-notice-warning">
				<?php
				$course_id = dozent_utils()->get_course_id_by_lesson();
				?>

                <h2><?php _e('Please enroll This course first', 'dozent'); ?></h2>
                <h3> <?php _e(sprintf('Course name : %s', get_the_title($course_id)), 'dozent'); ?> </h3>
                <a href="<?php echo get_permalink($course_id); ?>" class="dozent-button"><?php _e('View Course', 'dozent'); ?></a>
            </div>
        </div>
    </div><!-- .wrap -->

<?php do_action('dozent_lesson/single/after/wrap'); ?>

<?php
get_footer();
