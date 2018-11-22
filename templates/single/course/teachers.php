<?php
/**
 * Template for displaying course teachers/ instructor
 *
 * @since v.1.0.0
 *
 * @author Themeum
 * @url https://themeum.com
 */



do_action('dozent_course/single/enrolled/before/teachers');

$teachers = dozent_utils()->get_teachers_by_course();
if ($teachers){
	?>
	<h4 class="dozent-segment-title"><?php _e('About the teachers', 'dozent'); ?></h4>

	<div class="dozent-course-teachers-wrap dozent-single-course-segment">
		<?php
		foreach ($teachers as $teacher){
		    $profile_url = dozent_utils()->profile_url($teacher->ID);
			?>
			<div class="single-teacher-wrap">
				<div class="single-teacher-top">
                    <div class="dozent-teacher-left">
                        <div class="teacher-avatar">
                            <a href="<?php echo $profile_url; ?>">
                                <?php echo dozent_utils()->get_dozent_avatar($teacher->ID); ?>
                            </a>
                        </div>

                        <div class="teacher-name">
                            <h3><a href="<?php echo $profile_url; ?>"><?php echo $teacher->display_name; ?></a> </h3>
                            <h4><?php echo $teacher->dozent_profile_job_title; ?></h4>
                        </div>
                    </div>
					<div class="teacher-bio">
						<?php echo $teacher->dozent_profile_bio ?>
					</div>
				</div>

                <?php
                $teacher_rating = dozent_utils()->get_teacher_ratings($teacher->ID);
                ?>

				<div class="single-teacher-bottom">
					<div class="ratings">
						<span class="rating-generated">
							<?php dozent_utils()->star_rating_generator($teacher_rating->rating_avg); ?>
						</span>

						<?php
						echo " <span class='rating-digits'>{$teacher_rating->rating_avg}</span> ";
						echo " <span class='rating-total-meta'>({$teacher_rating->rating_count} ".__('ratings', 'dozent').")</span> ";
						?>
					</div>

					<div class="courses">
						<p>
							<i class='dozent-icon-mortarboard'></i>
							<?php echo dozent_utils()->get_course_count_by_teacher($teacher->ID); ?> <span class="dozent-text-mute"> <?php _e('Courses', 'dozent'); ?></span>
						</p>

					</div>

					<div class="students">
						<?php
						$total_students = dozent_utils()->get_total_students_by_teacher($teacher->ID);
						?>

						<p>
							<i class='dozent-icon-user'></i>
							<?php echo  $total_students; ?>
							<span class="dozent-text-mute">  <?php _e('students', 'dozent'); ?></span>
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

do_action('dozent_course/single/enrolled/after/teachers');
