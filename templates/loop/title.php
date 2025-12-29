<?php
/**
 * Course loop title
 *
 * @package Tutor\Templates
 * @subpackage CourseLoopPart
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.4.3
 */

?>

<h3 class="tutor-course-name tutor-text-p2 tutor-font-medium tutor-text-primary tutor-mt-2" title="<?php the_title(); ?>">
	<a href="<?php echo esc_url( get_the_permalink() ); ?>"><?php the_title(); ?></a>
</h3>
