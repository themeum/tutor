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

$shortcode_arg = isset($GLOBALS['tutor_shortcode_arg']) ? $GLOBALS['tutor_shortcode_arg']['column_per_row'] : null;
$courseCols = $shortcode_arg===null ? tutor_utils()->get_option( 'courses_col_per_row', 3 ) : $shortcode_arg;
?>
<div class="<?php if ($courseCols !== 2) { echo esc_html("tutor-course-listing-item tutor-course-listing-item-sm"); } else{ echo "tutor-course-listing-item"; } ?>">
