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



do_action('tutor_course/single/enrolled/before/instructors');

$instructors = tutor_utils()->get_instructors_by_course();

if($instructors && count($instructors)) {
	?>
	<div class="tutor-mt-65">
		<div class="color-text-primary text-medium-h6 tutor-mb-25">
			<?php _e('About the instructors', 'tutor'); ?>
		</div>
		<?php foreach($instructors as $instructor): ?>
			<div class="tutor-instructor-info-card tutor-mb-15">
				<div class="tutor-instructor-info-card-body tutor-bs-d-sm-flex tutor-bs-align-items-start tutor-px-30 tutor-py-24">
					<div class="tutor-ins-avatar tutor-mr-sm-15">
						<img src="<?php echo get_avatar_url($instructor->ID); ?>" alt="instructor avatar" />
					</div>
					<div class="tutor-ins-rest">
						<div class="tutor-ins-title text-medium-body color-text-primary">
							<a href="<?php echo $profile_url; ?>"><?php echo $instructor->display_name; ?></a>
						</div>
						<?php if ( ! empty($instructor->tutor_profile_job_title)): ?>
							<div class="tutor-ins-designation text-regular-caption color-text-hints tutor-mt-3">
								<?php echo $instructor->tutor_profile_job_title; ?>
							</div>
						<?php endif; ?>
						<div class="tutor-ins-summary text-regular-body text-subsued tutor-mt-18">
							<?php echo htmlspecialchars($instructor->tutor_profile_bio); ?>
						</div>
					</div>
				</div>
				<div class="tutor-instructor-info-card-footer tutor-bs-d-sm-flex tutor-bs-align-items-center tutor-bs-justify-content-between tutor-px-30 tutor-py-15">
					<!-- <div class="tutor-ratings flex-center">
						<div class="tutor-rating-stars">
							<span class="ttr-star-full-filled"></span>
							<span class="ttr-star-full-filled"></span>
							<span class="ttr-star-full-filled"></span>
							<span class="ttr-star-half-filled"></span>
							<span class="ttr-star-line-filled"></span>
						</div>
						<div class="tutor-rating-text text-medium-small color-text-primary tutor-mt-3">
							4.5
						</div>
					</div> -->

					<div class="tutor-ratings flex-center ratings">
						<span class="rating-generated">
							<?php tutor_utils()->star_rating_generator($instructor_rating->rating_avg); ?>
						</span>

						<?php
						echo " <span class='rating-digits'>{$instructor_rating->rating_avg}</span> ";
						echo " <span class='rating-total-meta'>({$instructor_rating->rating_count} ".__('ratings', 'tutor').")</span> ";
						?>
					</div>

					<div class="tutor-ins-meta tutor-bs-d-flex">
						<div class="tutor-ins-meta-item color-design-dark flex-center">
							<span class="tutor-icon-30 ttr-user-filled"></span>
							<span class="text-bold-body color-text-primary tutor-mr-4">
								<?php echo tutor_utils()->get_total_students_by_instructor($instructor->ID); ?>
							</span>
							<span class="text-regular-caption color-text-subsued">
								<?php _e('Students', 'tutor'); ?>
							</span>
						</div>
						<div class="tutor-ins-meta-item color-design-dark flex-center ">
							<span class="tutor-icon-30 ttr-mortarboard-line"></span>
							<span class="text-bold-body color-text-primary tutor-mr-4">
								<?php echo tutor_utils()->get_course_count_by_instructor($instructor->ID); ?>
							</span>
							<span class="text-regular-caption color-text-subsued">
								<?php _e('Courses', 'tutor'); ?>
							</span>
						</div>
					</div>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
	<?php
}

do_action('tutor_course/single/enrolled/after/instructors');
