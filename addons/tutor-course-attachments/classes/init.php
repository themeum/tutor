<?php
namespace TUTOR_CA;

if ( ! defined( 'ABSPATH' ) )
	exit;

class init{
	public $version = TUTOR_CA_VERSION;
	public $path;
	public $url;
	public $basename;

	//Module
	public $course_attachments;

	function __construct() {
		$this->path = plugin_dir_path(TUTOR_CA_FILE);
		$this->url = plugin_dir_url(TUTOR_CA_FILE);
		$this->basename = plugin_basename(TUTOR_CA_FILE);

		add_action('init', array($this, 'load_TUTOR_CA'));
	}

	public function load_TUTOR_CA(){
		/**
		 * Loading Autoloader
		 */

		spl_autoload_register(array($this, 'loader'));
		$this->course_attachments = new CourseAttachments();

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

			$className = str_replace('TUTOR_CA/', 'classes/', $className);
			$file_name = $this->path.$className.'.php';

			if (file_exists($file_name) && is_readable( $file_name ) ) {
				require_once $file_name;
			}
		}
	}


	//Run the TUTOR right now
	public function run(){
		register_activation_hook( TUTOR_CA_FILE, array( $this, 'tutor_activate' ) );
	}

	/**
	 * Do some task during plugin activation
	 */
	public function tutor_activate(){
		$version = get_option('TUTOR_CA_version');
		//Save Option
		if ( ! $version){
			update_option('TUTOR_CA_version', TUTOR_CA_VERSION);
		}
	}

	public function add_options($attr){
		$attr['tutor_course_attachments'] = array(
			'label' => __( 'Tutor Course Attachments', 'tutor-course-attachments' ),

			'sections'    => array(
				'general' => array(
					'label' => __('General', 'tutor-course-attachments'),
					'desc' => __('Tutor Course Attachments Settings', 'tutor-course-attachments'),
					'fields' => array(
						'course_allow_upload_private_files' => array(
							'type'          => 'checkbox',
							'label'         => __('Private file uploading', 'tutor'),
							'label_title'   => __('Allow uploading private files', 'tutor'),
							'desc'          => __('This will allow uploading files to courses and only enrolled students can access these files',	'tutor'),
						),
					),
				),
			),
		);

		return $attr;
	}

}