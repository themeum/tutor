<?php
/**
 * Course Loop End
 *
 * @package TutorLMS/Templates
 * @version 1.4.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$course_archive_arg = isset($GLOBALS['tutor_course_archive_arg']) ? $GLOBALS['tutor_course_archive_arg']['column_per_row'] : null;
$columns = $course_archive_arg === null ? tutor_utils()->get_option( 'courses_col_per_row', 3 ) : $course_archive_arg;

?>
<div class="<?php echo tutor_utils()->get_responsive_columns( (int) $columns ); ?> tutor-mb-32">
	<div class="tutor-course-list-item tutor-card">
