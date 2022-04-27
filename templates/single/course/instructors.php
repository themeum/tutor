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
	<div class="tutor-mt-60">
		<h3 class="tutor-fs-5 tutor-fw-bold tutor-color-black tutor-mb-24">
			<?php echo _n( 'About the instructor', 'About the instructors', count( $instructors ), 'tutor' ); ?>
		</h3>

		<?php foreach($instructors as $instructor): ?>
			<div class="tutor-instructor-info-card tutor-card tutor-mb-32">
				<div class="tutor-card-body">
					<div class="tutor-d-flex">
						<?php echo tutor_utils()->get_tutor_avatar($instructor->ID, 'md'); ?>

						<div class="tutor-ml-16">
							<div class="tutor-instructor-name tutor-fs-6 tutor-fw-medium">
								<a class="tutor-color-black" href="<?php echo tutor_utils()->profile_url($instructor->ID, true); ?>"><?php echo $instructor->display_name; ?></a>
							</div>
							
							<?php if ( ! empty($instructor->tutor_profile_job_title)): ?>
								<div class="tutor-instructor-designation tutor-fs-7 tutor-color-muted tutor-break-word tutor-mt-4">
									<?php echo $instructor->tutor_profile_job_title; ?>
								</div>
							<?php endif; ?>

							<?php if (! empty($instructor->tutor_profile_bio) ) : ?>
								<div class="tutor-instructor-summary tutor-fs-6 tutor-color-secondary tutor-mt-20">
									<?php echo tutor_utils()->clean_html_content($instructor->tutor_profile_bio); ?>
								</div>
							<?php endif; ?>
						</div>
					</div>
				</div>

				<div class="tutor-card-footer">
					<div class="tutor-row">
						<div class="tutor-col tutor-mb-12 tutor-mb-md-0">
							<?php 
								$instructor_rating = tutor_utils()->get_instructor_ratings($instructor->ID);
								tutor_utils()->star_rating_generator_v2($instructor_rating->rating_avg, $instructor_rating->rating_count, true); 
							?>
						</div>
						<div class="tutor-col-md-auto">
							<div class="tutor-meta">
								<div>
									<span class="tutor-icon-user-line tutor-meta-icon tutor-color-black" area-hidden="true"></span>
									<span class="tutor-meta-value">
										<?php echo tutor_utils()->get_total_students_by_instructor($instructor->ID); ?>
									</span>
									<span>
										<?php _e('Students', 'tutor'); ?>
									</span>
								</div>
								
								<div>
									<span class="tutor-icon-mortarboard tutor-meta-icon tutor-color-black" area-hidden="true"></span>
									<span class="tutor-meta-value">
										<?php echo tutor_utils()->get_course_count_by_instructor($instructor->ID); ?>
									</span>
									<span>
										<?php _e('Courses', 'tutor'); ?>
									</span>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
<?php endif;

do_action( 'tutor_course/single/enrolled/after/instructors' );
