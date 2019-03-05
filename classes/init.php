<?php
namespace TUTOR;

if ( ! defined( 'ABSPATH' ) )
	exit;

class init{
	public $version = TUTOR_VERSION;
	public $path;
	public $url;
	public $basename;

	//Components
	public $utils;
	public $admin;
	public $ajax;
	public $options;
	public $shortcode;

	private $post_types;
	private $assets;
	private $course;
	private $lesson;
	private $rewrite_rules;
	private $template;
	private $instructor;
	private $student;
	private $q_and_a;
	private $quiz;
	private $question;
	private $tools;
	private $user;
	private $theme_compatibility;
	private $gutenberg;

	function __construct() {

		$this->path = plugin_dir_path(TUTOR_FILE);
		$this->url = plugin_dir_url(TUTOR_FILE);
		$this->basename = plugin_basename(TUTOR_FILE);

		/**
		 * Include Files
		 */
		add_action( 'after_setup_theme', array( $this, 'include_template_functions' ), 11 );

		/**
		 * Loading Autoloader
		 */

		spl_autoload_register(array($this, 'loader'));

		do_action('tutor_before_load');

		$this->post_types = new Post_types();
		$this->assets = new Assets();
		$this->admin = new Admin();
		$this->ajax = new Ajax();
		$this->options = new Options();
		$this->shortcode = new Shortcode();
		$this->course = new Course();
		$this->lesson = new Lesson();
		$this->rewrite_rules = new Rewrite_Rules();
		$this->template = new Template();
		$this->instructor = new  Instructor();
		$this->student = new Student();
		$this->q_and_a = new Q_and_A();
		$this->quiz = new Quiz();
		$this->question = new Question();
		$this->tools = new Tools();
		$this->user = new User();
		$this->theme_compatibility = new Theme_Compatibility();
		$this->gutenberg = new Gutenberg();

		do_action('tutor_loaded');
	}
	/**
	 * @param $className
	 *
	 * Auto Load class and the files
	 */
	private function loader($className) {
		if ( ! class_exists($className)){
			$className = preg_replace(
				array('/([a-z])([A-Z])/', '/\\\/'),
				array('$1-$2', DIRECTORY_SEPARATOR),
				$className
			);

			$className = str_replace('TUTOR/', 'classes/', $className);
			$file_name = $this->path.$className.'.php';

			if (file_exists($file_name) && is_readable( $file_name ) ) {
				require_once $file_name;
			}
		}
	}

	public function include_template_functions(){
		include tutor()->path.'includes/tutor-template-functions.php';
		include tutor()->path.'includes/tutor-template-hook.php';
	}

	//Run the TUTOR right now
	public function run(){
		do_action('tutor_before_run');

		register_activation_hook( TUTOR_FILE, array( $this, 'tutor_activate' ) );
		register_deactivation_hook(TUTOR_FILE, array($this, 'tutor_deactivation'));

		do_action('tutor_after_run');
	}

	/**
	 * Do some task during plugin activation
	 */
	public function tutor_activate(){
		$version = get_option('tutor_version');
		//Save Option
		if ( ! $version){
			//Create Database
			$this->create_database();

			$options = self::default_options();
			update_option('tutor_option', $options);

			//Rewrite Flush
			update_option('required_rewrite_flush', time());
			self::manage_tutor_roles_and_permissions();

			self::save_data();//Save initial Page
			update_option('tutor_version', TUTOR_VERSION);
		}

		//Set Schedule
		if (! wp_next_scheduled ( 'tutor_once_in_day_run_schedule' )) {
			wp_schedule_event(time(), 'twicedaily', 'tutor_once_in_day_run_schedule');
		}

		/**
		 * backward / Alpha version compatibility
		 */
		if (version_compare(get_option('TUTOR_VERSION'), '1.0.0', '<')){
			//Create Database
			$this->create_database();
			update_option('tutor_version', '1.0.0');
		}
		/**
		 * backward / v.1.0.0 compatibility
		 */
		if (get_option('TUTOR_VERSION') == '1.0.0' && version_compare(get_option('TUTOR_VERSION'), '1.0.1', '<')){
			//Adding column course_id in prefix_tutor_quiz_attempts
			$this->upgrading_db_1_0_1();
			update_option('tutor_version', '1.0.1');
		}

	}

	//Run task on deactivation
	public function tutor_deactivation() {
		wp_clear_scheduled_hook('tutor_once_in_day_run_schedule');
	}

	public function create_database(){
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();

		/**
		 * Table SQL
		 *
		 * {$wpdb->prefix}tutor_quiz_attempts
		 * {$wpdb->prefix}tutor_quiz_attempt_answers
		 * {$wpdb->prefix}tutor_quiz_questions
		 * {$wpdb->prefix}tutor_quiz_question_answers
		 *
		 * @since v.1.0.0
		 */
		$quiz_attempts_sql = "CREATE TABLE {$wpdb->prefix}tutor_quiz_attempts (
				attempt_id int(11) NOT NULL AUTO_INCREMENT,
				course_id int(11) DEFAULT NULL,
				quiz_id int(11) DEFAULT NULL,
				user_id int(11) DEFAULT NULL,
				total_questions int(11) DEFAULT NULL,
				total_answered_questions int(11) DEFAULT NULL,
				total_marks decimal(9,2) DEFAULT NULL,
				earned_marks decimal(9,2) DEFAULT NULL,
				attempt_info text,
				attempt_status varchar(50) DEFAULT NULL,
				attempt_ip varchar(250) DEFAULT NULL,
				attempt_started_at datetime DEFAULT NULL,
				attempt_ended_at datetime DEFAULT NULL,
				is_manually_reviewed int(1) DEFAULT NULL,
				manually_reviewed_at datetime DEFAULT NULL,
				PRIMARY KEY  (attempt_id)
			) $charset_collate;";

		$quiz_attempt_answers = "CREATE TABLE {$wpdb->prefix}tutor_quiz_attempt_answers (
			  	attempt_answer_id int(11) NOT NULL AUTO_INCREMENT,
				user_id int(11) DEFAULT NULL,
			  	quiz_id int(11) DEFAULT NULL,
			  	question_id int(11) DEFAULT NULL,
			  	quiz_attempt_id int(11) DEFAULT NULL,
			  	given_answer longtext,
			  	question_mark decimal(8,2) DEFAULT NULL,
			  	achieved_mark decimal(8,2) DEFAULT NULL,
			  	minus_mark decimal(8,2) DEFAULT NULL,
			  	is_correct tinyint(4) DEFAULT NULL,
			  	PRIMARY KEY  (attempt_answer_id)
			) $charset_collate;";

		$tutor_quiz_questions = "CREATE TABLE {$wpdb->prefix}tutor_quiz_questions (
				question_id int(11) NOT NULL AUTO_INCREMENT,
				quiz_id int(11) DEFAULT NULL,
				question_title text,
				question_description longtext,
				question_type varchar(50) DEFAULT NULL,
				question_mark decimal(9,2) DEFAULT NULL,
				question_settings longtext,
				question_order int(11) DEFAULT NULL,
				PRIMARY KEY (question_id)
			) $charset_collate;";

		$tutor_quiz_question_answers = "CREATE TABLE {$wpdb->prefix}tutor_quiz_question_answers (
			 	answer_id int(11) NOT NULL AUTO_INCREMENT,
			  	belongs_question_id int(11) DEFAULT NULL,
			  	belongs_question_type varchar(250) DEFAULT NULL,
			  	answer_title text,
			  	is_correct tinyint(4) DEFAULT NULL,
			  	image_id int(11) DEFAULT NULL,
			  	answer_two_gap_match text,
			  	answer_view_format varchar(250) DEFAULT NULL,
			  	answer_settings text,
			  	answer_order int(11) DEFAULT '0',
				PRIMARY KEY (answer_id)
			) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $quiz_attempts_sql );
		dbDelta( $quiz_attempt_answers );
		dbDelta( $tutor_quiz_questions );
		dbDelta( $tutor_quiz_question_answers );
	}

	/**
	 * upgrading quiz_attempts_database adding course_id
	 * @since v.1.0.1
	 */
	public function upgrading_db_1_0_1(){
		global $wpdb;
		/**
		 * Adding course_id column in tutor_quiz_attempts table
		 */
		$sql = "ALTER TABLE {$wpdb->prefix}tutor_quiz_attempts ADD course_id INT NULL DEFAULT NULL AFTER attempt_id;";
		$wpdb->query($sql);
		/**
		 * Setting Course_id column data;
		 */
		$attempts = $wpdb->get_results("SELECT * from {$wpdb->prefix}tutor_quiz_attempts;");
		if (is_array($attempts) && count($attempts)){
			foreach ($attempts as $attempt){
				$course = tutor_utils()->get_course_by_quiz($attempt->quiz_id);
				$wpdb->update($wpdb->prefix."tutor_quiz_attempts", array('course_id' => $course->ID), array('attempt_id' => $attempt->attempt_id));
			}
		}
	}

	public static function manage_tutor_roles_and_permissions(){
		/**
		 * Add role for instructor
		 */
		$instructor_role = tutor()->instructor_role;

		remove_role($instructor_role);
		add_role( $instructor_role, __('Tutor Instructor', 'tutor'), array() );

		$custom_post_type_permission = array(
			//Manage Instructor
			'manage_tutor_instructor',

			//Tutor Posts Type Permission
			'edit_tutor_course',
			'read_tutor_course',
			'delete_tutor_course',
			'delete_tutor_courses',
			'edit_tutor_courses',
			'edit_others_tutor_courses',
			'read_private_tutor_courses',
			'edit_tutor_courses',

			'edit_tutor_lesson',
			'read_tutor_lesson',
			'delete_tutor_lesson',
			'delete_tutor_lessons',
			'edit_tutor_lessons',
			'edit_others_tutor_lessons',
			'read_private_tutor_lessons',
			'edit_tutor_lessons',
			'publish_tutor_lessons',

			'edit_tutor_quiz',
			'read_tutor_quiz',
			'delete_tutor_quiz',
			'delete_tutor_quizzes',
			'edit_tutor_quizzes',
			'edit_others_tutor_quizzes',
			'read_private_tutor_quizzes',
			'edit_tutor_quizzes',
			'publish_tutor_quizzes',

			'edit_tutor_question',
			'read_tutor_question',
			'delete_tutor_question',
			'delete_tutor_questions',
			'edit_tutor_questions',
			'edit_others_tutor_questions',
			'publish_tutor_questions',
			'read_private_tutor_questions',
			'edit_tutor_questions',
		);

		$instructor = get_role( $instructor_role );
		if ( $instructor ) {
			$instructor_cap = array (
				'edit_posts',
				'read',
				'upload_files',
			);

			$instructor_cap = array_merge($instructor_cap, $custom_post_type_permission);

			$can_publish_course = (bool) tutor_utils()->get_option('instructor_can_publish_course');
			if ($can_publish_course){
				$instructor_cap[] = 'publish_tutor_courses';
			}

			foreach ($instructor_cap as $cap){
				$instructor->add_cap( $cap );
			}
		}

		$administrator = get_role( 'administrator' );
		if ( $administrator ) {
			$administrator_cap = array (
				'manage_tutor',
			);
			$administrator_cap = array_merge($administrator_cap, $custom_post_type_permission);
			$administrator_cap[] = 'publish_tutor_courses';

			foreach ($administrator_cap as $cap){
				$administrator->add_cap( $cap );
			}
		}
	}

	/**
	 * Save data like page
	 */
	public static function save_data(){
		$student_dashboard_args = array(
			'post_title'    => __('Student Dashboard', 'tutor'),
			'post_content'  => '[tutor_student_dashboard]',
			'post_type'     => 'page',
			'post_status'   => 'publish',
		);
		$student_dashboard_page_id = wp_insert_post( $student_dashboard_args );
		tutor_utils()->update_option('student_dashboard', $student_dashboard_page_id);

		$student_registration_args = array(
			'post_title'    => __('Student Registration', 'tutor'),
			'post_content'  => '[tutor_student_registration_form]',
			'post_type'     => 'page',
			'post_status'   => 'publish',
		);
		$student_register_page_id = wp_insert_post( $student_registration_args );
		tutor_utils()->update_option('student_register_page', $student_register_page_id);

		$instructor_registration_args = array(
			'post_title'    => __('Instructor Registration', 'tutor'),
			'post_content'  => '[tutor_instructor_registration_form]',
			'post_type'     => 'page',
			'post_status'   => 'publish',
		);
		$instructor_registration_id = wp_insert_post( $instructor_registration_args );
		tutor_utils()->update_option('instructor_register_page', $instructor_registration_id);
	}

	public static function default_options(){
		$options = array (
			'load_tutor_css'                    => '1',
			'load_tutor_js'                     => '1',
			'course_allow_upload_private_files' => '1',
			'display_course_instructors'           => '1',
			'enable_q_and_a_on_course'          => '1',
			'courses_col_per_row'               => '3',
			'courses_per_page'                  => '3',
			'lesson_permalink_base'             => 'lesson',
			'quiz_time_limit'                   =>
				array (
					'value' => '0',
					'time' => 'minutes',
				),
			'quiz_when_time_expires'            => 'autosubmit',
			'quiz_attempts_allowed'             => '10',
			'quiz_grade_method'                 => 'highest_grade',
			'enable_public_profile'         => '1',
			'email_to_students'                 =>
				array (
					'quiz_completed' => '1',
					'completed_course' => '1',
				),
			'email_to_instructors'                     =>
				array (
					'a_student_enrolled_in_course'  => '1',
					'a_student_completed_course'    => '1',
					'a_student_completed_lesson'    => '1',
					'a_student_placed_question'     => '1',
				),
			'email_from_name'                   => get_option('blogname'),
			'email_from_address'                => get_option('admin_email'),
			'email_footer_text'                 => '',
			'enable_course_sell_by_woocommerce' => '1',
		);
		return $options;
	}


}