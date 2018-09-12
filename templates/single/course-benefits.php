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
if ( ! $course_benefits){
	//return; disabled for filter hook
}

$benefits_arr = array();
if ($course_benefits){
	$benefits_arr = explode("\n", $course_benefits);
}
$benefits_arr = apply_filters('lms_topic/benefits_arr', $benefits_arr, get_the_ID());

if (is_array($benefits_arr) && count($benefits_arr)){
	?>


	<div class="lms-course-benefits-wrap">

		<div class="course-benefits-title">
			<h4><?php _e('What Will I Learn?', 'lms'); ?></h4>
		</div>

		<div class="lms-course-benefits-content">
			<ul class="lms-course-benefits-items">
				<?php
				foreach ($benefits_arr as $benefit){
					if ( ! empty(trim($benefit))){
						echo "<li>{$benefit}</li>";
					}
				}
				?>
			</ul>
		</div>
	</div>

<?php } ?>