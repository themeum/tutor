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

<div class="dozent-single-course-segment dozent-course-login-wrap">
    <div class="course-login-title">
        <h4><?php _e('Please Sign-In to view this section', 'dozent'); ?></h4>
    </div>

    <div class="dozent-single-course-login-form">
	    <?php dozent_load_template( 'global.login' ); ?>
    </div>
</div>
