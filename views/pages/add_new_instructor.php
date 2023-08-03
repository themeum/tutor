<?php
/**
 * Add new instructor page
 *
 * @package Tutor\Views
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.0
 */

?>
<div class="wrap">
	<h1 class="wp-heading-inline"><?php esc_html_e( 'Add new instructor', 'tutor' ); ?></h1>
	<hr class="wp-header-end">

	<form action="" id="new-instructor-form" method="post">
		<?php tutor_nonce_field(); ?>
		<input type="hidden" name="action" value="add_new_instructor">


		<div id="form-response"></div>
		<?php
		$validation_errors = apply_filters( 'tutor_instructor_register_validation_errors', array() );
		if ( is_array( $validation_errors ) && count( $validation_errors ) ) :
			?>
			<div class="tutor-alert tutor-warning tutor-mb-12">
				<ul class="tutor-required-fields">
					<?php foreach ( $validation_errors as $validation_error ) : ?>
						<li>
							<?php echo esc_html( $validation_error ); ?>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endif; ?>
		<?php do_action( 'tutor_add_new_instructor_form_fields_before' ); ?>

		<div class="tutor-option-field-row">
			<div class="tutor-option-field-label">
				<label for="">
					<?php esc_html_e( 'First Name', 'tutor' ); ?>

					<span class="tutor-required-fields">*</span>
				</label>
			</div>
			<div class="tutor-option-field">
				<input type="text" name="first_name" value="<?php echo esc_attr( tutor_utils()->input_old( 'first_name' ) ); ?>" placeholder="<?php esc_attr_e( 'First Name', 'tutor' ); ?>">
			</div>
		</div>


		<div class="tutor-option-field-row">
			<div class="tutor-option-field-label">
				<label>
					<?php esc_html_e( 'Last Name', 'tutor' ); ?>
					<span class="tutor-required-fields">*</span>
				</label>
			</div>

			<div class="tutor-option-field">
				<input type="text" name="last_name" value="<?php echo esc_attr( tutor_utils()->input_old( 'last_name' ) ); ?>" placeholder="<?php esc_attr_e( 'Last Name', 'tutor' ); ?>">
			</div>
		</div>

		<div class="tutor-option-field-row">
			<div class="tutor-option-field-label">
				<label>
					<?php esc_html_e( 'User Name', 'tutor' ); ?>
					<span class="tutor-required-fields">*</span>
				</label>
			</div>

			<div class="tutor-option-field">
				<input type="text" name="user_login" class="tutor_user_name" value="<?php echo esc_attr( tutor_utils()->input_old( 'user_login' ) ); ?>" placeholder="<?php esc_attr_e( 'User Name', 'tutor' ); ?>">
			</div>
		</div>

		<div class="tutor-option-field-row">
			<div class="tutor-option-field-label">
				<label>
					<?php esc_html_e( 'E-Mail', 'tutor' ); ?>
					<span class="tutor-required-fields">*</span>
				</label>
			</div>

			<div class="tutor-option-field">
				<input type="text" name="email" value="<?php echo esc_attr( tutor_utils()->input_old( 'email' ) ); ?>" placeholder="<?php esc_attr_e( 'E-Mail', 'tutor' ); ?>">
			</div>
		</div>

		<div class="tutor-option-field-row">
			<div class="tutor-option-field-label">
				<label>
					<?php esc_html_e( 'Phone Number', 'tutor' ); ?>
					<span class="tutor-required-fields">*</span>
				</label>
			</div>

			<div class="tutor-option-field">
				<input type="text" name="phone_number" value="<?php echo esc_attr( tutor_utils()->input_old( 'phone_number' ) ); ?>" placeholder="<?php esc_attr_e( 'Phone Number', 'tutor' ); ?>">
			</div>
		</div>

		<div class="tutor-option-field-row">
			<div class="tutor-option-field-label">
				<label>
					<?php esc_html_e( 'Password', 'tutor' ); ?>
					<span class="tutor-required-fields">*</span>
				</label>
			</div>

			<div class="tutor-option-field">
				<input type="password" name="password" value="<?php echo esc_attr( tutor_utils()->input_old( 'password' ) ); ?>" placeholder="<?php esc_attr_e( 'Password', 'tutor' ); ?>">
			</div>
		</div>

		<div class="tutor-option-field-row">
			<div class="tutor-option-field-label">
				<label>
					<?php esc_html_e( 'Password confirmation', 'tutor' ); ?>
					<span class="tutor-required-fields">*</span>
				</label>
			</div>

			<div class="tutor-option-field">
				<input type="password" name="password_confirmation" value="<?php echo esc_attr( tutor_utils()->input_old( 'password_confirmation' ) ); ?>" placeholder="<?php esc_attr_e( 'Password Confirmation', 'tutor' ); ?>">
			</div>
		</div>

		<div class="tutor-option-field-row">
			<div class="tutor-option-field-label">
				<label>
					<?php esc_html_e( 'Bio', 'tutor' ); ?>
				</label>
			</div>
			<div class="tutor-option-field">
				<textarea name="tutor_profile_bio"><?php echo esc_html( tutor_utils()->input_old( 'tutor_profile_bio' ) ); ?></textarea>
			</div>
		</div>

		<?php do_action( 'tutor_add_new_instructor_form_fields_after' ); ?>

		<div class="tutor-option-field-row">
			<div class="tutor-option-field-label"></div>

			<div class="tutor-option-field">
				<div class="tutor-form-group tutor-reg-form-btn-wrap">
					<button type="submit" name="tutor_register_instructor_btn" value="register" class="tutor-button tutor-button-primary">
						<i class="tutor-icon-plus-square"></i>
						<?php esc_html_e( 'Add new instructor', 'tutor' ); ?></button>
				</div>
			</div>
		</div>

	</form>
</div>
