<?php
namespace TUTOR_MT;

if ( ! defined( 'ABSPATH' ) )
	exit;

class init{
	public $version = TUTOR_MT_VERSION;
	public $path;
	public $url;
	public $basename;

	//Module
	public $multi_instructors;

	function __construct() {
		$this->path = plugin_dir_path(TUTOR_MT_FILE);
		$this->url = plugin_dir_url(TUTOR_MT_FILE);
		$this->basename = plugin_basename(TUTOR_MT_FILE);

		add_action('init', array($this, 'load_TUTOR_MT'));
	}

	public function load_TUTOR_MT(){
		/**
		 * Loading Autoloader
		 */

		spl_autoload_register(array($this, 'loader'));
		$this->multi_instructors = new MultiInstructors();

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

			$className = str_replace('TUTOR_MT/', 'classes/', $className);
			$file_name = $this->path.$className.'.php';

			if (file_exists($file_name) && is_readable( $file_name ) ) {
				require_once $file_name;
			}
		}
	}


	//Run the TUTOR right now
	public function run(){
		register_activation_hook( TUTOR_MT_FILE, array( $this, 'tutor_activate' ) );
	}

	/**
	 * Do some task during plugin activation
	 */
	public function tutor_activate(){
		$version = get_option('TUTOR_MT_version');
		//Save Option
		if ( ! $version){
			update_option('TUTOR_MT_version', TUTOR_MT_VERSION);
		}
	}


	public function add_options($attr){
		$attr['tutor_multi_instructors'] = array(
			'label' => __( 'Tutor Multi Instructors', 'tutor-multi-instructors' ),

			'sections'    => array(
				'general' => array(
					'label' => __('General', 'tutor-multi-instructors'),
					'desc' => __('Tutor Multi Instructors Settings', 'tutor-multi-instructors'),
					'fields' => array(
						'enable_course_multi_instructors' => array(
							'type'      => 'checkbox',
							'label'     => __('Enable Tutor Multi Instructors', 'tutor-multi-instructors'),
							'desc'      => __('By integrating Tutor Multi Instructors, you can add unlimited instructors to your course',	'tutor-multi-instructors'),
						),
					),
				),
			),
		);
		return $attr;
	}

}