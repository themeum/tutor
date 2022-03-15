<?php
/**
 * @package TutorLMS/Templates
 * @version 1.4.3
 */

?>

<div class="tutor-dashboard-content-inner">
	<div class="tutor-dashboard-inline-links">
		<?php
			tutor_load_template( 'dashboard.settings.nav-bar', array( 'active_setting_nav' => 'reset_password' ) );
		?>
	</div>

	<form action="" method="post" enctype="multipart/form-data">
		<?php do_action( 'tutor_reset_password_input_before' ); ?>

        <div class="tutor-row">
            <div class="tutor-col-12 tutor-col-sm-8 tutor-col-md-12 tutor-col-lg-7 tutor-mb-32">
                <label class="tutor-form-label tutor-color-black-60"> <?php esc_html_e('Current Password', 'tutor'); ?> </label>
                <input class="tutor-form-control" type="password" name="previous_password" placeholder="Current Password">
            </div>
        </div>

		<div class="tutor-row">
			<div class="tutor-col-12 tutor-col-sm-8 tutor-col-md-12 tutor-col-lg-7 tutor-mb-32">
				<div class="tutor-password-strength-checker">
					<div class="tutor-password-field">
						<label class="field-label tutor-form-label" for="new-password-1">
							<?php esc_html_e( 'New Password', 'tutor' ); ?>
						</label>
						<div class="field-group">
							<input
								class="password-checker tutor-form-control"
								id="new-password-1"
								type="password"
								name="new_password"
								placeholder="Type Password"
							/>
							<span class="show-hide-btn"></span>
						</div>
					</div>
					<div class="tutor-passowrd-strength-hint">
						<div class="indicator">
							<span class="weak"></span>
							<span class="medium"></span>
							<span class="strong"></span>
						</div>
						<div class="text text-regular-caption tutor-color-muted"></div>
					</div>
				</div>
			</div>
		</div>


		<div class="tutor-row">
			<div class="tutor-col-12 tutor-col-sm-8 tutor-col-md-12 tutor-col-lg-7 tutor-mb-32">
				<div class="tutor-password-field tutor-settings-pass-field">
					<label class="field-label tutor-form-label" for="password-field-icon-1">
						<?php esc_html_e( 'Re-type New Password', 'tutor' ); ?>
					</label>
					<div class="field-group tutor-input-group">
						<input
							class="tutor-form-control"
							id="cpassword-field-icon-1"
							type="password"
							placeholder="Type Password"
							name="confirm_new_password"
						/>
						<span class="validation-icon valid-btn tutor-icon-mark-filled" style="display:none"></span>
					</div>
				</div>
			</div>
		</div>

		<?php do_action( 'tutor_reset_password_input_after' ); ?>

		<div class="tutor-row">
			<div class="tutor-col-12">
				<button type="submit" class="tutor-btn tutor-profile-password-reset">
					<?php esc_html_e( 'Reset Password', 'tutor' ); ?>
				</button>
			</div>
		</div>
	</form>
</div>
