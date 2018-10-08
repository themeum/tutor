<?php
namespace TUTOR;

if ( ! defined( 'ABSPATH' ) )
	exit;

class Ajax{
	public function __construct() {

		add_action('wp_ajax_sync_video_playback', array($this, 'sync_video_playback'));
		add_action('wp_ajax_tutor_place_rating', array($this, 'tutor_place_rating'));

	}

	/**
	 * Update video information and data when necessary
	 *
	 * @since v.1.0.0
	 */
	public function sync_video_playback(){
		tutor_utils()->checking_nonce();

		$duration = sanitize_text_field($_POST['duration']);
		$currentTime = sanitize_text_field($_POST['currentTime']);
		$post_id = sanitize_text_field($_POST['post_id']);

		/**
		 * Update posts attached video
		 */
		$video = tutor_utils()->get_video($post_id);

		if ($duration) {
			$video['duration_sec'] = $duration; //secs
			$video['playtime']     = tutor_utils()->playtime_string( $duration );
			$video['runtime']      = tutor_utils()->playtime_array( $duration );
		}
		tutor_utils()->update_video($post_id, $video);

		/**
		 * Sync Lesson Reading Info by Users
		 */

		$user_id = get_current_user_id();

		$best_watch_time = tutor_utils()->get_lesson_reading_info($post_id, $user_id, 'video_best_watched_time');
		if ($best_watch_time < $currentTime){
			tutor_utils()->update_lesson_reading_info($post_id, $user_id, 'video_best_watched_time', $currentTime);
		}

		if (tutor_utils()->avalue_dot('is_ended', $_POST)){
			tutor_utils()->mark_lesson_complete($post_id);
		}
		exit();
	}


	public function tutor_place_rating(){
		global $wpdb;

		$rating = sanitize_text_field(tutor_utils()->avalue_dot('rating', $_POST));
		$course_id = sanitize_text_field(tutor_utils()->avalue_dot('course_id', $_POST));

		$review = wp_kses_post(tutor_utils()->avalue_dot('review', $_POST));


		$user_id = get_current_user_id();
		$user = get_userdata($user_id);
		$date = date("Y-m-d H:i:s");

		$previous_rating_id = $wpdb->get_var("select comment_ID from {$wpdb->comments} WHERE comment_post_ID={$course_id} AND user_id = {$user_id} AND comment_type = 'tutor_course_rating' LIMIT 1;");

		if ( $previous_rating_id){
			if ($review){
				$wpdb->update( $wpdb->comments, array('comment_content' => $review),
					array('comment_ID' => $previous_rating_id)
				);
			}

			if ($rating){
				$wpdb->update( $wpdb->commentmeta, array('meta_value' => $rating),
					array('comment_id' => $previous_rating_id, 'meta_key' => 'tutor_rating')
				);
			}
		}else{
			$data = array(
				'comment_post_ID'   => $course_id,
				'comment_approved'  => 'approved',
				'comment_type'      => 'tutor_course_rating',
				'comment_date'      => $date,
				'comment_date_gmt'  => get_gmt_from_date($date),
				'user_id'           => $user_id,
				'comment_author'    => $user->user_login,
				'comment_agent'     => 'TutorLMSPlugin',
			);
			if ($review){
				$data['comment_content'] = $review;
			}

			$wpdb->insert($wpdb->comments, $data);
			$comment_id = (int) $wpdb->insert_id;

			if ($comment_id && $rating){
				$result = $wpdb->insert( $wpdb->commentmeta, array(
					'comment_id' => $comment_id,
					'meta_key' => 'tutor_rating',
					'meta_value' => $rating
				) );
			}
		}

		wp_send_json_success(__('Rating placed success', 'tutor'));
	}



}