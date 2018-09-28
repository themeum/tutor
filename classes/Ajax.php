<?php
namespace LMS;

if ( ! defined( 'ABSPATH' ) )
	exit;

class Ajax{
	public function __construct() {

		add_action('wp_ajax_sync_video_playback', array($this, 'sync_video_playback'));
	}

	/**
	 * Update video information and data when necessary
	 *
	 * @since v.1.0.0
	 */
	public function sync_video_playback(){
		lms_utils()->checking_nonce();

		$duration = sanitize_text_field($_POST['duration']);
		$currentTime = sanitize_text_field($_POST['currentTime']);
		$post_id = sanitize_text_field($_POST['post_id']);

		/**
		 * Update posts attached video
		 */
		$video = lms_utils()->get_video($post_id);

		if ($duration) {
			$video['duration_sec'] = $duration; //secs
			$video['playtime']     = lms_utils()->playtime_string( $duration );
			$video['runtime']      = lms_utils()->playtime_array( $duration );
		}
		lms_utils()->update_video($post_id, $video);

		/**
		 * Sync Lesson Reading Info by Users
		 */

		$user_id = get_current_user_id();

		$best_watch_time = lms_utils()->get_lesson_reading_info($post_id, $user_id, 'video_best_watched_time');
		if ($best_watch_time < $currentTime){
			lms_utils()->update_lesson_reading_info($post_id, $user_id, 'video_best_watched_time', $currentTime);
		}

		if (lms_utils()->avalue_dot('is_ended', $_POST)){
			lms_utils()->mark_lesson_complete($post_id);
		}
		exit();
	}

}