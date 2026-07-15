<?php
/**
 * Settings Billing Address
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use Tutor\Ecommerce\BillingController;

$default_values        = BillingController::get_default_values();
$country_state_options = BillingController::get_country_state_options();

$country_options = $country_state_options->country_options;
$state_mapping   = $country_state_options->state_options;

$billing_country = $default_values['billing_country'] ?? '';
$initial_states  = $state_mapping[ $billing_country ] ?? array();
$form_id         = $form_id ?? 'tutor-billing-address-form';
?>

<section class="tutor-flex tutor-flex-column tutor-gap-4">
	<h5 class="tutor-h5 tutor-md-hidden"><?php echo esc_html__( 'Billing Address', 'tutor' ); ?></h5>

	<div class="tutor-card tutor-flex tutor-flex-column tutor-gap-5 tutor-border">
		<form
			id="<?php echo esc_attr( $form_id ); ?>"
			x-data="tutorForm({ 
				id: '<?php echo esc_attr( $form_id ); ?>',
				mode: 'onChange', 
				shouldFocusError: true,
				defaultValues: <?php echo esc_attr( wp_json_encode( $default_values ) ); ?>,
				stateOptions: <?php echo esc_attr( wp_json_encode( $state_mapping ) ); ?>
			})"
			x-bind="getFormBindings()"
			x-init="$watch('values.billing_country', () => !isResetting && setValue('billing_state', '', { shouldDirty: true }))"
			@submit="handleSubmit((data) => handleSaveBillingInfo(data, '<?php echo esc_attr( $form_id ); ?>'))($event)"
			class="tutor-flex tutor-flex-column tutor-gap-5"
		>
			<?php require tutor_get_template( 'ecommerce.billing-form-fields' ); ?>
		</form>
	</div>
</section>
