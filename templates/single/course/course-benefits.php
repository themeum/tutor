<?php
/**
 * Template for displaying course benefits
 *
 * @since v.1.0.0
 *
 * @author Themeum
 * @url https://themeum.com
 */



do_action('dozent_course/single/before/benefits');


$course_benefits = dozent_course_benefits();
if ( empty($course_benefits)){
	return;
}

if (is_array($course_benefits) && count($course_benefits)){
	?>

	<div class="dozent-single-course-segment dozent-course-benefits-wrap">

		<div class="course-benefits-title">
			<h4 class="dozent-segment-title"><?php _e('What Will I Learn?', 'dozent'); ?></h4>
		</div>

		<div class="dozent-course-benefits-content">
			<ul class="dozent-course-benefits-items dozent-custom-list-style">
				<?php
				foreach ($course_benefits as $benefit){
					echo "<li>{$benefit}</li>";
				}
				?>
			</ul>
		</div>
	</div>

<?php } ?>

<?php do_action('dozent_course/single/after/benefits'); ?>

