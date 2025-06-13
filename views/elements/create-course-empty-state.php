<?php
/**
 * Create course empty state views
 *
 * @package Tutor\Views
 * @subpackage Tutor\ViewElements
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 3.7.0
 */

?>
<div class="tutor-divider tutor-radius-12 tutor-overflow-hidden">
	<div class="tutor-px-32 tutor-py-36 tutor-bg-white tutor-d-flex tutor-flex-column tutor-flex-sm-row tutor-gap-2 tutor-align-center tutor-justify-between">
		<div>
			<h6 class="tutor-fs-5 tutor-fw-medium tutor-mt-0 tutor-mb-8">
				<?php esc_html_e( 'Create Your First Course', 'tutor' ); ?>
			</h6>
			<p class="tutor-fs-6 tutor-color-hints tutor-mt-0 tutor-mb-32">
				<?php esc_html_e( 'Build an engaging eLearning course by adding lessons, quizzes, assignments, and more.', 'tutor' ); ?>	
			</p>
			<div>
				<button class="tutor-btn tutor-btn-primary tutor-d-flex tutor-align-center tutor-gap-1 tutor-create-new-course">
					<i class="tutor-icon-plus-light"></i>
					<?php esc_html_e( 'Create Course', 'tutor' ); ?>
				</button>
			</div>
		</div>
		<div class="tutor-pr-lg-40">
			<img src="<?php echo esc_url( tutor()->url . 'assets/images/course-empty-state.svg' ); ?>" alt="Create Course">
		</div>
	</div>
	<div class="tutor-px-32 tutor-py-40 tutor-divider-top" style="background-color: #f8f8f8;">
		<h6 class="tutor-fs-5 tutor-fw-medium tutor-mt-0 tutor-mb-8">
			<?php esc_html_e( 'Need help creating courses with Tutor LMS?', 'tutor' ); ?>
		</h6>
		<p class="tutor-fs-6 tutor-color-hints tutor-mt-0 tutor-mb-32">
			<?php esc_html_e( 'Explore our in-depth tutorials and create your eLearning courses with ease.', 'tutor' ); ?>
		</p>
		<div class="tutor-d-flex">
			<a class="tutor-btn tutor-btn-tertiary tutor-d-flex tutor-align-center tutor-gap-1" href="https://www.youtube.com/@tutorlms" target="_blank">
				<i class="tutor-icon-external-link"></i>
				<?php esc_html_e( 'Watch Tutorials', 'tutor' ); ?>
			</a>
		</div>
	</div>
</div>
