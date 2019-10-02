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

<?php do_action('tutor/template/login/before/wrap'); ?>
    <div <?php tutor_post_class('tutor-page-wrap'); ?>>

        <div class="tutor-template-segment tutor-login-wrap">
            <div class="tutor-login-title">
                <h4><?php _e('Please Sign-In to view this section', 'tutor'); ?></h4>
            </div>

            <div class="tutor-template-login-form">
				<?php tutor_load_template( 'global.login' ); ?>
            </div>
        </div>
    </div><!-- .wrap -->

<?php do_action('tutor/template/login/after/wrap'); ?>



<?php
get_footer();
