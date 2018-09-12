<?php
namespace LMS;

if ( ! defined( 'ABSPATH' ) )
	exit;

class Course {
	public function __construct() {
		add_action( 'add_meta_boxes', array($this, 'register_meta_box') );
		add_action('save_post_course', array($this, 'save_course_meta'));
		add_action('wp_ajax_lms_update_topic', array($this, 'lms_update_topic'));

		//Add Column
		add_filter( 'manage_course_posts_columns', array($this, 'add_column'), 10,1 );
		add_action( 'manage_course_posts_custom_column' , array($this, 'custom_lesson_column'), 10, 2 );

		add_action('admin_action_lms_delete_topic', array($this, 'lms_delete_topic'));
	}

	/**
	 * Registering metabox
	 */
	public function register_meta_box(){
		add_meta_box( 'lms-course-benefits', __( 'What will i learn', 'lms' ), array($this, 'course_benefits_meta_box'), 'course' );
		add_meta_box( 'lms-course-topics', __( 'Topics', 'lms' ), array($this, 'course_meta_box'), 'course' );
	}

	public function course_meta_box(){
		include  lms()->path.'views/metabox/course-topics.php';
	}

	public function course_benefits_meta_box(){
		include  lms()->path.'views/metabox/course-benefits.php';
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
			global $wpdb;

			$count_lesson = $wpdb->get_var("select count(meta_id) from {$wpdb->postmeta} where meta_key = '_lms_course_id_for_lesson' AND meta_value = {$post_id} ");
			echo $count_lesson;
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


}


