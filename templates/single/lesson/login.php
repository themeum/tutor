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

        <div class="lms-single-lesson-segment lms-lesson-login-wrap">
            <div class="lesson-login-title">
                <h2><?php _e('Please Sign-In to start lesson', 'lms'); ?></h2>
            </div>

            <div class="lms-single-lesson-login-form">
				<?php lms_load_template( 'global.login' ); ?>
            </div>
        </div>
    </div><!-- .wrap -->

<?php do_action('lms_lesson/single/after/wrap'); ?>



<?php
get_footer();
