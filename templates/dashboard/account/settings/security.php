<?php
/**
 * Security Settings
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use TUTOR\Icon;
use Tutor\Components\Button;
use Tutor\Components\Modal;
use Tutor\Components\InputField;
use Tutor\Components\Constants\Size;
use Tutor\Components\Constants\Variant;
use Tutor\Components\Constants\InputType;

$user                = wp_get_current_user();
$reset_password_form = 'tutor-reset-password-form';
?>

<section class="tutor-flex tutor-flex-column tutor-gap-8">
	<!-- Login Info Section -->
	<div class="tutor-flex tutor-flex-column tutor-gap-4">
		<h5 class="tutor-h5"><?php esc_html_e( 'Login Info', 'tutor' ); ?></h5>

		<div class="tutor-card tutor-flex tutor-flex-column tutor-gap-5">
			<?php
				InputField::make()
					->type( InputType::EMAIL )
					->label( __( 'Account Email', 'tutor' ) )
					->name( 'account_email' )
					->id( 'account_email' )
					->placeholder( __( 'Enter Your Email', 'tutor' ) )
					->value( $user->user_email )
					->disabled()
					->render();

				Button::make()
					->label( __( 'Reset Password', 'tutor' ) )
					->variant( Variant::SECONDARY )
					->size( Size::SMALL )
					->icon( Icon::LOCK )
					->attr( '@click', "TutorCore.modal.showModal('reset-password-modal')" )
					->render();
			?>
		</div>
	</div>
</section>

<!-- Reset Password Modal -->
<?php
ob_start();
Button::make()
	->label( __( 'Cancel', 'tutor' ) )
	->variant( Variant::SECONDARY )
	->size( Size::SMALL )
	->attr( '@click', "TutorCore.modal.closeModal('reset-password-modal')" )
	->render();

Button::make()
	->label( __( 'Update Password', 'tutor' ) )
	->variant( Variant::PRIMARY )
	->size( Size::SMALL )
	->attr( 'form', $reset_password_form )
	->attr( 'type', 'submit' )
	->render();
$modal_footer = ob_get_clean();

Modal::make()
	->id( 'reset-password-modal' )
	->title( __( 'Change Account Password', 'tutor' ) )
	->template( tutor()->path . 'templates/dashboard/account/settings/reset-password-modal.php' )
	->footer_buttons( $modal_footer )
	->footer_alignment( 'right' )
	->width( '480px' )
	->render();
?>
