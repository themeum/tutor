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

<?php do_action('dozent/template/login/before/wrap'); ?>
    <div <?php dozent_post_class(); ?>>

        <div class="dozent-template-segment dozent-login-wrap">
            <div class="dozent-login-title">
                <h4><?php _e('Please Sign-In to view this section', 'dozent'); ?></h4>
            </div>

            <div class="dozent-template-login-form">
				<?php dozent_load_template( 'global.login' ); ?>
            </div>
        </div>
    </div><!-- .wrap -->

<?php do_action('dozent/template/login/after/wrap'); ?>



<?php
get_footer();
