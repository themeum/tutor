<?php
/**
 * Template for displaying course teachers/ instructor
 *
 * @since v.1.0.0
 *
 * @author Themeum
 * @url https://themeum.com
 */



do_action('tutor_course/single/enrolled/before/teachers');

$teachers = tutor_utils()->get_teachers_by_course();
if ($teachers){
	?>
	<h4 class="tutor-segment-title"><?php _e('About the teachers', 'tutor'); ?></h4>

	<div class="tutor-course-teachers-wrap tutor-single-course-segment">
		<?php
		foreach ($teachers as $teacher){
		    $profile_url = tutor_utils()->student_url($teacher->ID);
			?>
			<div class="single-teacher-wrap">
				<div class="single-teacher-top">
					<div class="teacher-avatar">
                        <a href="<?php echo $profile_url; ?>">
	                        <?php echo tutor_utils()->get_tutor_avatar($teacher->ID); ?>
                        </a>
					</div>

					<div class="teacher-name">
						<h3><a href="<?php echo $profile_url; ?>"><?php echo $teacher->display_name; ?></a> </h3>
						<h4><?php echo $teacher->tutor_profile_job_title; ?></h4>
					</div>

					<div class="teacher-bio">
						<?php echo $teacher->tutor_profile_bio ?>
					</div>
				</div>

                <?php
                $teacher_rating = tutor_utils()->get_teacher_ratings($teacher->ID);
                ?>

				<div class="single-teacher-bottom">
					<div class="ratings">
						<span class="rating-generated">
							<?php tutor_utils()->star_rating_generator($teacher_rating->rating_avg); ?>
						</span>

						<?php
						echo " <span class='rating-digits'>{$teacher_rating->rating_avg}</span> ";
						echo " <span class='rating-total-meta'>({$teacher_rating->rating_count} ".__('ratings', 'tutor').")</span> ";
						?>
					</div>

					<div class="courses">
						<p>
							<i class='tutor-icon-mortarboard'></i>
							<?php echo tutor_utils()->get_course_count_by_teacher($teacher->ID); ?> <span class="tutor-text-mute"> <?php _e('Courses', 'tutor'); ?></span>
						</p>

					</div>

					<div class="students">
						<?php
						$total_students = tutor_utils()->get_total_students_by_teacher($teacher->ID);
						?>

						<p>
							<i class='tutor-icon-user'></i>
							<?php echo  $total_students; ?>
							<span class="tutor-text-mute">  <?php _e('students', 'tutor'); ?></span>
						</p>
					</div>
				</div>
			</div>
			<?php
		}
		?>
	</div>
	<?php
}

do_action('tutor_course/single/enrolled/after/teachers');
