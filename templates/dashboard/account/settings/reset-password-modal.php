<?php
/**
 * Reset Password Modal Template
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use Tutor\Components\InputField;
use Tutor\Components\Constants\InputType;

$reset_password_form = 'tutor-reset-password-form';

$default_values = array(
	'current_password'     => '',
	'new_password'         => '',
	'confirm_new_password' => '',
);
?>

<form
	id="<?php echo esc_attr( $reset_password_form ); ?>"
	x-data='tutorForm({ 
		id: "<?php echo esc_attr( $reset_password_form ); ?>",
		mode: "onChange",
		defaultValues: <?php echo wp_json_encode( $default_values ); ?>,
	})'
	x-bind="getFormBindings()"
	@submit="handleSubmit(handleResetPassword)($event)"
	class="tutor-modal-body tutor-flex tutor-flex-column tutor-gap-5"
>
	<?php
		InputField::make()
			->type( InputType::PASSWORD )
			->label( __( 'Current Password', 'tutor' ) )
			->name( 'current_password' )
			->id( 'current_password' )
			->placeholder( __( 'Current Password', 'tutor' ) )
			->required()
			->attr( 'x-bind', "register('current_password', { required: true })" )
			->render();

		InputField::make()
			->type( InputType::PASSWORD )
			->label( __( 'New Password', 'tutor' ) )
			->name( 'new_password' )
			->id( 'new_password' )
			->placeholder( __( 'Type Password', 'tutor' ) )
			->required()
			->attr( 'x-bind', "register('new_password', { required: true })" )
			->render();

		InputField::make()
			->type( InputType::PASSWORD )
			->label( __( 'Confirm New Password', 'tutor' ) )
			->name( 'confirm_new_password' )
			->id( 'confirm_new_password' )
			->placeholder( __( 'Type Password', 'tutor' ) )
			->required()
			->attr( 'x-bind', "register('confirm_new_password', { required: true })" )
			->render();
	?>
</form>
