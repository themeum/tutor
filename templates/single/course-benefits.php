<?php
/**
 * Template for displaying course benefits
 *
 * @since v.1.0.0
 *
 * @author Themeum
 * @url https://themeum.com
 */

$course_benefits = lms_course_benefits();
if ( empty($course_benefits)){
	return;
}

if (is_array($course_benefits) && count($course_benefits)){
	?>

	<div class="lms-single-course-segment lms-course-benefits-wrap">

		<div class="course-benefits-title">
			<h4><?php _e('What Will I Learn?', 'lms'); ?></h4>
		</div>

		<div class="lms-course-benefits-content">
			<ul class="lms-course-benefits-items">
				<?php
				foreach ($course_benefits as $benefit){
					echo "<li>{$benefit}</li>";
				}
				?>
			</ul>
		</div>
	</div>

<?php } ?>