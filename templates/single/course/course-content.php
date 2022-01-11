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

if (tutor_utils()->get_option('enable_course_about', true, true)) {
	$string = get_the_content();
	$limit = 500;
	$has_readmore = false;
	if (strlen($string) > $limit) {
		$has_readmore = true;
		// truncate string
		$first_part = substr($string, 0, $limit);
		$last_part = substr($string, $limit);
	}
?>
	<div class='tab-item-content <?php echo $has_readmore ? 'tutor-has-showmore' : '' ?>'>
		<div class='tutor-showmore-content'>
			<div class="text-medium-h6 tutor-color-text-primary">
				<?php _e('About Course', 'tutor'); ?>
			</div>
			<div class="text-regular-body tutor-color-text-subsued tutor-mt-12">
				<?php
				if ($has_readmore) {
					echo $first_part;
					echo "<div class='showmore-text'>{$last_part}</div>";
				} else {
					echo $string;
				}
				?>
			</div>
		</div>
		<?php
		if ($has_readmore) :
			echo '<div class="tutor-showmore-btn tutor-mt-22" data-showmore="true"><button class="tutor-btn tutor-btn-icon tutor-btn-disable-outline tutor-btn-ghost tutor-no-hover tutor-btn-md btn-showmore"><span class="btn-icon ttr-plus-filled tutor-color-design-brand"></span><span class="tutor-color-text-subsued">Show More</span></button><button class="tutor-btn tutor-btn-icon tutor-btn-disable-outline tutor-btn-ghost tutor-no-hover tutor-btn-md btn-showless"><span class="btn-icon ttr-minus-filled tutor-color-design-brand"></span><span class="tutor-color-text-subsued">Show Less</span></button></div>';
		endif;
		?>
	</div>
<?php
}

do_action('tutor_course/single/after/content'); ?>