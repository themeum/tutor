<?php
/**
 * Template for password protected course-bundle.
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 3.0.0
 */

$course_id   = get_the_ID();
$is_enrolled = tutor_utils()->is_enrolled( $course_id, get_current_user_id() );

?>
<?php tutor_utils()->tutor_custom_header(); ?>

<div class="tutor-container tutor-password-protected-course">
	<?php ( isset( $is_enrolled ) && $is_enrolled ) ? tutor_course_enrolled_lead_info() : tutor_course_lead_info(); ?>
	<?php tutor_utils()->has_video_in_single() ? tutor_course_video() : get_tutor_course_thumbnail(); ?>

	<div class="tutor-modal tutor-is-active">
		<div class="tutor-modal-overlay"></div>
		<div class="tutor-modal-window" style="max-width: 834px;">
			<div class="tutor-modal-content tutor-bg-white tutor-p-40">
				<a href="<?php echo esc_url( tutor_utils()->course_archive_page_url() ); ?>" class="tutor-iconic-btn tutor-modal-close-o">
					<span class="tutor-icon-times" area-hidden="true"></span>
				</a>
				<div class="tutor-row">
					<div class="tutor-col-md-7">
						<div class="tutor-d-flex tutor-flex-column">
							<div class="tutor-fs-3 tutor-mb-12">
								<i class="tutor-icon-lock-line"></i>
							</div>
							<span class="tutor-locked-badge tutor-mb-8"><?php esc_html_e( 'Course is locked', 'tutor' ); ?></span>
							<h3 class="tutor-fw-medium tutor-fs-5 tutor-color-black"><?php the_title(); ?></h3>
						</div>
					</div>
					<div class="tutor-col-md-5">
						<form action="<?php echo esc_url( site_url( 'wp-login.php?action=postpass', 'login_post' ) ); ?>" method="post" class="tutor-mt-56">
							<div class="tutor-mb-12">
								<label class="tutor-form-label tutor-color-secondary">
									<?php esc_html_e( 'Enter your password', 'tutor' ); ?>
								</label>
								<input type="password" name="post_password" class="tutor-form-control" />
							</div>
							<div class="tutor-d-flex tutor-gap-1">
								<input type="checkbox" id="tutor-protected-show-password" class="tutor-form-check-input">
								<label for="tutor-protected-show-password" class="tutor-fs-7 tutor-color-hint">
									<?php esc_html_e( 'Show password', 'tutor' ); ?>
								</label>
							</div>
							<div class="tutor-mt-24 tutor-text-right">
								<button type="submit" class="tutor-btn tutor-btn-primary"><?php esc_html_e( 'Submit', 'tutor' ); ?></button>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<?php tutor_utils()->tutor_custom_footer(); ?>
