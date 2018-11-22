<?php
/**
 * Template for displaying course requirements
 *
 * @since v.1.0.0
 *
 * @author Themeum
 * @url https://themeum.com
 */


do_action('dozent_course/single/before/requirements');

$course_requirements = dozent_course_requirements();

if ( empty($course_requirements)){
	return;
}

if (is_array($course_requirements) && count($course_requirements)){
	?>

	<div class="dozent-single-course-segment  dozent-course-requirements-wrap">

		<div class="course-requirements-title">
			<h4 class="dozent-segment-title"><?php _e('Requirements', 'dozent'); ?></h4>
		</div>

		<div class="dozent-course-requirements-content">
			<ul class="dozent-course-requirements-items dozent-custom-list-style">
				<?php
				foreach ($course_requirements as $requirement){
					echo "<li>{$requirement}</li>";
				}
				?>
			</ul>
		</div>
	</div>

<?php } ?>

<?php do_action('dozent_course/single/after/requirements'); ?>
