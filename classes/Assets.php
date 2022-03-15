<?php

namespace TUTOR;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Assets {

	public function __construct()
	{
		/**
		 * Common scripts loading
		 */
		add_action('admin_enqueue_scripts', array($this, 'common_scripts'));
		add_action('wp_enqueue_scripts', array($this, 'common_scripts'));
		/**
		 * Front and backend script enqueue
		 */
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'frontend_scripts' ) );

		add_action('admin_enqueue_scripts', array($this, 'load_meta_data'));
		add_action('wp_enqueue_scripts', array($this, 'load_meta_data'));

		/**
		 * Text domain loading
		 */
		add_action( 'admin_enqueue_scripts', array( $this, 'tutor_script_text_domain' ), 100 );
		add_action( 'wp_enqueue_scripts', array( $this, 'tutor_script_text_domain' ), 100 );
		add_filter( 'tutor_localize_data', array( $this, 'modify_localize_data' ) );

		/**
		 * register translateable function to load
		 * handled script with text domain attached to
		 *
		 * @since 1.9.0
		*/
		add_action( 'admin_head', array( $this, 'tutor_add_mce_button' ) );
		add_filter( 'get_the_generator_html', array( $this, 'tutor_generator_tag' ), 10, 2 );
		add_filter( 'get_the_generator_xhtml', array( $this, 'tutor_generator_tag' ), 10, 2 );

		/**
		 * Add translation support for external tinyMCE button
		 *
		 * @since 1.9.7
		 */
		add_filter('mce_external_languages', array($this, 'tutor_tinymce_translate'));

		/**
		 * Identifier class to body tag
		 *
		 * @since v1.9.9
		 */
		add_filter( 'body_class', array( $this, 'add_identifier_class_to_body' ) );
		add_filter( 'admin_body_class', array( $this, 'add_identifier_class_to_body' ) );
	}

	private function get_default_localized_data()
	{
		global $wp_version;
		$home_url = get_home_url();
		$parsed   = parse_url($home_url);

		$base_path = (is_array($parsed) && isset($parsed['path'])) ? $parsed['path'] : '/';
		$base_path = rtrim($base_path, '/') . '/';

		$post_id   = get_the_ID();
		$post_type = get_post_type( $post_id );

		return array(
			'ajaxurl'                      => admin_url( 'admin-ajax.php' ),
			'home_url'                     => get_home_url(),
			'site_title'                   => get_bloginfo( 'title' ),
			'base_path'                    => tutor()->basepath,
			'tutor_url'                    => tutor()->url,
			'tutor_pro_url'                => function_exists( 'tutor_pro' ) ? tutor_pro()->url : null,
			'nonce_key'                    => tutor()->nonce,
			tutor()->nonce                 => wp_create_nonce( tutor()->nonce_action ),
			'loading_icon_url'             => get_admin_url() . 'images/wpspin_light.gif',
			'placeholder_img_src'          => tutor_placeholder_img_src(),
			'enable_lesson_classic_editor' => get_tutor_option( 'enable_lesson_classic_editor' ),
			'tutor_frontend_dashboard_url' => tutor_utils()->get_tutor_dashboard_page_permalink(),
			'wp_date_format'               => tutor_js_date_format_against_wp(),
			'is_admin'                     => is_admin(),
			'is_admin_bar_showing'         => is_admin_bar_showing(),
			'addons_data'                  => tutor_utils()->prepare_free_addons_data(),
			'current_user'                  => wp_get_current_user(),
			'content_change_event'         => 'tutor_content_changed_event',
			'is_tutor_course_edit'		   => isset( $_GET[ 'action'] ) && 'edit' === $_GET['action'] && tutor()->course_post_type === get_post_type( get_the_ID() ) || isset( $_GET['post_type'] ) && 'courses' === $_GET['post_type'] ? true : false,
			'assignment_max_file_allowed'  => 'tutor_assignments' === $post_type ? (int) tutor_utils()->get_assignment_option( $post_id, 'upload_files_limit' ) : 0,
		);
	}

	public function admin_scripts()
	{
		wp_enqueue_style('tutor-select2', tutor()->url . 'assets/packages/select2/select2.min.css', array(), TUTOR_VERSION);
		wp_enqueue_style('tutor-admin', tutor()->url . 'assets/css/tutor-admin.min.css', array(), TUTOR_VERSION);
		/**
		 * Scripts
		 */
		wp_enqueue_media();

		wp_enqueue_script('wp-color-picker');
		wp_enqueue_style('wp-color-picker');

		wp_enqueue_script( 'jquery-ui-slider' );
		wp_enqueue_script( 'jquery-ui-datepicker' );

		wp_enqueue_script('tutor-select2', tutor()->url . 'assets/packages/select2/select2.full.min.js', array('jquery'), TUTOR_VERSION, true);
		wp_enqueue_script('tutor-admin', tutor()->url . 'assets/js/tutor-admin.min.js', array('jquery', 'wp-color-picker', 'wp-i18n'), TUTOR_VERSION, true);
	}

	/**
	 * Load frontend scripts
	 */
	public function frontend_scripts()
	{
		global $post, $wp_query;

		/**
		 * We checked wp_enqueue_editor() in condition because it conflicting with Divi Builder
		 * condition updated @since v.1.7.4
		 */

		if ( is_single() ) {
			if ( function_exists( 'et_pb_is_pagebuilder_used' ) ) {
				$is_page_builder_used = et_pb_is_pagebuilder_used( get_the_ID() );
				if ( ! $is_page_builder_used ) {
					wp_enqueue_editor();
				}
			} else {
				wp_enqueue_editor();
			}
		}

		/**
		 * Initializing quicktags script to use in wp_editor();
		 */
		wp_enqueue_script( 'quicktags' );

		$tutor_dashboard_page_id = (int) tutor_utils()->get_option( 'tutor_dashboard_page_id' );
		if ( $tutor_dashboard_page_id === get_the_ID() ) {
			wp_enqueue_media();
		}

		/**
		 * Enabling Sorting, draggable, droppable...
		 */
		wp_enqueue_script( 'jquery-ui-sortable' );
		
		// Plyr
		if(is_single_course(true)) {
			wp_enqueue_style('tutor-plyr', tutor()->url . 'assets/packages/plyr/plyr.css', array(), TUTOR_VERSION);
			wp_enqueue_script('tutor-plyr', tutor()->url . 'assets/packages/plyr/plyr.polyfilled.min.js', array('jquery'), TUTOR_VERSION, true);
		}
		
		// Social Share
		wp_enqueue_script('tutor-social-share', tutor()->url . 'assets/packages/SocialShare/SocialShare.min.js', array('jquery'), TUTOR_VERSION, true);

		/**
		 * Chart Data
		 */
		if (!empty($wp_query->query_vars['tutor_dashboard_page'])) {
			wp_enqueue_script('jquery-ui-slider');

			wp_enqueue_style('tutor-select2', tutor()->url . 'assets/packages/select2/select2.min.css', array(), TUTOR_VERSION);
			wp_enqueue_script('tutor-select2', tutor()->url . 'assets/packages/select2/select2.full.min.js', array('jquery'), TUTOR_VERSION, true);

			if ($wp_query->query_vars['tutor_dashboard_page'] === 'earning') {
				wp_enqueue_script('tutor-front-chart-js', tutor()->url . 'assets/js/lib/Chart.bundle.min.js', array(), TUTOR_VERSION);
				wp_enqueue_script('jquery-ui-datepicker');
			}
		}
		/**
		 * dependency wp-i18n added for
		 * translate js file
		 *
		 * @since 1.9.0
		 */
		wp_enqueue_style('tutor-frontend', tutor()->url . 'assets/css/tutor-front.min.css', array(), TUTOR_VERSION);
		wp_enqueue_script('tutor-frontend', tutor()->url . 'assets/js/tutor-front.min.js', array('jquery', 'wp-i18n'), TUTOR_VERSION, true );

		/**
		 * Load frontend dashboard style
		 *
		 * @since v1.9.8
		 */
		if (tutor_utils()->is_tutor_frontend_dashboard()) {
			wp_enqueue_style('tutor-frontend-dashboard-css', tutor()->url . 'assets/css/tutor-frontend-dashboard.min.css', TUTOR_VERSION);
		}

		// Load date picker for announcement at frontend
		wp_enqueue_script( 'jquery-ui-datepicker' );
	}

	public function modify_localize_data($localize_data)
	{
		global $post;

		if ( is_admin() ) {
			if ( ! empty( $_GET['taxonomy'] ) && ( $_GET['taxonomy'] === 'course-category' || $_GET['taxonomy'] === 'course-tag' ) ) {
				$localize_data['open_tutor_admin_menu'] = true;
			}
		} else {

			// Assign quiz option
			if (!empty($post->post_type) && $post->post_type === 'tutor_quiz') {
				$single_quiz_options = (array) tutor_utils()->get_quiz_option($post->ID);
				$saved_quiz_options  = array(
					'quiz_when_time_expires' => tutor_utils()->get_option('quiz_when_time_expires'),
				);

				$quiz_options = array_merge($single_quiz_options, $saved_quiz_options);

				$previous_attempts = tutor_utils()->quiz_attempts();

				if ($previous_attempts && count($previous_attempts)) {
					$quiz_options['quiz_auto_start'] = 0;
				}

				$localize_data['quiz_options'] = $quiz_options;
			}

			// Including player assets if video exists
			if ( tutor_utils()->has_video_in_single() ) {
				$localize_data['post_id']         = get_the_ID();
				$localize_data['best_watch_time'] = 0;

				$best_watch_time = tutor_utils()->get_lesson_reading_info( get_the_ID(), 0, 'video_best_watched_time' );
				if ( $best_watch_time > 0 ) {
					$localize_data['best_watch_time'] = $best_watch_time;
				}
			}
		}

		return $localize_data;
	}

	public function common_scripts() {

		// Fonts
		wp_enqueue_style('tutor-inter-fonts', 'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap', array(), TUTOR_VERSION);
		wp_enqueue_style('tutor-icon', tutor()->url . 'assets/css/tutor-icon.css', array(), TUTOR_VERSION);

		// Common css library
		wp_enqueue_style('tutor', tutor()->url . 'assets/css/tutor.min.css', array(), TUTOR_VERSION);

		// Load course builder resources
		if ($this->get_course_builder_screen()) {
			wp_enqueue_script('tutor-course-builder', tutor()->url . 'assets/js/tutor-course-builder.min.js', array('jquery', 'wp-i18n'), TUTOR_VERSION, true);
			wp_enqueue_style('tutor-course-builder-css', tutor()->url . 'assets/css/tutor-course-builder.min.css', array(), TUTOR_VERSION);
		}
		/**
		 * Load tutor common scripts both backend and frontend
		 *
		 * @since v2.0.0
		 */
		wp_enqueue_script('tutor-script', tutor()->url . 'assets/js/tutor.min.js', array('jquery', 'wp-i18n'), TUTOR_VERSION, true);
	}

	public function load_meta_data()
	{
		// Localize scripts
		$localize_data = apply_filters('tutor_localize_data', $this->get_default_localized_data());
		wp_localize_script('tutor-frontend', '_tutorobject', $localize_data);
		wp_localize_script('tutor-admin', '_tutorobject', $localize_data);
		wp_localize_script('tutor-course-builder', '_tutorobject', $localize_data);
		wp_localize_script('tutor-script', '_tutorobject', $localize_data);

		// Inline styles
		wp_add_inline_style('tutor-frontend', $this->load_color_palette());
		wp_add_inline_style('tutor-admin', $this->load_color_palette());
	}

	private function load_color_palette()
	{
		$colors = array(
			'tutor_primary_color'       => '--tutor-primary-color',
			'tutor_primary_hover_color' => '--tutor-primary-hover-color',
			'tutor_text_color'          => '--tutor-text-color',
			'tutor_background_color'	=> '--tutor-background-color',
			'tutor_border_color'		=> '--tutor-border-color',
			'tutor_success_color'		=> '--tutor-success-color',
			'tutor_warning_color'		=> '--tutor-warning-color',
			'tutor_danger_color'		=> '--tutor-danger-color',
			'tutor_disable_color'		=> '--tutor-disable-color',
			'tutor_table_background_color' => '--tutor-table-background-color',

			'tutor_primary_text_color'  => '--tutor-primary-text-color',
			'tutor_light_color'         => '--tutor-light-color',
			'tutor_button_primary'      => '--tutor-primary-button-color',
			'tutor_button_danger'       => '--tutor-danger-button-color',
			'tutor_button_success'      => '--tutor-success-button-color',
			'tutor_button_warning'      => '--tutor-warning-button-color',
		);

		$color_string = '';
		foreach ($colors as $key => $property) {
			$color = tutor_utils()->get_option($key);
			if ($color) {
				$color_string .= $property . ':' . $color . ';';
			}
		}

		return ':root{' . $color_string . '}';
	}

	/**
	 * Add Tinymce button for placing shortcode
	 */
	function tutor_add_mce_button()
	{
		// check user permissions
		if (!current_user_can('edit_posts') && !current_user_can('edit_pages')) {
			return;
		}
		// check if WYSIWYG is enabled
		if ('true' == get_user_option('rich_editing')) {
			add_filter('mce_external_plugins', array($this, 'tutor_add_tinymce_js'));
			add_filter('mce_buttons', array($this, 'tutor_register_mce_button'));
		}
	}
	// Declare script for new button
	function tutor_add_tinymce_js($plugin_array)
	{
		$plugin_array['tutor_button'] = tutor()->url . 'assets/js/lib/mce-button.js';
		return $plugin_array;
	}
	// Register new button in the editor
	function tutor_register_mce_button($buttons)
	{
		array_push($buttons, 'tutor_button');
		return $buttons;
	}

	/**
	 * Output generator tag to aid debugging.
	 *
	 * @param string $gen Generator.
	 * @param string $type Type.
	 * @return string
	 */
	function tutor_generator_tag($gen, $type)
	{
		switch ($type) {
			case 'html':
				$gen .= "\n" . '<meta name="generator" content="TutorLMS ' . TUTOR_VERSION . '">';
				break;
			case 'xhtml':
				$gen .= "\n" . '<meta name="generator" content="TutorLMS ' . TUTOR_VERSION . '" />';
				break;
		}
		return $gen;
	}

	/**
	 * load text domain handled script after all enqueue_scripts
	 * registered functions
	 *
	 * @since 1.9.0
	 */
	function tutor_script_text_domain() {
		wp_set_script_translations( 'tutor-frontend', 'tutor', tutor()->path . 'languages/' );
		wp_set_script_translations( 'tutor-admin', 'tutor', tutor()->path . 'languages/' );
		wp_set_script_translations( 'tutor-course-builder', 'tutor', tutor()->path . 'languages/' );
	}

	/**
	 * Add translation support for external tinyMCE button
	 *
	 * @since 1.9.7
	 */
	function tutor_tinymce_translate() {
		$locales['tutor_button'] = tutor()->path . 'includes/tinymce_translate.php';
		return $locales;
	}

	private function get_course_builder_screen()
	{

		// Add course editor identifier class
		if ( is_admin() ) {
			$screen = get_current_screen();
			if ( is_object( $screen ) && $screen->base == 'post' && $screen->id == 'courses' ) {
				return $screen->is_block_editor ? 'gutenberg' : 'classic';
			}
		} elseif ( tutor_utils()->is_tutor_frontend_dashboard( 'create-course' ) ) {
			return 'frontend';
		}

		return null;
	}

	public function add_identifier_class_to_body($classes)
	{
		$course_builder_screen = $this->get_course_builder_screen();
		$to_add                = array();

		// Add backend course editor identifier class to body
		if ($course_builder_screen) {
			$to_add[] = is_admin() ? 'tutor-backend' : '';
			$to_add[] = ' tutor-screen-course-builder tutor-screen-course-builder-' . $course_builder_screen . ' ';
		}

		// Add frontend course builder identifier class
		if (!$course_builder_screen && tutor_utils()->is_tutor_frontend_dashboard()) {
			$to_add[] = 'tutor-screen-frontend-dashboard';
		}

		if (is_admin()) {
			$screen = function_exists('get_current_screen') ? get_current_screen() : null;
			$base = ($screen && is_object($screen) && property_exists($screen, 'base')) ? $screen->base : '';
			$index = strpos($base, 'tutor');

			if($index===0 || $index>0) {
				$to_add[] = 'tutor-backend';

				if (isset($_GET['page']) && $_GET['page'] == 'tutor_settings') {
					$to_add[] = 'tutor-screen-backend-settings ';
				}
				if (isset($_GET['page'])) {
					$to_add[] = 'tutor-backend-' . $_GET['page'];
				}
			}
		}

		// Remove duplicate classes if any
		$to_add = array_unique($to_add);

		if(is_array($classes)) {
			$classes = array_merge($classes, $to_add);
		} else {
			$classes .= implode(' ', $to_add);
		}

		return $classes;
	}
}
