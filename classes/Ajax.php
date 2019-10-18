<?php
namespace TUTOR;

if ( ! defined( 'ABSPATH' ) )
	exit;

class Ajax{
	public function __construct() {
		add_action('wp_ajax_sync_video_playback', array($this, 'sync_video_playback'));
		add_action('wp_ajax_nopriv_sync_video_playback', array($this, 'sync_video_playback_noprev'));
		add_action('wp_ajax_tutor_place_rating', array($this, 'tutor_place_rating'));

		add_action('wp_ajax_tutor_ask_question', array($this, 'tutor_ask_question'));
		add_action('wp_ajax_tutor_add_answer', array($this, 'tutor_add_answer'));

		add_action('wp_ajax_tutor_course_add_to_wishlist', array($this, 'tutor_course_add_to_wishlist'));
		add_action('wp_ajax_nopriv_tutor_course_add_to_wishlist', array($this, 'tutor_course_add_to_wishlist'));

		/**
		 * Addon Enable Disable Control
		 */
		add_action('wp_ajax_addon_enable_disable', array($this, 'addon_enable_disable'));

		/**
		 * Update Rating/review
		 * @since  v.1.4.0
		 */
		add_action('wp_ajax_tutor_load_edit_review_modal', array($this, 'tutor_load_edit_review_modal'));
		add_action('wp_ajax_tutor_update_review_modal', array($this, 'tutor_update_review_modal'));
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

	public function sync_video_playback_noprev(){

	}


	public function tutor_place_rating(){
		global $wpdb;

		//TODO: Check nonce

		$rating = sanitize_text_field(tutor_utils()->avalue_dot('rating', $_POST));
		$course_id = sanitize_text_field(tutor_utils()->avalue_dot('course_id', $_POST));
		$review = wp_kses_post(tutor_utils()->avalue_dot('review', $_POST));

		$user_id = get_current_user_id();
		$user = get_userdata($user_id);
		$date = date("Y-m-d H:i:s", tutor_time());

		do_action('tutor_before_rating_placed');

		$previous_rating_id = $wpdb->get_var("select comment_ID from {$wpdb->comments} WHERE comment_post_ID={$course_id} AND user_id = {$user_id} AND comment_type = 'tutor_course_rating' LIMIT 1;");

		$review_ID = $previous_rating_id;
		if ( $previous_rating_id){
			$wpdb->update( $wpdb->comments, array('comment_content' => $review),
				array('comment_ID' => $previous_rating_id)
			);

			$rating_info = $wpdb->get_row("SELECT * FROM {$wpdb->commentmeta} WHERE comment_id = {$previous_rating_id} AND meta_key = 'tutor_rating'; ");
			if ($rating_info){
				$wpdb->update( $wpdb->commentmeta, array('meta_value' => $rating), array('comment_id' => $previous_rating_id, 'meta_key' => 'tutor_rating') );
			}else{
				$wpdb->insert( $wpdb->commentmeta, array('comment_id' => $previous_rating_id, 'meta_key' => 'tutor_rating', 'meta_value' => $rating) );
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
			$review_ID = $comment_id;

			if ($comment_id && $rating){
				$result = $wpdb->insert( $wpdb->commentmeta, array(
					'comment_id' => $comment_id,
					'meta_key' => 'tutor_rating',
					'meta_value' => $rating
				) );

				do_action('tutor_after_rating_placed', $comment_id);
			}
		}

		$data = array('msg' => __('Rating placed success', 'tutor'), 'review_id' => $review_ID, 'review' => $review);
		wp_send_json_success($data);
	}

	public function tutor_ask_question(){
		tutor_utils()->checking_nonce();

		global $wpdb;

		$course_id = (int) sanitize_text_field($_POST['tutor_course_id']);
		$question_title = sanitize_text_field($_POST['question_title']);
		$question = wp_kses_post($_POST['question']);

		if (empty($question) || empty($question_title)){
			wp_send_json_error(__('Empty question title or body', 'tutor'));
		}

		$user_id = get_current_user_id();
		$user = get_userdata($user_id);
		$date = date("Y-m-d H:i:s", tutor_time());

		do_action('tutor_before_add_question', $course_id);
		$data = apply_filters('tutor_add_question_data', array(
			'comment_post_ID'   => $course_id,
			'comment_author'    => $user->user_login,
			'comment_date'      => $date,
			'comment_date_gmt'  => get_gmt_from_date($date),
			'comment_content'   => $question,
			'comment_approved'  => 'waiting_for_answer',
			'comment_agent'     => 'TutorLMSPlugin',
			'comment_type'      => 'tutor_q_and_a',
			'user_id'           => $user_id,
		));

		$wpdb->insert($wpdb->comments, $data);
		$comment_id = (int) $wpdb->insert_id;

		if ($comment_id){
			$result = $wpdb->insert( $wpdb->commentmeta, array(
				'comment_id' => $comment_id,
				'meta_key' => 'tutor_question_title',
				'meta_value' => $question_title
			) );
		}
		do_action('tutor_after_add_question', $course_id, $comment_id);

		wp_send_json_success(__('Question has been added successfully', 'tutor'));
	}


	public function tutor_add_answer(){
		tutor_utils()->checking_nonce();
		global $wpdb;

		$answer = wp_kses_post($_POST['answer']);
		if ( ! $answer){
			wp_send_json_error(__('Please write answer', 'tutor'));
		}

		$question_id = (int) sanitize_text_field($_POST['question_id']);
		$question = tutor_utils()->get_qa_question($question_id);

		$user_id = get_current_user_id();
		$user = get_userdata($user_id);
		$date = date("Y-m-d H:i:s", tutor_time());

		do_action('tutor_before_answer_to_question');
		$data = apply_filters('tutor_add_answer_data', array(
			'comment_post_ID'   => $question->comment_post_ID,
			'comment_author'    => $user->user_login,
			'comment_date'      => $date,
			'comment_date_gmt'  => get_gmt_from_date($date),
			'comment_content'   => $answer,
			'comment_approved'  => 'approved',
			'comment_agent'     => 'TutorLMSPlugin',
			'comment_type'      => 'tutor_q_and_a',
			'comment_parent'    => $question_id,
			'user_id'           => $user_id,
		));

		$wpdb->insert($wpdb->comments, $data);
		$comment_id = (int) $wpdb->insert_id;
		do_action('tutor_after_answer_to_question', $comment_id);

		wp_send_json_success(__('Answer has been added successfully', 'tutor'));
	}


	public function tutor_course_add_to_wishlist(){
		$course_id = (int) sanitize_text_field($_POST['course_id']);
		if ( ! is_user_logged_in()){
			wp_send_json_error(array('redirect_to' => wp_login_url( wp_get_referer() ) ) );
		}
		global $wpdb;

		$user_id = get_current_user_id();
		$if_added_to_list = $wpdb->get_row("select * from {$wpdb->usermeta} WHERE user_id = {$user_id} AND meta_key = '_tutor_course_wishlist' AND meta_value = {$course_id} ;");

		if ( $if_added_to_list){
			$wpdb->delete($wpdb->usermeta, array('user_id' => $user_id, 'meta_key' => '_tutor_course_wishlist', 'meta_value' => $course_id ));
			wp_send_json_success(array('status' => 'removed', 'msg' => __('Course removed from wish list', 'tutor')));
		}else{
			add_user_meta($user_id, '_tutor_course_wishlist', $course_id);
			wp_send_json_success(array('status' => 'added', 'msg' => __('Course added to wish list', 'tutor')));
		}
	}

	/**
	 * Method for enable / disable addons
	 */
	public function addon_enable_disable(){
		$addonsConfig = maybe_unserialize(get_option('tutor_addons_config'));

		$isEnable = (bool) sanitize_text_field(tutor_utils()->avalue_dot('isEnable', $_POST));
		$addonFieldName = sanitize_text_field(tutor_utils()->avalue_dot('addonFieldName', $_POST));

		do_action('tutor_addon_before_enable_disable');
		if ($isEnable){
			do_action("tutor_addon_before_enable_{$addonFieldName}");
			do_action('tutor_addon_before_enable', $addonFieldName);
			$addonsConfig[$addonFieldName]['is_enable'] = 1;
			update_option('tutor_addons_config', $addonsConfig);

			do_action('tutor_addon_after_enable', $addonFieldName);
			do_action("tutor_addon_after_enable_{$addonFieldName}");
		}else{
			do_action("tutor_addon_before_disable_{$addonFieldName}");
			do_action('tutor_addon_before_disable', $addonFieldName);
			$addonsConfig[$addonFieldName]['is_enable'] = 0;
			update_option('tutor_addons_config', $addonsConfig);

			do_action('tutor_addon_after_disable', $addonFieldName);
			do_action("tutor_addon_after_disable_{$addonFieldName}");
		}

		do_action('tutor_addon_after_enable_disable');
		wp_send_json_success();
	}


	/**
	 * Load review edit form
	 * @since v.1.4.0
	 */
	public function tutor_load_edit_review_modal(){
		tutor_utils()->checking_nonce();

		$review_id = (int) sanitize_text_field(tutils()->array_get('review_id', $_POST));
		$rating = tutils()->get_rating_by_id($review_id);

		ob_start();
		tutor_load_template('dashboard.reviews.edit-review-form', array('rating' => $rating));
		$output = ob_get_clean();

		wp_send_json_success(array('output' => $output));
	}

	public function tutor_update_review_modal(){
		global $wpdb;

		tutor_utils()->checking_nonce();

		$review_id = (int) sanitize_text_field(tutils()->array_get('review_id', $_POST));
		$rating = sanitize_text_field(tutor_utils()->avalue_dot('rating', $_POST));
		$review = wp_kses_post(tutor_utils()->avalue_dot('review', $_POST));

		$is_exists = $wpdb->get_var("select comment_ID from {$wpdb->comments} WHERE comment_ID={$review_id} AND comment_type = 'tutor_course_rating' ;");

		if ( $is_exists) {
			$wpdb->update( $wpdb->comments, array( 'comment_content' => $review ),
				array( 'comment_ID' => $review_id )
			);
			$wpdb->update( $wpdb->commentmeta, array( 'meta_value' => $rating ),
				array( 'comment_id' => $review_id, 'meta_key' => 'tutor_rating' )
			);

			wp_send_json_success();
		}
		wp_send_json_error();
	}

}