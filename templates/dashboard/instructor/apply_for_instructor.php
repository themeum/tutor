<?php
/**
 * Apply for instructor
 *
 * @package Tutor\Templates
 * @subpackage Dashboard\Instructor
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.4.3
 */

?>

<form method="post" enctype="multipart/form-data" id="tutor-instructor-application-form">
	<?php wp_nonce_field( tutor()->nonce_action, tutor()->nonce ); ?>
	<input type="hidden" value="tutor_apply_instructor" name="tutor_action"/>

	<div class="tutor-container">
		<div class="tutor-instructor-application-process tutor-pt-48 tutor-pb-48">
			<div class="tutor-app-process-image tutor-m-auto tutor-pt-32 tutor-pb-44 tutor-d-flex tutor-justify-center tutor-align-center">
				<span class="tutor-app-process-img">
					<img src="<?php echo esc_url( tutor()->url . 'assets/images/instructor-application-received.png' ); ?>" alt="<?php esc_attr_e( 'Instructor Application', 'tutor' ); ?>" />
				</span>
			</div>

			<div class="tutor-instructor-application-body">
				<div class="tutor-ins-app-title tutor-m-auto tutor-text-center">
					<span class="tutor-app-process-title tutor-fs-3 tutor-fw-medium tutor-color-black">
						<?php esc_html_e( 'Do you want to start your career as an instructor?', 'tutor' ); ?>
					</span>
				</div>

				<div class="tutor-ins-app-subtitle tutor-m-auto tutor-text-center tutor-pt-24 tutor-pb-48">
					<span class="tutor-app-process-subtitle tutor-fs-6 tutor-color-muted">
						<?php esc_html_e( 'Tell us your qualifications, show us your passion, and begin teaching with us!', 'tutor' ); ?>
					</span>
				</div>

				<div class="tutor-instructor-apply-button tutor-text-center">
					<button class="tutor-btn tutor-btn-primary tutor-color-white" type="submit" name="tutor_register_instructor_btn" value="apply">
						<?php esc_html_e( 'Apply Now', 'tutor' ); ?>
					</button>
				</div>
			</div>
		</div>
	</div>
</form>
