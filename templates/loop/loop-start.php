<?php
/**
 * Course Loop Start
 *
 * @since v.1.0.0
 * @author themeum
 * @url https://themeum.com
 *
 * @package TutorLMS/Templates
 * @version 1.4.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$supported_filters = tutor_utils()->get_option( 'supported_course_filters', array() );
$courseCols    	   = isset( $GLOBALS['tutor_course_archive_arg']['column_per_row'] ) ? $GLOBALS['tutor_course_archive_arg']['column_per_row'] : null;
$course_filter 	   = isset( $GLOBALS['tutor_course_archive_arg']['course_filter'] ) ? false : true;
$courseCols        = ( true === $course_filter && $courseCols > 3 ) ? 3 : $courseCols;
$add_class		   = ($course_filter && count( $supported_filters )) ? ' tutor-course-listing-filter-grid-2 ' : '';
?>

<div class="tutor-course-listing-grid tutor-course-listing-grid-<?php echo esc_html( $courseCols ); ?> <?php echo $add_class; ?>">
