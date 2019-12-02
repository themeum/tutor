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

$courseCols = tutor_utils()->get_option( 'courses_col_per_row', 4 );

?>

<div class="tutor-courses tutor-courses-loop-wrap tutor-courses-layout-<?php echo $courseCols; ?>">