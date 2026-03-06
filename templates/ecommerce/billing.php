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

$billing_controller = new BillingController( false );
$billing_info       = $billing_controller->get_billing_info();

$countries       = tutor_get_country_list();
$country_options = array();
$state_mapping   = array();

foreach ( $countries as $country ) {
	array_push(
		$country_options,
		array(
			'label' => $country['name'],
			'value' => $country['name'],
		)
	);

	if ( ! empty( $country['states'] ) ) {
		$state_mapping[ $country['name'] ] = array_map(
			function ( $state ) {
				return array(
					'label' => $state['name'],
					'value' => $state['name'],
				);
			},
			$country['states']
		);
	}
}

$billing_country = $billing_info->billing_country ?? tutor_utils()->input_old( 'billing_country', '' );
$initial_states  = $state_mapping[ $billing_country ] ?? array();

$default_values = array(
	'billing_first_name' => $billing_info->billing_first_name ?? '',
	'billing_last_name'  => $billing_info->billing_last_name ?? '',
	'billing_email'      => $billing_info->billing_email ?? '',
	'billing_country'    => $billing_country,
	'billing_state'      => $billing_info->billing_state ?? '',
	'billing_city'       => $billing_info->billing_city ?? '',
	'billing_phone'      => $billing_info->billing_phone ?? '',
	'billing_zip_code'   => $billing_info->billing_zip_code ?? '',
	'billing_address'    => $billing_info->billing_address ?? '',
);
?>

<section class="tutor-flex tutor-flex-column tutor-gap-4">
	<h5 class="tutor-h5 tutor-md-hidden"><?php echo esc_html__( 'Billing Address', 'tutor' ); ?></h5>

	<div class="tutor-surface-l1 tutor-rounded-2xl tutor-p-6 tutor-flex tutor-flex-column tutor-gap-5 tutor-border">
		<form
			id="<?php echo esc_attr( $form_id ); ?>"
			x-data="tutorForm({ 
				id: '<?php echo esc_attr( $form_id ); ?>',
				mode: 'onChange', 
				shouldFocusError: true,
				defaultValues: <?php echo esc_attr( wp_json_encode( $default_values ) ); ?>
			})"
			x-bind="getFormBindings()"
			x-init="$watch('values.billing_country', () => !isResetting && setValue('billing_state', '', { shouldDirty: true }))"
			@submit="handleSubmit((data) => handleSaveBillingInfo(data, '<?php echo esc_attr( $form_id ); ?>'))($event)"
			class="tutor-flex tutor-flex-column tutor-gap-2"
		>
			<?php require tutor_get_template( 'ecommerce.billing-form-fields' ); ?>
		</form>
	</div>
</section>
