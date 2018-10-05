<?php
namespace TUTOR;

if ( ! defined( 'ABSPATH' ) )
	exit;

class Lesson extends Tutor_Base {
	public function __construct() {
		parent::__construct();

		add_action( 'add_meta_boxes', array($this, 'register_meta_box') );
		add_action('save_post_'.$this->lesson_post_type, array($this, "save_lesson_meta"));

		add_filter('get_sample_permalink', array($this, 'change_lesson_permalink'), 10, 2);

		add_action('admin_init', array($this, 'flush_rewrite_rules'));

		/**
		 * Add Column
		 */

		add_filter( "manage_{$this->lesson_post_type}_posts_columns", array($this, 'add_column'), 10,1 );
		add_action( "manage_{$this->lesson_post_type}_posts_custom_column" , array($this, 'custom_lesson_column'), 10, 2 );

		//Frontend Action
		add_action('template_redirect', array($this, 'mark_lesson_complete'));
	}

	/**
	 * Registering metabox
	 */
	public function register_meta_box(){
		$lesson_post_type = $this->lesson_post_type;

		add_meta_box( 'tutor-course-select', __( 'Select Course', 'tutor' ), array($this, 'lesson_metabox'), $lesson_post_type );
		add_meta_box( 'tutor-lesson-videos', __( 'Lesson Video', 'tutor' ), array($this, 'lesson_video_metabox'), $lesson_post_type );
		add_meta_box( 'tutor-lesson-attachments', __( 'Attachments', 'tutor' ), array($this, 'lesson_attachments_metabox'), $lesson_post_type );
	}

	public function lesson_metabox(){
		include  tutor()->path.'views/metabox/lesson-metabox.php';
	}

	public function lesson_video_metabox(){
		include  tutor()->path.'views/metabox/video-metabox.php';
	}

	public function lesson_attachments_metabox(){
		include  tutor()->path.'views/metabox/lesson-attachments-metabox.php';
	}

	/**
	 * @param $post_ID
	 *
	 * Saving lesson meta and assets
	 *
	 */
	public function save_lesson_meta($post_ID){
		//Course
		if (isset($_POST['selected_course'])) {
			$course_id = (int) sanitize_text_field( $_POST['selected_course'] );
			if ( $course_id ) {
				update_post_meta( $post_ID, '_tutor_course_id_for_lesson', $course_id );
			}
		}

		//Video
		if ( ! empty($_POST['video']['source'])){
			$video = tutor_utils()->sanitize_array($_POST['video']);
			update_post_meta($post_ID, '_video', $video);
		}

		//Attachments
		$attachments = array();
		if ( ! empty($_POST['tutor_attachments'])){
			$attachments = tutor_utils()->sanitize_array($_POST['tutor_attachments']);
			$attachments = array_unique($attachments);
		}
		update_post_meta($post_ID, '_tutor_attachments', $attachments);

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

		if ($post && $post->post_type === $this->lesson_post_type){
			$uri_base = trailingslashit(site_url());

			$sample_course = "sample-course";
			$is_course = get_post_meta(get_the_ID(), '_tutor_course_id_for_lesson', true);
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
		$columns['course'] = __('Course', 'tutor');
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

			$course_id = get_post_meta($post_id, '_tutor_course_id_for_lesson', true);
			if ($course_id){
				echo '<a href="'.admin_url('post.php?post='.$course_id.'&action=edit').'">'.get_the_title($course_id).'</a>';
			}

		}
	}

	/**
	 *
	 * Mark lesson completed
	 *
	 * @since v.1.0.0
	 */
	public function mark_lesson_complete(){
		if ( ! isset($_POST['tutor_action'])  ||  $_POST['tutor_action'] !== 'tutor_complete_lesson' ){
			return;
		}
		//Checking nonce
		tutor_utils()->checking_nonce();

		$user_id = get_current_user_id();

		//TODO: need to show view if not signed_in
		if ( ! $user_id){
			die(__('Please Sign-In', 'tutor'));
		}

		$lesson_id = (int) sanitize_text_field($_POST['lesson_id']);

		/**
		 * Marking lesson at user meta, meta format, _tutor_completed_lesson_id_{id} and value = time();
		 */
		tutor_utils()->mark_lesson_complete($lesson_id);

		wp_redirect(get_the_permalink($lesson_id));
	}

}


