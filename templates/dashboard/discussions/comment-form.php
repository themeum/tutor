<?php
/**
 * Comment form template.
 *
 * @package Tutor\Templates
 * @subpackage Dashboard
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use Tutor\Components\Button;
use Tutor\Components\Constants\InputType;
use Tutor\Components\Constants\Size;
use Tutor\Components\Constants\Variant;
use Tutor\Components\InputField;
use TUTOR\Icon;
use Tutor\Components\SvgIcon;

$label         = $label ?? '';
$submit_label  = $submit_label ?? __( 'Update', 'tutor' );
$form_class    = $form_class ?? 'tutor-w-full';
$default_value = $default_value ?? '';
$is_pending    = $is_pending ?? 'false';
?>

<form 
	class="<?php echo esc_attr( $form_class ); ?>"
	x-data="{ ...tutorForm({ id: '<?php echo esc_attr( $form_id ); ?>', mode: 'onSubmit', defaultValues: { comment: '<?php echo esc_js( $default_value ); ?>' } }), focused: false }"
	x-bind="getFormBindings()"
	@submit.prevent="handleSubmit(<?php echo esc_js( $submit_handler ); ?>)($event)"
>
	<?php
	$input = InputField::make()
		->type( InputType::TEXTAREA )
		->name( 'comment' )
		->placeholder( $placeholder )
		->attr( 'x-bind', "register('comment', { required: '" . esc_js( __( 'Please enter a comment', 'tutor' ) ) . "' })" )
		->attr( '@keydown', 'handleKeydown($event)' )
		->attr( '@focus', 'focused = true' );

	if ( $label ) {
		$input->label( $label );
	}

	$input->render();
	?>

	<div 
		class="tutor-flex tutor-items-center tutor-mt-5 tutor-justify-between tutor-sm-justify-end"
		x-cloak 
		:class="{ 'tutor-hidden': !focused }"
	>
		<div class="tutor-tiny tutor-text-subdued tutor-flex tutor-items-center tutor-gap-2 tutor-sm-hidden">
			<?php SvgIcon::make()->name( Icon::COMMAND )->size( 12 )->render(); ?> 
			<?php esc_html_e( 'Cmd/Ctrl +', 'tutor' ); ?>
			<?php SvgIcon::make()->name( Icon::ENTER )->size( 12 )->render(); ?> 
			<?php esc_html_e( 'Enter to Save', 'tutor' ); ?>
		</div>

		<div class="tutor-flex tutor-items-center tutor-gap-4">
			<?php
			Button::make()
				->label( __( 'Cancel', 'tutor' ) )
				->variant( Variant::GHOST )
				->size( Size::X_SMALL )
				->attr( 'type', 'button' )
				->attr( '@click', $cancel_handler )
				->render();

			Button::make()
				->label( $submit_label )
				->variant( Variant::PRIMARY_SOFT )
				->size( Size::X_SMALL )
				->attr( 'type', 'submit' )
				->attr( ':disabled', $is_pending )
				->attr( ':class', "{ 'tutor-btn-loading': " . $is_pending . ' }' )
				->render();
			?>
		</div>
	</div>
</form>
