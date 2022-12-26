<?php
/**
 * Student attempt page frontend
 *
 * @package Tutor\Views
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.0
 */

if ( ! empty( $back_url ) ) : ?>
	<div class="tutor-mb-24">
		<a class="tutor-btn tutor-btn-ghost" href="<?php echo esc_url( $back_url ); ?>">
			<span class="tutor-icon-previous tutor-mr-8" area-hidden="true"></span>
			<?php esc_html_e( 'Back', 'tutor' ); ?>
		</a>
	</div>
<?php endif; ?>

<div class="tutor-fs-7 tutor-color-secondary">
	<?php esc_html_e( 'Course', 'tutor' ); ?>: <?php echo esc_html( $course_title ); ?>
</div>

<div class="header-title tutor-fs-5 tutor-fw-medium tutor-color-black tutor-mt-12 tutor-mb-20">
	<?php echo esc_html( $quiz_title ); ?>
</div>

<div class="tutor-mb-32 tutor-fs-7 tutor-color-secondary">
	<div class="tutor-d-flex">
		<div class="tutor-mr-16">
			<?php esc_html_e( 'Student', 'tutor' ); ?>: <span class="tutor-color-black"><strong><?php echo esc_html( $student_name ); ?></strong></span>
		</div>
		<div class="tutor-mr-16">
			<?php esc_html_e( 'Quiz Time', 'tutor' ); ?>: <span class="tutor-color-black"><strong><?php echo esc_html( $quiz_time ); ?></strong></span>
		</div>
		<div>
			<?php esc_html_e( 'Attempt Time', 'tutor' ); ?>: <span class="tutor-color-black"><strong><?php echo esc_html( $attempt_time ); ?></strong></span>
		</div>
	</div>
</div>
