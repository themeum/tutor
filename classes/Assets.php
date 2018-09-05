<?php
namespace LMS;

if ( ! defined( 'ABSPATH' ) )
	exit;

class Assets{

	public function __construct() {
		add_action('admin_enqueue_scripts', array($this, 'admin_scripts'));
	}


	public function admin_scripts(){


		wp_enqueue_style('lms-admin', lms()->url.'assets/css/lms-admin.css', array(), lms()->version );


		/**
		 * Scripts
		 */
		wp_enqueue_script('jquery-ui-slider');
		wp_enqueue_script('lms-admin', lms()->url.'assets/js/lms-admin.js', array('jquery'), lms()->version, true );
	}


}