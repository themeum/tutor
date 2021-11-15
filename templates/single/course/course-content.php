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

// Split the content
$content = get_the_content();
$segment1 = $content;
$segment2 = '';

do_action('tutor_course/single/before/content');
?>
<div class="tab-item-content <?php echo $segment2 ? 'tutor-has-showmore' : ''; ?>">
	<div class="tutor-showmore-content">
		<div class="text-medium-h6 color-text-primary">
			<?php _e('About Course', 'tutor'); ?>
		</div>
		<div class="text-regular-body color-text-subsued tutor-mt-12">
			<?php 
				echo $segment1; 

				if($segment2) {
					echo '<div class="showmore-text">'.$segment2.'</div>';
				}
			?>
		</div>
		<?php if($segment2): ?>
			<div class="tutor-showmore-btn tutor-mt-22" data-showmore>
				<button class="tutor-btn tutor-btn-icon tutor-btn-disable-outline tutor-btn-ghost tutor-no-hover tutor-btn-md btn-showmore">
					<span class="btn-icon ttr-plus-filled color-design-brand"></span>
					<span class="color-text-subsued"><?php _e('Show More', 'tutor') ?></span>
				</button>
				<button class="tutor-btn tutor-btn-icon tutor-btn-disable-outline tutor-btn-ghost tutor-no-hover tutor-btn-md btn-showless">
					<span class="btn-icon ttr-minus-filled color-design-brand"></span>
					<span class="color-text-subsued"><?php _e('Show Less', 'tutor'); ?></span>
				</button>
			</div>
		<?php endif; ?>
	</div>
</div>
<?php do_action('tutor_course/single/after/content'); ?>