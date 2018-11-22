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

<div class="dozent-price-preview-box">
    <div class="dozent-price-box-thumbnail">
        <?php
        if(dozent_utils()->has_video_in_single()){
            dozent_course_video();
        } else{
            get_dozent_course_thumbnail();
        }
        ?>
    </div>

	<?php dozent_course_price(); ?>
    <?php dozent_course_material_includes_html(); ?>

    <div class="dozent-single-course-segment  dozent-course-enrolled-wrap">
        <h><?php _e('Enrolled', 'dozent'); ?></h>
        <p>
            <?php
            $enrolled = dozent_utils()->is_enrolled();
            _e(sprintf("Enrolled at : %s", date(get_option('date_format'), strtotime($enrolled->post_date)) ), 'dozent');
            ?>
        </p>
        <?php
        $lesson_url = dozent_utils()->get_course_first_lesson();
        if ($lesson_url){
            ?>
            <a href="<?php echo $lesson_url; ?>" class="dozent-button"><?php _e('Start Course', 'dozent'); ?></a>
        <?php } ?>

    </div>

</div> <!-- dozent-price-preview-box -->

