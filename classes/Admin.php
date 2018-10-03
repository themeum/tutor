<?php
namespace TUTOR;

if ( ! defined( 'ABSPATH' ) )
	exit;

class Admin{
	public function __construct() {
		add_action('admin_menu', array($this, 'register_menu'));
	}

	public function register_menu(){
		add_menu_page(__('TUTOR', 'tutor'), __('TUTOR', 'tutor'), 'manage_options', 'tutor', array($this, 'tutor_page'), 'dashicons-welcome-learn-more', 2);

		add_submenu_page('tutor', __('Students', 'tutor'), __('Students', 'tutor'), 'manage_options', 'tutor-students', array($this, 'tutor_students') );
	}

	public function tutor_page(){
		$tutor_option = new Options();
		echo apply_filters('tutor/options/generated-html', $tutor_option->generate());
	}

	public function tutor_students(){
		include tutor()->path.'views/pages/students.php';
	}

}