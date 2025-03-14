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
	 * Page Title
	 *
	 * @var $page_title
	 */
	public $page_title;

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
		$this->page_title = __( 'Students', 'tutor' );
		/**
		 * Handle bulk action
		 *
		 * @since 2.0.0
		 */
		add_action( 'wp_ajax_tutor_student_bulk_action', array( $this, 'student_bulk_action' ) );
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
		$student_ids = array_map( 'intval', explode( ',', $student_ids ) );
		foreach ( $student_ids as $student_id ) {
			$args = array(
				'post_type' => tutor()->course_post_type,
			);

			if ( tutor_utils()->is_addon_enabled( 'course-bundle' ) ) {
				$args['post_type'] = array( tutor()->course_post_type, CourseBundle::POST_TYPE );
			}

			$enrolled_courses = tutor_utils()->get_enrolled_courses_by_user( $student_id, 'any', 0, -1, $args );
			if ( is_a( $enrolled_courses, 'WP_Query' ) ) {
				// Delete student flag.
				delete_user_meta( $student_id, User::TUTOR_STUDENT_META, true );

				// Delete the enrollment, course progress & comments.
				$courses = $enrolled_courses->get_posts();
				foreach ( $courses as $course ) {
					tutor_utils()->delete_enrollment_record( $student_id, $course->ID );
					tutor_utils()->delete_course_progress( $course->ID );
					tutor_utils()->delete_student_course_comment( $student_id, $course->ID );
				}
			}
		}

		return true;
	}
}
