<?php
/**
 * Tutor login form template
 *
 * This form template is for using on login template and on
 * modal.
 *
 * @package TutorLoginTemplate
 *
 * @since v2.0.1
 */

$lost_pass = apply_filters( 'tutor_lostpassword_url', wp_lostpassword_url() );
?>
<form id="tutor-login-form" method="post">
	<?php if ( is_single_course() ) : ?>
		<input type="hidden" name="tutor_course_enroll_attempt" value="<?php echo esc_attr( get_the_ID() ); ?>">
	<?php endif; ?>
	
	<input type="hidden" name="tutor_action" value="tutor_user_login" />
	<input type="hidden" name="redirect_to" value="<?php echo esc_url( tutor()->current_url ); ?>" />

	<div class="tutor-mb-20">
		<input type="text" class="tutor-form-control" placeholder="<?php esc_html_e( 'Username or Email Address', 'tutor' ); ?>" name="log" value="" size="20" />
	</div>

	<div class="tutor-mb-32">
		<input type="password" class="tutor-form-control" placeholder="<?php esc_html_e( 'Password', 'tutor' ); ?>" name="pwd" value="" size="20" />
	</div>

	<div class="tutor-login-error"></div>
	<?php
		do_action( 'tutor_login_form_middle' );
		do_action( 'login_form' );
		apply_filters( 'login_form_middle', '', '' );
	?>
	<div class="tutor-d-flex tutor-justify-between tutor-align-center tutor-mb-40">
		<div class="tutor-form-check">
			<input id="tutor-login-agmnt-1" type="checkbox" class="tutor-form-check-input tutor-bg-black-40" name="rememberme" value="forever" />
			<label for="tutor-login-agmnt-1" class="tutor-fs-7 tutor-color-muted">
				<?php esc_html_e( 'Keep me signed in', 'tutor' ); ?>
			</label>
		</div>
		<a href="<?php echo $lost_pass; ?>" class="tutor-btn tutor-btn-ghost">
			<?php esc_html_e( 'Forgot?', 'tutor' ); ?>
		</a>
	</div>

	<?php do_action( 'tutor_login_form_end' ); ?>
	<button type="submit" class="tutor-btn tutor-btn-primary tutor-btn-block">
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
		<div class="tutor-text-center tutor-fs-6 tutor-color-secondary tutor-mt-20">
			<?php esc_html_e( 'Don\'t have an account?', 'tutor' ); ?>&nbsp;
			<a href="<?php echo esc_url( add_query_arg( $url_arg, tutor_utils()->student_register_url() ) ); ?>" class="tutor-btn tutor-btn-link">
				<?php esc_html_e( 'Register Now', 'tutor' ); ?>
			</a>
		</div>
	<?php endif; ?>
</form>
