<?php
/**
 * Billing Template
 *
 * @package Tutor\Templates
 * @subpackage Dashboard\Settings
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 3.0.0
 */
?>

<div class="tutor-fs-5 tutor-fw-medium tutor-mb-24"><?php esc_html_e( 'Billing', 'tutor' ); ?></div>

<div class="tutor-dashboard-content-inner tutor-dashboard-setting-billing">

	<div class="tutor-mb-32">
		<?php tutor_load_template( 'dashboard.settings.nav-bar', array( 'active_setting_nav' => 'billing' ) ); ?>
		<div class="tutor-fs-6 tutor-fw-medium tutor-color-black tutor-mt-32"><?php esc_html_e( 'Billing Address', 'tutor' ); ?></div>
	</div>

	<form id="user_billing_form" action="" method="post" enctype="multipart/form-data" style="max-width: 600px;">
		<?php wp_nonce_field( tutor()->nonce_action, tutor()->nonce ); ?>
		<input type="hidden" value="tutor_save_billing_info" name="action" />

		<div class="tutor-row">
			<div class="tutor-col-12 tutor-col-sm-6 tutor-col-md-12 tutor-col-lg-6">
				<div class="tutor-mb-32">
					<label class="tutor-form-label tutor-color-secondary">
						<?php esc_html_e( 'First Name', 'tutor' ); ?>
					</label>
					<input class="tutor-form-control" type="text" name="first_name">
				</div>
			</div>

			<div class="tutor-col-12 tutor-col-sm-6 tutor-col-md-12 tutor-col-lg-6">
				<div class="tutor-mb-32">
					<label class="tutor-form-label tutor-color-secondary">
						<?php esc_html_e( 'Last Name', 'tutor' ); ?>
					</label>
					<input class="tutor-form-control" type="text" name="last_name">
				</div>
			</div>

			<div class="tutor-col-12">
				<div class="tutor-mb-32">
					<label class="tutor-form-label tutor-color-secondary">
						<?php esc_html_e( 'Email Address', 'tutor' ); ?>
					</label>
					<input class="tutor-form-control" type="email" name="email">
				</div>
			</div>

			<div class="tutor-col-12">
				<div class="tutor-mb-32">
					<label class="tutor-form-label tutor-color-secondary">
						<?php echo esc_html_e( 'Country', 'tutor' ); ?>
					</label>
					<select name="country" class="tutor-form-control">
						<option value="">Select Country</option>
						<?php foreach ( tutils()->country_options() as $key => $name ) : ?>
							<option value="<?php echo esc_attr( $key ); ?>">
								<?php echo esc_html( $name ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>
			</div>

			<div class="tutor-col-12 tutor-col-sm-6 tutor-col-md-12 tutor-col-lg-6">
				<div class="tutor-mb-32">
					<label class="tutor-form-label tutor-color-secondary">
						<?php esc_html_e( 'State', 'tutor' ); ?>
					</label>
					<input class="tutor-form-control" type="text" name="state">
				</div>
			</div>

			<div class="tutor-col-12 tutor-col-sm-6 tutor-col-md-12 tutor-col-lg-6">
				<div class="tutor-mb-32">
					<label class="tutor-form-label tutor-color-secondary">
						<?php esc_html_e( 'City', 'tutor' ); ?>
					</label>
					<input class="tutor-form-control" type="text" name="city">
				</div>
			</div>

			<div class="tutor-col-12 tutor-col-sm-6 tutor-col-md-12 tutor-col-lg-6">
				<div class="tutor-mb-32">
					<label class="tutor-form-label tutor-color-secondary">
						<?php esc_html_e( 'Postcode / ZIP', 'tutor' ); ?>
					</label>
					<input class="tutor-form-control" type="text" name="zip_code">
				</div>
			</div>

			<div class="tutor-col-12 tutor-col-sm-6 tutor-col-md-12 tutor-col-lg-6">
				<div class="tutor-mb-32">
					<label class="tutor-form-label tutor-color-secondary">
						<?php esc_html_e( 'Phone', 'tutor' ); ?>
					</label>
					<input class="tutor-form-control" type="text" name="phone">
				</div>
			</div>

			<div class="tutor-col-12">
				<div class="tutor-mb-32">
					<label class="tutor-form-label tutor-color-secondary">
						<?php esc_html_e( 'Address', 'tutor' ); ?>
					</label>
					<input class="tutor-form-control" type="text" name="address">
				</div>
			</div>
		</div>

		<div class="tutor-row">
			<div class="tutor-col-12">
				<button type="submit" class="tutor-btn tutor-btn-primary">
					<?php esc_html_e( 'Save Address', 'tutor' ); ?>
				</button>
			</div>
		</div>
	</form>
</div>
