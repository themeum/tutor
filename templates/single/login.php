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

<div class="lms-single-course-segment lms-course-login-wrap">
    <div class="course-login-title">
        <h2><?php _e('Please Sign-In to enroll course', 'lms'); ?></h2>
    </div>

    <div class="lms-single-course-login-form">
	    <?php lms_load_template( 'global.login' ); ?>
    </div>
</div>
