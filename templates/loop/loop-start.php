<?php
/**
 * Course Loop Start
 *
 * @package Tutor\Templates
 * @subpackage CourseLoopPart
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.4.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$course_archive_arg = isset( $GLOBALS['tutor_course_archive_arg'] ) ? $GLOBALS['tutor_course_archive_arg']['column_per_row'] : null;
$columns            = $course_archive_arg === null ? tutor_utils()->get_option( 'courses_col_per_row', 3 ) : $course_archive_arg;
?>
<div class="tutor-course-list tutor-grid tutor-grid-<?php echo esc_attr( $columns ); ?>">
