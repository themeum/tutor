<?php
/**
 * Display single login
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.0.0
 */

use TUTOR\Assets;

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

$should_load_legacy_scripts = Assets::should_load_legacy_scripts();
?>

<?php
//phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores
do_action( 'tutor/template/login/before/wrap' );
?>
<div <?php tutor_post_class( 'tutor-page-wrap tutor-w-full' ); ?>>
	<div class="tutor-template-segment tutor-login-wrap tutor-card tutor-shadow-md tutor-px-none tutor-py-9" style="max-width: 100%; width : 520px; margin: 40px auto;">
		<div class="tutor-login-form-wrapper tutor-p-8">
			<div class="tutor-small tutor-mb-5">
				<?php esc_html_e( 'Hi, Welcome back!', 'tutor' ); ?>
			</div>
			<?php
				// load form template.
				$login_form        = trailingslashit( tutor()->path ) . 'templates/login-form.php';
				$login_form_legacy = trailingslashit( tutor()->path ) . 'templates/login-form-legacy.php';
				tutor_load_template_from_custom_path(
					$should_load_legacy_scripts ? $login_form_legacy : $login_form,
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
