<?php
namespace LMS;

if ( ! defined( 'ABSPATH' ) )
	exit;

class Admin{
	public function __construct() {
		add_action('admin_menu', array($this, 'register_menu'));
	}

	public function register_menu(){
		add_menu_page(__('LMS', 'lms'), __('LMS', 'lms'), 'manage_options', 'lms', array($this, 'lms_page'), 'dashicons-welcome-learn-more', 2);
	}

	public function lms_page(){
		$lms_option = new Options();
		echo apply_filters('lms/options/generated-html', $lms_option->generate());
	}

}