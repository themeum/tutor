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
use Tutor\Components\SvgIcon;
use Tutor\Components\Constants\Color;
use TUTOR\Icon;

defined( 'ABSPATH' ) || exit;

$form_id              = $data['form_id'] ?? '';
$modal_id             = $data['modal_id'] ?? '';
$available_balance    = $data['available_balance'] ?? 0;
$min_withdrawal       = $data['min_withdrawal'] ?? 0;
$withdraw_method_name = $data['withdraw_method_name'] ?? '';
$currency_symbol      = $data['currency_symbol'] ?? '';
?>

<div class="tutor-px-6 tutor-pt-6 tutor-mt-7 tutor-border-t">
	<div class="tutor-withdrawal-available-balance">
		<div class="tutor-withdrawal-available-balance-icon">
			<?php SvgIcon::make()->name( Icon::WALLET )->size( 24 )->color( Color::BRAND )->render(); ?>
		</div>
		<div>
			<div class="tutor-h4 tutor-font-bold">
				<?php echo wp_kses( tutor_utils()->tutor_price( $available_balance ), tutor_price_allowed_html() ); ?>
			</div>
			<div class="tutor-tiny tutor-text-secondary"><?php esc_html_e( 'Available Balance', 'tutor' ); ?></div>
		</div>
	</div>
</div>

<form 
	x-data="tutorForm({ id: '<?php echo esc_attr( $form_id ); ?>', mode: 'onBlur', shouldFocusError: true })"
	x-bind="getFormBindings()"
	@submit="handleSubmit((data) => handleWithdrawalFormSubmit(data, '<?php echo esc_attr( $form_id ); ?>'))($event)"
	>

	<div class="tutor-p-6">
		<?php
		do_action( 'tutor_withdraw_form_before' );

		InputField::make()
			->type( InputType::TEXT )
			->name( 'payment_method' )
			->label( __( 'Selected Payment Method', 'tutor' ) )
			->value( $withdraw_method_name )
			->attr( 'disabled', true )
			->attr( 'class', 'tutor-mb-7' )
			->render();

		InputField::make()
			->type( InputType::NUMBER )
			->name( 'amount' )
			->label( __( 'Amount', 'tutor' ) )
			->left_icon( '<span class="tutor-input-currency-symbol tutor-font-medium">' . esc_html( $currency_symbol ) . '</span>' )
			->placeholder( '0.00' )
			->required()
			->clearable()
			->attr( 'x-bind', "register('amount', { required: 'Amount is required' })" )
			->render();
		?>
		<div class="tutor-flex tutor-gap-2 tutor-mt-4 tutor-items-center">
			<?php SvgIcon::make()->name( Icon::INFO_OCTAGON )->size( 16 )->color( Color::SECONDARY )->render(); ?>
			<div class="tutor-tiny tutor-font-regular tutor-text-secondary">
			<?php
			echo wp_kses(
				sprintf(
					// translators: %s: Minimum withdrawal amount.
					__( 'Minimum withdrawal amount is: %s', 'tutor' ),
					tutor_utils()->tutor_price( $min_withdrawal )
				),
				tutor_price_allowed_html()
			);
			?>
			</div>
		</div>
		<?php do_action( 'tutor_withdraw_form_after' ); ?>
	</div>

	<div class="tutor-flex tutor-gap-5 tutor-justify-end tutor-border-t tutor-px-6 tutor-py-5">
		<?php
		Button::make()
			->label( __( 'Cancel', 'tutor' ) )
			->variant( Variant::SECONDARY )
			->attr( 'type', 'button' )
			->attr( '@click', 'TutorCore.modal.closeModal("' . $modal_id . '")' )
			->render();

		Button::make()
			->label( __( 'Request Withdrawal', 'tutor' ) )
			->variant( Variant::PRIMARY )
			->attr( 'type', 'submit' )
			->attr( ':class', 'withdrawalRequestMutation?.isPending ? "tutor-btn-loading" : ""' )
			->attr( ':disabled', 'withdrawalRequestMutation?.isPending' )
			->render();
		?>
	</div>
</form>
