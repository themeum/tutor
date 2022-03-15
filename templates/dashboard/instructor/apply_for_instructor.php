<?php
/**
 * @package TutorLMS/Templates
 * @version 1.4.3
 */
?>

<form method="post" enctype="multipart/form-data" id="tutor-instructor-application-form">
	<?php wp_nonce_field( tutor()->nonce_action, tutor()->nonce ); ?>
	<input type="hidden" value="tutor_apply_instructor" name="tutor_action"/>
	<div class="tutor-container">
	<div class="tutor-instructor-application-process tutor-pt-50 tutor-pb-50">
		<div class="tutor-app-process-alert">
		<div style="border:1px solid var(--tutor-color-brand);" class="tutor-primary tutor-py-12 tutor-px-20 tutor-radius-6">
			<div class="tutor-alert-text tutor-d-flex tutor-align-items-center">
			<span class="tutor-icon-circle-outline-info-filled tutor-mr-10 tutor-h4 tutor-color-brand"></span>
			<span>
				<?php esc_html_e( 'Tutor LMS can be used to edit content built using that extension.
				It cannot edit layouts made before.', 'tutor' ); ?>
			</span>
			</div>
		</div>
		</div>
		<div class="tutor-app-process-image tutor-m-auto tutor-pt-30 tutor-pb-45 tutor-d-flex tutor-justify-content-center tutor-align-items-center">
		<span class="tutor-app-process-img">
			<img
			src="<?php echo esc_url( tutor()->url . 'assets/images/instructor-application-received.png' ); ?>"
			alt="<?php esc_attr_e( 'Instructor Application', 'tutor' ); ?>"
			/>
		</span>
		</div>
		<div class="tutor-instructor-application-body">
		<div class="tutor-ins-app-title tutor-m-auto tutor-text-center">
			<span class="tutor-app-process-title tutor-text-medium-h3 tutor-color-text-primary">
			<?php esc_html_e( 'Do you want to start your career as an instructor?', 'tutor' ); ?>
			</span>
		</div>
		<div class="tutor-ins-app-subtitle tutor-m-auto tutor-text-center tutor-pt-25 tutor-pb-50">
			<span class="tutor-app-process-subtitle tutor-text-regular-h6 tutor-color-text-subsued">
			<?php esc_html_e( 'Tell us your qualifications, show us your passion, and begin
			teaching with us!', 'tutor' ); ?>
			</span>
		</div>
		<div class="tutor-instructor-apply-button tutor-text-center">
			<button class="tutor-btn tutor-btn-primary tutor-color-text-white" type="submit" name="tutor_register_instructor_btn" value="apply">
			<?php esc_html_e( 'Apply Now', 'tutor' ); ?>
			</button>
		</div>
		</div>
	</div>
	</div>
</form>