<?php
/**
 * Lesson overview template.
 *
 * @package Tutor\Templates
 * @subpackage Single\Lesson
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

global $tutor_course_id;

?>
<div x-show="activeTab === 'overview'" x-cloak class="tutor-tab-panel tutor-p-6" role="tabpanel">
	<h4 class="tutor-heading-4 tutor-mb-4">
		<?php the_title(); ?>
	</h4>
	<?php do_action( 'tutor_lesson_before_the_content', $post, $tutor_course_id ); ?>
	<?php the_content(); ?>
</div>
