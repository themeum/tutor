<?php
/**
 * Initialize all the dependency classes
 *
 * @package Tutor
 * @author Themeum <support@themeum.com>
 * @link https://www.themeum.com/
 * @since 1.0.0
 */

namespace TUTOR;

defined( 'ABSPATH' ) || exit;

use Tutor\Core\Input;
use Tutor\Core\Singleton;
use Tutor\Database\TableManager;
use Tutor\Database\Tables\EarningsTable;
use Tutor\Database\Tables\WithdrawsTable;
use Tutor\Models\CourseModel;
use Tutor\Ecommerce\Ecommerce;
use Tutor\GDPR\GDPR;
use Tutor\Helpers\QueryHelper;
use Tutor\Migrations\Migration;

/**
 * Tutor final class
 *
 * @since 1.0.0
 */
final class Tutor extends Singleton {

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
	public $lesson;

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
	 * Q_And_A class object
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
	 * UserPreference class object
	 *
	 * @since 4.0.0
	 *
	 * @var UserPreference
	 */
	private $user_preference;

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
	 * @var object
	 */
	public $announcements;

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
	public $withdraw_list;

	/**
	 * Student_List class object
	 *
	 * @since 1.1.0
	 *
	 * @var object
	 */
	public $student_list;

	/**
	 * Instructor_List class object
	 *
	 * @since 1.1.0
	 *
	 * @var object
	 */
	public $instructor_list;

	/**
	 * Course List
	 *
	 * @var Course_List
	 *
	 * @since 2.0.0
	 */
	public $course_list;

	//phpcs:disable
	public $q_and_a_list;
	public $q_attempt;
	public $rest_api;
	public $setup;
	public $private_course_access;
	public $course_filter;
	//phpcs:enable

	/**
	 * Course Embed
	 *
	 * @var $course_embed
	 *
	 * @since 2.1.0
	 */
	private $course_embed;

	/**
	 * Rest Authentication
	 *
	 * @var $rest_auth
	 *
	 * @since 2.1.0
	 */
	private $rest_auth;

	/**
	 * Permalink
	 *
	 * @var Permalink
	 */
	private $permalink;

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
		 * Adding Tutor Database tables to $wpdb;
		 *
		 * @since 1.4.2
		 * @since 4.3.0 Registering tables dynamically like before.
		 * Example:
		 *  $wpdb->tutor_courses = $wpdb->prefix . 'tutor_courses';
		 *  $wpdb->tutor_lessons = $wpdb->prefix . 'tutor_lessons';
		 *  $wpdb->tutor_quizzes = $wpdb->prefix . 'tutor_quizzes';
		 * ... etc
		 */
		global $wpdb;
		foreach ( TableManager::get_table_names() as $base_name => $prefixed_name ) {
			$wpdb->{$base_name} = $prefixed_name;
		}

		/**
		 * Changing default wp doing ajax return based on tutor ajax action
		 */
		add_filter( 'wp_doing_ajax', array( $this, 'wp_doing_ajax' ) );

		/**
		 * Include Files
		 */
		$this->includes();

		do_action( 'tutor_before_load' );

		GDPR::get_instance();

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
		$this->q_and_a               = new Q_And_A();
		$this->q_and_a_list          = new Question_Answers_List();
		$this->q_attempt             = new Quiz_Attempts_List();
		$this->quiz                  = new Quiz();
		$this->tools                 = new Tools();
		$this->user                  = new User();
		$this->user_preference       = new UserPreference();
		$this->theme_compatibility   = new Theme_Compatibility();
		$this->gutenberg             = new Gutenberg();
		$this->course_settings_tabs  = new Course_Settings_Tabs();
		$this->withdraw              = new Withdraw();
		$this->upgrader              = new Upgrader();
		$this->dashboard             = new Dashboard();
		$this->form_handler          = new FormHandler();
		$this->frontend              = new Frontend();
		$this->rest_api              = new RestAPI();
		$this->setup                 = new Tutor_Setup();
		$this->private_course_access = new Private_Course_Access();
		$this->course_filter         = new Course_Filter();
		$this->permalink             = new Permalink();

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
		$this->rest_auth       = new RestAuth();

		/**
		 * New Course Builder.
		 *
		 * @since 3.0.0
		 */
		new QuizBuilder();

		/**
		 * Tutor native e-commerce
		 *
		 * @since 3.0.0
		 */
		new Ecommerce();

		/**
		 * Data migrations
		 *
		 * @since 3.8.0
		 */
		new Migration();

		/**
		 * Multisite manager
		 *
		 * @since 4.3.0
		 */
		if ( is_multisite() ) {
			new Multisite();
		}

		/**
		 * Run Method
		 *
		 * @since 1.2.0
		 */
		$this->run();

		do_action( 'tutor_loaded' );

		add_action( 'init', array( $this, 'init_action' ) );
		add_action( 'init', array( $this, 'update_instructor_capability' ) );

		/**
		 * Check activated plugin
		 *
		 * @since 1.5.7
		 */

		add_action( 'activated_plugin', array( $this, 'activated_tutor' ), 10, 2 );

		/**
		 * Redirect to setup page
		 *
		 * @since 2.8.0
		 */
		add_action( 'admin_init', array( $this, 'redirect_to_setup_page' ) );
	}

	/**
	 * Check activated plugin
	 *
	 * @since 1.5.7
	 *
	 * @param mixed $plugin plugin.
	 * @param mixed $network_wide network wide.
	 *
	 * @return void
	 */
	public function activated_tutor( $plugin, $network_wide = null ) {
		if ( tutor()->basename === $plugin ) {
			$this->redirect_to_setup_page();
		}
	}

	/**
	 * Redirect to setup page
	 *
	 * @since 2.8.0
	 *
	 * @return void
	 */
	public function redirect_to_setup_page() {
		if ( ( ! get_option( 'tutor_wizard' ) ) && version_compare( TUTOR_VERSION, '1.5.6', '>' ) ) {
			if ( ! wp_doing_ajax() ) {
				update_option( 'tutor_wizard', 'active' );
				$is_wp_cli = defined( 'WP_CLI' ) && \WP_CLI;
				if ( ! $is_wp_cli ) {
					wp_safe_redirect( admin_url( 'admin.php?page=tutor-setup' ) );
				}
				exit;
			}
		}
	}

	/**
	 * Include utility functions
	 *
	 * @return void
	 */
	public function includes() {
		$tutor_path = plugin_dir_path( TUTOR_FILE );

		include $tutor_path . 'includes/tutor-general-functions.php';
		include $tutor_path . 'includes/tutor-template-functions.php';
		include $tutor_path . 'includes/tutor-template-hook.php';
		include $tutor_path . 'includes/translate-text.php';
		include $tutor_path . 'includes/country.php';
		include $tutor_path . 'includes/ecommerce-functions.php';

		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		// Only kirki latest has class KirkiMain.
		$is_kirki_active = \is_plugin_active( 'kirki-pro/kirki-pro.php' ) && class_exists( 'KirkiProMain' );

		if ( $is_kirki_active ) {
			$tutor_kirki_path = $tutor_path . 'includes/kirki/kirki.php';
			if ( file_exists( $tutor_kirki_path ) ) {
				include $tutor_kirki_path;
			}
		} else {
			$is_droip_active  = \is_plugin_active( 'droip/droip.php' );
			$tutor_droip_path = $tutor_path . 'includes/droip/droip.php';
			if ( $is_droip_active && file_exists( $tutor_droip_path ) ) {
				include $tutor_droip_path;
			}
		}
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
	 * Do some task during plugin activation.
	 *
	 * @since 4.0.0 Flush rewrite rules on activation.
	 * @since 4.3.0 Param $network_wide added for multisite.
	 *
	 * @param bool $network_wide If true, network-wide activation.
	 *
	 * @return void
	 */
	public static function tutor_activate( $network_wide = false ) {
		// Network-wide activation in Multisite.
		if ( is_multisite() && $network_wide ) {
			Multisite::activate_network();
			return;
		}

		// Rewrite Flush.
		Permalink::set_permalink_flag();

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
	 * @since 1.0.0
	 * @since 4.3.0 Database table creation delegated to TableManager.
	 *
	 * @return void
	 */
	public static function create_database() {
		TableManager::create_core_tables( true );
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
			'edit_tutor_courses',

			'edit_tutor_lesson',
			'read_tutor_lesson',
			'delete_tutor_lesson',
			'delete_tutor_lessons',
			'edit_tutor_lessons',
			'edit_tutor_lessons',
			'publish_tutor_lessons',

			'edit_tutor_quiz',
			'read_tutor_quiz',
			'delete_tutor_quiz',
			'delete_tutor_quizzes',
			'edit_tutor_quizzes',
			'edit_tutor_quizzes',
			'publish_tutor_quizzes',

			'edit_tutor_question',
			'read_tutor_question',
			'delete_tutor_question',
			'delete_tutor_questions',
			'edit_tutor_questions',
			'publish_tutor_questions',
			'edit_tutor_questions',
		);

		$instructor = get_role( $instructor_role );
		if ( $instructor ) {
			$instructor_cap = array(
				'edit_posts',
				'edit_published_posts',
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
	 * @since 3.4.1 Supported video sources added
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
			'course_permalink_base'             => 'courses',
			'lesson_permalink_base'             => 'lessons',
			'quiz_when_time_expires'            => 'autosubmit',
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
			'earning_instructor_commission'     => '80',

			// Default options for tutor ecommerce.
			'monetize_by'                       => Ecommerce::MONETIZE_BY,
			'currency_code'                     => 'USD',
			'currency_position'                 => 'left',
			'thousand_separator'                => ',',
			'decimal_separator'                 => '.',
			'number_of_decimals'                => '2',
			'is_coupon_applicable'              => 'on',
			'supported_video_sources'           => array(
				'html5',
				'external_url',
				'youtube',
				'vimeo',
				'embedded',
				'shortcode',
			),
		);

		return $options;
	}


	/**
	 * Create withdraw database
	 *
	 * @since 1.2.0
	 */
	public static function create_withdraw_database() {
		EarningsTable::create();
		WithdrawsTable::create();

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

	/**
	 * Handle plugin un-installation
	 *
	 * @since 2.6.2
	 *
	 * @return void
	 */
	public static function tutor_uninstall() {
		self::erase_tutor_data();
	}

	/**
	 * Erase tutor data
	 *
	 * @since 2.6.2
	 *
	 * @return void
	 */
	public static function erase_tutor_data() {
		global $wpdb;

		$is_erase_data = tutor_utils()->get_option( 'delete_on_uninstall' );
		// Deleting Data.

		if ( $is_erase_data ) {
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

			$in_clause   = QueryHelper::prepare_in_clause( $post_types );
			$tutor_posts = $wpdb->get_col( $wpdb->prepare( "SELECT ID from {$wpdb->posts} WHERE post_type IN({$in_clause}) ;" ) ); //phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared

			if ( is_array( $tutor_posts ) && count( $tutor_posts ) ) {
				foreach ( $tutor_posts as $post_id ) {
					// Delete categories.
					$terms = wp_get_object_terms( $post_id, CourseModel::COURSE_CATEGORY );
					foreach ( $terms as $term ) {
						wp_remove_object_terms( $post_id, array( $term->term_id ), CourseModel::COURSE_CATEGORY );
					}

					// Delete tags if available.
					$terms = wp_get_object_terms( $post_id, CourseModel::COURSE_TAG );
					foreach ( $terms as $term ) {
						wp_remove_object_terms( $post_id, array( $term->term_id ), CourseModel::COURSE_TAG );
					}

					// Delete All Meta.
					$wpdb->delete( $wpdb->postmeta, array( 'post_id' => $post_id ) );
					$wpdb->delete( $wpdb->posts, array( 'ID' => $post_id ) );
				}
			}

			/**
			 * Deleting Comments (reviews, questions, quiz_answers, etc)
			 */
			$tutor_comments = $wpdb->get_col( "SELECT comment_ID from {$wpdb->comments} WHERE comment_agent = 'comment_agent' ;" );
			if ( is_array( $tutor_comments ) && count( $tutor_comments ) ) {
				$in_clause = QueryHelper::prepare_in_clause( $tutor_comments );
				$wpdb->query( $wpdb->prepare( "DELETE from {$wpdb->commentmeta} WHERE comment_ID in({$in_clause}) " ) ); //phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
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

			// Deleting Tables.
			TableManager::drop_tables();

		}
	}

	/**
	 * Update instructor capability
	 *
	 * Remove other author items permission
	 *
	 * @since 4.0.5
	 *
	 * @since 4.0.6 read_private_{items} permissions removed
	 */
	public function update_instructor_capability() {
		// Remove edit_others content cap from tutor_instructor role.
		$is_removed_edit_other_items_permissions = get_option( 'tutor_removed_edit_other_items_permission', false );
		$is_removed_private_items_permissions    = get_option( 'tutor_removed_read_private_items_permission', false );

		$role = get_role( tutor()->instructor_role );
		if ( ! ( $role instanceof \WP_Role ) ) {
			return;
		}

		if ( ! $is_removed_edit_other_items_permissions ) {

			$caps_to_be_removed = array(
				'edit_others_tutor_courses',
				'edit_others_tutor_lessons',
				'edit_others_tutor_quizzes',
				'edit_others_tutor_questions',
				'edit_others_tutor_assignments',
			);

			foreach ( $caps_to_be_removed as $cap ) {
				if ( $role->has_cap( $cap ) ) {
					$role->remove_cap( $cap );
				}
			}

			update_option( 'tutor_removed_edit_other_items_permission', true, false );
		}

		// Removed private items permission @since 4.0.6.
		if ( ! $is_removed_private_items_permissions ) {
			$caps_to_be_removed = array(
				'read_private_tutor_courses',
				'read_private_tutor_lessons',
				'read_private_tutor_quizzes',
				'read_private_tutor_assignments',
				'read_private_tutor_questions',
			);

			foreach ( $caps_to_be_removed as $cap ) {
				if ( $role->has_cap( $cap ) ) {
					$role->remove_cap( $cap );
				}
			}

			update_option( 'tutor_removed_read_private_items_permission', true, false );
		}
	}
}
