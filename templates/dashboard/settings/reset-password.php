<?php
/**
 * Reset password
 *
 * @package Tutor\Templates
 * @subpackage Dashboard\Settings
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @version 1.4.3
 */

?>

<div class="tutor-fs-5 tutor-fw-medium tutor-mb-24"><?php esc_html_e( 'Settings', 'tutor' ); ?></div>

<div class="tutor-dashboard-content-inner">
	<div class="tutor-mb-32">
		<?php
			tutor_load_template( 'dashboard.settings.nav-bar', array( 'active_setting_nav' => 'reset_password' ) );
		?>
	</div>

	<form action="" method="post" enctype="multipart/form-data">
		<?php do_action( 'tutor_reset_password_input_before' ); ?>

		<div class="tutor-row">
			<div class="tutor-col-12 tutor-col-sm-8 tutor-col-md-12 tutor-col-lg-7 tutor-mb-32">
				<label class="tutor-form-label tutor-color-secondary"> <?php esc_html_e( 'Current Password', 'tutor' ); ?> </label>
				<input class="tutor-form-control" type="password" name="previous_password" placeholder="<?php esc_attr_e( 'Current Password', 'tutor' ); ?>">
			</div>
		</div>

		<div class="tutor-row">
			<div class="tutor-col-12 tutor-col-sm-8 tutor-col-md-12 tutor-col-lg-7 tutor-mb-32">
				<div class="tutor-password-strength-checker">
					<div class="tutor-password-field">
						<label class="field-label tutor-form-label" for="tutor-new-password">
							<?php esc_html_e( 'New Password', 'tutor' ); ?>
						</label>
						<div class="field-group">
							<input
								class="password-checker tutor-form-control"
								id="tutor-new-password"
								type="password"
								name="new_password"
								placeholder="<?php esc_attr_e( 'Type Password', 'tutor' ); ?>"
							/>
							<span class="show-hide-btn"></span>
						</div>
					</div>

					<div class="tutor-password-strength-hint">
						<div class="indicator">
							<span class="weak"></span>
							<span class="medium"></span>
							<span class="strong"></span>
						</div>
						<div class="text tutor-fs-7 tutor-color-muted"></div>
					</div>
				</div>
			</div>
		</div>

		<div class="tutor-row">
			<div class="tutor-col-12 tutor-col-sm-8 tutor-col-md-12 tutor-col-lg-7 tutor-mb-32">
				<div class="tutor-password-field tutor-settings-pass-field">
					<label class="field-label tutor-form-label" for="tutor-confirm-password">
						<?php esc_html_e( 'Re-type New Password', 'tutor' ); ?>
					</label>
					<div class="tutor-form-wrap">
						<span class="tutor-validation-icon tutor-icon-mark tutor-color-success tutor-form-icon tutor-form-icon-reverse" style="display: none;"></span>
						<input
							class="tutor-form-control"
							id="tutor-confirm-password"
							type="password"
							placeholder="<?php esc_attr_e( 'Type Password', 'tutor' ); ?>"
							name="confirm_new_password"
						/>
					</div>
				</div>
			</div>
		</div>

		<?php do_action( 'tutor_reset_password_input_after' ); ?>

		<div class="tutor-row">
			<div class="tutor-col-12">
				<button type="submit" class="tutor-btn tutor-btn-primary tutor-profile-password-reset">
					<?php esc_html_e( 'Reset Password', 'tutor' ); ?>
				</button>
			</div>
		</div>
	</form>
</div>
