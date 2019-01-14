<?php
namespace TUTOR_EMAIL;

if ( ! defined( 'ABSPATH' ) )
	exit;

class init{
	public $version = TUTOR_EMAIL_VERSION;
	public $path;
	public $url;
	public $basename;

	//Module
	private $email_notification;

	function __construct() {
		$this->path = plugin_dir_path(TUTOR_EMAIL_FILE);
		$this->url = plugin_dir_url(TUTOR_EMAIL_FILE);
		$this->basename = plugin_basename(TUTOR_EMAIL_FILE);

		add_action('init', array($this, 'load_TUTOR_EMAIL'));
	}

	public function load_TUTOR_EMAIL(){
		/**
		 * Loading Autoloader
		 */

		spl_autoload_register(array($this, 'loader'));
		$this->email_notification = new EmailNotification();

		add_filter('tutor/options/attr', array($this, 'add_options'));
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
				array('$1$2', DIRECTORY_SEPARATOR),
				$className
			);

			$className = str_replace('TUTOR_EMAIL/', 'classes/', $className);
			$file_name = $this->path.$className.'.php';

			if (file_exists($file_name) && is_readable( $file_name ) ) {
				require_once $file_name;
			}
		}
	}


	//Run the TUTOR right now
	public function run(){
		register_activation_hook( TUTOR_EMAIL_FILE, array( $this, 'tutor_activate' ) );
	}

	/**
	 * Do some task during plugin activation
	 */
	public function tutor_activate(){
		$version = get_option('TUTOR_EMAIL_version');
		//Save Option
		if ( ! $version){
			update_option('TUTOR_EMAIL_version', TUTOR_EMAIL_VERSION);
		}
	}

	public function add_options($attr){
		$attr['email_notification'] = array(
			'label'     => __('E-Mail Notification', 'tutor'),
			'sections'    => array(
				'general' => array(
					'label' => __('Enable/Disable', 'tutor'),
					'desc' => __('Enable Disable Option to on/off notification on various event', 'tutor'),
					'fields' => array(
						'email_to_students' => array(
							'type'      => 'checkbox',
							'label'     => __('E-Mail to Students', 'tutor'),
							'options'   => array(
								'quiz_completed' => __('Quiz Completed', 'tutor'),
								'completed_course' => __('Completed a course', 'tutor'),
							),
							'desc'      => __('Select when to sent notification to the students',	'tutor'),
						),
						'email_to_teachers' => array(
							'type'      => 'checkbox',
							'label'     => __('E-Mail to Teachers', 'tutor'),
							'options'   => array(
								'a_student_enrolled_in_course'              => __('A Student enrolled in course ', 'tutor'),
								'a_student_completed_course'            => __('A Student Completed Course', 'tutor'),
								'a_student_completed_lesson'            => __('A Student Completed Lesson', 'tutor'),
								'a_student_placed_question'             => __('A Student placed question to course', 'tutor'),
							),
							'desc'      => __('Select when to sent notification to the teachers',	'tutor'),
						),
					),
				),
				'email_settings' => array(
					'label' => __('E-Mail Settings', 'tutor'),
					'desc' => __('Check and place necessary information here.', 'tutor'),
					'fields' => array(
						'email_from_name' => array(
							'type'      => 'text',
							'label'     => __('E-Mail From Name', 'tutor'),
							'default'   => get_option('blogname'),
							'desc'      => __('The name under which all the emails will be sent',	'tutor'),
						),
						'email_from_address' => array(
							'type'      => 'text',
							'label'     => __('From E-Mail Address', 'tutor'),
							'default'   => get_option('admin_email'),
							'desc'      => __('The E-Mail address from which all emails will be sent', 'tutor'),
						),
						'email_footer_text' => array(
							'type'      => 'textarea',
							'label'     => __('E-Mail Footer Text', 'tutor'),
							'default'   => '',
							'desc'      => __('The text to appear in E-Mail template footer', 'tutor'),
						),
					),
				),

			),
		);


		return $attr;
	}

}