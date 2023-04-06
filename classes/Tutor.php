<?php
/**
 * Initialize all the dependency classes
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
 * Tutor final class
 *
 * @since 1.0.0
 */
final class Tutor {
	/**
	 * Tutor version
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $version = TUTOR_VERSION;

	/**
	 * Plugin dir path
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $path;

	/**
	 * Plugin dir path
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $url;

	/**
	 * Plugin dir name
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $basename;

	/**
	 * The single instance of the class.
	 *
	 * @since 1.2.0
	 *
	 * @var object
	 */
	protected static $_instance = null;


	/**
	 * Utils class object
	 *
	 * @since 1.1.0
	 *
	 * @var object
	 */
	public $utils;

	/**
	 * Admin class object
	 *
	 * @since 1.1.0
	 *
	 * @var object
	 */
	public $admin;

	/**
	 * Ajax class object
	 *
	 * @since 1.1.0
	 *
	 * @var object
	 */
	public $ajax;

	/**
	 * Options class object
	 *
	 * @since 1.1.0
	 *
	 * @var object
	 */
	public $options;

	/**
	 * Short code class object
	 *
	 * @since 1.1.0
	 *
	 * @var object
	 */
	public $shortcode;

	/**
	 * Addons class object
	 *
	 * @since 1.1.0
	 *
	 * @var object
	 */
	private $addons;

	/**
	 * PostType class object
	 *
	 * @since 1.1.0
	 *
	 * @var object
	 */
	private $post_types;

	/**
	 * Taxonomies class object
	 *
	 * @since 1.1.0
	 *
	 * @var object
	 */
	private $taxonomies;

	/**
	 * Assets class object
	 *
	 * @since 1.1.0
	 *
	 * @var object
	 */
	private $assets;

	/**
	 * Course class object
	 *
	 * @since 1.1.0
	 *
	 * @var object
	 */
	private $course;

	/**
	 * Lesson class object
	 *
	 * @since 1.1.0
	 *
	 * @var object
	 */
	private $lesson;

	/**
	 * Rewrite_Rules class object
	 *
	 * @since 1.1.0
	 *
	 * @var object
	 */
	private $rewrite_rules;

	/**
	 * Template class object
	 *
	 * @since 1.1.0
	 *
	 * @var object
	 */
	private $template;

	/**
	 * Instructor class object
	 *
	 * @since 1.1.0
	 *
	 * @var object
	 */
	private $instructor;

	/**
	 * Student class object
	 *
	 * @since 1.1.0
	 *
	 * @var object
	 */
	private $student;

	/**
	 * Q_and_A class object
	 *
	 * @since 1.1.0
	 *
	 * @var object
	 */
	private $q_and_a;

	/**
	 * Quiz class object
	 *
	 * @since 1.1.0
	 *
	 * @var object
	 */
	private $quiz;

	/**
	 * Tools class object
	 *
	 * @since 1.1.0
	 *
	 * @var object
	 */
	private $tools;

	/**
	 * User class object
	 *
	 * @since 1.1.0
	 *
	 * @var object
	 */
	private $user;

	/**
	 * Theme_Compatibility class object
	 *
	 * @since 1.1.0
	 *
	 * @var object
	 */
	private $theme_compatibility;

	/**
	 * Gutenberg class object
	 *
	 * @since 1.1.0
	 *
	 * @var object
	 */
	private $gutenberg;

	/**
	 * Course_Settings_Tabs class object
	 *
	 * @since 1.1.0
	 *
	 * @var object
	 */
	private $course_settings_tabs;

	/**
	 * Withdraw class object
	 *
	 * @since 1.1.0
	 *
	 * @var object
	 */
	private $withdraw;

	/**
	 * Course_Widget class object
	 *
	 * @since 1.1.0
	 *
	 * @var object
	 */
	private $course_widget;

	/**
	 * Upgrader class object
	 *
	 * @since 1.1.0
	 *
	 * @var object
	 */
	private $upgrader;

	/**
	 * Dashboard class object
	 *
	 * @since 1.1.0
	 *
	 * @var object
	 */
	private $dashboard;

	/**
	 * FormHandler class object
	 *
	 * @since 1.1.0
	 *
	 * @var object
	 */
	private $form_handler;

	/**
	 * Frontend class object
	 *
	 * @since 1.1.0
	 *
	 * @var object
	 */
	private $frontend;

	/**
	 * Email property
	 *
	 * @since 1.1.0
	 *
	 * @var object
	 */
	private $email;

	/**
	 * WooCommerce integration class object
	 *
	 * @since 1.1.0
	 *
	 * @var object
	 */
	private $woocommerce;

	/**
	 * Tutor_EDD class object
	 *
	 * @since 1.1.0
	 *
	 * @var object
	 */
	private $edd;

	/**
	 * Announcement
	 *
	 * @since 2.0.0
	 *
	 * @var $announcements
	 */
	private $announcements;

	/**
	 * Reviews class object
	 *
	 * @since 1.1.0
	 *
	 * @var object
	 */
	private $reviews;

	/**
	 * Withdraw_List class object
	 *
	 * @since 1.1.0
	 *
	 * @var object
	 */
	private $withdraw_list;

	/**
	 * Student_List class object
	 *
	 * @since 1.1.0
	 *
	 * @var object
	 */
	private $student_list;

	/**
	 * Instructor_List class object
	 *
	 * @since 1.1.0
	 *
	 * @var object
	 */
	private $instructor_list;

	/**
	 * Course List
	 *
	 * @var $course_list
	 * @since 2.0.0
	 */
	private $Course_List;

	/**
	 * Course Embed
	 *
	 * @var $course_embed
	 *
	 * @since 2.1.0
	 */
	private $course_embed;

	/**
	 * Run the TUTOR
	 *
	 * @since 1.2.0
	 *
	 * @return null|Tutor
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Initialize props & other dependencies
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		$this->path     = plugin_dir_path( TUTOR_FILE );
		$this->url      = plugin_dir_url( TUTOR_FILE );
		$this->basename = plugin_basename( TUTOR_FILE );

		/**
		 * Adding Tutor Database table to $wpdb;
		 *
		 * @since 1.4.2
		 */
		global $wpdb;
		$wpdb->tutor_earnings              = $wpdb->prefix . 'tutor_earnings';
		$wpdb->tutor_gradebooks            = $wpdb->prefix . 'tutor_gradebooks';
		$wpdb->tutor_gradebooks_results    = $wpdb->prefix . 'tutor_gradebooks_results';
		$wpdb->tutor_quiz_attempts         = $wpdb->prefix . 'tutor_quiz_attempts';
		$wpdb->tutor_quiz_attempt_answers  = $wpdb->prefix . 'tutor_quiz_attempt_answers';
		$wpdb->tutor_quiz_questions        = $wpdb->prefix . 'tutor_quiz_questions';
		$wpdb->tutor_quiz_question_answers = $wpdb->prefix . 'tutor_quiz_question_answers';
		$wpdb->tutor_withdraws             = $wpdb->prefix . 'tutor_withdraws';
		$wpdb->tutor_email_queue           = $wpdb->prefix . 'tutor_email_queue';

		/**
		 * Changing default wp doing ajax return based on tutor ajax action
		 */
		add_filter( 'wp_doing_ajax', array( $this, 'wp_doing_ajax' ) );

		/**
		 * Include Files
		 */
		$this->includes();

		/**
		 * Loading Auto loader
		 */
		spl_autoload_register( array( $this, 'loader' ) );

		do_action( 'tutor_before_load' );

		$this->addons                = new Addons();
		$this->post_types            = new Post_types();
		$this->taxonomies            = new Taxonomies();
		$this->assets                = new Assets();
		$this->admin                 = new Admin();
		$this->ajax                  = new Ajax();
		$this->options               = new Options_V2();
		$this->shortcode             = new Shortcode();
		$this->course                = new Course();
		$this->lesson                = new Lesson();
		$this->rewrite_rules         = new Rewrite_Rules();
		$this->template              = new Template();
		$this->instructor            = new Instructor();
		$this->student               = new Student();
		$this->q_and_a               = new Q_and_A();
		$this->q_and_a_list          = new Question_Answers_List();
		$this->q_attempt             = new Quiz_Attempts_List();
		$this->quiz                  = new Quiz();
		$this->tools                 = new Tools();
		$this->user                  = new User();
		$this->theme_compatibility   = new Theme_Compatibility();
		$this->gutenberg             = new Gutenberg();
		$this->course_settings_tabs  = new Course_Settings_Tabs();
		$this->withdraw              = new Withdraw();
		$this->course_widget         = new Course_Widget();
		$this->upgrader              = new Upgrader();
		$this->dashboard             = new Dashboard();
		$this->form_handler          = new FormHandler();
		$this->frontend              = new Frontend();
		$this->rest_api              = new RestAPI();
		$this->setup                 = new Tutor_Setup();
		$this->private_course_access = new Private_Course_Access();
		$this->course_filter         = new Course_Filter();

		// Integrations.
		$this->woocommerce = new WooCommerce();
		$this->edd         = new TutorEDD();

		/**
		 * Init obj
		 *
		 * @since 2.0.0
		 */
		$this->announcements   = new Announcements();
		$this->course_list     = new Course_List();
		$this->reviews         = new Reviews();
		$this->withdraw_list   = new Withdraw_Requests_List();
		$this->student_list    = new Students_List();
		$this->instructor_list = new Instructors_List();
		$this->course_embed    = new Course_Embed();

		/**
		 * Run Method
		 *
		 * @since v.1.2.0
		 */
		$this->run();

		do_action( 'tutor_loaded' );

		add_action( 'init', array( $this, 'init_action' ) );

		/**
		 * Redirect to the wizard page
		 *
		 * @since 1.5.7
		 */

		add_action( 'activated_plugin', array( $this, 'activated_tutor' ), 10, 2 );
	}

	/**
	 * Redirect to the wizard page
	 *
	 * @since 1.5.7
	 *
	 * @param mixed $plugin plugin.
	 * @param mixed $network_wide network wide.
	 *
	 * @return void
	 */
	public function activated_tutor( $plugin, $network_wide = null ) {
		if ( $plugin == tutor()->basename ) {
			if ( ( ! get_option( 'tutor_wizard' ) ) && version_compare( TUTOR_VERSION, '1.5.6', '>' ) ) {
				update_option( 'tutor_wizard', 'active' );
				wp_safe_redirect( admin_url( 'admin.php?page=tutor-setup' ) );
				exit;
			}
		}
	}

	/**
	 * Auto Load class and the files
	 *
	 * @since 1.0.0
	 *
	 * @param string $className class name to load.
	 *
	 * @return void
	 */
	private function loader( $className ) {
		if ( ! class_exists( $className ) ) {
			$className = preg_replace(
				array( '/([a-z])([A-Z])/', '/\\\/' ),
				array( '$1$2', DIRECTORY_SEPARATOR ),
				$className
			);

			$className = str_replace( 'TUTOR' . DIRECTORY_SEPARATOR, 'classes' . DIRECTORY_SEPARATOR, $className );
			$file_name = $this->path . $className . '.php';

			if ( file_exists( $file_name ) ) {
				require_once $file_name;
			}
		}
	}

	/**
	 * Include utility functions
	 *
	 * @return void
	 */
	public function includes() {
		include tutor()->path . 'includes/tutor-general-functions.php';
		include tutor()->path . 'includes/tutor-template-functions.php';
		include tutor()->path . 'includes/tutor-template-hook.php';
		include tutor()->path . 'includes/translate-text.php';
	}

	/**
	 * Providing hooks
	 *
	 * @return void
	 */
	public function run() {
		do_action( 'tutor_before_run' );
		do_action( 'tutor_after_run' );
	}

	/**
	 * Tutor Action Via do_action
	 *
	 * @since 1.2.14
	 */
	public function init_action() {
		$tutor_action = Input::sanitize_request_data( 'tutor_action' );
		if ( '' !== $tutor_action ) {
			do_action( 'tutor_action_' . $tutor_action );
		}
	}

	/**
	 * Do some task during plugin activation
	 */
	public static function tutor_activate() {
		$version = get_option( 'tutor_version' );
		if ( ! function_exists( 'tutor_time' ) ) {
			include tutor()->path . 'includes/tutor-general-functions.php';
		}

		// Create Database.
		self::create_database();

		// Save Option.
		if ( ! $version ) {

			$options = self::default_options();
			update_option( 'tutor_option', $options );

			// Rewrite Flush.
			update_option( 'required_rewrite_flush', tutor_time() );
			self::manage_tutor_roles_and_permissions();

			// Save initial Page.
			self::save_data();
			update_option( 'tutor_version', TUTOR_VERSION );
		}

		// Set Schedule.
		if ( ! wp_next_scheduled( 'tutor_once_in_day_run_schedule' ) ) {
			wp_schedule_event( tutor_time(), 'twicedaily', 'tutor_once_in_day_run_schedule' );
		}

		/**
		 * Backward Compatibility for version < 1.2.0
		 */
		if ( version_compare( get_option( 'tutor_version' ), '1.2.0', '<' ) ) {
			/**
			 * Creating New Database
			 */
			self::create_withdraw_database();
			// Update the tutor version.
			update_option( 'tutor_version', '1.2.0' );
			// Rewrite Flush.
			update_option( 'required_rewrite_flush', tutor_time() );
		}

		/**
		 * Backward Compatibility to < 1.3.1 for make course plural
		 */
		if ( version_compare( get_option( 'tutor_version' ), '1.3.1', '<' ) ) {
			global $wpdb;

			if ( ! get_option( 'is_course_post_type_updated' ) ) {
				$wpdb->update( $wpdb->posts, array( 'post_type' => tutor()->course_post_type ), array( 'post_type' => 'course' ) );
				update_option( 'is_course_post_type_updated', true );
				update_option( 'tutor_version', '1.3.1' );
				flush_rewrite_rules();
			}
		}

		/**
		 * Save First activation Time
		 */
		$first_activation_date = get_option( 'tutor_first_activation_time' );
		if ( ! $first_activation_date ) {
			update_option( 'tutor_first_activation_time', tutor_time() );
		}
	}

	/**
	 * Run task on deactivation
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function tutor_deactivation() {
		wp_clear_scheduled_hook( 'tutor_once_in_day_run_schedule' );
	}

	/**
	 * Create database
	 *
	 * @return void
	 */
	public static function create_database() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();

		/**
		 * Table SQL
		 *
		 * {$wpdb->prefix}tutor_quiz_attempts
		 * {$wpdb->prefix}tutor_quiz_attempt_answers
		 * {$wpdb->prefix}tutor_quiz_questions
		 * {$wpdb->prefix}tutor_quiz_question_answers
		 * {$wpdb->prefix}tutor_earnings
		 * {$wpdb->prefix}tutor_withdraws
		 *
		 * @since 1.0.0
		 */
		$quiz_attempts_sql = "CREATE TABLE {$wpdb->prefix}tutor_quiz_attempts (
				attempt_id bigint(20) NOT NULL AUTO_INCREMENT,
				course_id bigint(20) DEFAULT NULL,
				quiz_id bigint(20) DEFAULT NULL,
				user_id bigint(20) DEFAULT NULL,
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
			  	attempt_answer_id bigint(20) NOT NULL AUTO_INCREMENT,
				user_id bigint(20) DEFAULT NULL,
			  	quiz_id bigint(20) DEFAULT NULL,
			  	question_id bigint(20) DEFAULT NULL,
			  	quiz_attempt_id bigint(20) DEFAULT NULL,
			  	given_answer longtext,
			  	question_mark decimal(8,2) DEFAULT NULL,
			  	achieved_mark decimal(8,2) DEFAULT NULL,
			  	minus_mark decimal(8,2) DEFAULT NULL,
			  	is_correct tinyint(4) DEFAULT NULL,
			  	PRIMARY KEY  (attempt_answer_id)
			) $charset_collate;";

		$tutor_quiz_questions = "CREATE TABLE {$wpdb->prefix}tutor_quiz_questions (
				question_id bigint(20) NOT NULL AUTO_INCREMENT,
				quiz_id bigint(20) DEFAULT NULL,
				question_title text,
				question_description longtext,
				question_type varchar(50) DEFAULT NULL,
				question_mark decimal(9,2) DEFAULT NULL,
				question_settings longtext,
				question_order int(11) DEFAULT NULL,
				PRIMARY KEY (question_id)
			) $charset_collate;";

		$tutor_quiz_question_answers = "CREATE TABLE {$wpdb->prefix}tutor_quiz_question_answers (
			 	answer_id bigint(20) NOT NULL AUTO_INCREMENT,
			  	belongs_question_id bigint(20) DEFAULT NULL,
			  	belongs_question_type varchar(250) DEFAULT NULL,
			  	answer_title text,
			  	is_correct tinyint(4) DEFAULT NULL,
			  	image_id bigint(20) DEFAULT NULL,
			  	answer_two_gap_match text,
			  	answer_view_format varchar(250) DEFAULT NULL,
			  	answer_settings text,
			  	answer_order int(11) DEFAULT '0',
				PRIMARY KEY (answer_id)
			) $charset_collate;";

		$earning_table = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}tutor_earnings (
			earning_id bigint(20) NOT NULL AUTO_INCREMENT,
			user_id bigint(20) DEFAULT NULL,
			course_id bigint(20) DEFAULT NULL,
			order_id bigint(20) DEFAULT NULL,
			order_status varchar(50) DEFAULT NULL,
			course_price_total decimal(16,2) DEFAULT NULL,
			course_price_grand_total decimal(16,2) DEFAULT NULL,
			instructor_amount decimal(16,2) DEFAULT NULL,
			instructor_rate decimal(16,2) DEFAULT NULL,
			admin_amount decimal(16,2) DEFAULT NULL,
			admin_rate decimal(16,2) DEFAULT NULL,
			commission_type varchar(20) DEFAULT NULL,
			deduct_fees_amount decimal(16,2) DEFAULT NULL,
			deduct_fees_name varchar(250) DEFAULT NULL,
			deduct_fees_type varchar(20) DEFAULT NULL,
			process_by varchar(20) DEFAULT NULL,
			created_at datetime DEFAULT NULL,
			PRIMARY KEY (earning_id)
		) $charset_collate;";

		$withdraw_table = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}tutor_withdraws (
			withdraw_id bigint(20) NOT NULL AUTO_INCREMENT,
			user_id bigint(20) DEFAULT NULL,
			amount decimal(16,2) DEFAULT NULL,
			method_data text DEFAULT NULL,
			status varchar(50) DEFAULT NULL,
			updated_at datetime DEFAULT NULL,
			created_at datetime DEFAULT NULL,
			PRIMARY KEY (withdraw_id)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $quiz_attempts_sql );
		dbDelta( $quiz_attempt_answers );
		dbDelta( $tutor_quiz_questions );
		dbDelta( $tutor_quiz_question_answers );
		dbDelta( $earning_table );
		dbDelta( $withdraw_table );
	}

	/**
	 * Manage tutor roles & permission
	 *
	 * @return void
	 */
	public static function manage_tutor_roles_and_permissions() {
		/**
		 * Add role for instructor
		 */
		$instructor_role = tutor()->instructor_role;

		remove_role( $instructor_role );
		add_role( $instructor_role, __( 'Tutor Instructor', 'tutor' ), array() );

		$custom_post_type_permission = array(
			// Manage Instructor.
			'manage_tutor_instructor',

			// Tutor Posts Type Permission.
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
			$instructor_cap = array(
				'edit_posts',
				'read',
				'upload_files',
			);

			$instructor_cap = array_merge( $instructor_cap, $custom_post_type_permission );

			$can_publish_course = (bool) tutor_utils()->get_option( 'instructor_can_publish_course' );
			if ( $can_publish_course ) {
				$instructor_cap[] = 'publish_tutor_courses';
			}

			foreach ( $instructor_cap as $cap ) {
				$instructor->add_cap( $cap );
			}
		}

		$administrator = get_role( 'administrator' );
		if ( $administrator ) {

			$administrator_cap   = array(
				'manage_tutor',
			);
			$administrator_cap   = array_merge( $administrator_cap, $custom_post_type_permission );
			$administrator_cap[] = 'publish_tutor_courses';

			foreach ( $administrator_cap as $cap ) {
				$administrator->add_cap( $cap );
			}
		}

		/**
		 * Add Instructor role to administrator
		 */
		if ( current_user_can( 'administrator' ) ) {
			tutor_utils()->add_instructor_role( get_current_user_id() );
		}

	}

	/**
	 * On plugin activate save initial data
	 * Like: generate tutor pages
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function save_data() {

		$student_dashboard_args    = array(
			'post_title'   => __( 'Dashboard', 'tutor' ),
			'post_content' => '',
			'post_type'    => 'page',
			'post_status'  => 'publish',
		);
		$student_dashboard_page_id = wp_insert_post( $student_dashboard_args );
		tutor_utils()->update_option( 'tutor_dashboard_page_id', $student_dashboard_page_id );

		$student_registration_args = array(
			'post_title'   => __( 'Student Registration', 'tutor' ),
			'post_content' => '[tutor_student_registration_form]',
			'post_type'    => 'page',
			'post_status'  => 'publish',
		);
		$student_register_page_id  = wp_insert_post( $student_registration_args );
		tutor_utils()->update_option( 'student_register_page', $student_register_page_id );

		$instructor_registration_args = array(
			'post_title'   => __( 'Instructor Registration', 'tutor' ),
			'post_content' => '[tutor_instructor_registration_form]',
			'post_type'    => 'page',
			'post_status'  => 'publish',
		);
		$instructor_registration_id   = wp_insert_post( $instructor_registration_args );
		tutor_utils()->update_option( 'instructor_register_page', $instructor_registration_id );
	}

	/**
	 * Default options
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public static function default_options() {
		$options = array(
			'pagination_per_page'               => '20',
			'course_allow_upload_private_files' => '1',
			'display_course_instructors'        => '1',
			'enable_q_and_a_on_course'          => '1',
			'courses_col_per_row'               => '3',
			'courses_per_page'                  => '12',
			'lesson_permalink_base'             => 'lesson',
			'quiz_when_time_expires'            => 'autosubmit',
			'quiz_attempts_allowed'             => '10',
			'quiz_grade_method'                 => 'highest_grade',
			'enable_public_profile'             => '1',
			'email_to_students'                 =>
				array(
					'quiz_completed'   => '1',
					'completed_course' => '1',
				),
			'email_to_instructors'              =>
				array(
					'a_student_enrolled_in_course' => '1',
					'a_student_completed_course'   => '1',
					'a_student_completed_lesson'   => '1',
					'a_student_placed_question'    => '1',
				),
			'email_from_name'                   => get_option( 'blogname' ),
			'email_from_address'                => get_option( 'admin_email' ),
			'email_footer_text'                 => '',
			'earning_admin_commission'          => '20',
			'earning_admin_commission'          => '20',
			'earning_instructor_commission'     => '80',
			'color_preset_type'                 => 'default',
		);
		return $options;
	}


	/**
	 * Create withdraw database
	 *
	 * @since 1.2.0
	 */
	public static function create_withdraw_database() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();

		/**
		 * Table SQL
		 *
		 * {$wpdb->prefix}tutor_earnings
		 * {$wpdb->prefix}tutor_withdraws
		 *
		 * @since 1.2.0
		 */

		$earning_table = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}tutor_earnings (
			earning_id bigint(20) NOT NULL AUTO_INCREMENT,
			user_id bigint(20) DEFAULT NULL,
			course_id bigint(20) DEFAULT NULL,
			order_id bigint(20) DEFAULT NULL,
			order_status varchar(50) DEFAULT NULL,
			course_price_total decimal(16,2) DEFAULT NULL,
			course_price_grand_total decimal(16,2) DEFAULT NULL,
			instructor_amount decimal(16,2) DEFAULT NULL,
			instructor_rate decimal(16,2) DEFAULT NULL,
			admin_amount decimal(16,2) DEFAULT NULL,
			admin_rate decimal(16,2) DEFAULT NULL,
			commission_type varchar(20) DEFAULT NULL,
			deduct_fees_amount decimal(16,2) DEFAULT NULL,
			deduct_fees_name varchar(250) DEFAULT NULL,
			deduct_fees_type varchar(20) DEFAULT NULL,
			process_by varchar(20) DEFAULT NULL,
			created_at datetime DEFAULT NULL,
			PRIMARY KEY (earning_id)
		) $charset_collate;";

		$withdraw_table = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}tutor_withdraws (
			withdraw_id bigint(20) NOT NULL AUTO_INCREMENT,
			user_id bigint(20) DEFAULT NULL,
			amount decimal(16,2) DEFAULT NULL,
			method_data text DEFAULT NULL,
			status varchar(50) DEFAULT NULL,
			updated_at datetime DEFAULT NULL,
			created_at datetime DEFAULT NULL,
			PRIMARY KEY (withdraw_id)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $earning_table );
		dbDelta( $withdraw_table );

		/**
		 * Setting previous dashboard to new dashboard
		 */
		$previous_dashboard_page_id = (int) tutor_utils()->get_option( 'student_dashboard' );
		tutor_utils()->update_option( 'tutor_dashboard_page_id', $previous_dashboard_page_id );
	}

	/**
	 * Filter the wp_doing_ajax from tutor requests to get advanced
	 * advantages from Tutor
	 *
	 * @since 1.3.4
	 *
	 * @param bool $bool default value.
	 *
	 * @return bool
	 */
	public function wp_doing_ajax( $bool ) {
		// Don't use Input::has helper to avoid conflict.
		if ( isset( $_REQUEST['tutor_ajax_action'] ) ) {
			return true;
		}
		return $bool;
	}
}
