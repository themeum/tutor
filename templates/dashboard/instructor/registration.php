<?php
/**
 * Registration template
 *
 * @package Tutor\Templates
 * @subpackage Dashboard\Instructor
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.4.3
 */

use Tutor\Components\Alert;
use Tutor\Components\SvgIcon;
use Tutor\GDPR\Controllers\LegalConsent;
use TUTOR\Icon;

?>

<?php if ( ! get_option( 'users_can_register', false ) ) : ?>
	<?php
		$args = array(
			'image_path'  => tutor()->url . 'assets/images/construction.png',
			'title'       => __( 'Ooh! Access Denied', 'tutor' ),
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

	<div id="tutor-registration-wrap" class="tutor-card" style="max-width: 520px; margin: 40px auto;">

		<?php do_action( 'tutor_before_instructor_reg_form' ); ?>

		<form method="post" enctype="multipart/form-data" id="tutor-registration-form">

			<?php do_action( 'tutor_instructor_reg_form_start' ); ?>

			<?php wp_nonce_field( tutor()->nonce_action, tutor()->nonce ); ?>
			<input type="hidden" value="tutor_register_instructor" name="tutor_action"/>

			<?php
			$errors = apply_filters( 'tutor_instructor_register_validation_errors', array() ); //phpcs:ignore
			if ( is_array( $errors ) && count( $errors ) ) :
				foreach ( $errors as $error_key => $error_value ) :
					Alert::make()
						->text( $error_value )
						->variant( Alert::ERROR )
						->icon( Icon::WARNING )
						->attr( 'class', 'tutor-mb-8' )
						->render();
					endforeach;
				endif;
			?>
			<div class="tutor-input-field tutor-mb-8">
				<label class="tutor-block tutor-mb-3"><?php esc_html_e( 'First Name', 'tutor' ); ?></label>
				<input class="tutor-form-control tutor-input" type="text" name="first_name" value="<?php echo esc_attr( tutor_utils()->input_old( 'first_name' ) ); ?>" placeholder="<?php esc_html_e( 'First Name', 'tutor' ); ?>" required autocomplete="given-name">
			</div>

			<div class="tutor-input-field tutor-mb-8">
				<label class="tutor-block tutor-mb-3"><?php esc_html_e( 'Last Name', 'tutor' ); ?></label>
				<input class="tutor-form-control tutor-input" type="text" name="last_name" value="<?php echo esc_attr( tutor_utils()->input_old( 'last_name' ) ); ?>" placeholder="<?php esc_html_e( 'Last Name', 'tutor' ); ?>" required autocomplete="family-name">
			</div>

			<div class="tutor-input-field tutor-mb-8">
				<label class="tutor-block tutor-mb-3"><?php esc_html_e( 'User Name', 'tutor' ); ?></label>
				<input class="tutor-form-control tutor-input" type="text" name="user_login" value="<?php echo esc_attr( tutor_utils()->input_old( 'user_login' ) ); ?>" placeholder="<?php esc_html_e( 'User Name', 'tutor' ); ?>" required autocomplete="username">
			</div>

			<div class="tutor-input-field tutor-mb-8">
				<label class="tutor-block tutor-mb-3"><?php esc_html_e( 'E-Mail', 'tutor' ); ?></label>
				<input class="tutor-form-control tutor-input" type="text" name="email" value="<?php echo esc_attr( tutor_utils()->input_old( 'email' ) ); ?>" placeholder="<?php esc_html_e( 'E-Mail', 'tutor' ); ?>" required autocomplete="email">
			</div>

			<div class="tutor-password-strength-checker tutor-mb-8" x-data="{ show: false, value: '<?php echo esc_attr( tutor_utils()->input_old( 'password' ) ); ?>' }">
				<div class="tutor-input-field">
					<label class="tutor-block tutor-mb-3"><?php esc_html_e( 'Password', 'tutor' ); ?></label>
					<div class="tutor-form-wrap" style="position: relative;">
						<span 
							class="tutor-flex"
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
							class="tutor-form-control tutor-input" 
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

			<div class="tutor-input-field tutor-mb-8">
				<label class="tutor-block tutor-mb-3"><?php esc_html_e( 'Password confirmation', 'tutor' ); ?></label>
				<div class="tutor-form-wrap">
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

			<div class="tutor-form-row">
				<div class="tutor-form-col-12">
					<div class="tutor-form-group">
						<?php
							// Providing register_form hook.
							do_action( 'tutor_instructor_reg_form_middle' );
							do_action( 'register_form' );
						?>
					</div>
				</div>
			</div> 

			<?php do_action( 'tutor_instructor_reg_form_end' ); ?>

			<?php
			$tutor_toc_page_link = tutor_utils()->get_toc_page_link();
			$consents            = LegalConsent::get_consent_by_display_key( LegalConsent::DISPLAY_ON_INS_REG );
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
								<input type="checkbox" id="tutor-terms-conditions" name="terms_conditions" class="tutor-checkbox" required>
								<label for="tutor-terms-conditions" class="tutor-label">
									<?php esc_html_e( 'By signing up, you agree to the ', 'tutor' ); ?> <a target="_blank" href="<?php echo esc_url( $tutor_toc_page_link ); ?>" title="<?php esc_html_e( 'Terms and Conditions', 'tutor' ); ?>"><?php esc_html_e( 'Terms and Conditions', 'tutor' ); ?></a>
								</label>
							</div>
						</div>
					</div>
				<?php endif; ?>
			<?php endif; ?>

			<button type="submit" name="tutor_register_instructor_btn" value="register" class="tutor-btn tutor-btn-primary tutor-btn-block"><?php esc_html_e( 'Register as instructor', 'tutor' ); ?></button>
			<?php do_action( 'tutor_after_register_button' ); ?>

		</form>
		<?php do_action( 'tutor_after_registration_form_wrap' ); ?>
	</div>

	<?php do_action( 'tutor_after_instructor_reg_form' ); ?>
<?php endif; ?>