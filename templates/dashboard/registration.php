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
			'class' => 'tutor-btn',
		),
	);
	tutor_load_template( 'feature_disabled', $args );
	?>
<?php else : ?>
	<div id="tutor-registration-wrap" class="tutor-card tutor-p-none tutor-py-9" style="max-width: 520px; margin: 0px auto;">

		<?php do_action( 'tutor_before_student_reg_form' ); ?>

		<form method="post" enctype="multipart/form-data" id="tutor-registration-form" class="tutor-p-8">
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
						->attr( 'class', 'tutor-mb-8' )
						->render();
				endforeach;
			endif;
			?>
			<div class="tutor-form-group">
				<label class="tutor-block tutor-mb-3"><?php esc_html_e( 'First Name', 'tutor' ); ?></label>
				<div class="tutor-input-field tutor-mb-8">
					<input class="tutor-form-control tutor-input" type="text" name="first_name" value="<?php echo esc_attr( tutor_utils()->input_old( 'first_name' ) ); ?>" placeholder="<?php esc_attr_e( 'First Name', 'tutor' ); ?>" required autocomplete="given-name">
				</div>
			</div>

			<div class="tutor-form-group">
				<label class="tutor-block tutor-mb-3"><?php esc_html_e( 'Last Name', 'tutor' ); ?></label>
				<div class="tutor-input-field tutor-mb-8">
					<input class="tutor-form-control tutor-input" type="text" name="last_name" value="<?php echo esc_attr( tutor_utils()->input_old( 'last_name' ) ); ?>" placeholder="<?php esc_attr_e( 'Last Name', 'tutor' ); ?>" required autocomplete="family-name">
				</div>
			</div>

			<div class="tutor-form-group">
				<label class="tutor-block tutor-mb-3"><?php esc_html_e( 'User Name', 'tutor' ); ?></label>
				<div class="tutor-input-field tutor-mb-8">
					<input class="tutor-form-control tutor-input" type="text" name="user_login" class="tutor_user_name" value="<?php echo esc_attr( tutor_utils()->input_old( 'user_login' ) ); ?>" placeholder="<?php esc_html_e( 'User Name', 'tutor' ); ?>" required autocomplete="username">
				</div>
			</div>

			<div class="tutor-form-group">
				<label class="tutor-block tutor-mb-3"><?php esc_html_e( 'E-Mail', 'tutor' ); ?></label>
				<div class="tutor-input-field tutor-mb-8">
					<input class="tutor-form-control tutor-input" type="text" name="email" value="<?php echo esc_attr( tutor_utils()->input_old( 'email' ) ); ?>" placeholder="<?php esc_html_e( 'E-Mail', 'tutor' ); ?>" required autocomplete="email">
				</div>
			</div>

			<div class="tutor-password-strength-checker" x-data="{ show: false, value: '<?php echo esc_attr( tutor_utils()->input_old( 'password' ) ); ?>' }">
				<div class="tutor-password-field">
					<label class="tutor-block tutor-mb-3"><?php esc_html_e( 'Password', 'tutor' ); ?></label>
					<div class="tutor-input-field tutor-mb-8" style="position: relative;">
						<span 
							class="tutor-flex tutor-items-center tutor-justify-center"
							style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); cursor: pointer; z-index: 1;"
							x-show="value.length > 0"
							@click="show = !show"
						>
							<template x-if="!show">
								<?php SvgIcon::make()->name( Icon::EYE )->size( 20 )->render(); ?>
							</template>
							<template x-if="show">
								<?php SvgIcon::make()->name( Icon::EYE_OFF )->size( 20 )->render(); ?>
							</template>
						</span>
						<input 
							class="tutor-form-control tutor-input password-checker" 
							id="tutor-new-password" 
							:type="show ? 'text' : 'password'" 
							name="password" 
							x-model="value" 
							placeholder="<?php esc_html_e( 'Password', 'tutor' ); ?>" 
							required 
							autocomplete="new-password" 
						>
					</div>
				</div>
			</div>

			<div class="tutor-form-group">
				<label class="tutor-block tutor-mb-3"><?php esc_html_e( 'Password confirmation', 'tutor' ); ?></label>
				<div class="tutor-form-wrap">
					<div class="tutor-input-field tutor-mb-8">
						<input 
							class="tutor-form-control tutor-input" 
							type="password" 
							name="password_confirmation" 
							placeholder="<?php esc_html_e( 'Password Confirmation', 'tutor' ); ?>" 
							required 
							autocomplete="new-password" 
						>
					</div>
				</div>
			</div>

			<div class="tutor-form-row">
				<div class="tutor-form-col-12">
					<div class="tutor-form-group">
					<?php
						// providing register_form hook.
						do_action( 'tutor_student_reg_form_middle' );
						do_action( 'register_form' );
					?>
					</div>
				</div>
			</div>    

				<?php do_action( 'tutor_student_reg_form_end' ); ?>

			<?php
			$tutor_toc_page_link = tutor_utils()->get_toc_page_link();
			$consents            = LegalConsent::get_consent_by_display_key( LegalConsent::DISPLAY_ON_STD_REG );
			if ( tutor_utils()->count( $consents ) ) :

				?>
				<?php foreach ( $consents as $consent ) : ?>
					<?php LegalConsent::render_consent_field( $consent, 'tutor-mb-8' ); ?>
				<?php endforeach; ?>

			<?php else : ?>
				<?php if ( $tutor_toc_page_link ) : ?>	
					<div class="tutor-form-row tutor-mb-8">
						<div class="tutor-input-field">
							<div class="tutor-input-wrapper">
								<input type="checkbox" id="tutor-terms-conditions" name="terms_conditions" class="tutor-checkbox tutor-checkbox-md" required>
								<label for="tutor-terms-conditions" class="tutor-label">
									<?php esc_html_e( 'By signing up, you agree to the ', 'tutor' ); ?> <a target="_blank" href="<?php echo esc_url( $tutor_toc_page_link ); ?>" title="<?php esc_html_e( 'Terms and Conditions', 'tutor' ); ?>"><?php esc_html_e( 'Terms and Conditions', 'tutor' ); ?></a>
								</label>
							</div>
						</div>
					</div>
				<?php endif; ?>
			<?php endif; ?>
			<div>
				<button type="submit" name="tutor_register_student_btn" value="register" class="tutor-btn tutor-btn-primary tutor-btn-block"><?php esc_html_e( 'Register', 'tutor' ); ?></button>
				<div class="tutor-flex tutor-items-center tutor-justify-center tutor-gap-2 tutor-mt-8">
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
