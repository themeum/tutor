<?php
namespace LMS;

if ( ! defined( 'ABSPATH' ) )
	exit;

class Assets{

	public function __construct() {
		add_action('admin_enqueue_scripts', array($this, 'admin_scripts'));
		add_action('wp_enqueue_scripts', array($this, 'frontend_scripts'));
	}


	public function admin_scripts(){
		wp_enqueue_style('lms-select2', lms()->url.'assets/packages/select2/select2.min.css', array(), lms()->version);
		wp_enqueue_style('lms-admin', lms()->url.'assets/css/lms-admin.css', array(), lms()->version);

		/**
		 * Scripts
		 */
		wp_enqueue_script('jquery-ui-slider');
		wp_enqueue_script('lms-select2', lms()->url.'assets/packages/select2/select2.min.js', array('jquery'), lms()->version, true );
		wp_enqueue_script('lms-admin', lms()->url.'assets/js/lms-admin.js', array('jquery'), lms()->version, true );
	}

	/**
	 * Load frontend scripts
	 */
	public function frontend_scripts(){

		//Including player assets if video exists
		if (lms_utils()->has_video_in_single()) {
			//Plyr
			wp_enqueue_style( 'lms-plyr', lms()->url . 'assets/packages/plyr/plyr.css', array(), lms()->version );
			wp_enqueue_script( 'lms-plyr', lms()->url . 'assets/packages/plyr/plyr.polyfilled.min.js', array( 'jquery' ), lms()->version, true );
		}


		wp_enqueue_style('lms-frontend', lms()->url.'assets/css/lms-front.css', array(), lms()->version);

		wp_enqueue_script('lms-frontend', lms()->url.'assets/js/lms-front.js', array('jquery'), lms()->version, true );

	}


}