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

		//Modal Perform
		add_action('wp_ajax_tutor_load_teachers_modal', array($this, 'tutor_load_teachers_modal'));
		add_action('wp_ajax_tutor_add_teachers_to_course', array($this, 'tutor_add_teachers_to_course'));
		add_action('wp_ajax_detach_teacher_from_course', array($this, 'detach_teacher_from_course'));
	}

	/**
	 * Registering metabox
	 */
	public function register_meta_box(){
		$coursePostType = tutor()->course_post_type;
		$allow_private_files = tutor_utils()->get_option('course_allow_upload_private_files');

		add_meta_box( 'tutor-course-additional-data', __( 'Additional Data', 'tutor' ), array($this, 'course_additional_data_meta_box'), $coursePostType );
		add_meta_box( 'tutor-course-topics', __( 'Topics', 'tutor' ), array($this, 'course_meta_box'), $coursePostType );

		/**
		 * Check is allow private file upload
		 */
		if ($allow_private_files){
			add_meta_box( 'tutor-course-attachments', __( 'Attachments, private files', 'tutor' ), array($this, 'course_attachments_metabox'), $coursePostType );
		}

		add_meta_box( 'tutor-course-videos', __( 'Video', 'tutor' ), array($this, 'video_metabox'), $coursePostType );
		add_meta_box( 'tutor-teachers', __( 'Teachers', 'tutor' ), array($this, 'teachers_metabox'), $coursePostType );
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

	public function teachers_metabox(){
		include  tutor()->path.'views/metabox/teachers-metabox.php';
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

		//Course Duration
		if ( ! empty($_POST['course_duration'])){
			$video = tutor_utils()->sanitize_array($_POST['course_duration']);
			update_post_meta($post_ID, '_course_duration', $video);
		}

		if ( ! empty($_POST['course_level'])){
			$course_level = sanitize_text_field($_POST['course_level']);
			update_post_meta($post_ID, '_tutor_course_level', $course_level);
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

		if ( ! empty($_POST['course_material_includes'])){
			$material_includes = wp_kses_post($_POST['course_material_includes']);
			update_post_meta($post_ID, '_tutor_course_material_includes', $material_includes);
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

	
	public function tutor_load_teachers_modal(){
		global $wpdb;

		$course_id = (int) sanitize_text_field($_POST['course_id']);
		$search_terms = sanitize_text_field(tutor_utils()->avalue_dot('search_terms', $_POST));

		$saved_teachers = tutor_utils()->get_teachers_by_course($course_id);

		$teachers = array();


		$not_in_sql = '';
		if ($saved_teachers){
			$saved_teachers_ids = wp_list_pluck($saved_teachers, 'ID');
			$teacher_not_in_ids = implode(',', $saved_teachers_ids);
			$activated = apply_filters('tutor_teacher_query_when_exists', " AND ID <1 ");
			$not_in_sql = $activated."AND ID NOT IN($teacher_not_in_ids) ";
		}

		$search_sql = '';
		if ($search_terms){
			$search_sql = "AND user_login like '%{$search_terms}%' or user_nicename like '%{$search_terms}%' or display_name like '%{$search_terms}%' ";
		}

		$teachers = $wpdb->get_results("select ID, display_name from {$wpdb->users} 
			INNER JOIN {$wpdb->usermeta} ON ID = user_id AND meta_key = '_tutor_teacher_status' AND meta_value = 'approved'
			WHERE ID > 0 {$not_in_sql} {$search_sql} limit 10 ");

		$output = '';
		if (is_array($teachers) && count($teachers)){
			$teacher_output = '';
			foreach ($teachers as $teacher){
				$teacher_output .= "<p><label><input type='radio' name='tutor_teacher_ids[]' value='{$teacher->ID}' > {$teacher->display_name} </label></p>";
			}

			$output .= apply_filters('tutor_course_teachers_html', $teacher_output, $teachers);
			$output .= '<p class="quiz-search-suggest-text">Search to get the specific teachers</p>';

		}else{
			$output .= __('No teacher available or you have already added maximum teachers', 'tutor');
		}

		wp_send_json_success(array('output' => $output));
	}

	public function tutor_add_teachers_to_course(){
		$course_id = (int) sanitize_text_field($_POST['course_id']);
		$teacher_ids = tutor_utils()->avalue_dot('tutor_teacher_ids', $_POST);

		if (is_array($teacher_ids) && count($teacher_ids)){
			foreach ($teacher_ids as $teacher_id){
				add_user_meta($teacher_id, '_tutor_teacher_course_id', $course_id);
			}
		}
		
		$saved_teachers = tutor_utils()->get_teachers_by_course($course_id);
		$output = '';

		if ($saved_teachers){
			foreach ($saved_teachers as $t){

				$output .= '<div id="added-teacher-id-'.$t->ID.'" class="added-teacher-item added-teacher-item-'.$t->ID.'" data-teacher-id="'.$t->ID.'">
                                <span class="teacher-icon"><i class="dashicons dashicons-admin-users"></i></span>
                                <span class="teacher-name"> '.$t->display_name.' </span>
                                <span class="teacher-control">
                                    <a href="javascript:;" class="tutor-teacher-delete-btn"><i class="dashicons dashicons-trash"></i></a>
                                </span>
                            </div>';
			}
		}
		

		wp_send_json_success(array('output' => $output));
	}

	public function detach_teacher_from_course(){
		global $wpdb;

		$teacher_id = (int) sanitize_text_field($_POST['teacher_id']);
		$course_id = (int) sanitize_text_field($_POST['course_id']);

		$wpdb->delete($wpdb->usermeta, array('user_id' => $teacher_id, 'meta_key' => '_tutor_teacher_course_id', 'meta_value' => $course_id) );
		wp_send_json_success();
	}


}