<?php
/**
 * Template for displaying course Material Includes assets
 *
 * @since v.1.0.0
 *
 * @author Themeum
 * @url https://themeum.com
 *
 * @package TutorLMS/Templates
 * @version 1.4.3
 */


do_action('tutor_course/single/before/material_includes');

$materials = tutor_course_material_includes();

if ( empty($materials)){
	return;
}

if (is_array($materials) && count($materials)){
	?>
	<div class="tutor-course-details-widget tutor-mt-40">
        <div class="widget-title">
			<span class="color-text-primary text-medium-h6">
				<?php _e('Material Includes', 'tutor'); ?>
			</span>
        </div>
        <ul class="widget-list tutor-ml-0 tutor-mt-16">
			<?php foreach ($materials as $material): ?>
				<li class="tutor-bs-d-flex tutor-bs-align-items-center color-text-primary text-regular-body tutor-mb-10">
					<span className="ttr-mark-filled color-design-brand tutor-mr-5"></span>
					<span><?php echo $material; ?></span>
				</li>
			<?php endforeach; ?>
        </ul>
	</div>
	<?php 
} 

do_action('tutor_course/single/after/material_includes'); 

?>

