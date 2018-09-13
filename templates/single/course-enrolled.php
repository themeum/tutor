<?php
/**
 * Template for displaying course content
 *
 * @since v.1.0.0
 *
 * @author Themeum
 * @url https://themeum.com
 */

if ( ! defined( 'ABSPATH' ) )
	exit;
?>

<div class="lms-single-course-segment  lms-course-enrolled-wrap">
    <h2><?php _e('Enrolled', 'lms'); ?></h2>

    <?php
    /**
     * TODO: Wrong URL comming, need to check
     */
    $lesson_url = lms_utils()->start_course_url();

    if ($lesson_url){
    ?>
    <a href="<?php echo $lesson_url; ?>"><?php _e('Start Course', 'lms'); ?></a>

    <?php } ?>
</div>