<?php
/**
 * Template for course archive filter
 *
 * @package Tutor\Templates
 * @subpackage Course_Filter
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.4.3
 */

if ( ! tutor_utils()->get_option( 'course_archive_filter_sorting', true, true, true ) ) {
	return;
}

$sort_by = \TUTOR\Input::get( 'course_order', '' );
?>

<!--
	Note: Do not remove tutor-course-filter attr. It required by _archive.js for filter function.
!-->
<div class="tutor-course-filter tutor-course-archive-sort tutor-mb-24" tutor-course-filter>
	<form class="tutor-d-flex tutor-align-center tutor-justify-end tutor-flex-wrap tutor-gap-1">
		<label class="tutor-fs-6 tutor-color-secondary tutor-text-nowrap tutor-flex-shrink-0 tutor-mb-0" for="tutor-course-order"><?php esc_html_e( 'Sort by', 'tutor' ); ?></label>
		<select id="tutor-course-order" class="tutor-form-control tutor-form-select" name="course_order">
			<option value="newest_first" <?php selected( 'newest_first', $sort_by ); ?> >
				<?php esc_html_e( 'Release Date (newest first)', 'tutor' ); ?>
			</option>
			<option value="oldest_first" <?php selected( 'oldest_first', $sort_by ); ?>>
				<?php esc_html_e( 'Release Date (oldest first)', 'tutor' ); ?>
			</option>
			<option value="course_title_az" <?php selected( 'course_title_az', $sort_by ); ?>>
				<?php esc_html_e( 'Course Title (a-z)', 'tutor' ); ?>
			</option>
			<option value="course_title_za" <?php selected( 'course_title_za', $sort_by ); ?>>
				<?php esc_html_e( 'Course Title (z-a)', 'tutor' ); ?>
			</option>
		</select>
	</form>
</div>
