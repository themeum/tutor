<?php
namespace TUTOR_EDD;

if ( ! defined( 'ABSPATH' ) )
	exit;

class init{
	public $version = TUTOR_EDD_VERSION;
	public $path;
	public $url;
	public $basename;

	//Module
	public $tutor_edd;

	function __construct() {
		$this->path = plugin_dir_path(TUTOR_EDD_FILE);
		$this->url = plugin_dir_url(TUTOR_EDD_FILE);
		$this->basename = plugin_basename(TUTOR_EDD_FILE);

		add_action('init', array($this, 'load_TUTOR_EDD'));
	}

	public function load_TUTOR_EDD(){
		$hasEdd = tutor_utils()->has_edd();
		if ( ! $hasEdd){
			return;
		}

		/**
		 * Loading Autoloader
		 */

		spl_autoload_register(array($this, 'loader'));
		$this->tutor_edd = new TutorEDD();

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

			$className = str_replace('TUTOR_EDD/', 'classes/', $className);
			$file_name = $this->path.$className.'.php';

			if (file_exists($file_name) && is_readable( $file_name ) ) {
				require_once $file_name;
			}
		}
	}


	//Run the TUTOR right now
	public function run(){
		register_activation_hook( TUTOR_EDD_FILE, array( $this, 'tutor_activate' ) );
	}

	/**
	 * Do some task during plugin activation
	 */
	public function tutor_activate(){
		$version = get_option('TUTOR_EDD_version');
		//Save Option
		if ( ! $version){
			update_option('TUTOR_EDD_version', TUTOR_EDD_VERSION);
		}
	}

	public function add_options($attr){
		$attr['tutor_edd'] = array(
			'label' => __( 'EDD', 'tutor-edd' ),

			'sections'    => array(
				'general' => array(
					'label' => __('General', 'tutor-edd'),
					'desc' => __('Tutor Course Attachments Settings', 'tutor-edd'),
					'fields' => array(
						'enable_tutor_edd' => array(
							'type'          => 'checkbox',
							'label'         => __('Enable EDD', 'tutor'),
							'desc'          => __('This will enable sell your product via EDD',	'tutor'),
						),
					),
				),
			),
		);
		return $attr;
	}

}