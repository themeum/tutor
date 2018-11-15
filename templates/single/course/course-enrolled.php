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

<div class="tutor-single-course-segment  tutor-course-enrolled-wrap">
    <h2><?php _e('Enrolled', 'tutor'); ?></h2>

    <p>
        <?php
        $enrolled = tutor_utils()->is_enrolled();
        _e(sprintf("Enrolled at : %s", date(get_option('date_format'), strtotime($enrolled->post_date)) ), 'tutor');
        ?>
    </p>

	<?php
	$lesson_url = tutor_utils()->get_course_first_lesson();
	if ($lesson_url){
		?>
        <a href="<?php echo $lesson_url; ?>" class="tutor-button"><?php _e('Start Course', 'tutor'); ?></a>
	<?php } ?>

</div>