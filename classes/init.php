<?php
namespace DOZENT;

if ( ! defined( 'ABSPATH' ) )
	exit;

class init{
	public $version = DOZENT_VERSION;
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
	private $teacher;
	private $student;
	private $q_and_a;
	private $quiz;
	private $question;
	private $tools;
	private $email_notification;
	private $user;
	private $theme_compatibility;

	function __construct() {

		$this->path = plugin_dir_path(DOZENT_FILE);
		$this->url = plugin_dir_url(DOZENT_FILE);
		$this->basename = plugin_basename(DOZENT_FILE);

		/**
		 * Include Files
		 */
		add_action( 'after_setup_theme', array( $this, 'include_template_functions' ), 11 );

		/**
		 * Loading Autoloader
		 */

		spl_autoload_register(array($this, 'loader'));

		do_action('dozent_before_load');

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
		$this->teacher = new  Teacher();
		$this->student = new Student();
		$this->q_and_a = new Q_and_A();
		$this->quiz = new Quiz();
		$this->question = new Question();
		$this->tools = new Tools();
		$this->email_notification = new Email_Notification();
		$this->user = new User();
		$this->theme_compatibility = new Theme_Compatibility();

		do_action('dozent_loaded');
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

			$className = str_replace('DOZENT/', 'classes/', $className);
			$file_name = $this->path.$className.'.php';

			if (file_exists($file_name) && is_readable( $file_name ) ) {
				require_once $file_name;
			}
		}
	}

	public function include_template_functions(){
		include dozent()->path.'includes/dozent-template-functions.php';
		include dozent()->path.'includes/dozent-template-hook.php';
	}

	//Run the DOZENT right now
	public function run(){
		do_action('dozent_before_run');

		register_activation_hook( DOZENT_FILE, array( $this, 'dozent_activate' ) );
		register_deactivation_hook(DOZENT_FILE, array($this, 'dozent_deactivation'));

		do_action('dozent_after_run');
	}

	/**
	 * Do some task during plugin activation
	 */
	public function dozent_activate(){
		$version = get_option('dozent_version');
		//Save Option
		if ( ! $version){
			$options = self::default_options();
			update_option('dozent_option', $options);

			//Rewrite Flush
			update_option('required_rewrite_flush', time());
			self::manage_dozent_roles_and_permissions();

			self::save_data();//Save initial Page
			update_option('dozent_version', DOZENT_VERSION);
		}

		//Set Schedule
		if (! wp_next_scheduled ( 'dozent_once_in_day_run_schedule' )) {
			wp_schedule_event(time(), 'twicedaily', 'dozent_once_in_day_run_schedule');
		}
	}

	//Run task on deactivation
	public function dozent_deactivation() {
		wp_clear_scheduled_hook('dozent_once_in_day_run_schedule');
	}


	public static function manage_dozent_roles_and_permissions(){
		/**
		 * Add role for teacher
		 */
		$teacher_role = dozent()->teacher_role;

		remove_role($teacher_role);
		add_role( $teacher_role, __('Dozent Teacher', 'dozent'), array() );

		$custom_post_type_permission = array(
			//Manage Teacher
			'manage_dozent_teacher',

			//Dozent Posts Type Permission
			'edit_dozent_course',
			'read_dozent_course',
			'delete_dozent_course',
			'delete_dozent_courses',
			'edit_dozent_courses',
			'edit_others_dozent_courses',
			'read_private_dozent_courses',
			'edit_dozent_courses',

			'edit_dozent_lesson',
			'read_dozent_lesson',
			'delete_dozent_lesson',
			'delete_dozent_lessons',
			'edit_dozent_lessons',
			'edit_others_dozent_lessons',
			'read_private_dozent_lessons',
			'edit_dozent_lessons',
			'publish_dozent_lessons',

			'edit_dozent_quiz',
			'read_dozent_quiz',
			'delete_dozent_quiz',
			'delete_dozent_quizzes',
			'edit_dozent_quizzes',
			'edit_others_dozent_quizzes',
			'read_private_dozent_quizzes',
			'edit_dozent_quizzes',
			'publish_dozent_quizzes',

			'edit_dozent_question',
			'read_dozent_question',
			'delete_dozent_question',
			'delete_dozent_questions',
			'edit_dozent_questions',
			'edit_others_dozent_questions',
			'publish_dozent_questions',
			'read_private_dozent_questions',
			'edit_dozent_questions',
		);

		$teacher = get_role( $teacher_role );
		if ( $teacher ) {
			$teacher_cap = array (
				'edit_posts',
				'read',
				'upload_files',
			);

			$teacher_cap = array_merge($teacher_cap, $custom_post_type_permission);

			$can_publish_course = (bool) dozent_utils()->get_option('teacher_can_publish_course');
			if ($can_publish_course){
				$teacher_cap[] = 'publish_dozent_courses';
			}

			foreach ($teacher_cap as $cap){
				$teacher->add_cap( $cap );
			}
		}

		$administrator = get_role( 'administrator' );
		if ( $administrator ) {
			$administrator_cap = array (
				'manage_dozent',
			);
			$administrator_cap = array_merge($administrator_cap, $custom_post_type_permission);
			$administrator_cap[] = 'publish_dozent_courses';

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
			'post_title'    => __('Student Dashboard', 'dozent'),
			'post_content'  => '[dozent_student_dashboard]',
			'post_type'     => 'page',
			'post_status'   => 'publish',
		);
		$student_dashboard_page_id = wp_insert_post( $student_dashboard_args );
		dozent_utils()->update_option('dozent_student_dashboard', $student_dashboard_page_id);

		$student_registration_args = array(
			'post_title'    => __('Student Registration', 'dozent'),
			'post_content'  => '[dozent_student_registration_form]',
			'post_type'     => 'page',
			'post_status'   => 'publish',
		);
		$student_register_page_id = wp_insert_post( $student_registration_args );
		dozent_utils()->update_option('student_register_page', $student_register_page_id);

		$teacher_registration_args = array(
			'post_title'    => __('Teacher Registration', 'dozent'),
			'post_content'  => '[dozent_teacher_registration_form]',
			'post_type'     => 'page',
			'post_status'   => 'publish',
		);
		$teacher_registration_id = wp_insert_post( $teacher_registration_args );
		dozent_utils()->update_option('teacher_register_page', $teacher_registration_id);
	}

	public static function default_options(){
		$options = array (
			'load_dozent_css'                    => '1',
			'load_dozent_js'                     => '1',
			'course_allow_upload_private_files' => '1',
			'display_course_teachers'           => '1',
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
			'email_to_teachers'                     =>
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