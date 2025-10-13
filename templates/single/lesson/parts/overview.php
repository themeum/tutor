<?php
/**
 * Lesson overview template.
 *
 * @package Tutor\Templates
 * @subpackage Single\Lesson
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 3.9.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div id="tutor-course-spotlight-overview" class="tutor-tab-item<?php echo esc_attr( $is_active ? ' is-active' : '' ); ?>">
	<div class="tutor-container">
		<div class="tutor-row tutor-justify-center">
			<div class="tutor-col-xl-8">
				<?php do_action( 'tutor_lesson_before_the_content', $post, $course_id ); ?>
				<div class="tutor-fs-6 tutor-color-secondary tutor-lesson-wrapper">
					<?php the_content(); ?>
				</div>
			</div>
		</div>
	</div>
</div>
