<?php
/**
 * Template for displaying course content
 *
 * @since v.1.0.0
 *
 * @author Themeum
 * @url https://themeum.com
 */

global $post;
$content = get_the_content();
if ( empty($content)){
	return;
}

?>

<div class="tutor-single-course-segment  tutor-course-content-wrap">

    <div class="course-content-title">
        <h3  class="tutor-segment-title"><?php _e('Description', 'tutor'); ?></h3>
    </div>

    <div class="tutor-course-content-content">
        <?php echo $content; ?>
    </div>
</div>
