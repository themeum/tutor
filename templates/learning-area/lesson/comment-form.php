<?php
/**
 * Comment Form Template
 *
 * @package Tutor\Templates
 * @subpackage LearningArea\Lesson
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use Tutor\Components\Button;
use Tutor\Components\Constants\InputType;
use Tutor\Components\Constants\Size;
use Tutor\Components\Constants\Variant;
use Tutor\Components\InputField;
use TUTOR\Icon;

defined( 'ABSPATH' ) || exit;

$form_id          = $form_id ?? '';
$placeholder      = $placeholder ?? '';
$submit_handler   = $submit_handler ?? '';
$cancel_handler   = $cancel_handler ?? '';
$is_pending       = $is_pending ?? 'false';
$default_values   = $default_values ?? array();
$class            = $class ?? '';
$x_show           = $x_show ?? '';
$hide_footer_init = $hide_footer_init ?? false;
?>

<form 
	class="<?php echo esc_attr( $class ); ?>" 
	<?php if ( $x_show ) : ?>
		x-show="<?php echo esc_attr( $x_show ); ?>" 
		x-collapse
		x-init="$watch('<?php echo esc_js( $x_show ); ?>', value => value && $nextTick(() => $refs.commentInput.focus()))"
	<?php endif; ?>
	x-data="tutorForm({ id: '<?php echo esc_attr( $form_id ); ?>', mode: 'onChange', defaultValues: <?php echo esc_js( wp_json_encode( $default_values ) ); ?> })"
	x-bind="getFormBindings()"
	@submit.prevent="handleSubmit((data) => <?php echo esc_js( $submit_handler ); ?>)($event)"
>
	<?php
	$input_field = InputField::make()
		->type( InputType::TEXTAREA )
		->name( 'comment' )
		->placeholder( $placeholder )
		->attr( 'x-bind', "register('comment', { required: '" . esc_js( __( 'Please enter a comment', 'tutor' ) ) . "' })" )
		->attr( 'x-ref', 'commentInput' )
		->attr( '@keydown', 'handleKeydown($event)' );

	if ( $hide_footer_init ) {
		$input_field->attr( '@focus', 'focused = true' );
	}

	$input_field->render();
	?>

	<div 
		class="tutor-flex tutor-items-center tutor-justify-between tutor-mt-5" 
		<?php if ( $hide_footer_init ) : ?>
			x-cloak 
			:class="{ 'tutor-hidden': !focused }"
		<?php endif; ?>
	>
		<div class="tutor-tiny tutor-text-subdued tutor-flex tutor-items-center tutor-gap-2">
			<?php tutor_utils()->render_svg_icon( Icon::COMMAND, 12, 12 ); ?> 
			<?php esc_html_e( 'Cmd/Ctrl +', 'tutor' ); ?>
			<?php tutor_utils()->render_svg_icon( Icon::ENTER, 12, 12 ); ?> 
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
				->attr( ':disabled', $is_pending )
				->render();

			Button::make()
				->label( __( 'Save', 'tutor' ) )
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
