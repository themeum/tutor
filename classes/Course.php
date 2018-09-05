<?php
namespace LMS;

if ( ! defined( 'ABSPATH' ) )
	exit;

class Course {
	public function __construct() {
		add_action( 'add_meta_boxes', array($this, 'register_meta_box') );
	}

	public function register_meta_box(){
		add_meta_box( 'lms-course-topocs', __( 'Topics', 'lms' ), array($this, 'course_meta_box'), 'course' );
	}

	public function course_meta_box(){
		include  lms()->path.'views/metabox/course-topics.php';
	}

}


