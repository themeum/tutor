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
	private $teacher;
	private $woocommerce;

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

		if (tutor_utils()->has_wc()){
			$this->woocommerce = new  Woo_Commerce();
		}
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
		register_activation_hook( TUTOR_FILE, array( $this, 'tutor_activate' ) );
	}

	/**
	 * Do some task during plugin activation
	 */
	public static function tutor_activate(){
		//Rewrite Flush
		update_option('required_rewrite_flush', time());
		self::manage_tutor_roles_and_permissions();


		$is_previously_installed = get_option('tutor_version', TUTOR_VERSION);
		if ( ! $is_previously_installed){
			self::save_data();
			update_option('tutor_version', TUTOR_VERSION);
		}
	}

	public static function manage_tutor_roles_and_permissions(){
		/**
		 * Add role for teacher
		 */
		$teacher_role = tutor()->teacher_role;

		remove_role($teacher_role);
		add_role( $teacher_role, __('Tutor Teacher', 'tutor'), array() );
		
		$custom_post_type_permission = array(
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
			'publish_tutor_lessons'
		);

		$teacher = get_role( $teacher_role );
		if ( $teacher ) {
			$teacher_cap = array (
				'edit_posts',
				'read',
				'upload_files',
			);

			$teacher_cap = array_merge($teacher_cap, $custom_post_type_permission);

			$can_publish_course = (bool) tutor_utils()->get_option('teacher_can_publish_course');
			if ($can_publish_course){
				$teacher_cap[] = 'publish_tutor_courses';
			}

			foreach ($teacher_cap as $cap){
				$teacher->add_cap( $cap );
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
			'post_content'  => '[tutor_dashboard]',
			'post_type'     => 'page',
			'post_status'   => 'publish',
		);
		$student_dashboard_page_id = wp_insert_post( $student_dashboard_args );
		tutor_utils()->update_option('student_dashboard', $student_dashboard_page_id);

		$teacher_registration_args = array(
			'post_title'    => __('Teacher Registration', 'tutor'),
			'post_content'  => '[tutor_teacher_registration_form]',
			'post_type'     => 'page',
			'post_status'   => 'publish',
		);
		$teacher_registration_id = wp_insert_post( $teacher_registration_args );


	}


}