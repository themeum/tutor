<?php
/**
 * Template for displaying course Material Includes assets
 *
 * @since v.1.0.0
 *
 * @author Themeum
 * @url https://themeum.com
 */


do_action('dozent_course/single/before/material_includes');

$materials = dozent_course_material_includes();

if ( empty($materials)){
	return;
}

if (is_array($materials) && count($materials)){
	?>

	<div class="dozent-single-course-segment  dozent-course-material-includes-wrap">
        <h4 class="dozent-segment-title"><?php _e('Material Includes', 'dozent'); ?></h4>
		<div class="dozent-course-target-audience-content">
			<ul class="dozent-course-target-audience-items dozent-custom-list-style">
				<?php
				foreach ($materials as $material){
					echo "<li>{$material}</li>";
				}
				?>
			</ul>
		</div>
	</div>

<?php } ?>

<?php do_action('dozent_course/single/after/material_includes'); ?>

