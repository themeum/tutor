<?php
/**
 * Billing Address Form Fields
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 *
 * Received data
 * - $show_close_button
 * - $close_action
 * - $country_options
 * - $initial_states
 */

defined( 'ABSPATH' ) || exit;

use Tutor\Components\Button;
use Tutor\Components\SvgIcon;
use Tutor\Components\Constants\Color;
use Tutor\Components\Constants\Size;
use Tutor\Components\Constants\Variant;
use Tutor\Components\Constants\InputType;
use Tutor\Components\InputField;
use TUTOR\Icon;

$show_close_button = $show_close_button ?? false;
$close_action      = $close_action ?? 'editBilling = false';
$country_options   = $country_options ?? array();
$initial_states    = $initial_states ?? array();
?>

<?php if ( $show_close_button ) : ?>
	<div class="tutor-flex tutor-items-center tutor-justify-between tutor-mb-4">
		<div class="tutor-flex tutor-items-center tutor-gap-2">
			<span class="tutor-icon tutor-icon-md tutor-text-primary">
				<?php
					SvgIcon::make()
						->name( Icon::MAP_PIN )
						->color( Color::BRAND )
						->size( 20 )
						->render();
				?>
			</span>
			<div class="tutor-medium tutor-mb-none tutor-font-semibold">
				<?php echo esc_html__( 'Billing Address', 'tutor' ); ?>
			</div>
		</div>
		<?php
			Button::make()
				->variant( Variant::LINK )
				->size( Size::SMALL )
				->label( __( 'Close', 'tutor' ) )
				->attr( 'type', 'button' )
				->attr( '@click', "{$close_action}" )
				->render();
		?>
	</div>
<?php endif; ?>

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
