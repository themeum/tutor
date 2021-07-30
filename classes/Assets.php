<?php
namespace TUTOR;

if ( ! defined( 'ABSPATH' ) )
	exit;

class Assets{

	public function __construct() {
		add_action('admin_enqueue_scripts', array($this, 'admin_scripts'));
		add_action('admin_enqueue_scripts', array($this, 'tutor_script_text_domain'),100);
		add_action('wp_enqueue_scripts', array($this, 'frontend_scripts'));
		/**
		 * register translateable function to load
		 * handled script with text domain attached to
		 * @since 1.9.0
		*/
		add_action('wp_enqueue_scripts', array($this, 'tutor_script_text_domain'),100);
		add_action( 'admin_head', array($this, 'tutor_add_mce_button'));
		add_filter( 'get_the_generator_html', array($this, 'tutor_generator_tag'), 10, 2 );
		add_filter( 'get_the_generator_xhtml', array($this, 'tutor_generator_tag'), 10, 2 );
	}

	private function get_default_localized_data() {
		
		$home_url = get_home_url();
		$parsed = parse_url($home_url);

		$base_path = (is_array( $parsed ) && isset( $parsed['path'] )) ? $parsed['path'] : '/';
		$base_path = rtrim($base_path, '/') . '/';

		return array(
			'ajaxurl'       => admin_url('admin-ajax.php'),
			'home_url'		=> $home_url,
			'base_path'		=> $base_path,
			'tutor_url' 	=> tutor()->url,
			'tutor_pro_url' => function_exists('tutor_pro') ? tutor_pro()->url : null,
			'nonce_key'     => tutor()->nonce,
			tutor()->nonce  => wp_create_nonce( tutor()->nonce_action ),
			'loading_icon_url' => get_admin_url() . 'images/wpspin_light.gif',
			'placeholder_img_src' => tutor_placeholder_img_src(),
			'enable_lesson_classic_editor' => get_tutor_option('enable_lesson_classic_editor'),
			'tutor_frontend_dashboard_url' => tutor_utils()->get_tutor_dashboard_page_permalink(),
		);
	}

	public function admin_scripts(){
		wp_enqueue_style('tutor-select2', tutor()->url.'assets/packages/select2/select2.min.css', array(), tutor()->version);
		wp_enqueue_style('tutor-admin', tutor()->url.'assets/css/tutor-admin.min.css', array(), tutor()->version);
		wp_enqueue_style('tutor-icon', tutor()->url.'assets/icons/css/tutor-icon.css', array(), tutor()->version);

		/**
		 * Scripts
		 */
		wp_enqueue_media();

		wp_enqueue_script( 'wp-color-picker' );
		wp_enqueue_style( 'wp-color-picker' );

		wp_enqueue_script('jquery-ui-slider');
		wp_enqueue_script('jquery-ui-datepicker');

		wp_enqueue_script('tutor-select2', tutor()->url.'assets/packages/select2/select2.full.min.js', array('jquery'), tutor()->version, true );
		wp_enqueue_script( 'tutor-main', tutor()->url . 'assets/js/tutor.js', array( 'jquery', 'wp-i18n' ), tutor()->version, true );
		wp_enqueue_script('tutor-admin', tutor()->url.'assets/js/tutor-admin.js', array('jquery', 'wp-color-picker', 'wp-i18n'), tutor()->version, true );

		$tutor_localize_data = $this->get_default_localized_data();

		if ( ! empty($_GET['taxonomy']) && ( $_GET['taxonomy'] === 'course-category' || $_GET['taxonomy'] === 'course-tag') ){
			$tutor_localize_data['open_tutor_admin_menu'] = true;
		}

		$tutor_localize_data = apply_filters( 'tutor_localize_data', $tutor_localize_data );
		wp_localize_script('tutor-admin', '_tutorobject', $tutor_localize_data);

		wp_add_inline_style( 'tutor-admin', $this->load_color_palette() );
	}

	/**
	 * Load frontend scripts
	 */
	public function frontend_scripts(){
		global $post, $wp_query;

		$is_script_debug = tutor_utils()->is_script_debug();
		$suffix = $is_script_debug ? '' : '.min';

		/**
		 * We checked wp_enqueue_editor() in condition because it conflicting with Divi Builder
		 * condition updated @since v.1.7.4
		 */

		if (is_single()){
			if (function_exists('et_pb_is_pagebuilder_used')) {
				$is_page_builder_used = et_pb_is_pagebuilder_used(get_the_ID());
				if (!$is_page_builder_used) {
					wp_enqueue_editor();
				}
			} else {
				wp_enqueue_editor();
			}
		}

		/**
		 * Initializing quicktags script to use in wp_editor();
		 */
		wp_enqueue_script( 'quicktags');

		$tutor_dashboard_page_id = (int) tutor_utils()->get_option('tutor_dashboard_page_id');
		if ($tutor_dashboard_page_id === get_the_ID()){
			wp_enqueue_media();
		}

		//$options = tutor_utils()->get_option();
		$localize_data = $this->get_default_localized_data();

		if ( ! empty($post->post_type) && $post->post_type === 'tutor_quiz'){
			$single_quiz_options = (array) tutor_utils()->get_quiz_option($post->ID);
			$saved_quiz_options = array(
			    'quiz_when_time_expires' => tutils()->get_option('quiz_when_time_expires'),
            );

			$quiz_options = array_merge($single_quiz_options, $saved_quiz_options);

			$previous_attempts = tutor_utils()->quiz_attempts();

			if ($previous_attempts && count($previous_attempts)) {
				$quiz_options['quiz_auto_start'] = 0;
			}
			
			$localize_data['quiz_options'] = $quiz_options;
		}

		/**
		 * Enabling Sorting, draggable, droppable...
		 */
		wp_enqueue_script('jquery-ui-sortable');
		/**
		 * Tutor Icon
		 */
		wp_enqueue_style('tutor-icon', tutor()->url.'assets/icons/css/tutor-icon.css', array(), tutor()->version);


		//Plyr
		wp_enqueue_style( 'tutor-plyr', tutor()->url . 'assets/packages/plyr/plyr.css', array(), tutor()->version );
		wp_enqueue_script( 'tutor-plyr', tutor()->url . 'assets/packages/plyr/plyr.polyfilled.min.js', array( 'jquery' ), tutor()->version, true );

        //Social Share
        wp_enqueue_script( 'tutor-social-share', tutor()->url . 'assets/packages/SocialShare/SocialShare.min.js', array( 'jquery' ), tutor()->version, true );

		//Including player assets if video exists
		if (tutor_utils()->has_video_in_single()) {
			$localize_data['post_id'] = get_the_ID();
			$localize_data['best_watch_time'] = 0;

			$best_watch_time = tutor_utils()->get_lesson_reading_info(get_the_ID(), 0, 'video_best_watched_time');
			if ($best_watch_time > 0){
				$localize_data['best_watch_time'] = $best_watch_time;
			}
		}

		/**
		 * Chart Data
		 */
		if ( ! empty($wp_query->query_vars['tutor_dashboard_page']) ) {
			wp_enqueue_script('jquery-ui-slider');

			wp_enqueue_style('tutor-select2', tutor()->url.'assets/packages/select2/select2.min.css', array(), tutor()->version);
			wp_enqueue_script('tutor-select2', tutor()->url.'assets/packages/select2/select2.full.min.js', array('jquery'), tutor()->version, true );

			if ($wp_query->query_vars['tutor_dashboard_page'] === 'earning'){
				wp_enqueue_script( 'tutor-front-chart-js', tutor()->url . 'assets/js/Chart.bundle.min.js', array(), tutor()->version );
				wp_enqueue_script( 'jquery-ui-datepicker' );
			}
		}
		//End: chart data

		$localize_data = apply_filters('tutor_localize_data', $localize_data);
		if (tutor_utils()->get_option('load_tutor_css')){
			wp_enqueue_style('tutor-frontend', tutor()->url."assets/css/tutor-front{$suffix}.css", array(), tutor()->version);
		}
		if (tutor_utils()->get_option('load_tutor_js')) {
			wp_enqueue_script( 'tutor-main', tutor()->url . 'assets/js/tutor.js', array( 'jquery', 'wp-i18n' ), tutor()->version, true );
			/**
			 * dependency wp-i18n added for 
			 * translate js file
			 * @since 1.9.0
			*/
			wp_register_script( 'tutor-frontend', tutor()->url . 'assets/js/tutor-front.js', array( 'jquery', 'wp-i18n'), tutor()->version, true );
			wp_enqueue_script( 'tutor-frontend');
			wp_localize_script('tutor-frontend', '_tutorobject', $localize_data);
			/**
			 * Location data added for providing support on the frontend
			 * 
			 * for zoom
			 * 
			 * @since 1.9.4
			 */
			wp_localize_script('tutor-main', '_tutorobject', $localize_data);
		}

		wp_add_inline_style( 'tutor-frontend', $this->load_color_palette() );

		// Load date picker for announcement at frontend
		wp_enqueue_script('jquery-ui-datepicker');
	}

	private function load_color_palette() {

		/**
		 * Default Color
		 */
		$tutor_css = ":root{";
		$tutor_primary_color = tutor_utils()->get_option('tutor_primary_color');
		$tutor_primary_hover_color = tutor_utils()->get_option('tutor_primary_hover_color');
		$tutor_text_color = tutor_utils()->get_option('tutor_text_color');
		$tutor_light_color = tutor_utils()->get_option('tutor_light_color');

		/**
		 * tutor buttons style
		 */
		$tutor_button_primary = tutor_utils()->get_option('tutor_button_primary');
		$tutor_button_danger = tutor_utils()->get_option('tutor_button_danger');
		$tutor_button_success = tutor_utils()->get_option('tutor_button_success');
		$tutor_button_warning = tutor_utils()->get_option('tutor_button_warning');

		if ($tutor_primary_color){
			$tutor_css .= " --tutor-primary-color: {$tutor_primary_color};";
		}
		if ($tutor_primary_hover_color){
			$tutor_css .= " --tutor-primary-hover-color: {$tutor_primary_hover_color};";
		}
		if ($tutor_text_color){
			$tutor_css .= " --tutor-text-color: {$tutor_text_color};";
		}
		if ($tutor_light_color){
			$tutor_css .= " --tutor-light-color: {$tutor_light_color};";
		}

		/**
		 * check if button style setup
		 */
		if($tutor_button_primary){
			$tutor_css .= " --tutor-primary-button-color: {$tutor_button_primary}; ";
		}
		if($tutor_button_danger){
			$tutor_css .= " --tutor-danger-button-color: {$tutor_button_danger}; ";
		}
		if($tutor_button_success){
			$tutor_css .= " --tutor-success-button-color: {$tutor_button_success}; ";
		}
		if($tutor_button_warning){
			$tutor_css .= " --tutor-warning-button-color: {$tutor_button_warning}; ";
		}

		$tutor_css .= "}";

		return $tutor_css;
	}

	/**
	 * Add Tinymce button for placing shortcode
	 */
	function tutor_add_mce_button() {
		// check user permissions
		if ( !current_user_can( 'edit_posts' ) && !current_user_can( 'edit_pages' ) ) {
			return;
		}
		// check if WYSIWYG is enabled
		if ( 'true' == get_user_option( 'rich_editing' ) ) {
			add_filter( 'mce_external_plugins', array($this, 'tutor_add_tinymce_js') );
			add_filter( 'mce_buttons', array($this, 'tutor_register_mce_button') );
		}
	}
	// Declare script for new button
	function tutor_add_tinymce_js( $plugin_array ) {
		$plugin_array['tutor_button'] = tutor()->url .'assets/js/mce-button.js';
		return $plugin_array;
	}
	// Register new button in the editor
	function tutor_register_mce_button( $buttons ) {
		array_push( $buttons, 'tutor_button' );
		return $buttons;
	}

	/**
	 * Output generator tag to aid debugging.
	 *
	 * @param string $gen Generator.
	 * @param string $type Type.
	 * @return string
	 */
	function tutor_generator_tag( $gen, $type ) {
		switch ( $type ) {
			case 'html':
				$gen .= "\n" . '<meta name="generator" content="TutorLMS ' . esc_attr( TUTOR_VERSION ) . '">';
				break;
			case 'xhtml':
				$gen .= "\n" . '<meta name="generator" content="TutorLMS ' . esc_attr( TUTOR_VERSION ) . '" />';
				break;
		}
		return $gen;
	}

	/**
	 * load text domain handled script after all enqueue_scripts 
	 * registered functions
	 * @since 1.9.0
	*/
	function tutor_script_text_domain() {
		wp_set_script_translations( 'tutor-frontend', 'tutor', tutor()->path.'languages/' );
		wp_set_script_translations( 'tutor-main', 'tutor', tutor()->path.'languages/' );
		wp_set_script_translations( 'tutor-admin', 'tutor', tutor()->path.'languages/' );
	}
	
}