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


do_action( 'tutor_course/single/before/material_includes' );

$materials = tutor_course_material_includes();

if ( empty( $materials ) ) {
	return;
}

if ( is_array( $materials ) && count( $materials ) ) {
	?>
	<div class="tutor-course-details-widget tutor-mt-40">
		<div class="tutor-course-details-widget-title tutor-mb-16">
			<span class="tutor-color-black tutor-fs-6 tutor-fw-medium">
				<?php _e('Material Includes', 'tutor'); ?>
			</span>
		</div>
		<ul class="tutor-course-details-widget-list">
			<?php foreach ($materials as $material): ?>
				<li class="tutor-d-flex tutor-color-black tutor-fs-6 tutor-fw-normal tutor-mb-12">
					<span class="tutor-icon-mark-filled tutor-color-design-brand tutor-mr-4"></span>
					<span><?php echo $material; ?></span>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
	<?php 
} 

do_action('tutor_course/single/after/material_includes'); 

?>

