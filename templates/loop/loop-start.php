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
$shortcode         = isset($GLOBALS['tutor_shortcode_arg']['shortcode_enabled']) && true === $GLOBALS['tutor_shortcode_arg']['shortcode_enabled'] ? true : false;

if ( true === $shortcode ) {
	$courseCols    = isset( $GLOBALS['tutor_shortcode_arg']['column_per_row'] ) ? $GLOBALS['tutor_shortcode_arg']['column_per_row'] : null;
	$course_filter = isset( $GLOBALS['tutor_shortcode_arg']['include_course_filter'] ) ? false : true;
} else {
	$courseCols    = tutor_utils()->get_option( 'courses_col_per_row', 3 );
	$course_filter = (bool) tutor_utils()->get_option( 'course_archive_filter', false );

}

$courseCols    = ( true === $course_filter && $courseCols > 3 ) ? 3 : $courseCols;

?>
<div class="tutor-course-listing-grid tutor-course-listing-grid-<?php echo esc_html( $courseCols ); ?> <?php
if ( $course_filter && count( $supported_filters ) ) {
	echo wp_kses_post( 'tutor-course-listing-filter-grid-2' );
} else {
	echo ''; }
?>
">
