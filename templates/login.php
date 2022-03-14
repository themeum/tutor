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


if(!tutor_utils()->get_option('enable_tutor_native_login', null, true, true)) {
    // Refer to login oage
    header('Location: '.wp_login_url($_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']));
    exit;
}
    
tutor_utils()->tutor_custom_header();
$login_url = tutor_utils()->get_option('enable_tutor_native_login', null, true, true) ? '' : wp_login_url(tutor()->current_url);
?>

<?php do_action('tutor/template/login/before/wrap'); ?>
<div <?php tutor_post_class('tutor-page-wrap'); ?>>
    <div class="tutor-template-segment tutor-login-wrap">
        <div class="tutor-login-title">
            <h4>
                <?php echo sprintf( __('Please %sSign-In%s to view this section', 'tutor'), '<a data-login_url="'.$login_url.'" href="" class="tutor-open-login-modal">', '</a>'); ?>
            </h4>
        </div>
    </div>
</div>
<?php 
    do_action('tutor/template/login/after/wrap');
    tutor_load_template_from_custom_path(tutor()->path . '/views/modal/login.php');
    tutor_utils()->tutor_custom_footer();
?>
