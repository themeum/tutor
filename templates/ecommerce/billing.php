<?php
/**
 * Settings Billing Address
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use TUTOR\Icon;
use Tutor\Components\InputField;
use Tutor\Components\Constants\InputType;
use Tutor\Ecommerce\BillingController;

$form_id = $data['form_id'] ?? 'tutor-billing-address-form';

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
	<h5 class="tutor-h5 tutor-sm-hidden"><?php echo esc_html__( 'Billing Address', 'tutor' ); ?></h5>

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
			x-init="$watch('values.billing_country', (newCountry, oldCountry) => { if (oldCountry !== undefined && newCountry !== oldCountry) setValue('billing_state', '', { shouldDirty: true }) })"
			@submit="handleSubmit(handleSaveBillingInfo)($event)"
			class="tutor-flex tutor-flex-column tutor-gap-2"
		>
			<div class="tutor-grid tutor-md-grid-cols-1 tutor-grid-cols-2 tutor-gap-5">
				<?php
					InputField::make()
						->type( InputType::TEXT )
						->name( 'billing_first_name' )
						->label( __( 'First Name', 'tutor' ) )
						->clearable()
						->id( 'billing_first_name' )
						->required()
						->placeholder( __( 'Enter your first name', 'tutor' ) )
						->attr( 'x-bind', "register('billing_first_name', { required: true })" )
						->render();

					InputField::make()
						->type( InputType::TEXT )
						->name( 'billing_last_name' )
						->label( __( 'Last Name', 'tutor' ) )
						->clearable()
						->id( 'billing_last_name' )
						->required()
						->placeholder( __( 'Enter your last name', 'tutor' ) )
						->attr( 'x-bind', "register('billing_last_name', { required: true })" )
						->render();
				?>
			</div>

			<?php
				InputField::make()
					->type( InputType::EMAIL )
					->name( 'billing_email' )
					->label( __( 'Email Address', 'tutor' ) )
					->clearable()
					->id( 'billing_email' )
					->required()
					->placeholder( __( 'Enter your email address', 'tutor' ) )
					->attr( 'x-bind', "register('billing_email', { required: true })" )
					->render();

				InputField::make()
					->type( InputType::TEXT )
					->name( 'billing_phone' )
					->label( __( 'Phone', 'tutor' ) )
					->clearable()
					->id( 'billing_phone' )
					->required()
					->placeholder( __( 'Enter your phone number', 'tutor' ) )
					->attr( 'x-bind', "register('billing_phone', { required: true })" )
					->render();

				InputField::make()
					->type( InputType::SELECT )
					->name( 'billing_country' )
					->label( __( 'Country', 'tutor' ) )
					->options( $country_options )
					->searchable()
					->clearable()
					->id( 'billing_country' )
					->required()
					->placeholder( __( 'Enter your country', 'tutor' ) )
					->attr( 'x-bind', "register('billing_country', { required: true })" )
					->render();

				InputField::make()
					->type( InputType::SELECT )
					->name( 'billing_state' )
					->label( __( 'State', 'tutor' ) )
					->options( $initial_states )
					->clearable()
					->searchable()
					->id( 'billing_state' )
					->required()
					->placeholder( __( 'Enter your state', 'tutor' ) )
					->attr( 'x-bind', "register('billing_state', { required: true })" )
					->attr( 'x-effect', 'options = (fetchCountriesQuery.data || []).find(country => country.name === values.billing_country)?.states.map(state => ({ label: state.name, value: state.name })) || []' )
					->render();
			?>

			<!-- City & Postcode Row -->
			<div class="tutor-grid tutor-md-grid-cols-1 tutor-grid-cols-2 tutor-gap-5">
				<?php
					InputField::make()
						->type( InputType::TEXT )
						->name( 'billing_city' )
						->label( __( 'City', 'tutor' ) )
						->clearable()
						->id( 'billing_city' )
						->required()
						->placeholder( __( 'Enter your city', 'tutor' ) )
						->attr( 'x-bind', "register('billing_city', { required: true })" )
						->render();

					InputField::make()
						->type( InputType::TEXT )
						->name( 'billing_zip_code' )
						->label( __( 'Postcode/ Zip', 'tutor' ) )
						->clearable()
						->id( 'billing_zip_code' )
						->required()
						->placeholder( __( 'Enter your postcode/ zip', 'tutor' ) )
						->attr( 'x-bind', "register('billing_zip_code', { required: true })" )
						->render();
				?>
			</div>

			<!-- Address -->
			<?php
				InputField::make()
					->type( InputType::TEXTAREA )
					->name( 'billing_address' )
					->label( __( 'Address', 'tutor' ) )
					->clearable()
					->id( 'billing_address' )
					->required()
					->placeholder( __( 'Enter your address', 'tutor' ) )
					->attr( 'x-bind', "register('billing_address', { required: true })" )
					->render();
			?>
		</form>
	</div>
</section>