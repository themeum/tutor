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

defined( 'ABSPATH' ) || exit;

$form_id  = $data['form_id'] ?? '';
$modal_id = $data['modal_id'] ?? '';
?>

<div class="tutor-p-8">
<form 
	x-data="tutorForm({ id: '<?php echo esc_attr( $form_id ); ?>', mode: 'onBlur', shouldFocusError: true })"
	x-bind="getFormBindings()"
	@submit="handleSubmit((data) => handleWithdrawalFormSubmit(data, '<?php echo esc_attr( $form_id ); ?>'))($event)"
	>
<?php
do_action( 'tutor_withdraw_form_before' );

InputField::make()
	->type( InputType::NUMBER )
	->name( 'amount' )
	->label( 'Amount' )
	->placeholder( 'Enter amount' )
	->required()
	->clearable()
	->attr( 'x-bind', "register('amount', { required: 'Amount is required' })" )
	->render();

do_action( 'tutor_withdraw_form_after' );
?>

<div class="tutor-mt-6 tutor-flex tutor-justify-between">
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
