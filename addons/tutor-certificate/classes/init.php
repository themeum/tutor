<?php
namespace TUTOR_CERT;

if ( ! defined( 'ABSPATH' ) )
	exit;

class init{
	public $version = TUTOR_CERT_VERSION;
	public $path;
	public $url;
	public $basename;

	//Module
	public $certificate;

	function __construct() {
		$this->path = plugin_dir_path(TUTOR_CERT_FILE);
		$this->url = plugin_dir_url(TUTOR_CERT_FILE);
		$this->basename = plugin_basename(TUTOR_CERT_FILE);

		add_action('init', array($this, 'load_TUTOR_CERT'));
	}

	public function load_TUTOR_CERT(){
		/**
		 * Loading Autoloader
		 */

		spl_autoload_register(array($this, 'loader'));
		$this->certificate = new Certificate();

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

			$className = str_replace('TUTOR_CERT/', 'classes/', $className);
			$file_name = $this->path.$className.'.php';

			if (file_exists($file_name) && is_readable( $file_name ) ) {
				require_once $file_name;
			}
		}
	}


	//Run the TUTOR right now
	public function run(){
		register_activation_hook( TUTOR_CERT_FILE, array( $this, 'tutor_activate' ) );
	}

	/**
	 * Do some task during plugin activation
	 */
	public function tutor_activate(){
		$version = get_option('TUTOR_CERT_version');
		//Save Option
		if ( ! $version){
			update_option('TUTOR_CERT_version', TUTOR_CERT_VERSION);
		}
	}

	public function add_options($attr){
		$attr['tutor_certificate'] = array(
			'label' => __( 'Tutor Certificate', 'tutor-certificate' ),

			'sections'    => array(
				'general' => array(
					'label' => __('General', 'tutor-certificate'),
					'desc' => __('Tutor Certificate', 'tutor-certificate'),
					'fields' => array(
						'enable_course_certificate' => array(
							'type'      => 'checkbox',
							'label'     => __('Enable Tutor Certificate', 'tutor-certificate'),
							'desc'      => __('By integrating Tutor Certificate, student will be able to download the certificate',	'tutor-certificate'),
						),
						'tutor_cert_authorised_name' => array(
							'type'      => 'text',
							'label'     => __('Authorised Name', 'tutor-certificate'),
							'desc'      => __('Authorised name will be printed under signature.',	'tutor-certificate'),
						),
						'tutor_cert_authorised_company_name' => array(
							'type'      => 'text',
							'label'     => __('Authorised Company Name', 'tutor-certificate'),
							'desc'      => __('Authorised company name will be printed under authorised name.',	'tutor-certificate'),
						),
						'tutor_cert_signature_image_id' => array(
							'type'          => 'media',
							'label'         => __('Upload Signature', 'tutor-certificate'),
							'attr'          => array('media_type' => 'image'), //image,file
							'desc'          => __('Upload a signature that will be printed at certificate.',	'tutor-certificate'),
						),

					),
				),
			),
		);
		return $attr;
	}

}