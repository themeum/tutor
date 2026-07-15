<?php
/**
 * Q&A Form Template
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

$form_id             = $form_id ?? '';
$label               = $label ?? '';
$submit_label        = $submit_label ?? __( 'Update', 'tutor' );
$form_class          = $form_class ?? '';
$default_value       = $default_value ?? '';
$submit_handler      = $submit_handler ?? '';
$cancel_handler      = $cancel_handler ?? '';
$is_pending          = $is_pending ?? '';
$placeholder         = $placeholder ?? __( 'Write your question', 'tutor' );
$keep_footer_visible = $keep_footer_visible ?? false;

?>

<form
	class="<?php echo esc_attr( $form_class ); ?>"
	x-data="{ ...tutorForm({ id: '<?php echo esc_attr( $form_id ); ?>', mode: 'onSubmit', defaultValues: { answer: '<?php echo esc_js( $default_value ); ?>' } }), focused: false }"
	x-bind="getFormBindings()"
	@submit.prevent="handleSubmit(<?php echo esc_js( $submit_handler ); ?>)($event)"
	@wp-editor-focus="focused = true"
>
	<?php
	$editor_html   = '';
	$custom_editor = apply_filters(
		'tutor_qna_editor',
		$editor_html,
		array(
			'form_id'       => $form_id,
			'default_value' => $default_value,
			'placeholder'   => $placeholder,
			'label'         => $label,
		)
	);

	if ( ! $custom_editor ) {
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
	}

	//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo $custom_editor;
	?>

	<div
		class="tutor-flex tutor-items-center tutor-mt-5 tutor-justify-between tutor-sm-justify-end"
		x-cloak
		<?php echo ! $keep_footer_visible ? ":class=\"{ 'tutor-hidden': !focused }\"" : ''; ?>
	>
		<div class="tutor-tiny tutor-text-subdued tutor-flex tutor-items-center tutor-gap-2 tutor-sm-hidden">
			<?php SvgIcon::make()->name( Icon::COMMAND )->size( 12 )->render(); ?>
			<?php esc_html_e( 'Cmd/Ctrl +', 'tutor' ); ?>
			<?php SvgIcon::make()->name( Icon::ENTER )->size( 12 )->render(); ?>
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
				->label( $submit_label )
				->size( Size::X_SMALL )
				->attr( 'type', 'submit' )
				->attr( ':disabled', $is_pending )
				->attr( ':class', "{ 'tutor-btn-loading': {$is_pending} }" )
				->render();
			?>
		</div>
	</div>
</form>
