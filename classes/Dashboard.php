<?php
/**
 * Manage Frontend Dashboard
 *
 * @package Tutor
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.3.4
 */

namespace TUTOR;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Dashboard Class
 *
 * @since 1.3.4
 */
class Dashboard {

	/**
	 * Constructor
	 *
	 * @since 1.3.4
	 * @return void
	 */
	public function __construct() {
		add_action( 'tutor_load_template_after', array( $this, 'tutor_load_template_after' ), 10, 2 );
		add_filter( 'should_tutor_load_template', array( $this, 'should_tutor_load_template' ), 10, 2 );
		add_action( 'wp_ajax_tutor_create_new_draft_course', array( __CLASS__, 'create_new_draft_course' ) );
	}

	/**
	 * Load template after
	 *
	 * @since 1.3.4
	 * @return void
	 */
	public function tutor_load_template_after() {
		global $wp_query;

		$tutor_dashboard_page = tutor_utils()->array_get( 'query_vars.tutor_dashboard_page', $wp_query );
		if ( 'create-course' === $tutor_dashboard_page ) {
			wp_reset_query();
		}
	}

	/**
	 * Check template need to load or not
	 *
	 * @since 1.3.4
	 *
	 * @param bool   $bool true or false.
	 * @param string $template template name.
	 *
	 * @return boolean
	 */
	public function should_tutor_load_template( $bool, $template ) {
		if ( 'dashboard.create-course' === $template && ! tutor()->has_pro ) {
			return false;
		}
		return $bool;
	}

	/**
	 * Create new draft course
	 *
	 * @since 2.0.3
	 * @return void  JSON response
	 */
	public static function create_new_draft_course() {
		$can_publish_course = (bool) current_user_can( 'tutor_instructor' ) || current_user_can( 'administrator' );
		tutor_utils()->checking_nonce();
		if ( $can_publish_course ) {
			$post_type = tutor()->course_post_type;
			$course_id = wp_insert_post(
				array(
					'post_title'  => __( 'New Course', 'tutor' ),
					'post_type'   => $post_type,
					'post_status' => 'draft',
					'post_name'   => 'new-course',
				)
			);
			if ( $course_id ) {
				$response = array(
					'course_id' => $course_id,
					'url'       => add_query_arg(
						array(
							'course_ID' => $course_id,
						),
						tutor_utils()->tutor_dashboard_url( 'create-course' )
					),
				);
				wp_send_json_success( $response );
			} else {
				wp_send_json_error();
			}
		} else {
			$response = array(
				'error_message' => __( 'You are not allowed to publish course', 'tutor' ),
			);
			wp_send_json_error( $response );
		}
	}
}
