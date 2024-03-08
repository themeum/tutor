<?php
/**
 * Manage Assets
 *
 * @package Tutor
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.0.0
 */

namespace TUTOR;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Assets class
 *
 * @since 1.0.0
 */
class Assets {

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function __construct() {
		 /**
		 * Common scripts loading
		 */
		add_action( 'admin_enqueue_scripts', array( $this, 'common_scripts' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'common_scripts' ) );
		/**
		 * Front and backend script enqueue
		 */
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'frontend_scripts' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'load_meta_data' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'load_meta_data' ) );

		/**
		 * Text domain loading
		 */
		add_action( 'admin_enqueue_scripts', array( $this, 'tutor_script_text_domain' ), 100 );
		add_action( 'wp_enqueue_scripts', array( $this, 'tutor_script_text_domain' ), 100 );
		add_filter( 'tutor_localize_data', array( $this, 'modify_localize_data' ) );

		/**
		 * Register translatable function to load
		 * Handled script with text domain attached to
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
		add_filter( 'mce_external_languages', array( $this, 'tutor_tinymce_translate' ) );

		/**
		 * Identifier class to body tag
		 *
		 * @since v1.9.9
		 */
		add_filter( 'body_class', array( $this, 'add_identifier_class_to_body' ) );
		add_filter( 'admin_body_class', array( $this, 'add_identifier_class_to_body' ) );

		/**
		 * Add edit with front end builder button on Gutenberg editor
		 *
		 * @since v2.0.5
		 */
		add_action( 'enqueue_block_editor_assets', __CLASS__ . '::add_frontend_editor_button' );
	}

	/**
	 * Load default localized data
	 *
	 * @since 1.0.0
	 * @return array
	 */
	private function get_default_localized_data() {
		$home_url = get_home_url();
		$parsed   = parse_url( $home_url );

		$base_path = ( is_array( $parsed ) && isset( $parsed['path'] ) ) ? $parsed['path'] : '/';
		$base_path = rtrim( $base_path, '/' ) . '/';

		$post_id   = get_the_ID();
		$post_type = get_post_type( $post_id );

		$current_page = tutor_utils()->get_current_page_slug();

		/**
		 * Only required current user data.
		 *
		 * @since 2.6.2
		 */
		$current_user = array();
		$userdata     = get_userdata( get_current_user_id() );

		if ( $userdata ) {
			$current_user = array(
				'roles' => $userdata->roles,
				'data'  => array(
					'id'           => $userdata->ID,
					'display_name' => $userdata->display_name,
				),
			);
		}

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
			'current_user'                 => $current_user,
			'content_change_event'         => 'tutor_content_changed_event',
			'is_tutor_course_edit'         => isset( $_GET['action'] ) && 'edit' === $_GET['action'] && tutor()->course_post_type === get_post_type( get_the_ID() ) ? true : false,
			'assignment_max_file_allowed'  => 'tutor_assignments' === $post_type ? (int) tutor_utils()->get_assignment_option( $post_id, 'upload_files_limit' ) : 0,
			'current_page'                 => $current_page,
			'quiz_answer_display_time'     => 1000 * (int) tutor_utils()->get_option( 'quiz_answer_display_time' ),
			'is_ssl'                       => is_ssl(),
			'course_list_page_url'         => admin_url( 'admin.php?page=tutor' ),
			'course_post_type'             => tutor()->course_post_type,
		);
	}

	/**
	 * Enqueue scripts for admin
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function admin_scripts() {
		wp_enqueue_style( 'tutor-select2', tutor()->url . 'assets/packages/select2/select2.min.css', array(), TUTOR_VERSION );
		wp_enqueue_style( 'tutor-admin', tutor()->url . 'assets/css/tutor-admin.min.css', array(), TUTOR_VERSION );
		/**
		 * Scripts
		 */
		wp_enqueue_media();

		wp_enqueue_script( 'wp-color-picker' );
		wp_enqueue_style( 'wp-color-picker' );

		wp_enqueue_script( 'jquery-ui-slider' );
		wp_enqueue_script( 'jquery-ui-datepicker' );

		wp_enqueue_script( 'tutor-select2', tutor()->url . 'assets/packages/select2/select2.full.min.js', array( 'jquery' ), TUTOR_VERSION, true );
		wp_enqueue_script( 'tutor-admin', tutor()->url . 'assets/js/tutor-admin.min.js', array( 'jquery', 'tutor-script', 'wp-color-picker', 'wp-i18n', 'wp-data' ), TUTOR_VERSION, true );
	}

	/**
	 * Load frontend scripts
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function frontend_scripts() {
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
		if ( get_the_ID() === $tutor_dashboard_page_id ) {
			wp_enqueue_media();
		}

		/**
		 * Enabling Sorting, draggable, droppable...
		 */
		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_script( 'jquery-touch-punch', array( 'jquery-ui-sortable' ) ); //phpcs:ignore

		// Plyr.
		if ( is_single_course( true ) ) {
			wp_enqueue_style( 'tutor-plyr', tutor()->url . 'assets/packages/plyr/plyr.css', array(), TUTOR_VERSION );
			wp_enqueue_script( 'tutor-plyr', tutor()->url . 'assets/packages/plyr/plyr.polyfilled.min.js', array( 'jquery' ), TUTOR_VERSION, true );
		}

		// Social Share.
		wp_enqueue_script( 'tutor-social-share', tutor()->url . 'assets/packages/SocialShare/SocialShare.min.js', array( 'jquery' ), TUTOR_VERSION, true );

		/**
		 * Chart Data
		 */
		if ( ! empty( $wp_query->query_vars['tutor_dashboard_page'] ) ) {
			wp_enqueue_script( 'jquery-ui-slider' );

			wp_enqueue_style( 'tutor-select2', tutor()->url . 'assets/packages/select2/select2.min.css', array(), TUTOR_VERSION );
			wp_enqueue_script( 'tutor-select2', tutor()->url . 'assets/packages/select2/select2.full.min.js', array( 'jquery' ), TUTOR_VERSION, true );

			if ( 'earning' === $wp_query->query_vars['tutor_dashboard_page'] ) {
				wp_enqueue_script( 'tutor-front-chart-js', tutor()->url . 'assets/js/lib/Chart.bundle.min.js', array(), TUTOR_VERSION );
				wp_enqueue_script( 'jquery-ui-datepicker' );
			}
		}
		/**
		 * Dependency wp-i18n added for translate js file
		 *
		 * @since 1.9.0
		 */
		wp_enqueue_style( 'tutor-frontend', tutor()->url . 'assets/css/tutor-front.min.css', array(), TUTOR_VERSION );
		wp_enqueue_script( 'tutor-frontend', tutor()->url . 'assets/js/tutor-front.min.js', array( 'jquery', 'wp-i18n' ), TUTOR_VERSION, true );

		/**
		 * Load frontend dashboard style
		 *
		 * @since v1.9.8
		 */
		$should_load_dashboard_styles = apply_filters( 'tutor_should_load_dashboard_styles', tutor_utils()->is_tutor_frontend_dashboard() );
		if ( $should_load_dashboard_styles ) {
			wp_enqueue_style( 'tutor-frontend-dashboard-css', tutor()->url . 'assets/css/tutor-frontend-dashboard.min.css', array(), TUTOR_VERSION );
		}

		// Load date picker for announcement at frontend.
		wp_enqueue_script( 'jquery-ui-datepicker' );
		$css = '.mce-notification.mce-notification-error{display: none !important;}';
		wp_add_inline_style( 'tutor-frontend', $css );
	}

	/**
	 * Modify localize data
	 *
	 * @since 1.0.0
	 *
	 * @param array $localize_data localize data.
	 * @return array
	 */
	public function modify_localize_data( $localize_data ) {
		global $post;

		if ( is_admin() ) {
			$taxonomy = Input::get( 'taxonomy' );
			if ( 'course-category' === $taxonomy || 'course-tag' === $taxonomy ) {
				$localize_data['open_tutor_admin_menu'] = true;
			}
		} else {

			// Assign quiz option.
			if ( ! empty( $post->post_type ) && 'tutor_quiz' === $post->post_type ) {
				$single_quiz_options = (array) tutor_utils()->get_quiz_option( $post->ID );
				$saved_quiz_options  = array(
					'quiz_when_time_expires' => tutor_utils()->get_option( 'quiz_when_time_expires' ),
				);

				$quiz_options = array_merge( $single_quiz_options, $saved_quiz_options );

				$previous_attempts = tutor_utils()->quiz_attempts();

				if ( $previous_attempts && count( $previous_attempts ) ) {
					$quiz_options['quiz_auto_start'] = 0;
				}

				$localize_data['quiz_options'] = $quiz_options;
			}

			// Including player assets if video exists.
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

	/**
	 * Load common scripts for frontend and backend
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function common_scripts() {

		/**
		 * Load TinyMCE for tutor settings page if tutor pro is not available.
		 *
		 * @since v2.0.8
		 */
		$baseurl      = includes_url( 'js/tinymce' );
		$current_page = Input::get( 'page' );

		// If it is settings page & tutor pro not activated.
		if ( 'tutor_settings' === $current_page && ! wp_script_is( 'wp-tinymce-root' ) ) {
			wp_enqueue_script( 'tutor-tiny', $baseurl . '/tinymce.min.js', array( 'jquery' ), TUTOR_VERSION, true );
		}
		wp_enqueue_style( 'tutor-icon', tutor()->url . 'assets/css/tutor-icon.min.css', array(), TUTOR_VERSION );

		// Common css library.
		if ( is_rtl() ) {
			wp_enqueue_style( 'tutor', tutor()->url . 'assets/css/tutor.rtl.min.css', array(), TUTOR_VERSION );
		} else {
			wp_enqueue_style( 'tutor', tutor()->url . 'assets/css/tutor.min.css', array(), TUTOR_VERSION );
		}

		// Load course builder resources.
		$load_course_builder_scripts = apply_filters( 'tutor_load_course_builder_scripts', tutor_utils()->get_course_builder_screen() );
		if ( $load_course_builder_scripts ) {
			wp_enqueue_script( 'tutor-course-builder', tutor()->url . 'assets/js/tutor-course-builder.min.js', array( 'jquery', 'wp-i18n' ), TUTOR_VERSION, true );
			wp_enqueue_style( 'tutor-course-builder-css', tutor()->url . 'assets/css/tutor-course-builder.min.css', array(), TUTOR_VERSION );
		}
		/**
		 * Load tutor common scripts both backend and frontend
		 *
		 * @since v2.0.0
		 */
		wp_enqueue_script( 'tutor-script', tutor()->url . 'assets/js/tutor.min.js', array( 'jquery', 'wp-i18n' ), TUTOR_VERSION, true );

		/**
		 * Enqueue datetime countdown scripts & styles
		 *
		 * Add filter to enqueue countdown scripts & styles
		 * don't return false if it is true to prevent conflict
		 * with other filters
		 *
		 * @since v2.1.0
		 */
		$should_enqueue = apply_filters( 'tutor_should_enqueue_countdown_scripts', false );
		if ( $should_enqueue ) {
			wp_enqueue_script( 'tutor-moment', tutor()->url . 'assets/js/lib/countdown/moment.min.js', array(), TUTOR_VERSION, true );

			wp_enqueue_script( 'tutor-moment-timezone', tutor()->url . 'assets/js/lib/countdown/moment-timezone-with-data.min.js', array(), TUTOR_VERSION, true );

			wp_enqueue_script( 'tutor-jquery-countdown', tutor()->url . 'assets/js/lib/countdown/jquery.countdown.min.js', array( 'jquery' ), TUTOR_VERSION, true );

			wp_enqueue_script( 'tutor-countdown', tutor()->url . 'assets/js/lib/countdown/tutor-countdown.min.js', array( 'jquery' ), TUTOR_VERSION, true );

			wp_enqueue_style( 'tutor-countdown', tutor()->url . 'assets/js/lib/countdown/tutor-countdown.min.css', '', TUTOR_VERSION );
		}
	}

	/**
	 * Load meta data
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function load_meta_data() {
		// Localize scripts.
		$localize_data = apply_filters( 'tutor_localize_data', $this->get_default_localized_data() );
		wp_localize_script( 'tutor-frontend', '_tutorobject', $localize_data );
		wp_localize_script( 'tutor-admin', '_tutorobject', $localize_data );
		wp_localize_script( 'tutor-course-builder', '_tutorobject', $localize_data );
		wp_localize_script( 'tutor-script', '_tutorobject', $localize_data );

		// Inline styles.
		wp_add_inline_style( 'tutor-frontend', $this->load_color_palette() );
		wp_add_inline_style( 'tutor-admin', $this->load_color_palette() );
	}

	/**
	 * Load color palette
	 *
	 * @since 1.0.0
	 * @return string
	 */
	private function load_color_palette() {
		 $colors = array(
			 'tutor_primary_color'       => '--tutor-color-primary',
			 'tutor_primary_hover_color' => '--tutor-color-primary-hover',
			 'tutor_text_color'          => '--tutor-body-color',
			 'tutor_border_color'        => '--tutor-border-color',
			 'tutor_gray_color'          => '--tutor-color-gray',
		 );

		 // Admin colors.
		 $admin_colors = array();
		 if ( is_admin() ) {
			 $admin_color = get_user_option( 'admin_color' );

			 switch ( $admin_color ) {
				 case 'light':
					 $admin_color_codes = array( '#04a4cc', '#04b0db' );
					 break;

				 case 'modern':
					 $admin_color_codes = array( '#3858e9', '#4664eb' );
					 break;

				 case 'blue':
					 $admin_color_codes = array( '#e1a948', '#e3af55' );
					 break;

				 case 'coffee':
					 $admin_color_codes = array( '#c7a589', '#ccad93' );
					 break;

				 case 'ectoplasm':
					 $admin_color_codes = array( '#a3b745', '#a9bd4f' );
					 break;

				 case 'midnight':
					 $admin_color_codes = array( '#e14d43', '#e35950' );
					 break;

				 case 'ocean':
					 $admin_color_codes = array( '#9ebaa0', '#a7c0a9' );
					 break;

				 case 'sunrise':
					 $admin_color_codes = array( '#dd823b', '#df8a48' );
					 break;

				 default:
					 $admin_color_codes = array( '#007cba', '#006ba1' );
					 break;
			 }

			 $admin_colors = array(
				 '--tutor-color-primary'       => $admin_color_codes[0],
				 '--tutor-color-primary-hover' => $admin_color_codes[1],
			 );
		 }

		 $fallback_colors = array(
			 'tutor_primary_color'       => '#3E64DE',
			 'tutor_primary_hover_color' => '#395BCA',
			 'tutor_text_color'          => '#212327',
			 'tutor_border_color'        => '#E3E5EB',
			 'tutor_gray_color'          => '#CDCFD5',
		 );

		 $color_string = '';
		 foreach ( $colors as $key => $property ) {
			 $fallback_color = isset( $fallback_colors[ $key ] ) ? $fallback_colors[ $key ] : '#212327';
			 $color          = tutor_utils()->get_option( $key, $fallback_color );
			 $color_rgb      = tutor_utils()->hex2rgb( $color );

			 if ( is_admin() && isset( $admin_colors[ $property ] ) ) {
				 $color     = $admin_colors[ $property ];
				 $color_rgb = tutor_utils()->hex2rgb( $admin_colors[ $property ] );
			 }

			 if ( $color ) {
				 $color_string .= $property . ':' . $color . ';';
			 }

			 if ( $color_rgb ) {
				 $color_string .= $property . '-rgb:' . $color_rgb . ';';
			 }
		 }

		 return ':root{' . $color_string . '}';
	}

	/**
	 * Add Tinymce button for placing shortcode
	 *
	 * @since 1.0.0
	 * @return void|null
	 */
	public function tutor_add_mce_button() {
		// Check user permissions.
		if ( ! current_user_can( 'edit_posts' ) && ! current_user_can( 'edit_pages' ) ) {
			return;
		}
		// Check if WYSIWYG is enabled.
		if ( 'true' == get_user_option( 'rich_editing' ) ) {
			add_filter( 'mce_external_plugins', array( $this, 'tutor_add_tinymce_js' ) );
			add_filter( 'mce_buttons', array( $this, 'tutor_register_mce_button' ) );
		}
	}

	/**
	 * Add tinymce button
	 *
	 * @since 1.0.0
	 *
	 * @param array $plugin_array plugin array.
	 * @return array
	 */
	public function tutor_add_tinymce_js( $plugin_array ) {
		$plugin_array['tutor_button'] = tutor()->url . 'assets/js/lib/mce-button.js';
		return $plugin_array;
	}

	/**
	 * Register new button in the editor
	 *
	 * @since 1.0.0
	 *
	 * @param array $buttons buttons.
	 * @return array
	 */
	public function tutor_register_mce_button( $buttons ) {
		array_push( $buttons, 'tutor_button' );
		return $buttons;
	}

	/**
	 * Output generator tag to aid debugging.
	 *
	 * @since 1.0.0
	 *
	 * @param string $gen Generator.
	 * @param string $type Type.
	 *
	 * @return string
	 */
	public function tutor_generator_tag( $gen, $type ) {
		switch ( $type ) {
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
	 * Load text domain handled script after all enqueue_scripts
	 * registered functions
	 *
	 * @since 1.9.0
	 * @return void
	 */
	public function tutor_script_text_domain() {
		wp_set_script_translations( 'tutor-frontend', 'tutor', tutor()->path . 'languages/' );
		wp_set_script_translations( 'tutor-admin', 'tutor', tutor()->path . 'languages/' );
		wp_set_script_translations( 'tutor-course-builder', 'tutor', tutor()->path . 'languages/' );
	}

	/**
	 * Add translation support for external tinyMCE button
	 *
	 * @since 1.9.7
	 * @return array
	 */
	public function tutor_tinymce_translate() {
		$locales['tutor_button'] = tutor()->path . 'includes/tinymce_translate.php';
		return $locales;
	}

	/**
	 * Add an identifier class to body
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $classes classes.
	 * @return mixed
	 */
	public function add_identifier_class_to_body( $classes ) {
		$course_builder_screen = tutor_utils()->get_course_builder_screen();
		$to_add                = array( 'tutor-lms' );

		// Add backend course editor identifier class to body.
		if ( $course_builder_screen ) {
			$to_add[] = is_admin() ? 'tutor-backend' : 'tutor-frontend';
			$to_add[] = ' tutor-screen-course-builder tutor-screen-course-builder-' . $course_builder_screen . ' ';
		}

		// Add frontend course builder identifier class.
		if ( ! $course_builder_screen && tutor_utils()->is_tutor_frontend_dashboard() ) {
			$to_add[] = 'tutor-screen-frontend-dashboard';
		}

		if ( is_post_type_archive( tutor()->course_post_type ) ) {
			$to_add[] = 'tutor-frontend';
		}

		if ( tutor_utils()->is_tutor_frontend_dashboard() ) {
			$to_add[] = 'tutor-frontend';
		}

		if ( is_single() ) {
			global $post;

			$post_types = array(
				tutor()->course_post_type,
				tutor()->lesson_post_type,
				tutor()->quiz_post_type,
				tutor()->assignment_post_type,
				tutor()->zoom_post_type,
				tutor()->meet_post_type,
			);

			if ( isset( $post->post_type ) && in_array( $post->post_type, $post_types, true ) ) {
				$to_add[] = 'tutor-frontend';
			}
		}

		if ( is_admin() ) {
			$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
			$base   = ( $screen && is_object( $screen ) && property_exists( $screen, 'base' ) ) ? $screen->base : '';
			$index  = strpos( $base, 'tutor' );

			if ( 0 === $index || $index > 0 ) {
				$to_add[] = 'tutor-backend';

				$page = Input::get( 'page' );
				if ( 'tutor_settings' === $page ) {
					$to_add[] = 'tutor-screen-backend-settings ';
				}
				if ( ! empty( $page ) ) {
					$to_add[] = 'tutor-backend-' . $page;
				}
			}
		}

		// Remove duplicate classes if any.
		$to_add = array_unique( $to_add );

		if ( is_array( $classes ) ) {
			$classes = array_merge( $classes, $to_add );
		} else {
			$classes .= implode( ' ', $to_add );
		}

		return $classes;
	}

	/**
	 * Enqueue script for adding edit with frontend course builder button
	 * on the Gutenberg editor
	 *
	 * @since 2.0.5
	 * @return void
	 */
	public static function add_frontend_editor_button() {
		$wp_screen = get_current_screen();

		if ( is_a( $wp_screen, 'WP_Screen' ) && tutor()->course_post_type === $wp_screen->post_type ) {
			wp_enqueue_script( 'tutor-gutenberg', tutor()->url . 'assets/js/tutor-gutenberg.min.js', array(), TUTOR_VERSION, true );
			$data = array(
				'frontend_dashboard_url' => esc_url( trailingslashit( tutor_utils()->tutor_dashboard_url( 'create-course' ) ) ) . '?course_ID=' . get_the_ID(),
			);

			wp_add_inline_script(
				'tutor-gutenberg',
				'const tutorInlineData =' . json_encode( $data ),
				'before'
			);
		}
	}
}
