<?php
/**
 * Q&A Edit Form Template
 *
 * @package Tutor\Templates
 * @subpackage Dashboard
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.0.0
 */

use Tutor\Components\Button;
use Tutor\Components\Constants\Size;
use Tutor\Components\Constants\Variant;
use Tutor\Components\InputField;
use Tutor\Components\Constants\InputType;

defined( 'ABSPATH' ) || exit;

$form_id        = isset( $form_id ) ? $form_id : '';
$label          = isset( $label ) ? $label : '';
$form_class     = isset( $form_class ) ? $form_class : 'tutor-mb-3';
$default_value  = isset( $default_value ) ? $default_value : '';
$submit_handler = isset( $submit_handler ) ? $submit_handler : '';
$cancel_handler = isset( $cancel_handler ) ? $cancel_handler : '';
$is_pending     = isset( $is_pending ) ? $is_pending : '';
$placeholder    = isset( $placeholder ) ? $placeholder : __( 'Write your question', 'tutor' );

?>

<form 
	class="<?php echo esc_attr( $form_class ); ?>"
	x-data="{ ...tutorForm({ id: '<?php echo esc_attr( $form_id ); ?>', defaultValues: { answer: '<?php echo esc_js( $default_value ); ?>' } }), focused: false }"
	x-bind="getFormBindings()"
	@submit.prevent="handleSubmit(<?php echo esc_js( $submit_handler ); ?>)($event)"
>
	<?php
	$input = InputField::make()
		->type( InputType::TEXTAREA )
		->name( 'answer' )
		->placeholder( $placeholder )
		->attr( 'x-bind', "register('answer', { required: '" . esc_js( __( 'Please enter your response.', 'tutor' ) ) . "' })" )
		->attr( '@keydown', 'handleKeydown($event)' )
		->attr( '@focus', 'focused = true' );

	if ( $label ) {
		$input->label( $label );
	}

	$input->render();
	?>

	<div 
		class="tutor-flex tutor-items-center tutor-mt-5 tutor-justify-between" 
		x-cloak 
		:class="{ 'tutor-hidden': !focused }"
	>
		<div class="tutor-tiny tutor-text-subdued tutor-flex tutor-items-center tutor-gap-2 tutor-sm-hidden">
			<?php tutor_utils()->render_svg_icon( \TUTOR\Icon::COMMAND, 12, 12 ); ?> 
			<?php esc_html_e( 'Cmd/Ctrl +', 'tutor' ); ?>
			<?php tutor_utils()->render_svg_icon( \TUTOR\Icon::ENTER, 12, 12 ); ?> 
			<?php esc_html_e( 'Enter to Save', 'tutor' ); ?>
		</div>
		<div class="tutor-flex tutor-items-center tutor-gap-2">
			<?php
			Button::make()
				->label( __( 'Cancel', 'tutor' ) )
				->variant( Variant::GHOST )
				->size( Size::X_SMALL )
				->attr( 'type', 'button' )
				->attr( '@click', $cancel_handler )
				->attr( ':disabled', $is_pending )
				->render();

			Button::make()
				->label( __( 'Update', 'tutor' ) )
				->variant( Variant::PRIMARY )
				->size( Size::X_SMALL )
				->attr( 'type', 'submit' )
				->attr( ':disabled', $is_pending )
				->attr( ':class', "{ 'tutor-btn-loading': {$is_pending} }" )
				->render();
			?>
		</div>
	</div>
</form>
