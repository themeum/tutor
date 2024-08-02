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

use Tutor\Ecommerce\BillingController;

$billing_controller = new BillingController();
$billing_info       = $billing_controller->get_billing_info();

$billing_name = $billing_info ? $billing_info->billing_name : '';
$name_parts   = $billing_name ? explode( ' ', $billing_name, 2 ) : array( '', '' );

$first_name = $name_parts[0] ?? '';
$last_name  = $name_parts[1] ?? '';

$email    = $billing_info ? $billing_info->billing_email : '';
$phone    = $billing_info ? $billing_info->billing_phone : '';
$zip_code = $billing_info ? $billing_info->billing_zip_code : '';
$address  = $billing_info ? $billing_info->billing_address : '';
$country  = $billing_info ? $billing_info->billing_country : '';
$state    = $billing_info ? $billing_info->billing_state : '';
$city     = $billing_info ? $billing_info->billing_city : '';
?>

<div class="tutor-fs-5 tutor-fw-medium tutor-mb-24"><?php esc_html_e( 'Billing', 'tutor' ); ?></div>

<div class="tutor-dashboard-content-inner tutor-dashboard-setting-billing">
	<div class="tutor-mb-32">
		<?php tutor_load_template( 'dashboard.settings.nav-bar', array( 'active_setting_nav' => 'billing' ) ); ?>
		<div class="tutor-fs-6 tutor-fw-medium tutor-color-black tutor-mt-32"><?php esc_html_e( 'Billing Address', 'tutor' ); ?></div>
	</div>

	<form id="user_billing_form" style="max-width: 600px;">
		<?php tutor_nonce_field(); ?>
		<input type="hidden" value="tutor_save_billing_info" name="action" />

		<div class="tutor-row">
			<div class="tutor-col-12 tutor-col-sm-6 tutor-col-md-12 tutor-col-lg-6">
				<div class="tutor-mb-32">
					<label class="tutor-form-label tutor-color-secondary">
						<?php esc_html_e( 'First Name', 'tutor' ); ?>
					</label>
					<input class="tutor-form-control" type="text" name="first_name" value="<?php echo esc_attr( $first_name ); ?>" required>
				</div>
			</div>

			<div class="tutor-col-12 tutor-col-sm-6 tutor-col-md-12 tutor-col-lg-6">
				<div class="tutor-mb-32">
					<label class="tutor-form-label tutor-color-secondary">
						<?php esc_html_e( 'Last Name', 'tutor' ); ?>
					</label>
					<input class="tutor-form-control" type="text" name="last_name" value="<?php echo esc_attr( $last_name ); ?>" required>
				</div>
			</div>

			<div class="tutor-col-12">
				<div class="tutor-mb-32">
					<label class="tutor-form-label tutor-color-secondary">
						<?php esc_html_e( 'Email Address', 'tutor' ); ?>
					</label>
					<input class="tutor-form-control" type="email" name="email" value="<?php echo esc_attr( $email ); ?>" required>
				</div>
			</div>

			<div class="tutor-col-12">
				<div class="tutor-mb-32">
					<label class="tutor-form-label tutor-color-secondary">
						<?php esc_html_e( 'Country', 'tutor' ); ?>
					</label>
					<select name="country" class="tutor-form-control" required>
						<option value=""><?php esc_html_e( 'Select Country', 'tutor' ); ?></option>
						<?php foreach ( tutils()->country_options() as $key => $name ) : ?>
							<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $country, $key ); ?>>
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
					<input class="tutor-form-control" type="text" name="state" value="<?php echo esc_attr( $state ); ?>" required>
				</div>
			</div>

			<div class="tutor-col-12 tutor-col-sm-6 tutor-col-md-12 tutor-col-lg-6">
				<div class="tutor-mb-32">
					<label class="tutor-form-label tutor-color-secondary">
						<?php esc_html_e( 'City', 'tutor' ); ?>
					</label>
					<input class="tutor-form-control" type="text" name="city" value="<?php echo esc_attr( $city ); ?>" required>
				</div>
			</div>

			<div class="tutor-col-12 tutor-col-sm-6 tutor-col-md-12 tutor-col-lg-6">
				<div class="tutor-mb-32">
					<label class="tutor-form-label tutor-color-secondary">
						<?php esc_html_e( 'Postcode / ZIP', 'tutor' ); ?>
					</label>
					<input class="tutor-form-control" type="text" name="zip_code" value="<?php echo esc_attr( $zip_code ); ?>" required>
				</div>
			</div>

			<div class="tutor-col-12 tutor-col-sm-6 tutor-col-md-12 tutor-col-lg-6">
				<div class="tutor-mb-32">
					<label class="tutor-form-label tutor-color-secondary">
						<?php esc_html_e( 'Phone', 'tutor' ); ?>
					</label>
					<input class="tutor-form-control" type="text" name="phone" value="<?php echo esc_attr( $phone ); ?>" required>
				</div>
			</div>

			<div class="tutor-col-12">
				<div class="tutor-mb-32">
					<label class="tutor-form-label tutor-color-secondary">
						<?php esc_html_e( 'Address', 'tutor' ); ?>
					</label>
					<input class="tutor-form-control" type="text" name="address" value="<?php echo esc_attr( $address ); ?>" required>
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
