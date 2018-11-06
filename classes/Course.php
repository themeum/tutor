<?php
namespace TUTOR;

if ( ! defined( 'ABSPATH' ) )
	exit;

class Course extends Tutor_Base {
	public function __construct() {
		parent::__construct();

		add_action( 'add_meta_boxes', array($this, 'register_meta_box') );
		add_action('save_post_'.$this->course_post_type, array($this, 'save_course_meta'));
		add_action('wp_ajax_tutor_update_topic', array($this, 'tutor_update_topic'));

		//Add Column
		add_filter( "manage_{$this->course_post_type}_posts_columns", array($this, 'add_column'), 10,1 );
		add_action( "manage_{$this->course_post_type}_posts_custom_column" , array($this, 'custom_lesson_column'), 10, 2 );

		add_action('admin_action_tutor_delete_topic', array($this, 'tutor_delete_topic'));
		add_action('admin_action_tutor_delete_announcement', array($this, 'tutor_delete_announcement'));

		//Frontend Action
		add_action('template_redirect', array($this, 'enroll_now'));
		add_action('template_redirect', array($this, 'mark_course_complete'));
	}

	/**
	 * Registering metabox
	 */
	public function register_meta_box(){
		$coursePostType = tutor()->course_post_type;
		
		add_meta_box( 'tutor-course-additional-data', __( 'Additional Data', 'tutor' ), array($this, 'course_additional_data_meta_box'), $coursePostType );
		add_meta_box( 'tutor-course-topics', __( 'Topics', 'tutor' ), array($this, 'course_meta_box'), $coursePostType );
		add_meta_box( 'tutor-course-attachments', __( 'Attachments', 'tutor' ), array($this, 'course_attachments_metabox'), $coursePostType );
		add_meta_box( 'tutor-course-videos', __( 'Video', 'tutor' ), array($this, 'video_metabox'), $coursePostType );


		add_meta_box( 'tutor-announcements', __( 'Announcements', 'tutor' ), array($this, 'announcements_metabox'), $coursePostType );
	}

	public function course_meta_box(){
		include  tutor()->path.'views/metabox/course-topics.php';
	}

	public function course_additional_data_meta_box(){
		include  tutor()->path.'views/metabox/course-additional-data.php';
	}

	public function course_attachments_metabox(){
		include  tutor()->path.'views/metabox/course-attachments-metabox.php';
	}

	public function video_metabox(){
		include  tutor()->path.'views/metabox/video-metabox.php';
	}

	public function announcements_metabox(){
		include  tutor()->path.'views/metabox/announcements-metabox.php';
	}

	/**
	 * @param $post_ID
	 *
	 * Insert Topic and attached it with Course
	 */
	public function save_course_meta($post_ID){
		global $wpdb;
		/**
		 * Insert Topic
		 */
		if ( ! empty($_POST['topic_title'])) {
			$topic_title   = sanitize_text_field( $_POST['topic_title'] );
			$topic_summery = wp_kses_post( $_POST['topic_summery'] );

			$post_arr = array(
				'post_type'    => 'topics',
				'post_title'   => $topic_title,
				'post_content' => $topic_summery,
				'post_status'  => 'publish',
				'post_author'  => get_current_user_id(),
				'post_parent'  => $post_ID,
			);
			wp_insert_post( $post_arr );
		}


		if ( ! empty($_POST['course_benefits'])){
			$course_benefits = wp_kses_post($_POST['course_benefits']);
			update_post_meta($post_ID, '_tutor_course_benefits', $course_benefits);
		}

		if ( ! empty($_POST['course_requirements'])){
			$requirements = wp_kses_post($_POST['course_requirements']);
			update_post_meta($post_ID, '_tutor_course_requirements', $requirements);
		}

		if ( ! empty($_POST['course_target_audience'])){
			$target_audience = wp_kses_post($_POST['course_target_audience']);
			update_post_meta($post_ID, '_tutor_course_target_audience', $target_audience);
		}

		/**
		 * Sorting Topics and lesson
		 */
		if ( ! empty($_POST['tutor_topics_lessons_sorting'])){
			$new_order = sanitize_text_field(stripslashes($_POST['tutor_topics_lessons_sorting']));
			$order = json_decode($new_order, true);

			if (is_array($order) && count($order)){
				$i = 0;
				foreach ($order as $topic ){
					$i++;
					$wpdb->update(
						$wpdb->posts,
						array('menu_order' => $i),
						array('ID' => $topic['topic_id'])
					);

					/**
					 * Removing All lesson with topic
					 */

					$wpdb->update(
						$wpdb->posts,
						array('post_parent' => 0),
						array('post_parent' => $topic['topic_id'])
					);

					/**
					 * Lesson Attaching with topic ID
					 * sorting lesson
					 */
					if (isset($topic['lesson_ids'])){
						$lesson_ids = $topic['lesson_ids'];
					}else{
						$lesson_ids = array();
					}
					if (count($lesson_ids)){
						foreach ($lesson_ids as $lesson_key => $lesson_id ){
							$wpdb->update(
								$wpdb->posts,
								array('post_parent' => $topic['topic_id'], 'menu_order' => $lesson_key),
								array('ID' => $lesson_id)
							);
						}
					}


				}

			}

		}

		//Attachments
		$attachments = array();
		if ( ! empty($_POST['tutor_attachments'])){
			$attachments = tutor_utils()->sanitize_array($_POST['tutor_attachments']);
			$attachments = array_unique($attachments);
		}
		update_post_meta($post_ID, '_tutor_attachments', $attachments);

		//Video
		if ( ! empty($_POST['video']['source'])){
			$video = tutor_utils()->sanitize_array($_POST['video']);
			update_post_meta($post_ID, '_video', $video);
		}



		//Announcements
		$announcement_title = tutor_utils()->avalue_dot('announcements.title', $_POST );
		if ( ! empty($announcement_title)){
			$title = sanitize_text_field(tutor_utils()->avalue_dot('announcements.title', $_POST ));
			$content = wp_kses_post(tutor_utils()->avalue_dot('announcements.content', $_POST ));

			$post_arr = array(
				'post_type'    => 'tutor_announcements',
				'post_title'   => $title,
				'post_content' => $content,
				'post_status'  => 'publish',
				'post_author'  => get_current_user_id(),
				'post_parent'  => $post_ID,
			);
			wp_insert_post( $post_arr );
		}

	}


	/**
	 * Update the topic
	 */
	public function tutor_update_topic(){
		$topic_id = (int) sanitize_text_field($_POST['topic_id']);
		$topic_title = sanitize_text_field($_POST['topic_title']);
		$topic_summery = wp_kses_post($_POST['topic_summery']);

		$topic_attr = array(
			'ID'           => $topic_id,
			'post_title'   => $topic_title,
			'post_content' => $topic_summery,
		);
		wp_update_post( $topic_attr );

		wp_send_json_success(array('msg' => __('Topic has been updated', 'tutor') ));
	}


	/**
	 * @param $columns
	 *
	 * @return mixed
	 *
	 * Add Lesson column
	 */

	public function add_column($columns){
		$date_col = $columns['date'];
		unset($columns['date']);
		$columns['lessons'] = __('Lessons', 'tutor');
		$columns['students'] = __('Students', 'tutor');
		$columns['price'] = __('Price', 'tutor');
		$columns['date'] = $date_col;

		return $columns;
	}

	/**
	 * @param $column
	 * @param $post_id
	 *
	 */
	public function custom_lesson_column($column, $post_id ){
		if ($column === 'lessons'){
			echo tutor_utils()->get_lesson_count_by_course($post_id);
		}

		if ($column === 'students'){
			echo tutor_utils()->count_enrolled_users_by_course($post_id);
		}

		if ($column === 'price'){
			$price = tutor_utils()->get_course_price($post_id);

			if ($price && function_exists('wc_price')){
				echo '<span class="tutor-label-success">'.wc_price($price).'</span>';
			}else{
				echo 'free';
			}
		}
	}


	public function tutor_delete_topic(){
		if (!isset($_GET[tutor()->nonce]) || !wp_verify_nonce($_GET[tutor()->nonce], tutor()->nonce_action)) {
			exit();
		}
		if ( ! isset($_GET['topic_id'])){
			exit();
		}

		global $wpdb;

		$topic_id = (int) sanitize_text_field($_GET['topic_id']);
		$wpdb->update(
			$wpdb->posts,
			array('post_parent' => 0),
			array('post_parent' => $topic_id)
		);

		$wpdb->delete(
			$wpdb->postmeta,
			array('post_id' => $topic_id)
		);

		wp_delete_post($topic_id);
		wp_safe_redirect(wp_get_referer());
	}

	public function tutor_delete_announcement(){
		tutor_utils()->checking_nonce('get');

		$announcement_id = (int) sanitize_text_field($_GET['topic_id']);

		wp_delete_post($announcement_id);
		wp_safe_redirect(wp_get_referer());
	}

	public function enroll_now(){
		//Checking if action comes from Enroll form
		if ( ! isset($_POST['tutor_course_action']) || $_POST['tutor_course_action'] !== '_tutor_course_enroll_now' || ! isset($_POST['tutor_course_id']) ){
			return;
		}
		//Checking Nonce
		tutor_utils()->checking_nonce();

		$user_id = get_current_user_id();
		if ( ! $user_id){
			exit(__('Please Sign In first', 'tutor'));
		}

		$course_id = (int) sanitize_text_field($_POST['tutor_course_id']);
		$user_id = get_current_user_id();

		/**
		 * TODO: need to check purchase information
		 */


		$is_purchasable = tutor_utils()->is_course_purchasable($course_id);

		/**
		 * If is is not purchasable, it's free, and enroll right now
		 *
		 * if purchasable, then process purchase.
		 *
		 * @since: v.1.0.0
		 */
		if ($is_purchasable){
			//process purchase

		}else{
			//Free enroll
			tutor_utils()->do_enroll($course_id);
		}

		$referer_url = wp_get_referer();
		wp_redirect($referer_url);
	}

	/**
	 *
	 * Mark complete completed
	 *
	 * @since v.1.0.0
	 */
	public function mark_course_complete(){
		if ( ! isset($_POST['tutor_action'])  ||  $_POST['tutor_action'] !== 'tutor_complete_course' ){
			return;
		}
		//Checking nonce
		tutor_utils()->checking_nonce();

		$user_id = get_current_user_id();

		//TODO: need to show view if not signed_in
		if ( ! $user_id){
			die(__('Please Sign-In', 'tutor'));
		}

		$course_id = (int) sanitize_text_field($_POST['course_id']);

		do_action('tutor_course_complete_before', $course_id);
		/**
		 * Marking course at user meta, meta format, _tutor_completed_course_id_{id} and value = time();
		 */
		update_user_meta($user_id, '_tutor_completed_course_id_'.$course_id, time());

		do_action('tutor_course_complete_after', $course_id);

		wp_redirect(get_the_permalink($course_id));
	}

}