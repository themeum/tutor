<?php
namespace TUTOR_CP;

if ( ! defined( 'ABSPATH' ) )
	exit;

class init{
	public $version = TUTOR_CP_VERSION;
	public $path;
	public $url;
	public $basename;

	//Module
	public $course_preview;

	function __construct() {
		$this->path = plugin_dir_path(TUTOR_CP_FILE);
		$this->url = plugin_dir_url(TUTOR_CP_FILE);
		$this->basename = plugin_basename(TUTOR_CP_FILE);

		add_action('init', array($this, 'load_TUTOR_CP'));
	}

	public function load_TUTOR_CP(){
		/**
		 * Loading Autoloader
		 */

		spl_autoload_register(array($this, 'loader'));
		$this->course_preview = new CoursePreview();

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

			$className = str_replace('TUTOR_CP/', 'classes/', $className);
			$file_name = $this->path.$className.'.php';

			if (file_exists($file_name) && is_readable( $file_name ) ) {
				require_once $file_name;
			}
		}
	}


	//Run the TUTOR right now
	public function run(){
		register_activation_hook( TUTOR_CP_FILE, array( $this, 'tutor_activate' ) );
	}

	/**
	 * Do some task during plugin activation
	 */
	public function tutor_activate(){
		$version = get_option('TUTOR_CP_version');
		//Save Option
		if ( ! $version){
			update_option('TUTOR_CP_version', TUTOR_CP_VERSION);
		}
	}

	public function add_options($attr){
		$attr['tutor_course_preview'] = array(
			'label' => __( 'Course Preview', 'tutor-course-preview' ),

			'sections'    => array(
				'general' => array(
					'label' => __('General', 'tutor-course-preview'),
					'desc' => __('Tutor Course Attachments Settings', 'tutor-course-preview'),
					'fields' => array(
						'enable_course_preview' => array(
							'type'          => 'checkbox',
							'label'         => __('Enable Course Preview', 'tutor'),
							'desc'          => __('This will allow user/guest to check some preview lesson in course before enroll',	'tutor'),
						),
					),
				),
			),
		);
		return $attr;
	}

}