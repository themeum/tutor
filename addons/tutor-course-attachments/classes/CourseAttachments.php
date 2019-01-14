<?php
/**
 * Tutor Course attachments Main Class
 */

namespace TUTOR_CA;

use TUTOR\Tutor_Base;

class CourseAttachments extends Tutor_Base {

	public function __construct() {
		parent::__construct();

		add_action( 'add_meta_boxes', array($this, 'register_meta_box') );
		add_action('save_post_'.$this->course_post_type, array($this, 'save_course_meta'));
	}

	public function register_meta_box(){
		$coursePostType = tutor()->course_post_type;
		$allow_private_files = tutor_utils()->get_option('course_allow_upload_private_files');

		/**
		 * Check is allow private file upload
		 */
		if ($allow_private_files){
			add_meta_box( 'tutor-course-attachments', __( 'Attachments, private files', 'tutor' ), array($this, 'course_attachments_metabox'), $coursePostType, 'advanced', 'high' );
		}
	}

	public function course_attachments_metabox(){
		include  TUTOR_CA()->path.'views/metabox/course-attachments-metabox.php';
	}

	public function save_course_meta($post_ID){
		//Attachments
		$attachments = array();
		if ( ! empty($_POST['tutor_attachments'])){
			$attachments = tutor_utils()->sanitize_array($_POST['tutor_attachments']);
			$attachments = array_unique($attachments);
		}
		update_post_meta($post_ID, '_tutor_attachments', $attachments);
	}


}