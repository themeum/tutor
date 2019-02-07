<?php
/**
 * Display the content
 *
 * @since v.1.0.0
 * @author themeum
 * @url https://themeum.com
 */

if ( ! defined( 'ABSPATH' ) )
	exit;

do_action('tutor_lesson/single/before/content');

$jsonData = array();
$jsonData['post_id'] = get_the_ID();
$jsonData['best_watch_time'] = 0;

$best_watch_time = tutor_utils()->get_lesson_reading_info(get_the_ID(), 0, 'video_best_watched_time');
if ($best_watch_time > 0){
	$jsonData['best_watch_time'] = $best_watch_time;
}
?>




<div class="tutor-single-page-top-bar">
    <div class="tutor-topbar-item tutor-hide-sidebar-bar">
        <a href="javascript:;" class="tutor-lesson-sidebar-hide-bar">=</a>
    </div>

    <div class="tutor-topbar-item tutor-topbar-content-title-wrap">
        <?php
        tutor_utils()->get_lesson_type_icon(get_the_ID(), true, true);

        the_title(); ?>
    </div>

    <div class="tutor-topbar-item tutor-topbar-back-to-curse-wrap">
        <?php
        $course_id = get_post_meta(get_the_ID(), '_tutor_course_id_for_lesson', true);
        ?>
        <a href="<?php echo get_the_permalink($course_id); ?>">
            <i class="tutor-icon-next-2"></i> <?php echo sprintf(__('Go to %s Course Home %s', 'tutor'), '<strong>', '</strong>') ; ?>
        </a>
    </div>

</div>


<div class="tutor-lesson-content-area">

    <input type="hidden" id="tutor_video_tracking_information" value="<?php echo esc_attr(json_encode($jsonData)); ?>">
	<?php tutor_lesson_video(); ?>
	<?php the_content(); ?>
	<?php get_tutor_posts_attachments(); ?>
	<?php tutor_lesson_mark_complete_html(); ?>

</div>

<?php
do_action('tutor_lesson/single/after/content'); ?>