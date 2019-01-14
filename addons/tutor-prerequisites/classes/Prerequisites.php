<?php
/**
 * Tutor Prerequisites
 */

namespace TUTOR_PREREQUISITES;

use TUTOR\Tutor_Base;

if ( ! defined( 'ABSPATH' ) )
	exit;

class Prerequisites extends Tutor_Base {

	public function __construct() {
		parent::__construct();

		add_action( 'add_meta_boxes', array($this, 'register_meta_box') );

		add_action('save_post_'.$this->course_post_type, array($this, 'save_course_meta'));

		add_action('tutor_course/single/lead_meta/after', array($this, 'prerequisites_courses_lists'));


		add_filter('tutor_lesson/single/content', array($this, 'tutor_lesson_content'));
	}

	public function register_meta_box(){
		add_meta_box( 'tutor-course-prerequisites', __( 'Course Prerequisites', 'tutor-prerequisites' ), array($this, 'prerequisites_courses'), $this->course_post_type, 'advanced', 'high' );
	}

	/**
	 * @param $post
	 * Metabox for prerequisites lists
	 */
	public function prerequisites_courses(){
		include  TUTOR_PREREQUISITES()->path.'views/metabox/course-prerequisites-lists.php';
	}

	public function save_course_meta($post_ID){
		$prerequisites_course_ids = tutor_utils()->avalue_dot('_tutor_course_prerequisites_ids', $_POST);

		if (is_array($prerequisites_course_ids) && count($prerequisites_course_ids)){
			update_post_meta($post_ID, '_tutor_course_prerequisites_ids', $prerequisites_course_ids);
		}else{
			delete_post_meta($post_ID, '_tutor_course_prerequisites_ids');
		}
	}

	public function prerequisites_courses_lists(){
		$coursePrerequisitesIDS = maybe_unserialize(get_post_meta(get_the_ID(), '_tutor_course_prerequisites_ids', true));
		if ( is_array($coursePrerequisitesIDS) && count($coursePrerequisitesIDS)){
			include  TUTOR_PREREQUISITES()->path.'views/frontend/show-course-prerequisites.php';
		}
	}

	public function tutor_lesson_content($content){

		$lesson_id = get_the_ID();
		$course_id = tutor_utils()->get_course_id_by_lesson($lesson_id);

		$requiredComplete = false;

		$savedPrerequisitesIDS = maybe_unserialize(get_post_meta($course_id, '_tutor_course_prerequisites_ids', true));

		if (is_array($savedPrerequisitesIDS) && count($savedPrerequisitesIDS)){
			foreach ($savedPrerequisitesIDS as $courseID) {
				if ( ! tutor_utils()->is_completed_course($courseID)){
					$requiredComplete = true;
				}
			}
		}

		if ($requiredComplete){

			global $post;

			$post = get_post($course_id);
			setup_postdata($post);

			ob_start();
			include  TUTOR_PREREQUISITES()->path.'views/frontend/show-course-prerequisites.php';
			return ob_get_clean();
		}


		return $content;
	}

}