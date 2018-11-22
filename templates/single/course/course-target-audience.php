<?php
/**
 * Template for displaying course audience
 *
 * @since v.1.0.0
 *
 * @author Themeum
 * @url https://themeum.com
 */


do_action('dozent_course/single/before/audience');

$target_audience = dozent_course_target_audience();

if ( empty($target_audience)){
	return;
}

if (is_array($target_audience) && count($target_audience)){
	?>

	<div class="dozent-single-course-segment  dozent-course-target-audience-wrap">

        <h4 class="dozent-segment-title"><?php _e('Target Audience', 'dozent'); ?></h4>

		<div class="dozent-course-target-audience-content">
			<ul class="dozent-course-target-audience-items dozent-custom-list-style">
				<?php
				foreach ($target_audience as $audience){
					echo "<li>{$audience}</li>";
				}
				?>
			</ul>
		</div>
	</div>

<?php } ?>

<?php do_action('dozent_course/single/after/audience'); ?>

