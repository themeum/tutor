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

    <p>
        <?php
        $enrolled = lms_utils()->is_enrolled();
        _e(sprintf("Enrolled at : %s", date(get_option('date_format'), strtotime($enrolled->post_date)) ), 'lms');
        ?>
    </p>

	<?php
	$lesson_url = lms_utils()->get_course_first_lesson();
	if ($lesson_url){
		?>
        <a href="<?php echo $lesson_url; ?>" class="lms-button"><?php _e('Start Course', 'lms'); ?></a>
	<?php } ?>

</div>