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

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! session_id() ) {
	session_start();
}
class Dashboard {

	public function __construct() {
		add_action( 'tutor_load_template_before', array( $this, 'tutor_load_template_before' ), 10, 2 );
		add_action( 'tutor_load_template_after', array( $this, 'tutor_load_template_after' ), 10, 2 );
		add_filter( 'should_tutor_load_template', array( $this, 'should_tutor_load_template' ), 10, 2 );
		add_action( 'wp_ajax_tutor_unset_session_course_id', array( __CLASS__, 'unset_session') );
	}

	/**
	 * @param $template
	 * @param $variables
	 */
	public function tutor_load_template_before( $template, $variables ) {
		global $wp_query;

		$tutor_dashboard_page = tutor_utils()->array_get( 'query_vars.tutor_dashboard_page', $wp_query );
		if ( $tutor_dashboard_page === 'create-course' ) {
			global $post;
			wp_reset_query();

			/**
			 * Get course which currently in edit, or insert new course
			 * if not in $_GET then get from session
			 */
			$course_ID = (int) sanitize_text_field( tutor_utils()->array_get( 'course_ID', $_GET ) );
			if ( ! $course_ID ) {
				
				if ( isset( $_SESSION['tutor_course_id'] ) && self::is_session_course_valid( $_SESSION['tutor_course_id'] ) ) {
					$course_ID = (int) $_SESSION['tutor_course_id'];
				} else {
					//remove session in case id exists but not valid
					self::remove_course_id_from_session();
				}
			}
			if ( $course_ID ) {
				$post_id = $course_ID;
			} else {
				$post_type = tutor()->course_post_type;
				$post_id   = wp_insert_post(
					array(
						'post_title'  => __( 'Auto Draft', 'tutor' ),
						'post_type'   => $post_type,
						'post_status' => 'draft',
					)
				);
				$_SESSION['tutor_course_id'] = $post_id;
			}
			$post = get_post( $post_id );
			setup_postdata( $post );
		}
	}

	public function tutor_load_template_after() {
		global $wp_query;

		$tutor_dashboard_page = tutor_utils()->array_get( 'query_vars.tutor_dashboard_page', $wp_query );
		if ( $tutor_dashboard_page === 'create-course' ) {
			wp_reset_query();
		}
	}

	public function should_tutor_load_template( $bool, $template ) {
		if ( $template === 'dashboard.create-course' && ! tutor()->has_pro ) {
			return false;
		}
		return $bool;
	}

	/**
	 * Destroy course id from session if exists
	 *
	 * @since v2.0.3
	 *
	 * @return void  send JSON response
	 */
	public static function unset_session() {
		tutor_utils()->checking_nonce();
		self::remove_course_id_from_session();
		wp_send_json_success();
	}

	/**
	 * Remove course id session token
	 *
	 * @since v2.0.3
	 *
	 * @return void
	 */
	public static function remove_course_id_from_session() {
		if ( isset( $_SESSION['tutor_course_id'] ) ) {
			unset( $_SESSION['tutor_course_id'] );
		}
	}

	/**
	 * Check if course id in session is valid
	 *
	 * @since v2.0.3
	 *
	 * @param integer $course_id
	 *
	 * @return boolean
	 */
	public static function is_session_course_valid( int $course_id ): bool {
		$post = get_post( $course_id );
		return is_a( $post, 'WP_Post' ) && tutor()->course_post_type === $post->post_type;
	}
}
