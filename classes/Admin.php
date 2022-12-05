<?php
/**
 * Manage admin menu and plugin related logic
 *
 * @package Tutor
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.0.0
 */

namespace TUTOR;

use TUTOR\Input;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin Class
 *
 * @since 1.0.0
 */
class Admin {
	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
		// Force activate menu for necessary.
		add_filter( 'parent_file', array( $this, 'parent_menu_active' ) );
		add_filter( 'submenu_file', array( $this, 'submenu_file_active' ), 10, 2 );

		add_action( 'admin_init', array( $this, 'filter_posts_for_instructors' ) );
		add_action( 'load-post.php', array( $this, 'check_if_current_users_post' ) );

		add_action( 'admin_action_uninstall_tutor_and_erase', array( $this, 'erase_tutor_data' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( TUTOR_FILE ), array( $this, 'plugin_action_links' ) );

		// Plugin Row Meta.
		add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2 );

		// Admin Footer Text.
		add_filter( 'admin_footer_text', array( $this, 'admin_footer_text' ), 1 );
		// Register Course Widget.
		add_action( 'widgets_init', array( $this, 'register_course_widget' ) );

		// Handle flash toast message for redirect_to util helper.
		add_action( 'admin_head', array( new Utils(), 'handle_flash_message' ), 999 );
	}

	/**
	 * Register admin menus
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register_menu() {
		$has_pro = tutor()->has_pro;

		$unanswered_questions = tutor_utils()->unanswered_question_count();
		$unanswered_bubble    = '';
		if ( $unanswered_questions ) {
			$unanswered_bubble = '<span class="update-plugins count-' . $unanswered_questions . '"><span class="plugin-count">' . $unanswered_questions . '</span></span>';
		}

		$course_post_type = tutor()->course_post_type;

		$pro_text = '';
		if ( $has_pro ) {
			$pro_text = ' ' . __( 'Pro', 'tutor' );
		}

		$enable_course_marketplace = (bool) tutor_utils()->get_option( 'enable_course_marketplace' );

		$welcome = Tutor_Setup::is_welcome_page_visited();

		$root_page = array( $this, 'tutor_course_list' );
		if ( false === $welcome && Input::has( 'page' ) && 'tutor' === Input::get( 'page' ) && Input::has( 'welcome' ) ) {
			$root_page = array( $this, 'welcome_page' );
		}

		$icon_base64_uri = 'data:image/svg+xml;base64,' . base64_encode( '<svg fill="none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1139"><defs/><path fill-rule="evenodd" clip-rule="evenodd" d="M341.652 622.4c-23.412 0-43.222-19.8-43.222-43.2v-99c0-23.4 19.81-43.2 43.222-43.2 23.412 0 43.222 19.8 43.222 43.2v99c0 23.4-18.009 43.2-43.222 43.2 1.801 0 1.801 0 0 0zM655.01 622.4c-23.412 0-43.222-18-43.222-43.2v-99c0-23.4 19.81-43.2 43.222-43.2 23.412 0 43.222 19.8 43.222 43.2v99c0 25.2-19.81 43.2-43.222 43.2z" fill="#9DA2A8"/><path fill-rule="evenodd" clip-rule="evenodd" d="M255.204 406.4c18.009-37.8 54.028-63 95.449-63 61.231 1.8 108.055 52.2 106.254 113.4v203.4c3.602 21.6 23.412 37.8 45.023 34.2 18.009-1.8 32.416-16.2 34.217-34.2V455c-1.801-61.2 46.824-111.6 106.254-113.4 39.621 0 75.639 21.6 93.648 59.4 68.435 133.2 14.407 295.2-117.06 363.6C487.523 833 325.44 779 258.806 647.6c-39.62-75.6-39.62-165.6-3.602-241.2zM426.291 140h151.277v59.4c-25.212-5.4-52.226-9-77.439-9-25.213 0-50.426 3.6-75.639 7.2l1.801-57.6zm414.211 388.8c0-122.4-66.634-235.8-172.888-295.2V140h64.833c25.213 0 45.023-19.8 45.023-45s-21.611-45-45.023-45H271.413C246.2 51.8 226.39 71.6 226.39 96.8c0 25.2 19.81 45 45.023 45h66.633v91.8c-163.883 90-230.517 298.8-135.068 459 3.602 5.4-3.602-5.4 0 0C338.046 930.2 687.424 948.2 802.683 950c10.805 0 19.81-3.6 27.014-10.8 7.203-7.2 10.805-18 10.805-27V528.8z" fill="#9DA2A8"/></svg>' );
		$menu_position   = 2;

		add_menu_page(
			__( 'Tutor LMS', 'tutor' ) . $pro_text,
			__( 'Tutor LMS', 'tutor' ) . $pro_text,
			'manage_tutor_instructor',
			'tutor',
			$root_page,
			$icon_base64_uri,
			$menu_position
		);

		// Added @since v2.0.0.
		add_submenu_page( 'tutor', __( 'Courses', 'tutor' ), __( 'Courses', 'tutor' ), 'manage_tutor_instructor', 'tutor', array( $this, 'tutor_course_list' ) );

		add_submenu_page( 'tutor', __( 'Categories', 'tutor' ), __( 'Categories', 'tutor' ), 'manage_tutor', 'edit-tags.php?taxonomy=course-category&post_type=' . $course_post_type, null );

		add_submenu_page( 'tutor', __( 'Tags', 'tutor' ), __( 'Tags', 'tutor' ), 'manage_tutor', 'edit-tags.php?taxonomy=course-tag&post_type=' . $course_post_type, null );

		add_submenu_page( 'tutor', __( 'Students', 'tutor' ), __( 'Students', 'tutor' ), 'manage_tutor', Students_List::STUDENTS_LIST_PAGE, array( $this, 'tutor_students' ) );

		if ( $enable_course_marketplace ) {
			add_submenu_page( 'tutor', __( 'Instructors', 'tutor' ), __( 'Instructors', 'tutor' ), 'manage_tutor', Instructors_List::INSTRUCTOR_LIST_PAGE, array( $this, 'tutor_instructors' ) );
		}

		add_submenu_page( 'tutor', __( 'Announcements', 'tutor' ), __( 'Announcements', 'tutor' ), 'manage_tutor_instructor', 'tutor_announcements', array( $this, 'tutor_announcements' ) );

		add_submenu_page( 'tutor', __( 'Q & A', 'tutor' ), __( 'Q & A ', 'tutor' ) . $unanswered_bubble, 'manage_tutor_instructor', Question_Answers_List::QUESTION_ANSWER_PAGE, array( $this, 'question_answer' ) );

		add_submenu_page( 'tutor', __( 'Quiz Attempts', 'tutor' ), __( 'Quiz Attempts', 'tutor' ), 'manage_tutor_instructor', Quiz_Attempts_List::QUIZ_ATTEMPT_PAGE, array( $this, 'quiz_attempts' ) );

		if ( $enable_course_marketplace ) {
			add_submenu_page( 'tutor', __( 'Withdraw Requests', 'tutor' ), __( 'Withdraw Requests', 'tutor' ), 'manage_tutor', Withdraw_Requests_List::WITHDRAW_REQUEST_LIST_PAGE, array( $this, 'withdraw_requests' ) );
		}

		add_submenu_page( 'tutor', __( 'Add-ons', 'tutor' ), __( 'Add-ons', 'tutor' ), 'manage_tutor', 'tutor-addons', array( $this, 'enable_disable_addons' ) );

		do_action( 'tutor_admin_register' );

		add_submenu_page( 'tutor', __( 'Tools', 'tutor' ), __( 'Tools', 'tutor' ), 'manage_tutor', 'tutor-tools', array( new \TUTOR\Tools_V2(), 'load_tools_page' ) );

		add_submenu_page( 'tutor', __( 'Settings', 'tutor' ), __( 'Settings', 'tutor' ), 'manage_tutor', 'tutor_settings', array( new \TUTOR\Options_V2(), 'load_settings_page' ) );

		if ( ! $has_pro ) {
			add_submenu_page( 'tutor', __( 'Get Pro', 'tutor' ), __( '<span class="dashicons dashicons-awards tutor-get-pro-text"></span> Get Pro', 'tutor' ), 'manage_options', 'tutor-get-pro', array( $this, 'tutor_get_pro' ) );
		}
	}

	/**
	 * Show students page
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function tutor_students() {
		include tutor()->path . 'views/pages/students.php';
	}

	/**
	 * Show instructor page
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function tutor_instructors() {
		include tutor()->path . 'views/pages/instructors.php';
	}

	/**
	 * Show announcements page
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function tutor_announcements() {
		include tutor()->path . 'views/pages/announcements.php';
	}

	/**
	 * Show Q&A page
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function question_answer() {
		include tutor()->path . 'views/pages/question_answer.php';
	}

	/**
	 * Show quiz attempts page
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function quiz_attempts() {
		include tutor()->path . 'views/pages/quiz_attempts.php';
	}

	/**
	 * Show the withdraw requests table
	 *
	 * @since 1.2.0
	 * @return void
	 */
	public function withdraw_requests() {
		include tutor()->path . 'views/pages/withdraw_requests.php';
	}

	/**
	 * Enable or disable addons
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function enable_disable_addons() {

		if ( defined( 'TUTOR_PRO_VERSION' ) ) {
			include tutor()->path . 'views/pages/enable_disable_addons.php';
		} else {
			include tutor()->path . 'views/pages/tutor-pro-addons.php';
		}
	}

	/**
	 * Tutor tools page (OLD)
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function tutor_tools_old() {
		$tutor_admin_tools_page = Input::get( 'tutor_admin_tools_page' );
		if ( $tutor_admin_tools_page ) {
			include apply_filters( 'tutor_admin_tools_page', tutor()->path . "views/pages/{$tutor_admin_tools_page}.php", $tutor_admin_tools_page );
		} else {
			$pages = apply_filters(
				'tutor_tool_pages',
				array(
					'tutor_pages' => array( 'title' => __( 'Tutor Pages', 'tutor' ) ),
					'status'      => __( 'Status', 'tutor' ),
				)
			);

			$current_page   = 'tutor_pages';
			$requested_page = Input::get( 'sub_page' );
			if ( $requested_page ) {
				$current_page = $requested_page;
			}

			$current_page = str_replace( '/', '', $current_page );
			$current_page = str_replace( '.', '', $current_page );
			$current_page = str_replace( '\\', '', $current_page );
			$current_page = trim( $current_page );

			include tutor()->path . 'views/pages/tools.php';
		}
	}

	/**
	 * Show pro upgrade page
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function tutor_get_pro() {
		include tutor()->path . 'views/pages/get-pro.php';
	}

	/**
	 * Parent menu active
	 *
	 * @since 1.0.0
	 *
	 * @param string $parent_file parent file.
	 * @return string
	 */
	public function parent_menu_active( $parent_file ) {
		$taxonomy = Input::get( 'taxonomy' );
		if ( 'course-category' === $taxonomy || 'course-tag' === $taxonomy ) {
			return 'tutor';
		}

		return $parent_file;
	}

	/**
	 * Sub-menu active
	 *
	 * @since 1.0.0
	 *
	 * @param string $submenu_file submenu file.
	 * @param string $parent_file parent file.
	 *
	 * @return string
	 */
	public function submenu_file_active( $submenu_file, $parent_file ) {
		$taxonomy         = Input::get( 'taxonomy' );
		$course_post_type = tutor()->course_post_type;

		if ( 'course-category' === $taxonomy ) {
			return 'edit-tags.php?taxonomy=course-category&post_type=' . $course_post_type;
		}
		if ( 'course-tag' === $taxonomy ) {
			return 'edit-tags.php?taxonomy=course-tag&post_type=' . $course_post_type;
		}

		return $submenu_file;
	}

	/**
	 * Filter posts for instructor
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function filter_posts_for_instructors() {
		if ( ! current_user_can( 'administrator' ) && current_user_can( tutor()->instructor_role ) ) {
			@remove_menu_page( 'edit-comments.php' ); // Comments.
			add_filter( 'posts_clauses_request', array( $this, 'posts_clauses_request' ) );
		}
	}

	/**
	 * Request for posts clauses
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $clauses clauses.
	 * @return mixed
	 */
	public function posts_clauses_request( $clauses ) {

		if ( ! is_admin() || ( ! Input::has( 'post_type' ) || Input::get( 'post_type' ) != tutor()->course_post_type ) || tutor_utils()->has_user_role( array( 'administrator', 'editor' ) ) ) {
			return $clauses;
		}

		// Need multi instructor check.
		global $wpdb;

		$user_id = get_current_user_id();

		$get_assigned_courses_ids = $wpdb->get_col( $wpdb->prepare( "SELECT meta_value from {$wpdb->usermeta} WHERE meta_key = '_tutor_instructor_course_id' AND user_id = %d", $user_id ) );
		$own_courses              = is_array( $get_assigned_courses_ids ) ? $get_assigned_courses_ids : array();

		$in_query_pre   = count( $own_courses ) ? implode( ',', $own_courses ) : null;
		$in_query_where = $in_query_pre ? " OR {$wpdb->posts}.ID IN({$in_query_pre})" : '';

		$course_post_type    = tutor()->course_post_type;
		$custom_author_query = "  AND ({$wpdb->posts}.post_type!='{$course_post_type}' OR {$wpdb->posts}.post_author = {$user_id}) {$in_query_where}";

		$clauses['where'] .= $custom_author_query;

		return $clauses;
	}

	/**
	 * Prevent unauthorised course/lesson edit page by direct URL
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function check_if_current_users_post() {
		if ( current_user_can( 'administrator' ) || ! current_user_can( tutor()->instructor_role ) ) {
			return;
		}

		if ( ! empty( Input::get( 'post' ) ) ) {
			$get_post_id      = Input::get( 'post', Input::TYPE_INT );
			$get_post         = get_post( $get_post_id );
			$tutor_post_types = array( tutor()->course_post_type, tutor()->lesson_post_type );

			$current_user = get_current_user_id();

			if ( in_array( $get_post->post_type, $tutor_post_types ) && $get_post->post_author != $current_user ) {
				global $wpdb;

				$get_assigned_courses_ids = (int) $wpdb->get_var(
					$wpdb->prepare(
						"SELECT user_id
					from {$wpdb->usermeta}
					WHERE user_id = %d AND meta_key = '_tutor_instructor_course_id' AND meta_value = %d ",
						$current_user,
						$get_post_id
					)
				);

				if ( ! $get_assigned_courses_ids ) {
					wp_die( esc_html__( 'Permission Denied', 'tutor' ) );
				}
			}
		}
	}

	/**
	 * Scan template files
	 *
	 * @since 1.0.0
	 *
	 * @param string $template_path template file path.
	 * @return array
	 */
	public static function scan_template_files( $template_path = null ) {
		if ( ! $template_path ) {
			$template_path = tutor()->path . 'templates/';
		}

		$files  = @scandir($template_path); // @codingStandardsIgnoreLine.
		$result = array();

		if ( ! empty( $files ) ) {
			foreach ( $files as $key => $value ) {
				if ( ! in_array( $value, array( '.', '..', '.DS_Store' ), true ) ) {
					if ( is_dir( $template_path . DIRECTORY_SEPARATOR . $value ) ) {
						$sub_files = self::scan_template_files( $template_path . DIRECTORY_SEPARATOR . $value );
						foreach ( $sub_files as $sub_file ) {
							$result[] = $value . DIRECTORY_SEPARATOR . $sub_file;
						}
					} else {
						$result[] = $value;
					}
				}
			}
		}
		return $result;
	}

	/**
	 * Get Template overridden files
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public static function template_overridden_files() {
		$template_files = self::scan_template_files();

		$override_files = array();
		foreach ( $template_files as $file ) {
			$file_path = null;
			if ( file_exists( trailingslashit( get_stylesheet_directory() ) . tutor()->template_path . $file ) ) {
				$file_path = $file;
			} elseif ( file_exists( trailingslashit( get_template_directory() ) . tutor()->template_path . $file ) ) {
				$file_path = $file;
			}

			if ( $file_path ) {
				$override_files[] = str_replace( WP_CONTENT_DIR . '/themes/', '', $file_path );
			}
		}

		return $override_files;
	}

	/**
	 * Erase tutor data
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function erase_tutor_data() {
		global $wpdb;

		$is_erase_data = tutor_utils()->get_option( 'delete_on_uninstall' );
		// Deleting Data.

		$plugin_file = tutor()->basename;
		if ( $is_erase_data && current_user_can( 'deactivate_plugin', $plugin_file ) ) {
			/**
			 * Deleting Post Type, Meta Data, taxonomy
			 */
			$course_post_type = tutor()->course_post_type;
			$lesson_post_type = tutor()->lesson_post_type;

			$post_types = array(
				$course_post_type,
				$lesson_post_type,
				'tutor_quiz',
				'tutor_enrolled',
				'topics',
				'tutor_enrolled',
				'tutor_announcements',
			);

			$post_type_strings = "'" . implode( "','", $post_types ) . "'";
			$tutor_posts       = $wpdb->get_col( "SELECT ID from {$wpdb->posts} WHERE post_type in({$post_type_strings}) ;" ); //phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared

			if ( is_array( $tutor_posts ) && count( $tutor_posts ) ) {
				foreach ( $tutor_posts as $post_id ) {
					// Delete categories.
					$terms = wp_get_object_terms( $post_id, 'course-category' );
					foreach ( $terms as $term ) {
						wp_remove_object_terms( $post_id, array( $term->term_id ), 'course-category' );
					}

					// Delete tags if available.
					$terms = wp_get_object_terms( $post_id, 'course-tag' );
					foreach ( $terms as $term ) {
						wp_remove_object_terms( $post_id, array( $term->term_id ), 'course-tag' );
					}

					// Delete All Meta.
					$wpdb->delete( $wpdb->postmeta, array( 'post_id' => $post_id ) );
					$wpdb->delete( $wpdb->posts, array( 'ID' => $post_id ) );
				}
			}

			/**
			 * Deleting Comments (reviews, questions, quiz_answers, etc)
			 */
			$tutor_comments       = $wpdb->get_col( "SELECT comment_ID from {$wpdb->comments} WHERE comment_agent = 'comment_agent' ;" );
			$comments_ids_strings = "'" . implode( "','", $tutor_comments ) . "'";
			if ( is_array( $tutor_comments ) && count( $tutor_comments ) ) {
				$wpdb->query( "DELETE from {$wpdb->commentmeta} WHERE comment_ID in({$comments_ids_strings}) " ); //phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			}
			$wpdb->delete( $wpdb->comments, array( 'comment_agent' => 'comment_agent' ) );

			/**
			 * Delete Options
			 */

			delete_option( 'tutor_option' );
			$wpdb->delete( $wpdb->usermeta, array( 'meta_key' => '_is_tutor_student' ) );
			$wpdb->delete( $wpdb->usermeta, array( 'meta_key' => '_tutor_instructor_approved' ) );
			$wpdb->delete( $wpdb->usermeta, array( 'meta_key' => '_tutor_instructor_status' ) );
			$wpdb->delete( $wpdb->usermeta, array( 'meta_key' => '_is_tutor_instructor' ) );
			$wpdb->query( "DELETE FROM {$wpdb->usermeta} WHERE meta_key LIKE  '%_tutor_completed_lesson_id_%' " );

			// Deleting Table.
			$prefix = $wpdb->prefix;
			//phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$wpdb->query( "DROP TABLE IF EXISTS {$prefix}tutor_quiz_attempts, {$prefix}tutor_quiz_attempt_answers, {$prefix}tutor_quiz_questions, {$prefix}tutor_quiz_question_answers, {$prefix}tutor_earnings, {$prefix}tutor_withdraws " );

			deactivate_plugins( $plugin_file );
		}

		wp_redirect( 'plugins.php' );
		die();
	}

	/**
	 * Plugin activation link
	 *
	 * @since 1.0.0
	 *
	 * @param array $actions action list.
	 * @return array
	 */
	public function plugin_action_links( $actions ) {
		$has_pro = tutor()->has_pro;

		if ( ! $has_pro ) {
			$actions['tutor_pro_link'] =
				'<a href="https://www.themeum.com/product/tutor-lms/#pricing?utm_source=tutor_plugin_action_link&utm_medium=wordpress_dashboard&utm_campaign=go_premium" target="_blank">
					<span style="color: #ff7742; font-weight: bold;">' .
						__( 'Upgrade to Pro', 'wp-megamenu' ) .
					'</span>
				</a>';
		}

		$actions['settings'] = '<a href="admin.php?page=tutor_settings">' . __( 'Settings', 'tutor' ) . '</a>';

		return $actions;
	}

	/**
	 * Add plugin meta data in WP plugins list page
	 *
	 * @since 1.0.0
	 *
	 * @param array  $plugin_meta plugin meta data.
	 * @param string $plugin_file plugin file.
	 *
	 * @return array
	 */
	public function plugin_row_meta( $plugin_meta, $plugin_file ) {

		if ( tutor()->basename === $plugin_file ) {
			$plugin_meta[] = sprintf(
				'<a href="%s">%s</a>',
				esc_url( 'https://docs.themeum.com/tutor-lms/?utm_source=tutor&utm_medium=plugins_installation_list&utm_campaign=plugin_docs_link' ),
				__( '<strong style="color: #03bd24">Documentation</strong>', 'tutor' )
			);
			$plugin_meta[] = sprintf(
				'<a href="%s">%s</a>',
				esc_url( 'https://www.themeum.com/contact-us/?utm_source=tutor&utm_medium=plugins_installation_list&utm_campaign=plugin_support_link' ),
				__( '<strong style="color: #03bd24">Get Support</strong>', 'tutor' )
			);
		}

		return $plugin_meta;
	}

	/**
	 * Add footer text only on tutor pages
	 *
	 * @since 1.0.0
	 *
	 * @param string $footer_text footer text.
	 * @return string
	 */
	public function admin_footer_text( $footer_text ) {
		$current_screen = get_current_screen();

		/**
		 * We are making sure that this message will be only on Tutor Admin page
		 */
		if ( apply_filters( 'tutor_display_admin_footer_text', ( tutor_utils()->array_get( 'parent_base', $current_screen ) === 'tutor' ) ) ) {
			$footer_text = sprintf(
				/* translators: %s: plugin name */
				__( 'If you like %1$s please leave us a %2$s rating. A huge thanks in advance!', 'tutor' ),
				sprintf( '<strong>%s</strong>', esc_html__( 'Tutor LMS', 'tutor' ) ),
				'<a href="https://wordpress.org/support/plugin/tutor/reviews?rate=5#new-post" target="_blank" class="tutor-rating-link">&#9733;&#9733;&#9733;&#9733;&#9733;</a>'
			);
		}

		return $footer_text;
	}

	/**
	 * Register course widget
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register_course_widget() {
		register_widget( 'Tutor\Course_Widget' );
	}

	/**
	 * Tutor Course List
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function tutor_course_list() {
		include tutor()->path . 'views/pages/course-list.php';
	}

	/**
	 * Show welcome page
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function welcome_page() {
		Tutor_Setup::mark_as_visited();
		include tutor()->path . 'views/pages/welcome.php';
		exit;
	}
}
