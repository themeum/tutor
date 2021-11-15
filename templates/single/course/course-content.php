<?php
/**
 * Template for displaying course content
 *
 * @since v.1.0.0
 *
 * @author Themeum
 * @url https://themeum.com
 *
 * @package TutorLMS/Templates
 * @version 1.4.3
 */

global $post;
do_action('tutor_course/single/before/content');

?>
<div class="tutor-single-course-segment tutor-course-content-wrap">
	<?php
		if ( ! empty(get_the_content())){
			?>
			<div class="course-content-title">
				<h4>
					<?php _e('About Course', 'tutor'); ?>
				</h4>
			</div>
			<?php
		}
	?>
	<div class="tutor-course-content-content">
		<?php the_content(); ?>
	</div>
</div>
<?php do_action('tutor_course/single/after/content'); ?>