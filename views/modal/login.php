<?php
/**
 * Login View
 *
 * @package Tutor\Views
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.0.0
 */

$lost_pass = apply_filters( 'tutor_lostpassword_url', wp_lostpassword_url() );
?>
<div class="tutor-modal tutor-login-modal" role="dialog" aria-modal="true" aria-labelledby="tutor-login-modal-title" aria-hidden="true">
	<div class="tutor-modal-overlay"></div>
	<div class="tutor-modal-window tutor-modal-window-sm">
		<div class="tutor-modal-content tutor-modal-content-white">
			<button class="tutor-iconic-btn tutor-modal-close-o" data-tutor-modal-close aria-label="<?php esc_attr_e( 'Close login dialog', 'tutor' ); ?>">
				<span class="tutor-icon-times" aria-hidden="true"></span>
			</button>

			<div class="tutor-modal-body">
				<div class="tutor-py-48">
					<?php do_action( 'tutor_before_login_form' ); ?>
					<div id="tutor-login-modal-title" class="tutor-fs-4 tutor-fw-medium tutor-color-black tutor-mb-32"><?php esc_html_e( 'Hi, Welcome back!', 'tutor' ); ?></div>
					<?php
						// load form template.
						$login_form = trailingslashit( tutor()->path ) . 'templates/login-form.php';
						tutor_load_template_from_custom_path(
							$login_form,
							false
						);
						?>

					<?php do_action( 'tutor_after_login_form' ); ?>
				</div>
				<?php do_action( 'tutor_after_login_form_wrapper' ); ?>
			</div>
		</div>
	</div>
</div>
