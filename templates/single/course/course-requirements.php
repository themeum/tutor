<?php
/**
 * Template for displaying course requirements
 *
 * @since v.1.0.0
 *
 * @author Themeum
 * @url https://themeum.com
 */

$course_requirements = tutor_course_requirements();

if ( empty($course_requirements)){
	return;
}

if (is_array($course_requirements) && count($course_requirements)){
	?>

	<div class="tutor-single-course-segment  tutor-course-requirements-wrap">

		<div class="course-requirements-title">
			<h3 class="tutor-segment-title"><?php _e('Requirements', 'tutor'); ?></h3>
		</div>

		<div class="tutor-course-requirements-content">
			<ul class="tutor-course-requirements-items">
				<?php
				foreach ($course_requirements as $requirement){
					echo "<li>{$requirement}</li>";
				}
				?>
			</ul>
		</div>
	</div>

<?php } ?>