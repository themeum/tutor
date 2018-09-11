<?php
namespace LMS;

if ( ! defined( 'ABSPATH' ) )
	exit;

class init{
	public $version = LMS_VERSION;
	public $path;
	public $url;
	public $basename;

	//Components
	public $utils;
	public $admin;
	public $options;

	private $post_types;
	private $assets;
	private $course;
	private $lesson;
	private $rewrite_rules;
	private $template;

	function __construct() {
		$this->path = plugin_dir_path(LMS_FILE);
		$this->url = plugin_dir_url(LMS_FILE);
		$this->basename = plugin_basename(LMS_FILE);

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
		$this->course = new Course();
		$this->lesson = new Lesson();
		$this->rewrite_rules = new Rewrite_Rules();
		$this->template = new Template();
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

			$className = str_replace('LMS/', 'classes/', $className);
			$file_name = $this->path.$className.'.php';

			if (file_exists($file_name) && is_readable( $file_name ) ) {
				require_once $file_name;
			}
		}
	}

	public function include_template_functions(){
		include lms()->path.'includes/lms-template-functions.php';
		include lms()->path.'includes/lms-template-hook.php';
	}

	//Run the LMS right now
	public function run(){



	}



}

