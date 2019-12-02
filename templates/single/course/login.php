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
?>

<div class="tutor-single-course-segment tutor-course-login-wrap">
    <div class="course-login-title">
        <h4><?php _e('Login', 'tutor'); ?></h4>
    </div>

    <div class="tutor-single-course-login-form">
	    <?php tutor_load_template( 'global.login' ); ?>
    </div>
</div>
