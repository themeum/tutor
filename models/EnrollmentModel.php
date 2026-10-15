<?php
/**
 * Enrollment Model
 *
 * @package Tutor\Models
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

namespace Tutor\Models;

use Tutor\Cache\TutorCache;
use TUTOR\Course;
use Tutor\Helpers\QueryHelper;
use TUTOR\User;

/**
 * Class EnrollmentModel
 *
 * @since 4.0.0
 */
class EnrollmentModel {
	/**
	 * Enrollment status constants
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	const STATUS_COMPLETED = 'completed';
	const STATUS_PENDING   = 'pending';
	const STATUS_CANCEL    = 'cancel';

	/**
	 * Enrollment post type
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	const POST_TYPE = 'tutor_enrolled';


	/**
	 * Enrollment meta
	 *
	 * @since 4.0.0
	 */
	const ENROLLMENT_ORDER_ID_META   = '_tutor_enrolled_by_order_id';
	const ENROLLMENT_PRODUCT_ID_META = '_tutor_enrolled_by_product_id';

	/**
	 * Saving enroll information to posts table
	 * post_author = enrolled_student_id (wp_users id)
	 * post_parent = enrolled course id
	 *
	 * @since 1.0.0
	 * @since 2.6.0 Return enrolled id
	 * @since 3.3.0 Added $fire_hook parameter.
	 *
	 * @param int  $course_id course id.
	 * @param int  $order_id order id.
	 * @param int  $user_id user id.
	 * @param bool $fire_hook fire hook.
	 *
	 * @return int enrolled id
	 */
	public static function do_enroll( $course_id = 0, $order_id = 0, $user_id = 0, $fire_hook = true ) {
		$enrolled_id = 0;
		if ( ! CourseModel::is_course_accessible( $course_id ) ) {
			return $enrolled_id;
		}

		$can_enroll = apply_filters( 'tutor_allow_course_enrollment', true, $course_id );
		if ( is_wp_error( $can_enroll ) ) {
			return $enrolled_id;
		}

		$fire_hook ? do_action( 'tutor_before_enroll', $course_id ) : null;
		$user_id = tutor_utils()->get_user_id( $user_id );
		$title   = __( 'Course Enrolled', 'tutor' ) . ' &ndash; ' . gmdate( get_option( 'date_format' ) ) . ' @ ' . gmdate( get_option( 'time_format' ) );

		if ( $course_id && $user_id ) {
			$enrolled_info = self::is_enrolled( $course_id, $user_id );
			if ( $enrolled_info ) {
				return $enrolled_info->ID;
			}
		}

		$enrolment_status = self::STATUS_COMPLETED;

		if ( tutor_utils()->is_course_purchasable( $course_id ) ) {
			$enrolment_status = self::STATUS_PENDING;
		}

		$enroll_data = apply_filters(
			'tutor_enroll_data',
			array(
				'post_type'     => self::POST_TYPE,
				'post_title'    => $title,
				'post_status'   => $enrolment_status,
				'post_author'   => $user_id,
				'post_parent'   => $course_id,
				'post_date_gmt' => current_time( 'mysql', true ),
			)
		);

		// Insert the post into the database.
		$is_enrolled = wp_insert_post( $enroll_data );
		if ( $is_enrolled ) {

			// Run this hook for both of pending and completed enrollment.
			$fire_hook ? do_action( 'tutor_after_enroll', $course_id, $is_enrolled ) : null;

			// Mark Current User as Students with user meta data.
			update_user_meta( $user_id, User::TUTOR_STUDENT_META, tutor_time() );

			if ( $order_id ) {
				// Mark order for course and user.
				$product_id = tutor_utils()->get_course_product_id( $course_id );
				update_post_meta( $is_enrolled, self::ENROLLMENT_ORDER_ID_META, $order_id );
				update_post_meta( $is_enrolled, self::ENROLLMENT_PRODUCT_ID_META, $product_id );

				$monetize_by = tutor_utils()->get_option( 'monetize_by' );
				if ( 'wc' === $monetize_by ) {
					$order = wc_get_order( $order_id );
					$order->update_meta_data( Course::IS_TUTOR_ORDER_FOR_COURSE_META, tutor_time() );
					$order->update_meta_data( Course::TUTOR_ORDER_FOR_COURSE_ID_META . $course_id, $is_enrolled );
					$order->save();
				} elseif ( 'edd' === $monetize_by ) {
					$payment = new \EDD_Payment( $order_id );
					$payment->update_meta( Course::IS_TUTOR_ORDER_FOR_COURSE_META, tutor_time() );
					$payment->update_meta( Course::TUTOR_ORDER_FOR_COURSE_ID_META . $course_id, $is_enrolled );
					$payment->save();
				} else {
					update_post_meta( $order_id, Course::IS_TUTOR_ORDER_FOR_COURSE_META, tutor_time() );
					update_post_meta( $order_id, Course::TUTOR_ORDER_FOR_COURSE_ID_META . $course_id, $is_enrolled );
				}
			}

			$enrolled_id = $is_enrolled;

			// Run this hook for completed enrollment regardless of payment provider and free/paid mode.
			if ( $fire_hook && self::STATUS_COMPLETED === $enroll_data['post_status'] ) {
				do_action( 'tutor_after_enrolled', $course_id, $user_id, $enrolled_id );
			}
		}

		return $enrolled_id;
	}

	/**
	 * Check if current user has been enrolled or not
	 *
	 * @since 1.0.0
	 *
	 * @since 3.0.0  $is_complete parameter added to check with completed status
	 *               Default value set true for backward compatibility. It set
	 *               false then it will just check record.
	 *
	 * @since 3.3.0  param $is_complete added to cache key.
	 * @since 4.0.0  enrollment order_id and product_id added to enrollment info.
	 *
	 * @param int  $course_id course id.
	 * @param int  $user_id user id.
	 * @param bool $is_complete Whether to enrollment completed or not.
	 *
	 * @return array|bool|null|object
	 */
	public static function is_enrolled( $course_id = 0, $user_id = 0, bool $is_complete = true ) {
		global $wpdb;
		$course_id = tutor_utils()->get_post_id( $course_id );
		$user_id   = tutor_utils()->get_user_id( $user_id );

		$cache_key = "tutor_is_enrolled_{$course_id}_{$user_id}_{$is_complete}";
		$cached    = TutorCache::get( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		do_action( 'tutor_is_enrolled_before', $course_id, $user_id );

		$status_clause = '';
		if ( $is_complete ) {
			$status_clause = $wpdb->prepare( 'AND post_status = %s ', self::STATUS_COMPLETED );
		}

		//phpcs:disable
		$get_enrolled_info = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT ID,
				post_author,
				post_date,
				post_date_gmt,
				post_title
			FROM {$wpdb->posts}
			WHERE post_author > 0 
				AND post_parent > 0
				AND post_type = %s
				AND post_parent = %d
				AND post_author = %d
				{$status_clause};
			",
				self::POST_TYPE,
				$course_id,
				$user_id
			)
		);
		//phpcs:enable

		if ( $get_enrolled_info ) {
			$get_enrolled_info->order_id   = (int) get_post_meta( $get_enrolled_info->ID, self::ENROLLMENT_ORDER_ID_META, true );
			$get_enrolled_info->product_id = (int) get_post_meta( $get_enrolled_info->ID, self::ENROLLMENT_PRODUCT_ID_META, true );
		}

		TutorCache::set( $cache_key, $get_enrolled_info );

		if ( $get_enrolled_info ) {
			return apply_filters( 'tutor_is_enrolled', $get_enrolled_info, $course_id, $user_id );
		}

		return false;
	}

	/**
	 * Get enrollment by enrol_id
	 *
	 * @since 1.6.9
	 *
	 * @param int $enrol_id enrol id.
	 *
	 * @return array|object
	 */
	public static function get_enrolment_by_enrol_id( $enrol_id = 0 ) {
		global $wpdb;

		$enrolment = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT enrol.id      AS enrol_id,
					enrol.post_author AS student_id,
					enrol.post_date   AS enrol_date,
					enrol.post_title  AS enrol_title,
					enrol.post_status AS status,
					enrol.post_parent AS course_id,
					course.post_title AS course_title,
					student.user_nicename,
					student.user_email,
					student.display_name,
					student.ID
			FROM   {$wpdb->posts} enrol
					INNER JOIN {$wpdb->posts} course
							ON enrol.post_parent = course.id
					INNER JOIN {$wpdb->users} student
							ON enrol.post_author = student.id
			WHERE  enrol.id = %d;
		",
				$enrol_id
			)
		);

		if ( $enrolment ) {
			return $enrolment;
		}

		return false;
	}

	/**
	 * Get single or list of enrolled course data by a user
	 *
	 * @since 2.0.5
	 * @since 4.0.0 param $status added.
	 *
	 * @param integer $user_id user id.
	 * @param integer $course_id course id.
	 * @param string  $status the status of enrollment.
	 *
	 * @return object|mixed
	 */
	public static function get_enrolled_data( $user_id = 0, $course_id = 0, $status = self::STATUS_COMPLETED ) {
		global $wpdb;
		$status_clause = $status ? $wpdb->prepare( 'AND post_status = %s ', $status ) : '';
		// If course ID provided, it will return single row data.
		if ( $course_id > 0 ) {
			return $wpdb->get_row(
				$wpdb->prepare(
					"SELECT * FROM 	{$wpdb->posts} 
						WHERE post_type = %s
						AND post_parent = %d
						{$status_clause}
						AND post_author = %d;",
					self::POST_TYPE,
					$course_id,
					$user_id
				)
			);
		} else {
			// Return all enrolled data by user ID.
			return $wpdb->get_results(
				$wpdb->prepare(
					"SELECT * FROM 	{$wpdb->posts} 
						WHERE post_type = %s
						{$status_clause}
						AND post_author = %d;",
					self::POST_TYPE,
					$user_id
				)
			);
		}
	}

	/**
	 * Execute bulk action for enrollment list ex: complete | cancel
	 *
	 * @since 2.0.3
	 * @since 3.2.0 $trigger_hook param added.
	 *
	 * @param string $status hold status for updating.
	 * @param array  $enrollment_ids ids that need to update.
	 * @param bool   $trigger_hook optional - trigger hook or not.
	 *
	 * @return bool
	 */
	public static function update_enrollments( string $status, array $enrollment_ids, bool $trigger_hook = true ): bool {
		global $wpdb;
		$enrollment_ids_in = QueryHelper::prepare_in_clause( $enrollment_ids );
		$status            = 'complete' === $status ? 'completed' : $status;
		$post_table        = $wpdb->posts;

		//phpcs:disable
		$wpdb->query(
			$wpdb->prepare(
				"UPDATE {$post_table}
				SET post_status = %s
				WHERE ID IN ($enrollment_ids_in)
			",
				$status
			)
		);
		//phpcs:enable

		if ( $trigger_hook ) {
			// Run action hook.
			foreach ( $enrollment_ids as $id ) {
				do_action( 'tutor_enrollment/after/' . $status, $id );
			}
		}

		return true;
	}

	/**
	 * Delete enrollment record by providing the student and course id
	 *
	 * @since 3.4.0
	 *
	 * @param int $student_id Student id.
	 * @param int $course_id Course id.
	 *
	 * @return bool
	 */
	public static function delete_enrollment_record( int $student_id, int $course_id ): bool {
		return QueryHelper::delete(
			'posts',
			array(
				'post_author' => $student_id,
				'post_parent' => $course_id,
				'post_type'   => self::POST_TYPE,
			)
		);
	}
}
