<?php
/**
 * Template for displaying course audience
 *
 * @since v.1.0.0
 *
 * @author Themeum
 * @url https://themeum.com
 */

$target_audience = tutor_course_target_audience();

if ( empty($target_audience)){
	return;
}

if (is_array($target_audience) && count($target_audience)){
	?>

	<div class="tutor-single-course-segment  tutor-course-target-audience-wrap">

		<div class="course-target-audience-title">
			<h4><?php _e('Target Audience', 'tutor'); ?></h4>
		</div>

		<div class="tutor-course-target-audience-content">
			<ul class="tutor-course-target-audience-items">
				<?php
				foreach ($target_audience as $audience){
					echo "<li>{$audience}</li>";
				}
				?>
			</ul>
		</div>
	</div>

<?php } ?>