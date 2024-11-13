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

$billing_controller = new BillingController( false );
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

$country_info = tutor_get_country_info_by_name( $billing_country );
$states       = $country_info && isset( $country_info['states'] ) ? $country_info['states'] : array();
?>

<div class="tutor-row <?php echo isset( $is_checkout_page ) ? 'tutor-g-0' : ''; ?>">
	<div class="tutor-col-12 tutor-col-sm-6 tutor-col-md-12 tutor-col-lg-6">
		<div class="tutor-mb-16">
			<label class="tutor-form-label tutor-color-secondary">
				<?php esc_html_e( 'First Name', 'tutor' ); ?>
			</label>
			<input class="tutor-form-control" type="text" name="billing_first_name" placeholder="<?php esc_attr_e( 'First Name', 'tutor' ); ?>" value="<?php echo esc_attr( $billing_first_name ); ?>" required>
		</div>
	</div>

	<div class="tutor-col-12 tutor-col-sm-6 tutor-col-md-12 tutor-col-lg-6">
		<div class="tutor-mb-16">
			<label class="tutor-form-label tutor-color-secondary">
				<?php esc_html_e( 'Last Name', 'tutor' ); ?>
			</label>
			<input class="tutor-form-control" type="text" name="billing_last_name" placeholder="<?php esc_attr_e( 'Last Name', 'tutor' ); ?>" value="<?php echo esc_attr( $billing_last_name ); ?>" required>
		</div>
	</div>

	<div class="tutor-col-12">
		<div class="tutor-mb-16">
			<label class="tutor-form-label tutor-color-secondary">
				<?php esc_html_e( 'Email Address', 'tutor' ); ?>
			</label>
			<input class="tutor-form-control" type="email" name="billing_email" placeholder="<?php esc_attr_e( 'Email Address', 'tutor' ); ?>" value="<?php echo esc_attr( $billing_email ); ?>" required>
		</div>
	</div>
	<div class="tutor-col-12 tutor-position-relative">
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

	<div class="tutor-col-12 tutor-col-sm-6 tutor-col-md-12 tutor-col-lg-6 tutor-position-relative">
		<div class="tutor-mb-16">
			<label class="tutor-form-label tutor-color-secondary">
				<?php esc_html_e( 'State', 'tutor' ); ?>
			</label>
			<select name="billing_state" class="tutor-form-control">
				<?php if ( $billing_country && empty( $states ) ) : ?>
				<option value=""><?php esc_html_e( 'N/A', 'tutor' ); ?></option>
				<?php endif; ?>
				<?php if ( $billing_country && ( $states ) ) : ?>
				<option value=""><?php esc_html_e( 'Select State', 'tutor' ); ?></option>
				<?php endif; ?>
				<?php
				foreach ( $states as $state ) :
					?>
					<option value="<?php echo esc_attr( $state['name'] ); ?>" <?php selected( $billing_state, $state['name'] ); ?>>
						<?php echo esc_html( $state['name'] ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</div>
	</div>

	<div class="tutor-col-12 tutor-col-sm-6 tutor-col-md-12 tutor-col-lg-6">
		<div class="tutor-mb-16">
			<label class="tutor-form-label tutor-color-secondary">
				<?php esc_html_e( 'City', 'tutor' ); ?>
			</label>
			<input class="tutor-form-control" type="text" name="billing_city" placeholder="<?php esc_attr_e( 'City', 'tutor' ); ?>" value="<?php echo esc_attr( $billing_city ); ?>" required>
		</div>
	</div>

	<div class="tutor-col-12 tutor-col-sm-6 tutor-col-md-12 tutor-col-lg-6">
		<div class="tutor-mb-16">
			<label class="tutor-form-label tutor-color-secondary">
				<?php esc_html_e( 'Postcode / ZIP', 'tutor' ); ?>
			</label>
			<input class="tutor-form-control" type="text" name="billing_zip_code" placeholder="<?php esc_attr_e( 'Postcode / ZIP', 'tutor' ); ?>" value="<?php echo esc_attr( $billing_zip_code ); ?>" required>
		</div>
	</div>

	<div class="tutor-col-12 tutor-col-sm-6 tutor-col-md-12 tutor-col-lg-6">
		<div class="tutor-mb-16">
			<label class="tutor-form-label tutor-color-secondary">
				<?php esc_html_e( 'Phone', 'tutor' ); ?>
			</label>
			<input class="tutor-form-control" type="text" name="billing_phone" placeholder="<?php esc_attr_e( 'Phone', 'tutor' ); ?>" value="<?php echo esc_attr( $billing_phone ); ?>" required>
		</div>
	</div>

	<div class="tutor-col-12">
		<div class="tutor-mb-16">
			<label class="tutor-form-label tutor-color-secondary">
				<?php esc_html_e( 'Address', 'tutor' ); ?>
			</label>
			<input class="tutor-form-control" type="text" name="billing_address" placeholder="<?php esc_attr_e( 'Address', 'tutor' ); ?>" value="<?php echo esc_attr( $billing_address ); ?>" required>
		</div>
	</div>
</div>
