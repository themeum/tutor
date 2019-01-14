<?php
namespace TUTOR_PREREQUISITES;

if ( ! defined( 'ABSPATH' ) )
	exit;

class init{
	public $version = TUTOR_PREREQUISITES_VERSION;
	public $path;
	public $url;
	public $basename;

	//Module
	public $prerequisites;

	function __construct() {
		$this->path = plugin_dir_path(TUTOR_PREREQUISITES_FILE);
		$this->url = plugin_dir_url(TUTOR_PREREQUISITES_FILE);
		$this->basename = plugin_basename(TUTOR_PREREQUISITES_FILE);

		add_action('init', array($this, 'load_TUTOR_PREREQUISITES'));
	}

	public function load_TUTOR_PREREQUISITES(){
		/**
		 * Loading Autoloader
		 */

		spl_autoload_register(array($this, 'loader'));
		$this->prerequisites = new Prerequisites();

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

			$className = str_replace('TUTOR_PREREQUISITES/', 'classes/', $className);
			$file_name = $this->path.$className.'.php';

			if (file_exists($file_name) && is_readable( $file_name ) ) {
				require_once $file_name;
			}
		}
	}


	//Run the TUTOR right now
	public function run(){
		register_activation_hook( TUTOR_PREREQUISITES_FILE, array( $this, 'tutor_activate' ) );
	}

	/**
	 * Do some task during plugin activation
	 */
	public function tutor_activate(){
		$version = get_option('TUTOR_PREREQUISITES_version');
		//Save Option
		if ( ! $version){
			update_option('TUTOR_PREREQUISITES_version', TUTOR_PREREQUISITES_VERSION);
		}
	}


	public function add_options($attr){
		$attr['tutor_prerequisites'] = array(
			'label' => __( 'Tutor Prerequisites ', 'tutor-prerequisites' ),

			'sections'    => array(
				'general' => array(
					'label' => __('General', 'tutor-prerequisites'),
					'desc' => __('Tutor Prerequisites Settings', 'tutor-prerequisites'),
					'fields' => array(
						'enable_course_prerequisites' => array(
							'type'      => 'checkbox',
							'label'     => __('Enable Tutor Prerequisites', 'tutor-prerequisites'),
							'desc'      => '',
						),
					),
				),
			),
		);
		return $attr;
	}

}