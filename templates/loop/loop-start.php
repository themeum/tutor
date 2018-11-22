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

$courseCols = dozent_utils()->get_option( 'courses_col_per_row', 4 );

?>

<div class="dozent-courses dozent-courses-loop-wrap dozent-courses-layout-<?php echo $courseCols; ?>">