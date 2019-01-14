<?php
namespace TUTOR_REPORT;

if ( ! defined( 'ABSPATH' ) )
	exit;

class init{
	public $version = TUTOR_REPORT_VERSION;
	public $path;
	public $url;
	public $basename;

	//Module
	public $report;

	function __construct() {
		$this->path = plugin_dir_path(TUTOR_REPORT_FILE);
		$this->url = plugin_dir_url(TUTOR_REPORT_FILE);
		$this->basename = plugin_basename(TUTOR_REPORT_FILE);

		add_action('init', array($this, 'load_TUTOR_REPORT'));
	}

	public function load_TUTOR_REPORT(){
		/**
		 * Loading Autoloader
		 */

		spl_autoload_register(array($this, 'loader'));
		$this->report = new Report();

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

			$className = str_replace('TUTOR_REPORT/', 'classes/', $className);
			$file_name = $this->path.$className.'.php';

			if (file_exists($file_name) && is_readable( $file_name ) ) {
				require_once $file_name;
			}
		}
	}


	//Run the TUTOR right now
	public function run(){
		register_activation_hook( TUTOR_REPORT_FILE, array( $this, 'tutor_activate' ) );
	}

	/**
	 * Do some task during plugin activation
	 */
	public function tutor_activate(){
		$version = get_option('TUTOR_REPORT_version');
		//Save Option
		if ( ! $version){
			update_option('TUTOR_REPORT_version', TUTOR_REPORT_VERSION);
		}
	}

	public function add_options($attr){
		$attr['tutor_report'] = array(
			'label' => __( 'Report', 'tutor-report' ),

			'sections'    => array(
				'general' => array(
					'label' => __('General', 'tutor-report'),
					'desc' => __('Check tutor performance', 'tutor-report'),
					'fields' => array(
						'enable_report' => array(
							'type'          => 'checkbox',
							'label'         => __('Enable Report', 'tutor'),
							'desc'          => __('Check to get the report for Tutor LMS',	'tutor'),
						),
					),
				),
			),
		);
		return $attr;
	}

}