<?php
/**
 * Password retrieve template
 *
 * @package Tutor\Templates
 * @subpackage Template_Part
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.4.3
 */

defined( 'ABSPATH' ) || exit;

use TUTOR\Input;

if ( Input::get( 'reset_key' ) && Input::get( 'user_id' ) ) {
	tutor_load_template( 'template-part.form-retrieve-password' );
} else {
	do_action( 'tutor_before_retrieve_password_form' );
	?>
	<div class="tutor-card" style="max-width: 520px; margin: 20px auto;">
		<form method="post" class="tutor-forgot-password-form tutor-ResetPassword lost_reset_password" style="padding: 10px;">

			<div class="tutor-mb-8"><?php tutor_alert( null, 'any', true, true ); ?></div>
			<?php tutor_nonce_field(); ?>

			<input type="hidden" name="tutor_action" value="tutor_retrieve_password">
			<p class="tutor-small tutor-mb-5"><?php echo apply_filters( 'tutor_lost_password_message', esc_html__( 'Lost your password? Please enter your username or email address. You will receive a link to create a new password via email.', 'tutor' ) ); ?></p><?php // @codingStandardsIgnoreLine ?>

			<div class="tutor-form-row tutor-mt-5">
				<div class="tutor-form-col-12">
					<div class="tutor-form-group">
						<label class="tutor-block tutor-mb-2"><?php esc_html_e( 'Username or email', 'tutor' ); ?></label>
						<div class="tutor-input-field tutor-mb-8">
							<input class="tutor-form-control tutor-input" type="text" name="user_login" id="user_login" autocomplete="username">
						</div>
					</div>
				</div>
			</div>

			<div class="clear"></div>

			<?php do_action( 'tutor_lostpassword_form' ); ?>

			<div class="tutor-form-row">
				<div class="tutor-form-col-12">
					<div class="tutor-form-group">
						<button type="submit" class="tutor-btn tutor-btn-primary tutor-btn-block" value="<?php esc_attr_e( 'Reset password', 'tutor' ); ?>">
							<?php esc_html_e( 'Reset password', 'tutor' ); ?>
						</button>
					</div>
				</div>
			</div>

		</form>
	</div>
	<?php
	do_action( 'tutor_after_retrieve_password_form' );
}
