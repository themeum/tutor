<?php
/**
 * Single attempt page
 *
 * @package Tutor\Views
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.0
 */

if ( ! empty( $back_url ) ) : ?>
	<a class="tutor-btn tutor-btn-ghost" href="<?php echo esc_url( $back_url ); ?>">
		<span class="tutor-icon-previous tutor-mr-8" area-hidden="true"></span>
		<?php esc_html_e( 'Back', 'tutor' ); ?>
	</a>
<?php endif; ?>

<div class="tutor-fs-7 tutor-color-secondary tutor-mt-24">
	<?php esc_html_e( 'Course', 'tutor' ); ?>: <?php echo esc_html( $course_title ); ?>
</div>

<div class="header-title tutor-fs-5 tutor-fw-medium tutor-color-black tutor-mt-8 tutor-mb-12">
	<?php echo esc_html( $quiz_title ); ?>
</div>

<div class="tutor-mb-32 tutor-fs-7 tutor-color-secondary">
	<div class="tutor-d-flex">
		<div class="tutor-mr-16 tutor-color-secondary">
			<?php esc_html_e( 'Quiz Time', 'tutor' ); ?>: <span class="tutor-fw-medium"><?php echo esc_html( $quiz_time ); ?></span>
		</div>
		<div class="tutor-color-secondary">
			<?php esc_html_e( 'Attempt Time', 'tutor' ); ?>: <span class="tutor-fw-medium"><?php echo esc_html( $attempt_time ); ?></span>
		</div>
	</div>
</div>
