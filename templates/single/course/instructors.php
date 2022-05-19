<?php
/**
 * Template for displaying course instructors/ instructor
 *
 * @since v.1.0.0
 *
 * @author Themeum
 * @url https://themeum.com
 *
 * @package TutorLMS/Templates
 * @version 1.4.3
 */



do_action( 'tutor_course/single/enrolled/before/instructors' );

$instructors = tutor_utils()->get_instructors_by_course();

if($instructors && count($instructors)) : ?>
	<div class="tutor-mt-">
		<h3 class="tutor-fs-6 tutor-color-black tutor-mb-24">
			<?php echo _e("A course by", "tutor"); ?>
		</h3>

		<?php foreach($instructors as $instructor): ?>
			<div class="tutor-d-flex tutor-align-center tutor-mb-32">
				<div class="tutor-d-flex tutor-mr-16">
					<?php echo tutor_utils()->get_tutor_avatar($instructor->ID, 'lg'); ?>
				</div>

				<div>
					<a class="tutor-fs-6 tutor-fw-bold tutor-color-black" href="<?php echo tutor_utils()->profile_url($instructor->ID, true); ?>">
						<?php echo $instructor->display_name; ?>
					</a>

					<?php if ( ! empty($instructor->tutor_profile_job_title)): ?>
						<div class="tutor-instructor-designation tutor-fs-7 tutor-color-muted tutor-mt-4">
							<?php echo $instructor->tutor_profile_job_title; ?>
						</div>
					<?php endif; ?>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
<?php endif;

do_action( 'tutor_course/single/enrolled/after/instructors' );
