<?php
/**
 * Billing Address Form Fields
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use Tutor\Components\Constants\InputType;
use Tutor\Components\InputField;
?>

<div class="tutor-grid tutor-md-grid-cols-1 tutor-grid-cols-2 tutor-gap-5">
	<?php
		InputField::make()
			->name( 'billing_first_name' )
			->label( __( 'First Name', 'tutor' ) )
			->id( 'billing_first_name' )
			->required()
			->placeholder( __( 'Enter your first name', 'tutor' ) )
			->attr( 'x-bind', "register('billing_first_name', { required: true })" )
			->render();

		InputField::make()
			->name( 'billing_last_name' )
			->label( __( 'Last Name', 'tutor' ) )
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
		->id( 'billing_email' )
		->required()
		->placeholder( __( 'Enter your email address', 'tutor' ) )
		->attr( 'x-bind', "register('billing_email', { required: true })" )
		->render();

	InputField::make()
		->name( 'billing_phone' )
		->label( __( 'Phone', 'tutor' ) )
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
		->searchable()
		->id( 'billing_state' )
		->placeholder( __( 'Enter your state', 'tutor' ) )
		->attr( 'x-effect', 'options = (config.stateOptions || {})[values.billing_country] || []' )
		->render();
?>

<!-- City & Postcode Row -->
<div class="tutor-grid tutor-md-grid-cols-1 tutor-grid-cols-2 tutor-gap-5">
	<?php
		InputField::make()
			->name( 'billing_city' )
			->label( __( 'City', 'tutor' ) )
			->id( 'billing_city' )
			->required()
			->placeholder( __( 'Enter your city', 'tutor' ) )
			->attr( 'x-bind', "register('billing_city', { required: true })" )
			->render();

		InputField::make()
			->name( 'billing_zip_code' )
			->label( __( 'Postcode/ Zip', 'tutor' ) )
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
		->id( 'billing_address' )
		->required()
		->placeholder( __( 'Enter your address', 'tutor' ) )
		->attr( 'x-bind', "register('billing_address', { required: true })" )
		->render();
?>
