<?php
/**
 * @package Tutor\Templates
 * @subpackage CourseLoopPart
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.4.3
 */

?>

<div class="tutor-card-footer">
	<?php tutor_course_loop_price(); ?>
	<?php do_action( 'tutor_course_loop_footer_bottom', get_the_ID() ); ?>
</div>
