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

		/**
		 * Update the post
		 */

		$content = wp_kses_post(tutor_utils()->array_get('content', $_POST));
		$title = sanitize_text_field(tutor_utils()->array_get('title', $_POST));
		$tax_input = tutor_utils()->array_get('tax_input', $_POST);

		$postData = array(
			'ID'            => $post_ID,
			'post_title'    => $title,
			'post_name'     => sanitize_title($title),
			'post_content'  => $content,
		);

		//Publish or Pending...
		if (tutor_utils()->array_get('course_submit_btn', $_POST) === 'save_course_as_draft'){
			$postData['post_status'] = 'draft';
		}else{
			$can_publish_course = (bool) tutor_utils()->get_option('instructor_can_publish_course');
			if ($can_publish_course){
				$postData['post_status'] = 'publish';
			}else{
				$postData['post_status'] = 'pending';
			}
		}

		wp_update_post($postData);

		/**
		 * Setting Thumbnail
		 */
		$_thumbnail_id = (int) sanitize_text_field(tutor_utils()->array_get('tutor_course_thumbnail_id', $_POST));
		if ($_thumbnail_id){
			update_post_meta($post_ID, '_thumbnail_id', $_thumbnail_id);
		}else{
			delete_post_meta($post_ID, '_thumbnail_id');
		}

		/**
		 * Adding taxonomy
		 */
		if ( tutor_utils()->count($tax_input) ) {
			foreach ( $tax_input as $taxonomy => $tags ) {
				$taxonomy_obj = get_taxonomy($taxonomy);
				if ( ! $taxonomy_obj ) {
					/* translators: %s: taxonomy name */
					_doing_it_wrong( __FUNCTION__, sprintf( __( 'Invalid taxonomy: %s.' ), $taxonomy ), '4.4.0' );
					continue;
				}

				// array = hierarchical, string = non-hierarchical.
				if ( is_array( $tags ) ) {
					$tags = array_filter($tags);
				}
				wp_set_post_terms( $post_ID, $tags, $taxonomy );
			}
		}

		/**
		 * Adding support for do_action();
		 */
		do_action( "save_post_{$course_post_type}", $post_ID, $post, $update );
		do_action( 'save_post', $post_ID, $post, $update );
		do_action( 'save_tutor_course', $post_ID, $postData);

		/**
		 * If update request not comes from edit page, redirect it to edit page
		 */
		$edit_mode = (int) sanitize_text_field(tutor_utils()->array_get('course_ID', $_GET));
		if ( ! $edit_mode){
			$edit_page_url = add_query_arg(array('course_ID' => $post_ID));
			wp_redirect($edit_page_url);
			die();
		}

		/**
		 * Finally redirect it to previous page to avoid multiple post request
		 */
		wp_redirect(tutor_utils()->referer());
		die();
	}

}