<?php
/**
 * Withdrawal Request Modal
 *
 * @package Tutor\Templates
 * @subpackage Dashboard
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use Tutor\Components\Button;
use Tutor\Components\Constants\InputType;
use Tutor\Components\Constants\Variant;
use Tutor\Components\InputField;
use TUTOR\Icon;

defined( 'ABSPATH' ) || exit;

$form_id              = $data['form_id'] ?? '';
$modal_id             = $data['modal_id'] ?? '';
$available_balance    = $data['available_balance'] ?? 0;
$min_withdrawal       = $data['min_withdrawal'] ?? 0;
$withdraw_method_name = $data['withdraw_method_name'] ?? '';
?>

<div class="tutor-p-8">

<div class="tutor-flex tutor-gap-6 tutor-p-5 tutor-radius-6 tutor-mb-7">
	<div><?php tutor_utils()->render_svg_icon( Icon::WALLET, 24, 24 ); ?></div>
	<div>
		<div class="tutor-text-h4 tutor-font-bold"><?php echo esc_html( tutor_utils()->tutor_price( $available_balance ) ); ?></div>
		<div class="tutor-text-tiny"><?php esc_html_e( 'Available Balance', 'tutor' ); ?></div>
	</div>
</div>

<form 
	x-data="tutorForm({ id: '<?php echo esc_attr( $form_id ); ?>', mode: 'onBlur', shouldFocusError: true })"
	x-bind="getFormBindings()"
	@submit="handleSubmit((data) => handleWithdrawalFormSubmit(data, '<?php echo esc_attr( $form_id ); ?>'))($event)"
	>
<?php
do_action( 'tutor_withdraw_form_before' );

InputField::make()
	->type( InputType::TEXT )
	->name( 'payment_method' )
	->label( __( 'Selected Payment Method', 'tutor' ) )
	->value( $withdraw_method_name )
	->attr( 'readonly', true )
	->attr( 'class', 'tutor-mb-7' )
	->render();

InputField::make()
	->type( InputType::NUMBER )
	->name( 'amount' )
	->label( __( 'Amount', 'tutor' ) )
	->placeholder( '0.00' )
	->required()
	->clearable()
	->attr( 'x-bind', "register('amount', { required: 'Amount is required' })" )
	->render();
?>
<div class="tutor-flex tutor-gap-2 tutor-mt-4">
	<?php tutor_utils()->render_svg_icon( Icon::INFO_OCTAGON ); ?>
	<div>
	<?php
	echo esc_html(
		sprintf(
			// translators: %s: Minimum withdrawal amount.
			__( 'Minimum withdrawal amount is: %s', 'tutor' ),
			tutor_utils()->tutor_price( $min_withdrawal )
		),
	);
	?>
	</div>
</div>
<?php do_action( 'tutor_withdraw_form_after' ); ?>

<div class="tutor-mt-6 tutor-flex tutor-gap-6 tutor-justify-end">
	<?php
	Button::make()
		->label( __( 'Cancel', 'tutor' ) )
		->variant( Variant::OUTLINE )
		->attr( 'type', 'button' )
		->attr( '@click', 'TutorCore.modal.closeModal("' . $modal_id . '")' )
		->render();

	Button::make()
		->label( __( 'Submit Request', 'tutor' ) )
		->variant( Variant::PRIMARY )
		->attr( 'type', 'submit' )
		->attr( ':class', 'withdrawalRequestMutation?.isPending ? "tutor-btn-loading" : ""' )
		->attr( ':disabled', 'withdrawalRequestMutation?.isPending' )
		->render();
	?>
</div>
</form>
</div>
