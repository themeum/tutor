<?php
/**
 * Tutor Course attachments Main Class
 */

namespace TUTOR_CP;

use TUTOR\Tutor_Base;

class CoursePreview extends Tutor_Base {

	public function __construct() {
		parent::__construct();

		add_action( 'add_meta_boxes', array($this, 'register_meta_box') );
		add_action('tutor_lesson_edit_modal_form_after', array($this, 'additional_data_metabox_before'), 10, 0);
		add_action('save_post_'.$this->lesson_post_type, array($this, 'save_lesson_meta'));

		add_filter('tutor_course/contents/lesson/title', array($this, 'mark_lesson_title_preview'), 10, 2);
		//add_filter('tutor_lesson/required_login_to_view_lesson', array($this, 'required_login_to_view_lesson'), 10, 2);

		add_filter('tutor_lesson_template', array($this, 'tutor_lesson_template'));
		add_filter('tutor_video_stream_is_public', array($this, 'video_stream_is_public'), 10, 2);
	}

	public function register_meta_box(){
		add_meta_box( 'tutor-course-preview', __( 'Lesson Preview', 'tutor' ), array($this, 'additional_data_metabox_before'), $this->lesson_post_type, 'advanced', 'high' );
	}

	/**
	 * @param $post
	 * MetaBox for Lesson Modal Edit Mode
	 */
	public function additional_data_metabox_before(){
		include  TUTOR_CP()->path.'views/metabox/course-preview-metabox.php';
	}

	public function save_lesson_meta($post_ID){
		$_is_preview = sanitize_text_field(tutor_utils()->avalue_dot('_is_preview', $_POST));
		if ($_is_preview){
			update_post_meta($post_ID, '_is_preview', 1);
		}else{
			delete_post_meta($post_ID, '_is_preview');
		}
	}

	/**
	 * @param $title
	 * @param $post_id
	 *
	 * @return string
	 *
	 * Mark lesson title preview from this method
	 */
	public function mark_lesson_title_preview($title, $post_id){
		$is_preview = (bool) get_post_meta($post_id, '_is_preview', true);
		if ($is_preview){
			$newTitle = '<a href="'.get_the_permalink($post_id).'"><span class="lesson-preview-title">'.$title.'</span></a>';
			return $newTitle;
		}
		$modifiedTitle = '<span class="lesson-preview-title">'.$title.'</span><span class="lesson-preview-icon"><i class="tutor-icon-lock"></i> </span>';

		return $modifiedTitle;
	}

	public function required_login_to_view_lesson($bool, $post_id){
		return ! (bool) get_post_meta($post_id, '_is_preview', true);
	}

	public function tutor_lesson_template($template){
		$is_course_enrolled = tutor_utils()->is_course_enrolled_by_lesson();

		if ( ! $is_course_enrolled){
			$isPreview =  (bool) get_post_meta(get_the_ID(), '_is_preview', true);
			if ($isPreview){
				$template = tutor_get_template( 'single-preview-lesson' );
			}
		}
		return $template;
	}

	public function video_stream_is_public($bool, $post_id){
		return (bool) get_post_meta($post_id, '_is_preview', true);

	}

}