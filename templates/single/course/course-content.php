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
<div class="tab-item-content">
	<div class="tutor-showmore-content">
		<div class="text-medium-h6 tutor-color-text-primary">
			<?php _e('About Course', 'tutor'); ?>
		</div>
		<div class="text-regular-body tutor-color-text-subsued tutor-mt-12">
			<?php the_content(); ?>
		</div>
	</div>
</div>
<?php do_action('tutor_course/single/after/content'); ?>