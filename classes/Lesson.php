<?php
namespace LMS;

if ( ! defined( 'ABSPATH' ) )
	exit;

class Lesson {
	public function __construct() {
		add_action( 'add_meta_boxes', array($this, 'register_meta_box') );
		add_action('save_post_lesson', array($this, 'save_lesson_meta'));

		add_filter('get_sample_permalink', array($this, 'change_lesson_permalink'), 10, 2);

		add_action('admin_init', array($this, 'flush_rewrite_rules'));

		/**
		 * Add Column
		 */

		add_filter( 'manage_lesson_posts_columns', array($this, 'add_column'), 10,1 );
		add_action( 'manage_lesson_posts_custom_column' , array($this, 'custom_lesson_column'), 10, 2 );

	}

	/**
	 * Registering metabox
	 */
	public function register_meta_box(){
		add_meta_box( 'lms-course-select', __( 'Select Course', 'lms' ), array($this, 'lesson_metabox'), 'lesson' );
	}

	public function lesson_metabox(){
		include  lms()->path.'views/metabox/lesson-metabox.php';
	}

	public function save_lesson_meta($post_ID){
		if ( ! isset($_POST['selected_course'])){
			return;
		}
		$course_id = (int) sanitize_text_field($_POST['selected_course']);
		if ($course_id){
			update_post_meta($post_ID, '_lms_course_id_for_lesson', $course_id);
		}
	}

	/**
	 * @param $uri
	 * @param $lesson_id
	 *
	 * @return mixed
	 *
	 * Changed the URI based
	 */

	public function change_lesson_permalink($uri, $lesson_id){
		$post = get_post($lesson_id);

		if ($post && $post->post_type === 'lesson'){
			$uri_base = trailingslashit(site_url());

			$sample_course = "sample-course";
			$is_course = get_post_meta(get_the_ID(), '_lms_course_id_for_lesson', true);
			if ($is_course){
				$course = get_post($is_course);
				$sample_course = $course->post_name;
			}

			$new_course_base = $uri_base."course/{$sample_course}/lesson/%pagename%/";
			$uri[0] = $new_course_base;
		}

		return $uri;
	}


	public function flush_rewrite_rules(){
		$is_required_flush = get_option('required_rewrite_flush');
		if ($is_required_flush){
			flush_rewrite_rules();
		}
	}


	public function add_column($columns){
		$date_col = $columns['date'];
		unset($columns['date']);
		$columns['course'] = __('Course', 'lms');
		$columns['date'] = $date_col;

		return $columns;
	}

	/**
	 * @param $column
	 * @param $post_id
	 *
	 */
	public function custom_lesson_column($column, $post_id ){
		if ($column === 'course'){

			$course_id = get_post_meta($post_id, '_lms_course_id_for_lesson', true);
			if ($course_id){
				echo '<a href="'.admin_url('post.php?post='.$course_id.'&action=edit').'">'.get_the_title($course_id).'</a>';
			}

		}
	}


}


