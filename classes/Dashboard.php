<?php
/**
 * Dashboard class
 *
 * @author: themeum
 * @author_uri: https://themeum.com
 * @package Tutor
 * @since v.1.3.4
 */


namespace TUTOR;


if ( ! defined( 'ABSPATH' ) )
	exit;

class Dashboard {

	public function __construct() {
		add_action('tutor_load_template_before', array($this, 'tutor_load_template_before'), 10, 2);
		add_action('tutor_load_template_after', array($this, 'tutor_load_template_after'), 10, 2);

		add_action('tutor_action_tutor_add_course_builder', array($this, 'tutor_add_course_builder'));
	}

	/**
	 * @param $template
	 * @param $variables
	 */
	public function tutor_load_template_before($template, $variables){
		global $wp_query;

		$tutor_dashboard_page = tutor_utils()->array_get('query_vars.tutor_dashboard_page', $wp_query);
		if ($tutor_dashboard_page === 'create-course') {
			global $post;
			wp_reset_query();

			/**
			 * Get course which currently in edit, or insert new course
			 */

			$course_ID = (int) sanitize_text_field(tutor_utils()->array_get('course_ID', $_GET));

			if ($course_ID){
				$post_id = $course_ID;
			}else{
				$post_type = tutor()->course_post_type;
				$post_id = wp_insert_post( array( 'post_title' => __( 'Auto Draft', 'tutor' ), 'post_type' => $post_type, 'post_status' => 'auto-draft' ) );
			}

			$post = get_post( $post_id );
			setup_postdata( $post );
		}

	}

	public function tutor_load_template_after(){
		global $wp_query;

		$tutor_dashboard_page = tutor_utils()->array_get('query_vars.tutor_dashboard_page', $wp_query);
		if ($tutor_dashboard_page === 'create-course'){
			wp_reset_query();
		}

	}

	/**
	 * Process course submission from frontend course builder
	 *
	 * @since v.1.3.4
	 */
	public function tutor_add_course_builder(){
		//Checking nonce
		tutor_utils()->checking_nonce();

		$course_post_type = tutor()->course_post_type;

		$course_ID = (int) sanitize_text_field(tutor_utils()->array_get('course_ID', $_POST));
		$post_ID = (int) sanitize_text_field(tutor_utils()->array_get('post_ID', $_POST));

		$post = get_post($post_ID);
		$update = true;

		do_action( "save_post_{$course_post_type}", $post_ID, $post, $update );
		do_action( 'save_post', $post_ID, $post, $update );

		wp_redirect(tutor_utils()->referer());
		die();
	}

}