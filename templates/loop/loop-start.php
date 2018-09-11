<?php
/**
 * Course Loop Start
 *
 * @since v.1.0.0
 * @author themeum
 * @url https://themeum.com
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$courseCols = lms_utils()->get_option( 'courses_col_per_row', 4 );

?>

<div class="lms-courses lms-courses-loop-wrap lms-courses-layout-<?php echo $courseCols; ?>">