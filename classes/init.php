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
	public function tutor_activate(){
		update_option('required_rewrite_flush', time());
	}


}

