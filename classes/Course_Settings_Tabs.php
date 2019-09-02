<?php
namespace TUTOR;

if ( ! defined( 'ABSPATH' ) )
	exit;

class Course_Settings_Tabs{

	private $args = array();

	public function __construct() {
		$this->args = $this->get_default_args();

		add_action( 'edit_form_after_editor', array($this, 'display') );
	}

	private function get_default_args(){
		$args = array(
			'general' => array(
				'label' => __('General', 'tutor'),
				'desc' => __('General Settings', 'tutor'),
				'callback'  => '',
				'fields'    => array(
					'enable_public_profile' => array(
						'type'      => 'checkbox',
						'label'     => __('Public Profile', 'tutor'),
						'label_title' => __('Enable', 'tutor'),
						'default' => '0',
						'desc'      => __('If your theme has its own styling, then you can turn it off to load CSS from the plugin directory', 'tutor'),
					),
				),

			),

			'contentdrip' => array(
				'label' => __('Content Drip', 'tutor'),
				'desc' => __('Tutor Content Drip allow you to schedule publish topics / lesson', 'tutor'),
				'callback'  => '',
				'fields'    => array(
					'enable_content_drip' => array(
						'type'      => 'checkbox',
						'label'     => __('Enable', 'tutor'),
						'label_title' => __('Enable', 'tutor'),
						'default' => '0',
						'desc'      => __('Enable / Disable content drip', 'tutor'),
					),
				),

			),

		);

		return apply_filters('tutor_course_settings_tabs', $args);
	}

	public function display( $post){
		$course_type = tutor()->course_post_type;

		if (tutils()->count($this->args) && $post->post_type === $course_type) {
			include tutor()->path . "views/metabox/course/settings-tabs.php";
		}
	}


}