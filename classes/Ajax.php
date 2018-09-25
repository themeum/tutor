<?php
namespace LMS;

if ( ! defined( 'ABSPATH' ) )
	exit;

class Ajax{
	public function __construct() {

		add_action('wp_ajax_sync_video_playback', array($this, 'sync_video_playback'));
	}


	public function sync_video_playback(){
		lms_utils()->checking_nonce();

		$currentTime = sanitize_text_field($_POST['currentTime']);
		$post_id = sanitize_text_field($_POST['post_id']);

		$user_id = get_current_user_id();

		$best_watch_time = lms_utils()->get_lesson_reading_info($post_id, $user_id, 'video_best_watched_time');
		if ($best_watch_time < $currentTime){
			lms_utils()->update_lesson_reading_info($post_id, $user_id, 'video_best_watched_time', $currentTime);
		}

		exit();
	}

}