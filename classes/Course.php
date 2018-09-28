<?php
namespace LMS;

if ( ! defined( 'ABSPATH' ) )
	exit;

class Course extends LMS_Base {
	public function __construct() {
		parent::__construct();

		add_action( 'add_meta_boxes', array($this, 'register_meta_box') );
		add_action('save_post_'.$this->course_post_type, array($this, 'save_course_meta'));
		add_action('wp_ajax_lms_update_topic', array($this, 'lms_update_topic'));

		//Add Column
		add_filter( "manage_{$this->course_post_type}_posts_columns", array($this, 'add_column'), 10,1 );
		add_action( "manage_{$this->course_post_type}_posts_custom_column" , array($this, 'custom_lesson_column'), 10, 2 );

		add_action('admin_action_lms_delete_topic', array($this, 'lms_delete_topic'));


		//Frontend Action
		add_action('template_redirect', array($this, 'enroll_now'));
		add_action('template_redirect', array($this, 'mark_course_complete'));
	}

	/**
	 * Registering metabox
	 */
	public function register_meta_box(){
		$coursePostType = lms()->course_post_type;
		
		add_meta_box( 'lms-course-additional-data', __( 'Additional Data', 'lms' ), array($this, 'course_additional_data_meta_box'), $coursePostType );
		add_meta_box( 'lms-course-topics', __( 'Topics', 'lms' ), array($this, 'course_meta_box'), $coursePostType );
		add_meta_box( 'lms-course-attachments', __( 'Attachments', 'lms' ), array($this, 'course_attachments_metabox'), $coursePostType );
		add_meta_box( 'lms-course-videos', __( 'Video', 'lms' ), array($this, 'video_metabox'), $coursePostType );
	}

	public function course_meta_box(){
		include  lms()->path.'views/metabox/course-topics.php';
	}

	public function course_additional_data_meta_box(){
		include  lms()->path.'views/metabox/course-additional-data.php';
	}

	public function course_attachments_metabox(){
		include  lms()->path.'views/metabox/course-attachments-metabox.php';
	}

	public function video_metabox(){
		include  lms()->path.'views/metabox/video-metabox.php';
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
			update_post_meta($post_ID, '_lms_course_benefits', $course_benefits);
		}

		if ( ! empty($_POST['course_requirements'])){
			$requirements = wp_kses_post($_POST['course_requirements']);
			update_post_meta($post_ID, '_lms_course_requirements', $requirements);
		}

		if ( ! empty($_POST['course_target_audience'])){
			$target_audience = wp_kses_post($_POST['course_target_audience']);
			update_post_meta($post_ID, '_lms_course_target_audience', $target_audience);
		}

		/**
		 * Sorting Topics and lesson
		 */
		if ( ! empty($_POST['lms_topics_lessons_sorting'])){
			$new_order = sanitize_text_field(stripslashes($_POST['lms_topics_lessons_sorting']));
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
		if ( ! empty($_POST['lms_attachments'])){
			$attachments = lms_utils()->sanitize_array($_POST['lms_attachments']);
			$attachments = array_unique($attachments);
		}
		update_post_meta($post_ID, '_lms_attachments', $attachments);

		//Video
		if ( ! empty($_POST['video']['source'])){
			$video = lms_utils()->sanitize_array($_POST['video']);
			update_post_meta($post_ID, '_video', $video);
		}

	}


	/**
	 * Update the topic
	 */
	public function lms_update_topic(){
		$topic_id = (int) sanitize_text_field($_POST['topic_id']);
		$topic_title = sanitize_text_field($_POST['topic_title']);
		$topic_summery = wp_kses_post($_POST['topic_summery']);

		$topic_attr = array(
			'ID'           => $topic_id,
			'post_title'   => $topic_title,
			'post_content' => $topic_summery,
		);
		wp_update_post( $topic_attr );

		wp_send_json_success(array('msg' => __('Topic has been updated', 'lms') ));
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
		$columns['lessons'] = __('Lessons', 'lms');
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
			echo lms_utils()->get_lesson_count_by_course($post_id);
		}
	}


	public function lms_delete_topic(){
		if (!isset($_GET[lms()->nonce]) || !wp_verify_nonce($_GET[lms()->nonce], lms()->nonce_action)) {
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

	public function enroll_now(){
		//Checking if action comes from Enroll form
		if ( ! isset($_POST['lms_course_action']) || $_POST['lms_course_action'] !== '_lms_course_enroll_now' || ! isset($_POST['lms_course_id']) ){
			return;
		}
		//Checking Nonce
		lms_utils()->checking_nonce();

		$user_id = get_current_user_id();
		if ( ! $user_id){
			exit(__('Please Sign In first', 'lms'));
		}

		$course_id = (int) sanitize_text_field($_POST['lms_course_id']);
		$user_id = get_current_user_id();

		/**
		 * TODO: need to check purchase information
		 */


		$is_purchasable = lms_utils()->is_course_purchasable($course_id);

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
			$this->do_enroll($course_id);
		}


		$referer_url = wp_get_referer();
		wp_redirect($referer_url);
	}



	/**
	 * Saving enroll information to posts table
	 * post_author = enrolled_student_id (wp_users id)
	 * post_parent = enrolled course id
	 *
	 * @type: call when need
	 * @return bool;
	 */
	public function do_enroll($course_id = 0){
		if ( ! $course_id){
			return false;
		}
		$user_id = get_current_user_id();

		$title = __('Course Enrolled', 'lms')." &ndash; ".date_i18n(get_option('date_format')) .' @ '.date_i18n(get_option('time_format') ) ;
		$enroll_data = array(
			'post_type'     => 'lms_enrolled',
			'post_title'    => $title,
			'post_status'   => 'enrolled',
			'post_author'   => $user_id,
			'post_parent'   => $course_id,
		);

		// Insert the post into the database
		$isEnrolled = wp_insert_post( $enroll_data );
		if ($isEnrolled) {
			//Mark Current User as Students with user meta data
			update_user_meta( $user_id, '_is_lms_student', time() );

			return true;
		}

		return false;
	}

	/**
	 *
	 * Mark complete completed
	 *
	 * @since v.1.0.0
	 */
	public function mark_course_complete(){
		if ( ! isset($_POST['lms_action'])  ||  $_POST['lms_action'] !== 'lms_complete_course' ){
			return;
		}
		//Checking nonce
		lms_utils()->checking_nonce();

		$user_id = get_current_user_id();

		//TODO: need to show view if not signed_in
		if ( ! $user_id){
			die(__('Please Sign-In', 'lms'));
		}

		$course_id = (int) sanitize_text_field($_POST['course_id']);

		/**
		 * Marking course at user meta, meta format, _lms_completed_course_id_{id} and value = time();
		 */

		update_user_meta($user_id, '_lms_completed_course_id_'.$course_id, time());


		wp_redirect(get_the_permalink($course_id));
	}

}