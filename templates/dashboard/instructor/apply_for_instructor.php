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

	<div class="tutor-card tutor-p-9">
		<div class="tutor-instructor-application-process">
			<div class="tutor-mb-10 tutor-flex tutor-justify-center tutor-items-center">
				<img src="<?php echo esc_url( tutor()->url . 'assets/images/instructor-application-received.png' ); ?>" alt="<?php esc_attr_e( 'Instructor Application', 'tutor' ); ?>" />
			</div>

			<div class="tutor-instructor-application-body tutor-flex tutor-flex-column tutor-items-center">
				<h3 class="tutor-h3 tutor-mb-4 tutor-text-center">
					<?php esc_html_e( 'Do you want to start your career as an instructor?', 'tutor' ); ?>
				</h3>

				<p class="tutor-p2 tutor-text-secondary tutor-mb-10 tutor-text-center" style="max-width: 480px;">
					<?php esc_html_e( 'Tell us your qualifications, show us your passion, and begin teaching with us!', 'tutor' ); ?>
				</p>

				<div class="tutor-instructor-apply-button">
					<button class="tutor-btn tutor-btn-primary" type="submit" name="tutor_register_instructor_btn" value="apply" style="min-width: 140px;">
						<?php esc_html_e( 'Apply Now', 'tutor' ); ?>
					</button>
				</div>
			</div>
		</div>
	</div>
</form>
