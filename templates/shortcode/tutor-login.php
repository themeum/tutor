<?php

/**
 * Login page shortcode
 *
 * @author themeum
 * @link https://themeum.com
 * @since 2.0.10
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$dashboard_url  = tutor_utils()->tutor_dashboard_url();

if ( is_user_logged_in() ) {
    tutor_utils()->redirect_to( $dashboard_url );
}

if ( ! tutor_utils()->get_option( 'enable_tutor_native_login', null, true, true ) ) {
    tutor_utils()->redirect_to( wp_login_url() );
}

add_filter( 'tutor_after_login_redirect_url', function() use ( $dashboard_url ) {
    return $dashboard_url;
});
?>

<?php do_action( 'tutor/template/login/before/wrap' ); ?>
<div <?php tutor_post_class( 'tutor-page-wrap' ); ?>>
	<div class="tutor-template-segment tutor-login-wrap">
        <div class="tutor-login-form-wrapper">
			<div class="tutor-fs-5 tutor-color-black tutor-mb-32">
				<?php esc_html_e( 'Hi, Welcome back!', 'tutor' ); ?>
			</div>
			<?php
				$login_form = trailingslashit( tutor()->path ) . 'templates/login-form.php';
				tutor_load_template_from_custom_path( $login_form, false );
			?>
			<?php do_action( 'tutor_after_login_form' ); ?>
		</div>
	</div>
</div>
<?php do_action( 'tutor/template/login/after/wrap' ); ?>
