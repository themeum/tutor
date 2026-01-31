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

$label              = $label ?? '';
$submit_label       = $submit_label ?? __( 'Update', 'tutor' );
$form_class         = $form_class ?? 'tutor-w-full';
$show_shortcut_info = $show_shortcut_info ?? false;
$is_collapsible     = $is_collapsible ?? false;
$default_value      = $default_value ?? '';
?>

<form 
	class="<?php echo esc_attr( $form_class ); ?>"
	x-data="{ ...tutorForm({ id: '<?php echo esc_attr( $form_id ); ?>', defaultValues: { comment: '<?php echo esc_js( $default_value ); ?>' } }), focused: false }"
	x-bind="getFormBindings()"
	@submit.prevent="handleSubmit(<?php echo esc_js( $submit_handler ); ?>)($event)"
>
	<?php
	$input = InputField::make()
		->type( InputType::TEXTAREA )
		->name( 'comment' )
		->placeholder( $placeholder )
		->attr( 'x-bind', "register('comment', { required: '" . esc_js( __( 'Please enter a comment', 'tutor' ) ) . "' })" )
		->attr( '@keydown', 'handleKeydown($event)' );

	if ( $label ) {
		$input->label( $label );
	}

	if ( $is_collapsible ) {
		$input->attr( '@focus', 'focused = true' );
	}

	$input->render();
	?>

	<div 
		class="tutor-flex tutor-items-center tutor-mt-5 <?php echo $show_shortcut_info ? 'tutor-justify-between' : 'tutor-justify-end'; ?>" 
		<?php if ( $is_collapsible ) : ?>
			x-cloak 
			:class="{ 'tutor-hidden': !focused }"
		<?php endif; ?>
	>
		<?php if ( $show_shortcut_info ) : ?>
			<div class="tutor-tiny tutor-text-subdued tutor-flex tutor-items-center tutor-gap-2">
				<?php tutor_utils()->render_svg_icon( Icon::COMMAND, 12, 12 ); ?> 
				<?php esc_html_e( 'Cmd/Ctrl +', 'tutor' ); ?>
				<?php tutor_utils()->render_svg_icon( Icon::ENTER, 12, 12 ); ?> 
				<?php esc_html_e( 'Enter to Save', 'tutor' ); ?>
			</div>
		<?php endif; ?>

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
					->attr( ':disabled', $is_pending_prop )
					->attr( ':class', "{ 'tutor-btn-loading': " . $is_pending_prop . ' }' )
					->render();
			?>
		</div>
	</div>
</form>
