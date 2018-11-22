<?php
namespace DOZENT;

if ( ! defined( 'ABSPATH' ) )
	exit;

class Assets{

	public function __construct() {
		add_action('admin_enqueue_scripts', array($this, 'admin_scripts'));
		add_action('wp_enqueue_scripts', array($this, 'frontend_scripts'));

		add_action( 'admin_head', array($this, 'dozent_add_mce_button'));
	}


	public function admin_scripts(){
		wp_enqueue_style('dozent-select2', dozent()->url.'assets/packages/select2/select2.min.css', array(), dozent()->version);
		wp_enqueue_style('dozent-admin', dozent()->url.'assets/css/dozent-admin.css', array(), dozent()->version);

		/**
		 * Scripts
		 */
		wp_enqueue_script('jquery-ui-slider');
		wp_enqueue_script('dozent-select2', dozent()->url.'assets/packages/select2/select2.min.js', array('jquery'), dozent()->version, true );
		wp_enqueue_script('dozent-admin', dozent()->url.'assets/js/dozent-admin.js', array('jquery'), dozent()->version, true );



		$dozent_localize_data = array();
		if ( ! empty($_GET['taxonomy']) && ( $_GET['taxonomy'] === 'course-category' || $_GET['taxonomy'] === 'course-tag') ){
			$dozent_localize_data['open_dozent_admin_menu'] = true;
		}

		wp_localize_script('dozent-admin', 'dozent_data', $dozent_localize_data);
	}

	/**
	 * Load frontend scripts
	 */
	public function frontend_scripts(){

		wp_enqueue_editor();

		$localize_data = array(
			'ajaxurl'   => admin_url('admin-ajax.php'),
			'nonce_key' => dozent()->nonce,
			dozent()->nonce  => wp_create_nonce( dozent()->nonce_action ),
		);

		//Including player assets if video exists
		if (dozent_utils()->has_video_in_single()) {
			//Plyr
			wp_enqueue_style( 'dozent-plyr', dozent()->url . 'assets/packages/plyr/plyr.css', array(), dozent()->version );
			wp_enqueue_script( 'dozent-plyr', dozent()->url . 'assets/packages/plyr/plyr.polyfilled.min.js', array( 'jquery' ), dozent()->version, true );

			$localize_data['post_id'] = get_the_ID();
			$localize_data['best_watch_time'] = 0;

			$best_watch_time = dozent_utils()->get_lesson_reading_info(get_the_ID(), 0, 'video_best_watched_time');

			if ($best_watch_time > 0){
				$localize_data['best_watch_time'] = $best_watch_time;
			}
		}

		if (dozent_utils()->get_option('load_dozent_css')){
			wp_enqueue_style('dozent-frontend', dozent()->url.'assets/css/dozent-front.css', array(), dozent()->version);
		}
		if (dozent_utils()->get_option('load_dozent_js')) {
			wp_enqueue_script( 'dozent-frontend', dozent()->url . 'assets/js/dozent-front.js', array( 'jquery' ), dozent()->version, true );
			wp_localize_script('dozent-frontend', '_dozentobject', $localize_data);
		}
	}


	/**
	 * Add Tinymce button for placing shortcode
	 */
	function dozent_add_mce_button() {

		// check user permissions
		if ( !current_user_can( 'edit_posts' ) && !current_user_can( 'edit_pages' ) ) {
			return;
		}
		// check if WYSIWYG is enabled
		if ( 'true' == get_user_option( 'rich_editing' ) ) {
			add_filter( 'mce_external_plugins', array($this, 'dozent_add_tinymce_js') );
			add_filter( 'mce_buttons', array($this, 'dozent_register_mce_button') );
		}
	}
	// Declare script for new button
	function dozent_add_tinymce_js( $plugin_array ) {
		$plugin_array['dozent_button'] = dozent()->url .'assets/js/mce-button.js';
		return $plugin_array;
	}
	// Register new button in the editor
	function dozent_register_mce_button( $buttons ) {
		array_push( $buttons, 'dozent_button' );
		return $buttons;
	}
	
	
}