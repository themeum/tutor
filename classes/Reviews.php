<?php
/**
 * Tutor Ratings
 *
 * @package Tutor\Reviews
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.0
 */

namespace TUTOR;

use Tutor\Helpers\QueryHelper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Handle ratings related logics
 *
 * @since 2.0.0
 */
class Reviews {

	const COURSE_RATING = 'tutor_course_rating';

	/**
	 * Handle actions & dependencies
	 *
	 * @since 2.0.0
	 */
	public function __construct() {
		/**
		 * Delete reviews action
		 */
		add_action( 'wp_ajax_tutor_delete_review', array( $this, 'delete_review' ) );
		add_action( 'wp_ajax_tutor_single_course_reviews_load_more', array( $this, 'tutor_single_course_reviews_load_more' ) );
		add_action( 'wp_ajax_nopriv_tutor_single_course_reviews_load_more', array( $this, 'tutor_single_course_reviews_load_more' ) );
		add_action( 'wp_ajax_tutor_change_review_status', array( $this, 'tutor_change_review_status' ) );
	}

	/**
	 * Handle ajax request for deleting review
	 *
	 * @since 2.0.0
	 */
	public function delete_review() {
		tutor_utils()->checking_nonce();

		// Check if user is privileged.
		if ( ! current_user_can( 'administrator' ) ) {
			wp_send_json_error( array( 'message' => tutor_utils()->error_message() ) );
		}
		$review_id = Input::post( 'id', 0, Input::TYPE_INT );
		$delete    = self::delete( $review_id );

		if ( $delete ) {
			wp_send_json_success();
		} else {
			wp_send_json_error( array( 'message' => __( 'Something went wrong!', 'tutor' ) ) );
		}
		exit;
	}

	/**
	 * Delete review
	 *
	 * @since 2.0.0
	 *
	 * @param int $id review id required.
	 *
	 * @return bool true or false.
	 */
	public static function delete( int $id ): bool {
		$id = sanitize_text_field( $id );
		global $wpdb;
		$comment_table = $wpdb->comments;
		$delete        = $wpdb->delete(
			$comment_table,
			array(
				'comment_ID' => $id,
			)
		);
		return $delete ? true : false;
	}

	/**
	 * Load more reviews
	 *
	 * @since 2.0.0
	 *
	 * @return void send wp_json response
	 */
	public function tutor_single_course_reviews_load_more() {
		tutor_utils()->checking_nonce();

		ob_start();
		tutor_load_template( 'single.course.reviews' );
		$html = ob_get_clean();

		wp_send_json_success( array( 'html' => $html ) );
	}

	/**
	 * Change review status
	 *
	 * @since 2.0.0
	 *
	 * @return void send wp_json response
	 */
	public function tutor_change_review_status() {

		tutor_utils()->checking_nonce();

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Only admin can change review status', 'tutor' ) ) );
			exit;
		}

		$review_id = Input::post( 'id', 0, Input::TYPE_INT );
		$status    = Input::post( 'status' );

		global $wpdb;
		$wpdb->update( $wpdb->comments, array( 'comment_approved' => $status ), array( 'comment_ID' => $review_id ) );

		if ( 'approved' === $status ) {
			do_action( 'tutor_course_review_approved', $review_id );
		}

		wp_send_json_success();
	}

	/**
	 * Retrieves reviews for a specific course by its ID.
	 *
	 * @since 3.8.1
	 *
	 * @param int $course_id The ID of the course for which reviews are being fetched.
	 *
	 * @return array An array of reviews. If there is an error or no reviews
	 *               are found, an empty array is returned.
	 */
	public function get_reviews_by_course_id( $course_id ): array {

		$where = array(
			'comment_type'    => self::COURSE_RATING,
			'comment_post_ID' => $course_id,
			'comment_agent'   => 'TutorLMSPlugin',
		);

		$result = QueryHelper::get_all( 'comments', $where, 'comment_post_ID', -1, '', ARRAY_A );

		if ( empty( $result ) ) {
			return array();
		}

		return array_map(
			function ( $item ) {
				return array(
					'review'      => $item,
					'review_meta' => get_comment_meta( $item['comment_ID'] ),
				);
			},
			$result
		);
	}
}
