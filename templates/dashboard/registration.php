<?php
/**
 * Tutor registration template
 *
 * @package Tutor\Templates
 * @subpackage Dashboard
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.4.3
 */

defined( 'ABSPATH' ) || exit;

use Tutor\Components\Alert;
use Tutor\Components\SvgIcon;
use Tutor\GDPR\Controllers\LegalConsent;
use TUTOR\Icon;

?>

<?php if ( ! get_option( 'users_can_register', false ) ) : ?>
	<?php
	$args = array(
		'image_path'  => tutor()->url . 'assets/images/construction.png',
		'title'       => __( 'Oooh! Access Denied', 'tutor' ),
		'description' => __( 'You do not have access to this area of the application. Please refer to your system  administrator.', 'tutor' ),
		'button'      => array(
			'text'  => __( 'Go to Home', 'tutor' ),
			'url'   => get_home_url(),
			'class' => 'tutor-btn tutor-btn-primary',
		),
	);
	tutor_load_template( 'feature_disabled', $args );
	?>
<?php else : ?>
	<div id="tutor-registration-wrap" class="tutor-card tutor-shadow-md tutor-p-none tutor-py-9" style="max-width: 520px; margin: 40px auto;">

		<?php do_action( 'tutor_before_student_reg_form' ); ?>

		<form method="post" enctype="multipart/form-data" id="tutor-registration-form" class="tutor-p-8 tutor-flex tutor-flex-column tutor-gap-6">
			<input type="hidden" name="tutor_course_enroll_attempt" value="<?php echo isset( $_GET['enrol_course_id'] ) ? (int) $_GET['enrol_course_id'] : ''; ?>">
			<?php do_action( 'tutor_student_reg_form_start' ); ?>

			<?php wp_nonce_field( tutor()->nonce_action, tutor()->nonce ); ?>
			<input type="hidden" value="tutor_register_student" name="tutor_action"/>

			<?php
			$validation_errors = apply_filters( 'tutor_student_register_validation_errors', array() );
			if ( is_array( $validation_errors ) && count( $validation_errors ) ) :
				foreach ( $validation_errors as $validation_error ) :
					Alert::make()
						->text( $validation_error )
						->variant( Alert::ERROR )
						->icon( Icon::WARNING )
						->render();
				endforeach;
			endif;
			?>

			<div class="tutor-input-field">
				<label for="first_name" class="tutor-label tutor-label-required"><?php esc_html_e( 'First Name', 'tutor' ); ?></label>
				<div class="tutor-input-wrapper">
					<input class="tutor-input" id="first_name" type="text" name="first_name" value="<?php echo esc_attr( tutor_utils()->input_old( 'first_name' ) ); ?>" placeholder="<?php esc_attr_e( 'First Name', 'tutor' ); ?>" required autocomplete="given-name">
				</div>
			</div>

			<div class="tutor-input-field">
				<label for="last_name" class="tutor-label tutor-label-required"><?php esc_html_e( 'Last Name', 'tutor' ); ?></label>
				<div class="tutor-input-wrapper">
					<input class="tutor-input" id="last_name" type="text" name="last_name" value="<?php echo esc_attr( tutor_utils()->input_old( 'last_name' ) ); ?>" placeholder="<?php esc_attr_e( 'Last Name', 'tutor' ); ?>" required autocomplete="family-name">
				</div>
			</div>

			<div class="tutor-input-field">
				<label for="user_login" class="tutor-label tutor-label-required"><?php esc_html_e( 'User Name', 'tutor' ); ?></label>
				<div class="tutor-input-wrapper">
					<input class="tutor-input tutor_user_name" id="user_login" type="text" name="user_login" value="<?php echo esc_attr( tutor_utils()->input_old( 'user_login' ) ); ?>" placeholder="<?php esc_attr_e( 'User Name', 'tutor' ); ?>" required autocomplete="username">
				</div>
			</div>

			<div class="tutor-input-field">
				<label for="email" class="tutor-label tutor-label-required"><?php esc_html_e( 'E-Mail', 'tutor' ); ?></label>
				<div class="tutor-input-wrapper">
					<input class="tutor-input" id="email" type="email" name="email" value="<?php echo esc_attr( tutor_utils()->input_old( 'email' ) ); ?>" placeholder="<?php esc_attr_e( 'E-Mail', 'tutor' ); ?>" required autocomplete="email">
				</div>
			</div>

			<div class="tutor-password-strength-checker" x-data="{ show: false, value: '' }">
				<div class="tutor-password-field tutor-input-field">
					<label for="tutor-new-password" class="tutor-label tutor-label-required"><?php esc_html_e( 'Password', 'tutor' ); ?></label>
					<div class="tutor-input-wrapper">
						<input 
							class="tutor-input tutor-input-content-right password-checker" 
							id="tutor-new-password" 
							:type="show ? 'text' : 'password'" 
							name="password" 
							x-model="value" 
							placeholder="<?php esc_attr_e( 'Password', 'tutor' ); ?>" 
							required 
							autocomplete="new-password" 
						>
						<div class="tutor-input-content tutor-input-content-right">
							<button 
								type="button" 
								class="tutor-input-password-toggle"
								x-show="value.length"
								x-cloak
								@click="show = !show"
								aria-label="<?php esc_attr_e( 'Toggle password visibility', 'tutor' ); ?>"
							>
								<span x-show="!show" x-cloak><?php SvgIcon::make()->name( Icon::EYE_OFF )->size( 16 )->render(); ?></span>
								<span x-show="show" x-cloak><?php SvgIcon::make()->name( Icon::EYE )->size( 16 )->render(); ?></span>
							</button>
						</div>
					</div>
				</div>
			</div>

			<div class="tutor-password-field tutor-input-field" x-data="{ show: false, value: '' }">
				<label for="password_confirmation" class="tutor-label tutor-label-required"><?php esc_html_e( 'Password confirmation', 'tutor' ); ?></label>
				<div class="tutor-input-wrapper">
					<input 
						class="tutor-input tutor-input-content-right" 
						id="password_confirmation"
						:type="show ? 'text' : 'password'" 
						name="password_confirmation" 
						x-model="value"
						placeholder="<?php esc_attr_e( 'Password Confirmation', 'tutor' ); ?>" 
						required 
						autocomplete="new-password" 
					>
					<div class="tutor-input-content tutor-input-content-right">
						<button 
							type="button" 
							class="tutor-input-password-toggle"
							x-show="value.length"
							x-cloak
							@click="show = !show"
							aria-label="<?php esc_attr_e( 'Toggle password visibility', 'tutor' ); ?>"
						>
							<span x-show="!show" x-cloak><?php SvgIcon::make()->name( Icon::EYE_OFF )->size( 16 )->render(); ?></span>
							<span x-show="show" x-cloak><?php SvgIcon::make()->name( Icon::EYE )->size( 16 )->render(); ?></span>
						</button>
					</div>
				</div>
			</div>

			<div class="tutor-form-row">
				<div class="tutor-form-col-12">
					<?php
					// Providing register_form hook.
					do_action( 'tutor_student_reg_form_middle' );
					do_action( 'register_form' );
					?>
				</div>
			</div>    

			<?php do_action( 'tutor_student_reg_form_end' ); ?>

			<?php
			$tutor_toc_page_link = tutor_utils()->get_toc_page_link();
			$consents            = LegalConsent::get_consent_by_display_key( LegalConsent::DISPLAY_ON_STD_REG );
			if ( tutor_utils()->count( $consents ) ) :
				foreach ( $consents as $consent ) :
					LegalConsent::render_consent_field( $consent );
				endforeach;
			elseif ( $tutor_toc_page_link ) :
				?>
				<div class="tutor-input-field">
					<div class="tutor-input-wrapper">
						<input type="checkbox" id="tutor-terms-conditions" name="terms_conditions" class="tutor-checkbox tutor-checkbox-md" required>
						<label for="tutor-terms-conditions" class="tutor-label">
							<?php esc_html_e( 'By signing up, you agree to the ', 'tutor' ); ?> <a target="_blank" href="<?php echo esc_url( $tutor_toc_page_link ); ?>" title="<?php esc_attr_e( 'Terms and Conditions', 'tutor' ); ?>"><?php esc_html_e( 'Terms and Conditions', 'tutor' ); ?></a>
						</label>
					</div>
				</div>
			<?php endif; ?>

			<div>
				<button type="submit" name="tutor_register_student_btn" value="register" class="tutor-btn tutor-btn-primary tutor-btn-block"><?php esc_html_e( 'Register', 'tutor' ); ?></button>
				<div class="tutor-flex tutor-items-center tutor-justify-center tutor-gap-2 tutor-mt-6">
					<div class="tutor-small">
						<?php esc_html_e( 'Already have an account?', 'tutor' ); ?>
					</div>
					<a href="<?php echo esc_url( tutor_utils()->tutor_dashboard_url() ); ?>" class="tutor-btn tutor-btn-link">
						<?php esc_html_e( 'Login', 'tutor' ); ?>
					</a>
				</div>
			</div>
			<?php do_action( 'tutor_after_register_button' ); ?>
		</form>
		<?php do_action( 'tutor_after_registration_form_wrap' ); ?>
	</div>
	<?php do_action( 'tutor_after_student_reg_form' ); ?>
<?php endif; ?>
