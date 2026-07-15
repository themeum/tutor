<?php
/**
 * Password retrive form
 *
 * @package Tutor\Templates
 * @subpackage Template_Part
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.4.3
 */

defined( 'ABSPATH' ) || exit;

do_action( 'tutor_before_reset_password_form' ); ?>
<div  class="tutor-card" style="max-width: 550px; margin: 20px auto;">
<form method="post" class="tutor-reset-password-form tutor-ResetPassword lost_reset_password" style="padding: 10px;">
	<?php tutor_nonce_field(); ?>
	<input type="hidden" name="tutor_action" value="tutor_process_reset_password">
	<input type="hidden" name="reset_key" value="<?php echo esc_attr( \TUTOR\Input::get( 'reset_key' ) ); ?>" />
	<input type="hidden" name="user_id" value="<?php echo esc_attr( \TUTOR\Input::get( 'user_id' ) ); ?>" />

	<p class="tutor-small tutor-mb-5">
		<?php
		echo esc_html(
			apply_filters(
				'tutor_reset_password_message',
				esc_html__( 'Enter Password and Confirm Password to reset your password', 'tutor' )
			)
		);
		?>
	</p>

	<div class="tutor-form-row">
		<div class="tutor-form-col-12">
			<div class="tutor-form-group">
				<label class="tutor-block tutor-mb-2"><?php esc_html_e( 'Password', 'tutor' ); ?></label>
				<div class="tutor-input-field tutor-mb-8">
					<input class="tutor-form-control tutor-input" type="password" name="password" id="password">
				</div>
			</div>
		</div>
	</div>

	<div class="tutor-form-row">
		<div class="tutor-form-col-12">
			<div class="tutor-form-group">
				<label class="tutor-block tutor-mb-2"><?php esc_html_e( 'Confirm Password', 'tutor' ); ?></label>
				<div class="tutor-input-field tutor-mb-8">
					<input class="tutor-form-control tutor-input" type="password" name="confirm_password" id="confirm_password">
				</div>
			</div>
		</div>
	</div>

	<div class="clear"></div>

	<?php do_action( 'tutor_reset_password_form' ); ?>

	<div class="tutor-form-row">
		<div class="tutor-form-col-12">
			<div class="tutor-form-group">
				<button type="submit" class="tutor-btn tutor-btn-primary tutor-btn-block" value="Reset password">
					<?php esc_html_e( 'Reset password', 'tutor' ); ?>
				</button>
			</div>
		</div>
	</div>

</form>
</div>

<?php do_action( 'tutor_after_reset_password_form' ); ?>
