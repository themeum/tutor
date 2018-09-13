<?php
/**
 * Template for displaying course audience
 *
 * @since v.1.0.0
 *
 * @author Themeum
 * @url https://themeum.com
 */

$target_audience = lms_course_target_audience();

if ( empty($target_audience)){
	return;
}

if (is_array($target_audience) && count($target_audience)){
	?>

	<div class="lms-single-course-segment  lms-course-target-audience-wrap">

		<div class="course-target-audience-title">
			<h4><?php _e('Target Audience', 'lms'); ?></h4>
		</div>

		<div class="lms-course-target-audience-content">
			<ul class="lms-course-target-audience-items">
				<?php
				foreach ($target_audience as $audience){
					echo "<li>{$audience}</li>";
				}
				?>
			</ul>
		</div>
	</div>

<?php } ?>