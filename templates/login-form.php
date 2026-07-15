<?php
/**
 * Tutor login form template
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.1
 */

use TUTOR\Ajax;
use Tutor\Components\Alert;
use Tutor\Components\Constants\InputType;
use Tutor\Components\Constants\Size;
use Tutor\Components\InputField;
use Tutor\Components\SvgIcon;
use TUTOR\Icon;


$lost_pass = apply_filters( 'tutor_lostpassword_url', wp_lostpassword_url() );
/**
 * Get login validation errors & print
 *
 * @since 2.1.3
 */
$login_errors = get_transient( Ajax::LOGIN_ERRORS_TRANSIENT_KEY ) ? get_transient( Ajax::LOGIN_ERRORS_TRANSIENT_KEY ) : array();
foreach ( $login_errors as $login_error ) {
	Alert::make()
		->text( $login_error )
		->variant( Alert::ERROR )
		->icon( Icon::WARNING )
		->attr( 'class', 'tutor-mb-8' )
		->render();
}

do_action( 'tutor_before_login_form' );
?>
<form id="tutor-login-form" method="post">
	<?php if ( is_single_course() ) : ?>
		<input type="hidden" name="tutor_course_enroll_attempt" value="<?php echo esc_attr( get_the_ID() ); ?>">
	<?php endif; ?>
	<?php tutor_nonce_field(); ?>
	<input type="hidden" name="tutor_action" value="tutor_user_login" />
	<input type="hidden" name="redirect_to" value="<?php echo esc_url( apply_filters( 'tutor_after_login_redirect_url', tutor()->current_url ) ); ?>" />

	<div class="tutor-input-field tutor-mb-7">
		<input type="text" class="tutor-form-control tutor-input" placeholder="<?php esc_html_e( 'Username or Email Address', 'tutor' ); ?>" name="log" value="" size="20" required/>
	</div>

	<div class="tutor-input-field tutor-mb-8" x-data="{ show: false, value: '' }" style="position: relative;">
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
		<input :type="show ? 'text' : 'password'" class="tutor-form-control tutor-input" placeholder="<?php esc_html_e( 'Password', 'tutor' ); ?>" name="pwd" x-model="value" size="20" required style="padding-right: 45px;" autocomplete="off"/>
	</div>

	<div class="tutor-login-error" role="alert" aria-live="polite"></div>
	<?php
		do_action( 'tutor_login_form_middle' );
		do_action( 'login_form' );
		apply_filters( 'login_form_middle', '', '' );
	?>
	<div class="tutor-flex tutor-justify-between tutor-items-center tutor-mt-9">
		<div class="tutor-input-field tutor-w-auto">
			<div class="tutor-input-wrapper">
				<input id="tutor-login-agmnt-1" type="checkbox" class="tutor-checkbox tutor-checkbox-md" name="rememberme" value="forever" />
				<label for="tutor-login-agmnt-1" class="tutor-label">
					<?php esc_html_e( 'Keep me signed in', 'tutor' ); ?>
				</label>
			</div>
		</div>
		<a href="<?php echo esc_url( $lost_pass ); ?>" class="tutor-btn tutor-btn-link-gray tutor-btn-small">
			<?php esc_html_e( 'Forgot Password?', 'tutor' ); ?>
		</a>
	</div>

	<?php do_action( 'tutor_login_form_end' ); ?>
	<button type="submit" class="tutor-btn tutor-btn-primary tutor-btn-block tutor-mt-10">
		<?php esc_html_e( 'Sign In', 'tutor' ); ?>
	</button>

	<?php if ( get_option( 'users_can_register', false ) ) : ?>
		<?php
			$url_arg = array(
				'redirect_to' => tutor()->current_url,
			);
			if ( is_single_course() ) {
				$url_arg['enrol_course_id'] = get_the_ID();
			}
			?>
		<div class="tutor-flex tutor-items-center tutor-justify-center tutor-gap-2 tutor-mt-7">
			<div class="tutor-small">
				<?php esc_html_e( 'Don\'t have an account?', 'tutor' ); ?>
			</div>
			<a href="<?php echo esc_url( add_query_arg( $url_arg, tutor_utils()->student_register_url() ) ); ?>" class="tutor-btn tutor-btn-link">
				<?php esc_html_e( 'Register Now', 'tutor' ); ?>
			</a>
		</div>
	<?php endif; ?>
	<?php do_action( 'tutor_after_sign_in_button' ); ?>
</form>
<?php
do_action( 'tutor_after_login_form' );
if ( ! tutor_utils()->is_tutor_frontend_dashboard() ) :
	?>
<script>
	document.addEventListener('DOMContentLoaded', function() {
		var loginModal = document.querySelector('.tutor-modal.tutor-login-modal');
		var errors = <?php echo wp_json_encode( $login_errors ); ?>;
		if (loginModal && errors.length) {
			loginModal.classList.add('tutor-is-active');
		}
	});
</script>
<?php endif; ?>
<?php delete_transient( Ajax::LOGIN_ERRORS_TRANSIENT_KEY ); ?>
