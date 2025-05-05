<?php
/**
 * Student List page
 *
 * @package Tutor\Student
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.0.0
 */

namespace TUTOR;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use TUTOR\Backend_Page_Trait;
use Tutor\Helpers\QueryHelper;
use TutorPro\CourseBundle\CustomPosts\CourseBundle;

/**
 * Manage student lists
 *
 * @since 1.0.0
 */
class Students_List {

	/**
	 * Page slug
	 *
	 * @since 2.0.0
	 *
	 * @var string
	 */
	const STUDENTS_LIST_PAGE = 'tutor-students';

	/**
	 * Trait for utilities
	 *
	 * @var $page_title
	 */
	use Backend_Page_Trait;

	/**
	 * Bulk Action
	 *
	 * @var $bulk_action
	 */
	public $bulk_action = true;

	/**
	 * Handle dependencies
	 *
	 * @since 2.0.0
	 */
	public function __construct() {
		/**
		 * Handle bulk action
		 *
		 * @since 2.0.0
		 */
		add_action( 'wp_ajax_tutor_student_bulk_action', array( $this, 'student_bulk_action' ) );
	}

	/**
	 * Page title fallback
	 *
	 * @since 3.5.0
	 *
	 * @param string $name Property name.
	 *
	 * @return string
	 */
	public function __get( $name ) {
		if ( 'page_title' === $name ) {
			return esc_html__( 'Students', 'tutor' );
		}
	}

	/**
	 * Prepare bulk actions that will show on dropdown options
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	public function prpare_bulk_actions(): array {
		$actions = array(
			$this->bulk_action_default(),
			$this->bulk_action_delete(),
		);
		return $actions;
	}


	/**
	 * Handle bulk action for student delete
	 *
	 * @since 2.0.0
	 *
	 * @return string wp_json response
	 */
	public function student_bulk_action() {
		// check nonce.
		tutor_utils()->checking_nonce();

		// Check if user is privileged.
		if ( ! current_user_can( 'administrator' ) ) {
			wp_send_json_error( tutor_utils()->error_message() );
		}

		$action   = Input::post( 'bulk-action', '' );
		$bulk_ids = Input::post( 'bulk-ids', array() );
		if ( 'delete' === $action ) {
			return true === self::delete_students( $bulk_ids ) ? wp_send_json_success() : wp_send_json_error();
		}
		return wp_send_json_error();
	}

	/**
	 * Delete student
	 *
	 * @since v2.0.0
	 *
	 * @param string $student_ids ids comma separated value.
	 *
	 * @return bool
	 */
	public static function delete_students( string $student_ids ): bool {
		global $wpdb;
		$student_ids = array_map( 'intval', explode( ',', $student_ids ) );
		foreach ( $student_ids as $student_id ) {
			$enrollments = QueryHelper::get_all(
				$wpdb->posts,
				array(
					'post_author' => $student_id,
					'post_type'   => array(
						tutor()->enrollment_post_type,
						'course-bundle',
					),
				),
				'ID'
			);

			if ( is_array( $enrollments ) && count( $enrollments ) ) {
				delete_user_meta( $student_id, User::TUTOR_STUDENT_META );
				foreach ( $enrollments as $enrollment ) {
					$course_id = (int) $enrollment->post_parent;
					tutor_utils()->delete_enrollment_record( $student_id, $course_id );
					tutor_utils()->delete_course_progress( $course_id, $student_id );
					tutor_utils()->delete_student_course_comment( $student_id, $course_id );
				}
			}
		}

		return true;
	}
}
