<?php
/**
 * Display single login
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! tutor_utils()->get_option( 'enable_tutor_native_login', null, true, true ) ) {
	// Redirect to wp native login page.
	header( 'Location: ' . wp_login_url( tutor_utils()->get_current_url() ) );
	exit;
}

tutor_utils()->tutor_custom_header();
$login_url = tutor_utils()->get_option( 'enable_tutor_native_login', null, true, true ) ? '' : wp_login_url( tutor()->current_url );
?>

<?php
//phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores
do_action( 'tutor/template/login/before/wrap' );
?>
<div <?php tutor_post_class( 'tutor-page-wrap' ); ?>>
	<div class="tutor-template-segment tutor-login-wrap">

		<div class="tutor-login-form-wrapper">
			<div class="tutor-fs-5 tutor-color-black tutor-mb-32">
				<?php esc_html_e( 'Hi, Welcome back!', 'tutor' ); ?>
			</div>
			<?php
				// load form template.
				$login_form = trailingslashit( tutor()->path ) . 'templates/login-form.php';
				tutor_load_template_from_custom_path(
					$login_form,
					false
				);
				?>
		</div>
		<?php do_action( 'tutor_after_login_form_wrapper' ); ?>
	</div>
</div>
<?php
	//phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores
	do_action( 'tutor/template/login/after/wrap' );
	tutor_utils()->tutor_custom_footer();
?>
