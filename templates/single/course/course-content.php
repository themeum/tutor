<?php
/**
 * Template for displaying course content
 *
 * @since v.1.0.0
 *
 * @author Themeum
 * @url https://themeum.com
 */



do_action('dozent_course/single/before/content');

global $post;
$content = get_the_content();
if ( empty($content)){
	return;
}
?>

<div class="dozent-single-course-segment  dozent-course-content-wrap">
    <div class="course-content-title">
        <h4  class="dozent-segment-title"><?php _e('Description', 'dozent'); ?></h4>
    </div>

    <div class="dozent-course-content-content">
        <?php echo $content; ?>
    </div>
</div>


<?php do_action('dozent_course/single/after/content'); ?>