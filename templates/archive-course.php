<?php

/**
 * Template for displaying courses
 *
 * @since v.1.0.0
 *
 * @author Themeum
 * @url https://themeum.com
 *
 * @package TutorLMS/Templates
 * @version 1.5.8
 */

get_header();

$course_filter = (bool) tutor_utils()->get_option('course_archive_filter', false);
$supported_filters = tutor_utils()->get_option('supported_course_filters', array());

if ($course_filter && count($supported_filters)) {
?>
	<div class="tutor-course-listing-filter tutor-filter-course-grid-2 tutor-filter-course-grid-3">
		<div class="tutor-course-filter tutor-course-filter-container">
    		<div class="tutor-course-filter-widget">
				<?php tutor_load_template('course-filter.filters'); ?>
			</div>
		</div>
		<div id="tutor-course-filter-loop-container" class="<?php tutor_container_classes(); ?> tutor-course-filter-loop-container" data-column_per_row="<?php echo esc_html(tutor_utils()->get_option( 'courses_col_per_row', 3 )); ?>">
			<?php tutor_load_template('archive-course-init'); ?>
		</div><!-- .wrap -->
	</div>
<?php
} else {
	?>
		<div class="<?php tutor_container_classes(); ?>">
			<?php tutor_load_template('archive-course-init'); ?>
		</div>
	<?php
}
get_footer(); ?>