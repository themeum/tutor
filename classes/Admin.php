<?php
namespace TUTOR;

/**
 * Class Admin
 * @package TUTOR
 *
 * @since v.1.0.0
 */

if ( ! defined( 'ABSPATH' ) )
	exit;

class Admin{
	public function __construct() {
		add_action('admin_menu', array($this, 'register_menu'));
		//Force activate menu for necessary
		add_filter('parent_file', array($this, 'parent_menu_active'));
		add_filter('submenu_file', array($this, 'submenu_file_active'), 10, 2);

		add_action('admin_init', array($this, 'filter_posts_for_instructors'));
		add_action('load-post.php', array($this, 'check_if_current_users_post') );

		add_action('admin_action_uninstall_tutor_and_erase', array($this, 'erase_tutor_data'));
		add_filter('plugin_action_links_' . plugin_basename(TUTOR_FILE), array( $this, 'plugin_action_links' ) );

		//Plugin Row Meta
		add_filter('plugin_row_meta', array($this, 'plugin_row_meta'), 10, 2);

		//Admin Footer Text
		add_filter( 'admin_footer_text', array( $this, 'admin_footer_text' ), 1 );
		//Register Course Widget
		add_action( 'widgets_init', array($this, 'register_course_widget') );
	}

	public function register_menu(){
		$hasPro = tutor()->has_pro;

		$unanswered_questions = tutor_utils()->unanswered_question_count();
		$unanswered_bubble = '';
		if ($unanswered_questions){
			$unanswered_bubble = '<span class="update-plugins count-'.$unanswered_questions.'"><span class="plugin-count">'.$unanswered_questions.'</span></span>';
		}

		$course_post_type = tutor()->course_post_type;

		$pro_text = '';
		if ($hasPro){
			$pro_text = ' '.__('Pro', 'tutor');
		}

		$enable_course_marketplace = (bool) tutils()->get_option('enable_course_marketplace');

		add_menu_page(__('Tutor LMS', 'tutor').$pro_text, __('Tutor LMS', 'tutor').$pro_text, 'manage_tutor_instructor', 'tutor', null,
			'dashicons-welcome-learn-more', 2);
		add_submenu_page('tutor', __('Categories', 'tutor'), __('Categories', 'tutor'), 'manage_tutor', 'edit-tags.php?taxonomy=course-category&post_type='.$course_post_type, null );

		add_submenu_page('tutor', __('Tags', 'tutor'), __('Tags', 'tutor'), 'manage_tutor', 'edit-tags.php?taxonomy=course-tag&post_type='.$course_post_type, null );

		add_submenu_page('tutor', __('Students', 'tutor'), __('Students', 'tutor'), 'manage_tutor', 'tutor-students', array($this, 'tutor_students') );

        if ($enable_course_marketplace) {
            add_submenu_page('tutor', __('Instructors', 'tutor'), __('Instructors', 'tutor'), 'manage_tutor', 'tutor-instructors', array($this, 'tutor_instructors'));
        }

		add_submenu_page('tutor', __('Q & A', 'tutor'), __('Q & A '.$unanswered_bubble, 'tutor'), 'manage_tutor_instructor', 'question_answer', array($this, 'question_answer') );

		add_submenu_page('tutor', __('Quiz Attempts', 'tutor'), __('Quiz Attempts', 'tutor'), 'manage_tutor_instructor', 'tutor_quiz_attempts',array($this, 'quiz_attempts') );

		if ($enable_course_marketplace){
            add_submenu_page('tutor', __('Withdraw Requests', 'tutor'), __('Withdraw Requests', 'tutor'), 'manage_tutor_instructor', 'tutor_withdraw_requests', array($this, 'withdraw_requests') );
        }

		//add_submenu_page('tutor', __('Add-ons', 'tutor'), __('Add-ons', 'tutor'), 'manage_tutor', 'tutor-addons', array(new Addons(),'addons_page') );
		add_submenu_page( 'tutor', __( 'Add-ons', 'tutor' ), __( 'Add-ons', 'tutor' ), 'manage_tutor', 'tutor-addons', array( $this, 'enable_disable_addons' ) );

		do_action('tutor_admin_register');

		add_submenu_page('tutor', __('Settings', 'tutor'), __('Settings', 'tutor'), 'manage_tutor', 'tutor_settings', array($this, 'tutor_page') );

		add_submenu_page('tutor', __('Tools', 'tutor'), __('Tools', 'tutor'), 'manage_tutor', 'tutor-tools', array($this, 'tutor_tools') );

		if ( ! $hasPro){
			add_submenu_page( 'tutor', __( 'Get Pro', 'tutor' ), __( '<span class="dashicons dashicons-awards tutor-get-pro-text"></span> Get Pro', 'tutor' ), 'manage_options', 'tutor-get-pro', array($this, 'tutor_get_pro') );
		}
	}

	public function tutor_page(){
		$tutor_option = new Options();
		echo apply_filters('tutor/options/generated-html', $tutor_option->generate());
	}

	public function tutor_students(){
		include tutor()->path.'views/pages/students.php';
	}

	public function tutor_instructors(){
		include tutor()->path.'views/pages/instructors.php';
	}

	public function question_answer(){
		include tutor()->path.'views/pages/question_answer.php';
	}

	public function quiz_attempts(){
		include tutor()->path.'views/pages/quiz_attempts.php';
	}

	/**
	 * Show the withdraw requests table
	 *
	 * @since v.1.2.0
	 */
	public function withdraw_requests(){
		include tutor()->path.'views/pages/withdraw_requests.php';
	}

	public function enable_disable_addons(){

		if (defined('TUTOR_PRO_VERSION')) {
			include tutor()->path.'views/pages/enable_disable_addons.php';
		}else{
			include tutor()->path.'views/pages/tutor-pro-addons.php';
		}
	}

	public function tutor_tools(){
		$tutor_admin_tools_page = tutils()->array_get('tutor_admin_tools_page', $_GET);
		if ($tutor_admin_tools_page){
			include apply_filters('tutor_admin_tools_page', tutor()->path."views/pages/{$tutor_admin_tools_page}.php", $tutor_admin_tools_page);
		}else{
			$pages = apply_filters('tutor_tool_pages', array(
				'tutor_pages' => array('title' => __('Tutor Pages', 'tutor') ),
				'status' => __('Status', 'tutor'),
			));

			$current_page = 'tutor_pages';
			$requested_page = sanitize_text_field(tutils()->array_get('sub_page', $_GET));
			if ($requested_page){
				$current_page = $requested_page;
			}

			include tutor()->path.'views/pages/tools.php';
		}
	}

	public function tutor_get_pro(){
		include tutor()->path.'views/pages/get-pro.php';
	}

	public function parent_menu_active(  $parent_file ){
		$taxonomy = tutor_utils()->avalue_dot('taxonomy', $_GET);
		if ($taxonomy === 'course-category' || $taxonomy === 'course-tag'){
			return 'tutor';
		}

		return $parent_file;
	}

	public function submenu_file_active($submenu_file, $parent_file){
		$taxonomy = tutor_utils()->avalue_dot('taxonomy', $_GET);
		$course_post_type = tutor()->course_post_type;

		if ($taxonomy === 'course-category'){
			return 'edit-tags.php?taxonomy=course-category&post_type='.$course_post_type;
		}
		if ($taxonomy === 'course-tag'){
			return 'edit-tags.php?taxonomy=course-tag&post_type='.$course_post_type;
		}


		return $submenu_file;
	}

	/**
	 * Filter posts for instructor
	 */
	public function filter_posts_for_instructors(){
		if ( ! current_user_can('administrator') && current_user_can(tutor()->instructor_role)){
			remove_menu_page( 'edit-comments.php' ); //Comments
			add_filter( 'posts_clauses_request', array($this, 'posts_clauses_request') );
		}
	}

	public function posts_clauses_request($clauses){
		global $wpdb;

		$user_id = get_current_user_id();

		$get_assigned_courses_ids = $wpdb->get_col("SELECT meta_value from {$wpdb->usermeta} WHERE meta_key = '_tutor_instructor_course_id' AND user_id = {$user_id}  ");

		$custom_author_query = "AND {$wpdb->posts}.post_author = {$user_id}";
		if (is_array($get_assigned_courses_ids) && count($get_assigned_courses_ids)){
			$in_query_pre = implode(',', $get_assigned_courses_ids);
			$custom_author_query = "  AND ( {$wpdb->posts}.post_author = {$user_id} OR {$wpdb->posts}.ID IN({$in_query_pre}) ) ";
		}

		$clauses['where'] .= $custom_author_query;

		return $clauses;
	}

	/**
	 * Prevent unauthorised course/lesson edit page by direct URL
	 *
	 * @since v.1.0.0
	 */
	public function check_if_current_users_post(){
		if (current_user_can('administrator') || ! current_user_can(tutor()->instructor_role)) {
			return;
		}

		if (! empty($_GET['post']) ) {
			$get_post_id = (int) sanitize_text_field($_GET['post']);
			$get_post = get_post($get_post_id);
			$tutor_post_types = array(tutor()->course_post_type, tutor()->lesson_post_type);

			$current_user = get_current_user_id();

			if (in_array($get_post->post_type, $tutor_post_types) &&  $get_post->post_author != $current_user){
				global $wpdb;

				$get_assigned_courses_ids = (int) $wpdb->get_var("SELECT user_id from {$wpdb->usermeta} WHERE user_id = {$current_user} AND meta_key = '_tutor_instructor_course_id' AND meta_value = {$get_post_id} ");

				if ( ! $get_assigned_courses_ids){
					wp_die(__('Permission Denied', 'tutor'));
				}

			}
		}
	}

	/**
	 * Status
	 */

	public static function scan_template_files( $template_path = null ) {
		if ( ! $template_path){
			$template_path = tutor()->path.'templates/';
		}

		$files  = @scandir( $template_path ); // @codingStandardsIgnoreLine.
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
	 * @return array
	 *
	 *
	 */
	public static function template_overridden_files(){
		$template_files = self::scan_template_files();

		$override_files = array();
		foreach ($template_files as $file){
			$file_path = null;
			if (file_exists(trailingslashit(get_stylesheet_directory()).tutor()->template_path.$file)){
				$file_path = $file;
			}elseif (file_exists(trailingslashit(get_template_directory()).tutor()->template_path.$file)){
				$file_path = $file;
			}

			if ($file_path){
				$override_files[] = str_replace( WP_CONTENT_DIR.'/themes/', '', $file_path );
			}
		}

		return $override_files;
	}

	public static function get_environment_info(){

		// Figure out cURL version, if installed.
		$curl_version = '';
		if ( function_exists( 'curl_version' ) ) {
			$curl_version = curl_version();
			$curl_version = $curl_version['version'] . ', ' . $curl_version['ssl_version'];
		}


		// WP memory limit.
		$wp_memory_limit = tutor_utils()->let_to_num(WP_MEMORY_LIMIT);
		if ( function_exists( 'memory_get_usage' ) ) {
			$wp_memory_limit = max( $wp_memory_limit, tutor_utils()->let_to_num( @ini_get( 'memory_limit' ) ) );
		}

		$database_version = tutor_utils()->get_db_version();

		return array(
			'home_url'                  => get_option( 'home' ),
			'site_url'                  => get_option( 'siteurl' ),
			'version'                   => tutor()->version,
			'wp_version'                => get_bloginfo( 'version' ),
			'wp_multisite'              => is_multisite(),
			'wp_memory_limit'           => $wp_memory_limit,
			'wp_debug_mode'             => ( defined( 'WP_DEBUG' ) && WP_DEBUG ),
			'wp_cron'                   => ! ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ),
			'language'                  => get_locale(),
			'external_object_cache'     => wp_using_ext_object_cache(),
			'server_info'               => isset( $_SERVER['SERVER_SOFTWARE'] ) ? wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) : '',
			'php_version'               => phpversion(),
			'php_post_max_size'         => tutor_utils()->let_to_num( ini_get( 'post_max_size' ) ),
			'php_max_execution_time'    => ini_get( 'max_execution_time' ),
			'php_max_input_vars'        => ini_get( 'max_input_vars' ),
			'curl_version'              => $curl_version,
			'suhosin_installed'         => extension_loaded( 'suhosin' ),
			'max_upload_size'           => wp_max_upload_size(),
			'mysql_version'             => $database_version['number'],
			'mysql_version_string'      => $database_version['string'],
			'default_timezone'          => date_default_timezone_get(),
			'fsockopen_or_curl_enabled' => ( function_exists( 'fsockopen' ) || function_exists( 'curl_init' ) ),
			'soapclient_enabled'        => class_exists( 'SoapClient' ),
			'domdocument_enabled'       => class_exists( 'DOMDocument' ),
			'gzip_enabled'              => is_callable( 'gzopen' ),
			'mbstring_enabled'          => extension_loaded( 'mbstring' ),
		);
	}


	public function erase_tutor_data(){
		global $wpdb;

		$is_erase_data = tutor_utils()->get_option('delete_on_uninstall');
		/**D*/ //=> Deleting Data

		$plugin_file = tutor()->basename;
		if ($is_erase_data && current_user_can( 'deactivate_plugin', $plugin_file )) {
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

			$post_type_strings = "'".implode("','", $post_types)."'";
			$tutor_posts = $wpdb->get_col("SELECT ID from {$wpdb->posts} WHERE post_type in({$post_type_strings}) ;");

			if (is_array($tutor_posts) && count($tutor_posts)){
				foreach ($tutor_posts as $post_id){
					//Delete categories
					$terms = wp_get_object_terms( $post_id, 'course-category' );
					foreach( $terms as $term ){
						/**D*/ wp_remove_object_terms( $post_id, array( $term->term_id ), 'course-category' );
					}

					//Delete tags if available
					$terms = wp_get_object_terms( $post_id, 'course-tag' );
					foreach( $terms as $term ){
						/**D*/ wp_remove_object_terms( $post_id, array( $term->term_id ), 'course-tag' );
					}

					//Delete All Meta
					/**D*/ $wpdb->delete($wpdb->postmeta, array('post_id' => $post_id) );
					/**D*/ $wpdb->delete($wpdb->posts, array('ID' => $post_id) );
				}
			}

			/**
			 * Deleting Comments (reviews, questions, quiz_answers, etc)
			 */
			$tutor_comments = $wpdb->get_col("SELECT comment_ID from {$wpdb->comments} WHERE comment_agent = 'comment_agent' ;");
			$comments_ids_strings = "'".implode("','", $tutor_comments)."'";
			if (is_array($tutor_comments) && count($tutor_comments)){
				/**D*/ $wpdb->query("DELETE from {$wpdb->commentmeta} WHERE comment_ID in({$comments_ids_strings}) ");
			}
			/**D*/ $wpdb->delete($wpdb->comments, array('comment_agent' => 'comment_agent'));

			/**
			 * Delete Options
			 */

			/**D*/ delete_option('tutor_option');
			/**D*/ $wpdb->delete($wpdb->usermeta, array('meta_key' => '_is_tutor_student'));
			/**D*/ $wpdb->delete($wpdb->usermeta, array('meta_key' => '_tutor_instructor_approved'));
			/**D*/ $wpdb->delete($wpdb->usermeta, array('meta_key' => '_tutor_instructor_status'));
			/**D*/ $wpdb->delete($wpdb->usermeta, array('meta_key' => '_is_tutor_instructor'));
			/**D*/ $wpdb->query("DELETE FROM {$wpdb->usermeta} WHERE meta_key LIKE  '%_tutor_completed_lesson_id_%' ");

			//Deleting Table
			$prefix = $wpdb->prefix;
			/**D*/ $wpdb->query("DROP TABLE IF EXISTS {$prefix}tutor_quiz_attempts, {$prefix}tutor_quiz_attempt_answers, {$prefix}tutor_quiz_questions, {$prefix}tutor_quiz_question_answers, {$prefix}tutor_earnings, {$prefix}tutor_withdraws ");

			deactivate_plugins($plugin_file);
		}

		wp_redirect('plugins.php');
		die();
	}

	public function plugin_action_links($actions){
		$hasPro = tutor()->has_pro;

		if(!$hasPro){
			$actions['tutor_pro_link'] = '<a href="https://www.themeum.com/product/tutor-lms/#pricing?utm_source=tutor_plugin_action_link&utm_medium=wordpress_dashboard&utm_campaign=go_premium" target="_blank"><span
 style="color: #ff7742; font-weight: bold;">' . __('Upgrade to Pro', 'wp-megamenu') . '</span></a>';
		}

		$is_erase_data = tutor_utils()->get_option('delete_on_uninstall');

		if ($is_erase_data) {
			$plugin_file = tutor()->basename;
			if ( current_user_can( 'deactivate_plugin', $plugin_file ) ) {
				if ( isset( $actions['deactivate'] ) ) {
					$actions['deactivate'] = '<a href="admin.php?page=tutor-tools&tutor_admin_tools_page=uninstall">' . __('Uninstall', 'tutor') . '</a>';
				}
			}
		}

		$actions['settings'] = '<a href="admin.php?page=tutor_settings">' . __('Settings', 'tutor') . '</a>';
		return $actions;
	}

	public function plugin_row_meta($plugin_meta, $plugin_file){

		if ($plugin_file === tutor()->basename) {
			$plugin_meta[] = sprintf( '<a href="%s">%s</a>',
				esc_url( 'https://docs.themeum.com/tutor-lms/?utm_source=tutor&utm_medium=plugins_installation_list&utm_campaign=plugin_docs_link' ),
				__( '<strong style="color: #03bd24">Documentation</strong>', 'tutor' )
			);
			$plugin_meta[] = sprintf( '<a href="%s">%s</a>',
				esc_url( 'https://www.themeum.com/support-forums/?utm_source=tutor&utm_medium=plugins_installation_list&utm_campaign=plugin_support_link' ),
				__( '<strong style="color: #03bd24">Get Support</strong>', 'tutor' )
			);
		}

		return $plugin_meta;
	}

	/**
	 * @param $footer_text
	 *
	 * @return string
	 *
	 * Add footer text only on tutor pages
	 */
	public function admin_footer_text( $footer_text ) {
		$current_screen = get_current_screen();

		/**
		 * We are making sure that this message will be only on Tutor Admin page
		 */
		if ( apply_filters( 'tutor_display_admin_footer_text', (tutor_utils()->array_get('parent_base', $current_screen) === 'tutor' ) ) ) {
			$footer_text = sprintf(
				__( 'If you like %1$s please leave us a %2$s rating. A huge thanks in advance!', 'tutor' ),
				sprintf( '<strong>%s</strong>', esc_html__( 'Tutor LMS', 'tutor' ) ),
				'<a href="https://wordpress.org/support/plugin/tutor/reviews?rate=5#new-post" target="_blank" class="tutor-rating-link">&#9733;&#9733;&#9733;&#9733;&#9733;</a>'
			);
		}

		return $footer_text;
	}


	public function register_course_widget(){
		register_widget( 'Tutor\Course_Widget' );
	}


}