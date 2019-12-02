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

<div class="tutor-price-preview-box">
    <div class="tutor-price-box-thumbnail">
        <?php
        if(tutor_utils()->has_video_in_single()){
            tutor_course_video();
        } else{
            get_tutor_course_thumbnail();
        }
        ?>
    </div>

	<?php tutor_course_price(); ?>
    <?php tutor_course_material_includes_html(); ?>

    <div class="tutor-single-course-segment  tutor-course-enrolled-wrap">
        <h><?php _e('Enrolled', 'tutor'); ?></h>
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

        <?php do_action('tutor_enrolled_box_after') ?>

    </div>

</div> <!-- tutor-price-preview-box -->

