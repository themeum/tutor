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

$course_filter = (bool) tutor_utils()->get_option('course_filter', false);

if ($course_filter) {
?>
	<div class="tutor-course-filter-wrapper">
		<div class="tutor-course-filter-container">
			<?php tutor_load_template('course-filter.filters'); ?>
		</div>
		<div>
			<div class="<?php tutor_container_classes() ?> tutor-course-filter-loop-container">
				<?php tutor_load_template('archive-course-init'); ?>
			</div><!-- .wrap -->
		</div>
	</div>
<?php
} else {
	tutor_load_template('archive-course-init');
}
get_footer(); ?>