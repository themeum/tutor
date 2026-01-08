<?php
/**
 * Course thumb header
 *
 * @package Tutor\Templates
 * @subpackage CourseLoopPart
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.5.8
 */

?>
<?php
	tutor_course_loop_thumbnail();
	$is_enabled_wishlist = tutor_utils()->get_option( 'enable_wishlist', true );
