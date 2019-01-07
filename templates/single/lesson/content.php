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
<input type="hidden" id="tutor_video_tracking_information" value="<?php echo esc_attr(json_encode($jsonData)); ?>">
<?php tutor_lesson_video(); ?>
<?php the_content(); ?>
<?php get_tutor_posts_attachments(); ?>
<?php tutor_lesson_mark_complete_html(); ?>

<?php
do_action('tutor_lesson/single/after/content'); ?>