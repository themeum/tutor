<?php
/**
 * Security Settings
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use TUTOR\Icon;
use Tutor\Components\Button;
use Tutor\Components\Modal;
use Tutor\Components\InputField;
use Tutor\Components\Constants\Size;
use Tutor\Components\Constants\Variant;
use Tutor\Components\Constants\InputType;

$user    = wp_get_current_user();
$form_id = 'tutor-reset-password-form';
?>

<section class="tutor-flex tutor-flex-column tutor-gap-8">
	<div class="tutor-flex tutor-flex-column tutor-gap-4">
		<h5 class="tutor-h5"><?php esc_html_e( 'Login Info', 'tutor' ); ?></h5>

		<div 
			class="tutor-card tutor-card-rounded-2xl tutor-flex tutor-flex-column tutor-gap-5"
			x-data='tutorForm({
				mode: "onChange",
				defaultValues: <?php echo wp_json_encode( array( 'account_email' => $user->user_email ) ); ?>
			})'
		>
			<?php
				InputField::make()
					->type( InputType::EMAIL )
					->label( __( 'Account Email', 'tutor' ) )
					->name( 'account_email' )
					->id( 'account_email' )
					->placeholder( __( 'Enter Your Email', 'tutor' ) )
					->disabled()
					->attr( 'x-bind', "register('account_email', { required: true })" )
					->render();

				Button::make()
					->label( __( 'Reset Password', 'tutor' ) )
					->variant( Variant::SECONDARY )
					->size( Size::SMALL )
					->icon( Icon::KEY )
					->attr( '@click', "TutorCore.modal.showModal('reset-password-modal')" )
					->render();
			?>
		</div>
	</div>
</section>

<?php
$cancel_button = Button::make()
	->label( __( 'Cancel', 'tutor' ) )
	->variant( Variant::SECONDARY )
	->size( Size::SMALL )
	->attr( '@click', "TutorCore.modal.closeModal('reset-password-modal')" )
	->get();

$update_button = Button::make()
	->label( __( 'Update Password', 'tutor' ) )
	->variant( Variant::PRIMARY )
	->size( Size::SMALL )
	->attr( 'form', $form_id )
	->attr( 'type', 'submit' )
	->get();

$modal_footer = sprintf(
	'%s %s',
	$cancel_button,
	$update_button,
);

$modal_title = sprintf(
	'<div class="tutor-flex tutor-items-center tutor-gap-2 tutor-pb-7">%s%s</div>',
	tutor_utils()->get_svg_icon( Icon::LOCK_STROKE_2, 24, 24 ),
	esc_html__( 'Change Account Password', 'tutor' )
);

Modal::make()
	->id( 'reset-password-modal' )
	->title( $modal_title, 'tutor_kses_html' )
	->template( tutor_get_template( 'dashboard.account.settings.reset-password-modal' ) )
	->footer_buttons( $modal_footer )
	->footer_alignment( 'right' )
	->width( '458px' )
	->render();
?>
