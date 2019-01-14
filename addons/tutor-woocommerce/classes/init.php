<?php
namespace TUTOR_WC;

if ( ! defined( 'ABSPATH' ) )
	exit;

class init{
	public $version = TUTOR_WC_VERSION;
	public $path;
	public $url;
	public $basename;

	//Module
	private $woocommerce;

	function __construct() {
		$this->path = plugin_dir_path(TUTOR_WC_FILE);
		$this->url = plugin_dir_url(TUTOR_WC_FILE);
		$this->basename = plugin_basename(TUTOR_WC_FILE);

		add_action('init', array($this, 'load_tutor_wc'));
	}

	public function load_tutor_wc(){
		/**
		 * Loading Autoloader
		 */
		spl_autoload_register(array($this, 'loader'));
		if (tutor_utils()->has_wc()){
			$this->woocommerce = new  WooCommerce();
			add_filter('tutor/options/attr', array($this, 'add_options'));

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
				array('$1$2', DIRECTORY_SEPARATOR),
				$className
			);

			$className = str_replace('TUTOR_WC/', 'classes/', $className);
			$file_name = $this->path.$className.'.php';

			if (file_exists($file_name) && is_readable( $file_name ) ) {
				require_once $file_name;
			}
		}
	}


	//Run the TUTOR right now
	public function run(){
		register_activation_hook( TUTOR_WC_FILE, array( $this, 'tutor_activate' ) );
	}

	/**
	 * Do some task during plugin activation
	 */
	public function tutor_activate(){
		$version = get_option('tutor_wc_version');
		//Save Option
		if ( ! $version){
			update_option('tutor_wc_version', TUTOR_WC_VERSION);
		}
	}


	public function add_options($attr){

		$attr['woocommerce'] = array(
			'label' => __( 'WooCommerce', 'tutor' ),

			'sections'    => array(
				'general' => array(
					'label' => __('General', 'tutor'),
					'desc' => __('WooCommerce Settings', 'tutor'),
					'fields' => array(
						'enable_course_sell_by_woocommerce' => array(
							'type'      => 'checkbox',
							'label'     => __('Enable WooComerce to sell course', 'tutor'),
							'desc'      => __('By integrating WooCommerce, you can sell your course',	'tutor'),
						),
					),
				),
			),
		);

		return $attr;

	}

}