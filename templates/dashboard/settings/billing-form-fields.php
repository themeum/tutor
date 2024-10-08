<?php
/**
 * Billing form fields child template
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

$billing_first_name = $billing_info->billing_first_name ?? '';
$billing_last_name  = $billing_info->billing_last_name ?? '';
$billing_email      = $billing_info->billing_email ?? '';
$billing_phone      = $billing_info->billing_phone ?? '';
$billing_zip_code   = $billing_info->billing_zip_code ?? '';
$billing_address    = $billing_info->billing_address ?? '';
$billing_country    = $billing_info->billing_country ?? '';
$billing_state      = $billing_info->billing_state ?? '';
$billing_city       = $billing_info->billing_city ?? '';
?>

<div class="tutor-row">
	<div class="tutor-col-12 tutor-col-sm-6 tutor-col-md-12 tutor-col-lg-6">
		<div class="tutor-mb-16">
			<label class="tutor-form-label tutor-color-secondary">
				<?php esc_html_e( 'First Name', 'tutor' ); ?>
			</label>
			<input class="tutor-form-control" type="text" name="billing_first_name" value="<?php echo esc_attr( $billing_first_name ); ?>" required>
		</div>
	</div>

	<div class="tutor-col-12 tutor-col-sm-6 tutor-col-md-12 tutor-col-lg-6">
		<div class="tutor-mb-16">
			<label class="tutor-form-label tutor-color-secondary">
				<?php esc_html_e( 'Last Name', 'tutor' ); ?>
			</label>
			<input class="tutor-form-control" type="text" name="billing_last_name" value="<?php echo esc_attr( $billing_last_name ); ?>" required>
		</div>
	</div>

	<div class="tutor-col-12">
		<div class="tutor-mb-16">
			<label class="tutor-form-label tutor-color-secondary">
				<?php esc_html_e( 'Email Address', 'tutor' ); ?>
			</label>
			<input class="tutor-form-control" type="email" name="billing_email" value="<?php echo esc_attr( $billing_email ); ?>" required>
		</div>
	</div>

	<div class="tutor-col-12">
		<div class="tutor-mb-16">
			<label class="tutor-form-label tutor-color-secondary">
				<?php esc_html_e( 'Country', 'tutor' ); ?>
			</label>
			<select name="billing_country" class="tutor-form-control" required>
				<option value=""><?php esc_html_e( 'Select Country', 'tutor' ); ?></option>
				<?php
				$countries = array_column( tutor_get_country_list(), 'name' );
				foreach ( $countries as $name ) :
					?>
					<option value="<?php echo esc_attr( $name ); ?>" <?php selected( $billing_country, $name ); ?>>
						<?php echo esc_html( $name ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</div>
	</div>

	<div class="tutor-col-12 tutor-col-sm-6 tutor-col-md-12 tutor-col-lg-6">
		<div class="tutor-mb-16">
			<label class="tutor-form-label tutor-color-secondary">
				<?php esc_html_e( 'State', 'tutor' ); ?>
			</label>
			<input class="tutor-form-control" type="text" name="billing_state" value="<?php echo esc_attr( $billing_state ); ?>" required>
		</div>
	</div>

	<div class="tutor-col-12 tutor-col-sm-6 tutor-col-md-12 tutor-col-lg-6">
		<div class="tutor-mb-16">
			<label class="tutor-form-label tutor-color-secondary">
				<?php esc_html_e( 'City', 'tutor' ); ?>
			</label>
			<input class="tutor-form-control" type="text" name="billing_city" value="<?php echo esc_attr( $billing_city ); ?>" required>
		</div>
	</div>

	<div class="tutor-col-12 tutor-col-sm-6 tutor-col-md-12 tutor-col-lg-6">
		<div class="tutor-mb-16">
			<label class="tutor-form-label tutor-color-secondary">
				<?php esc_html_e( 'Postcode / ZIP', 'tutor' ); ?>
			</label>
			<input class="tutor-form-control" type="text" name="billing_zip_code" value="<?php echo esc_attr( $billing_zip_code ); ?>" required>
		</div>
	</div>

	<div class="tutor-col-12 tutor-col-sm-6 tutor-col-md-12 tutor-col-lg-6">
		<div class="tutor-mb-16">
			<label class="tutor-form-label tutor-color-secondary">
				<?php esc_html_e( 'Phone', 'tutor' ); ?>
			</label>
			<input class="tutor-form-control" type="text" name="billing_phone" value="<?php echo esc_attr( $billing_phone ); ?>" required>
		</div>
	</div>

	<div class="tutor-col-12">
		<div class="tutor-mb-16">
			<label class="tutor-form-label tutor-color-secondary">
				<?php esc_html_e( 'Address', 'tutor' ); ?>
			</label>
			<input class="tutor-form-control" type="text" name="billing_address" value="<?php echo esc_attr( $billing_address ); ?>" required>
		</div>
	</div>
</div>
