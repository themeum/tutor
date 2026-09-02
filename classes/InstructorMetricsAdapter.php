<?php
/**
 * Instructor Metrics Adapter
 *
 * Adapts disparate underlying models, analytics services, and query helpers
 * into normalized data contracts for dashboard sections, REST APIs, and exports.
 *
 * @package Tutor
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.9
 */

namespace TUTOR;

defined( 'ABSPATH' ) || exit;

use TUTOR_REPORT\Analytics;
use Tutor\Models\CourseModel;
use Tutor\Models\WithdrawModel;

/**
 * Class InstructorMetricsAdapter
 *
 * @since 4.0.9
 */
class InstructorMetricsAdapter {

	/**
	 * Date range array helper.
	 *
	 * @since 4.0.9
	 *
	 * @param string $from Start date.
	 * @param string $to   End date.
	 *
	 * @return array
	 */
	public static function date_range( string $from, string $to ): array {
		return array(
			'from' => sanitize_text_field( $from ),
			'to'   => sanitize_text_field( $to ),
		);
	}

	/**
	 * Get total earnings for an instructor within a date range.
	 *
	 * @since 4.0.9
	 *
	 * @param int    $user_id    Instructor user ID.
	 * @param string $start_date Start date (Y-m-d).
	 * @param string $end_date   End date (Y-m-d).
	 *
	 * @return float
	 */
	public static function get_total_earnings( int $user_id, string $start_date = '', string $end_date = '' ): float {
		$tutor_pro_enabled = tutor_utils()->is_plugin_active( 'tutor-pro/tutor-pro.php' );
		$is_pro_reports    = $tutor_pro_enabled && tutor_utils()->is_addon_enabled( 'tutor-report' );

		if ( $is_pro_reports && class_exists( 'TUTOR_REPORT\Analytics' ) ) {
			$earnings = Analytics::get_earnings_by_user( $user_id, '', $start_date, $end_date );
			$total    = (float) ( $earnings['total_earnings'] ?? 0 );
		} else {
			$date_arg = ( ! empty( $start_date ) && ! empty( $end_date ) ) ? self::date_range( $start_date, $end_date ) : null;
			$summary  = WithdrawModel::get_withdraw_summary( $user_id, $date_arg );
			$total    = (float) ( $summary->total_income ?? 0 );
		}

		return apply_filters( 'tutor_instructor_metrics_total_earnings', $total, $user_id, $start_date, $end_date );
	}

	/**
	 * Get total courses count for an instructor.
	 *
	 * @since 4.0.9
	 *
	 * @param int    $user_id    Instructor user ID.
	 * @param string $start_date Start date (Y-m-d).
	 * @param string $end_date   End date (Y-m-d).
	 *
	 * @return int
	 */
	public static function get_total_courses( int $user_id, string $start_date = '', string $end_date = '' ): int {
		if ( empty( $start_date ) && empty( $end_date ) ) {
			return (int) CourseModel::get_courses_by_instructor( $user_id, array( 'publish', 'private' ), 0, PHP_INT_MAX, true );
		}
		return (int) CourseModel::get_course_count_by_date( $start_date, $end_date, $user_id );
	}

	/**
	 * Get total students for an instructor.
	 *
	 * @since 4.0.9
	 *
	 * @param int    $user_id    Instructor user ID.
	 * @param string $start_date Start date (Y-m-d).
	 * @param string $end_date   End date (Y-m-d).
	 *
	 * @return int
	 */
	public static function get_total_students( int $user_id, string $start_date = '', string $end_date = '' ): int {
		$date_arg = ( ! empty( $start_date ) && ! empty( $end_date ) ) ? self::date_range( $start_date, $end_date ) : array();
		return (int) tutor_utils()->get_total_students_by_instructor( $user_id, $date_arg );
	}

	/**
	 * Get instructor average rating and review counts.
	 *
	 * @since 4.0.9
	 *
	 * @param int    $user_id    Instructor user ID.
	 * @param string $start_date Start date (Y-m-d).
	 * @param string $end_date   End date (Y-m-d).
	 *
	 * @return object
	 */
	public static function get_instructor_ratings( int $user_id, string $start_date = '', string $end_date = '' ): object {
		$date_arg = ( ! empty( $start_date ) && ! empty( $end_date ) ) ? self::date_range( $start_date, $end_date ) : array();
		$ratings  = tutor_utils()->get_instructor_ratings( $user_id, $date_arg );
		if ( ! is_object( $ratings ) ) {
			$ratings = (object) array(
				'rating_avg'   => 0,
				'rating_count' => 0,
			);
		}
		return $ratings;
	}

	/**
	 * Adapt and format Current Stats cards with comparison data.
	 *
	 * @since 4.0.9
	 *
	 * @param int    $user_id    Instructor user ID.
	 * @param string $start_date Start date (Y-m-d).
	 * @param string $end_date   End date (Y-m-d).
	 *
	 * @return array
	 */
	public static function get_stat_cards( int $user_id, string $start_date = '', string $end_date = '' ): array {
		$is_all_time    = empty( $start_date ) && empty( $end_date );
		$previous_dates = $is_all_time ? array() : Instructor::get_comparison_date_range( $start_date, $end_date );

		$total_earnings = self::get_total_earnings( $user_id, $start_date, $end_date );
		$total_courses  = self::get_total_courses( $user_id, $start_date, $end_date );
		$total_students = self::get_total_students( $user_id, $start_date, $end_date );
		$total_ratings  = self::get_instructor_ratings( $user_id, $start_date, $end_date );

		$total_earnings_details = array();
		$total_courses_details  = array();
		$total_students_details = array();
		$total_ratings_details  = array();

		$tutor_pro_enabled = tutor_utils()->is_plugin_active( 'tutor-pro/tutor-pro.php' );

		if ( $tutor_pro_enabled && ! $is_all_time && ! empty( $previous_dates ) ) {
			$prev_start = $previous_dates['previous_start_date'] ?? '';
			$prev_end   = $previous_dates['previous_end_date'] ?? '';

			$prev_earnings = self::get_total_earnings( $user_id, $prev_start, $prev_end );
			$prev_courses  = self::get_total_courses( $user_id, $prev_start, $prev_end );
			$prev_students = self::get_total_students( $user_id, $prev_start, $prev_end );
			$prev_ratings  = self::get_instructor_ratings( $user_id, $prev_start, $prev_end );

			$stat = function ( $current, $previous ) use ( $previous_dates ) {
				return array_merge( $previous_dates, Instructor::get_stat_card_details( (float) $current, (float) $previous ) );
			};

			$total_earnings_details = $stat( $total_earnings, $prev_earnings );
			$total_courses_details  = $stat( $total_courses, $prev_courses );
			$total_students_details = $stat( $total_students, $prev_students );
			$total_ratings_details  = $stat( $total_ratings->rating_avg, $prev_ratings->rating_avg );
		}

		return array(
			array(
				'variation'     => 'brand',
				'title'         => esc_html__( 'Total Earnings', 'tutor' ),
				'icon'          => Icon::EARNING,
				'value'         => tutor_utils()->tutor_price( $total_earnings ),
				'hover_content' => $total_earnings_details,
			),
			array(
				'variation'     => 'exception1',
				'title'         => esc_html__( 'Total Courses', 'tutor' ),
				'icon'          => Icon::COURSES,
				'value'         => $total_courses,
				'hover_content' => $total_courses_details,
			),
			array(
				'variation'     => 'exception5',
				'title'         => esc_html__( 'Total Students', 'tutor' ),
				'icon'          => Icon::PASSED,
				'value'         => $total_students,
				'hover_content' => $total_students_details,
			),
			array(
				'variation'     => 'exception4',
				'title'         => esc_html__( 'Avg. Rating', 'tutor' ),
				'icon'          => Icon::STAR_LINE,
				'value'         => $total_ratings->rating_avg,
				'hover_content' => $total_ratings_details,
			),
		);
	}

	/**
	 * Adapt Overview Chart data.
	 *
	 * @since 4.0.9
	 *
	 * @param int    $user_id    Instructor user ID.
	 * @param string $start_date Start date (Y-m-d).
	 * @param string $end_date   End date (Y-m-d).
	 *
	 * @return array
	 */
	public static function get_overview_chart_data( int $user_id, string $start_date = '', string $end_date = '' ): array {
		$tutor_pro_enabled = tutor_utils()->is_plugin_active( 'tutor-pro/tutor-pro.php' );
		$is_pro_reports    = $tutor_pro_enabled && tutor_utils()->is_addon_enabled( 'tutor-report' );

		if ( ! $is_pro_reports || ! class_exists( 'TUTOR_REPORT\Analytics' ) ) {
			return array();
		}

		$earnings    = Analytics::get_earnings_by_user( $user_id, '', $start_date, $end_date );
		$enrollments = Analytics::get_total_students_by_user( $user_id, '', $start_date, $end_date );

		$overview_chart_data = array(
			'earnings'        => array( 0 ),
			'enrolled'        => array( 0 ),
			'labels'          => array( '' ),
			'currency'        => tutor_utils()->get_monetization_currency_config(),
			'enrollment_date' => array( '' ),
			'earning_date'    => array( '' ),
		);

		foreach ( ( $earnings['earnings'] ?? array() ) as $item ) {
			$overview_chart_data['earnings'][]     = (float) ( $item->total ?? 0 );
			$overview_chart_data['labels'][]       = $item->label_name ?? '';
			$overview_chart_data['earning_date'][] = ! empty( $item->date_format ) ? wp_date( 'M d', strtotime( $item->date_format ) ) : '';
		}

		foreach ( ( $enrollments['enrollments'] ?? array() ) as $item ) {
			$overview_chart_data['enrolled'][]        = (float) ( $item->total ?? 0 );
			$overview_chart_data['enrollment_date'][] = ! empty( $item->date_format ) ? wp_date( 'M d', strtotime( $item->date_format ) ) : '';
		}

		$overview_chart_data['earnings'][]        = 0;
		$overview_chart_data['enrolled'][]        = 0;
		$overview_chart_data['labels'][]          = '';
		$overview_chart_data['earning_date'][]    = '';
		$overview_chart_data['enrollment_date'][] = '';

		return $overview_chart_data;
	}

	/**
	 * Adapt Course Completion Distribution data.
	 *
	 * @since 4.0.9
	 *
	 * @param int $user_id Instructor user ID.
	 *
	 * @return array
	 */
	public static function get_course_completion_distribution( int $user_id ): array {
		$instructor_course_ids = CourseModel::get_courses_by_args(
			array(
				'post_author'    => $user_id,
				'posts_per_page' => -1,
				'fields'         => 'ids',
			)
		)->posts;

		$distribution = Instructor::get_course_completion_distribution_data_by_instructor( $instructor_course_ids );

		return array(
			'enrolled'    => array(
				'label' => esc_html__( 'Enrolled', 'tutor' ),
				'value' => $distribution['enrolled'] ?? 0,
			),
			'completed'   => array(
				'label' => esc_html__( 'Completed', 'tutor' ),
				'value' => $distribution['completed'] ?? 0,
			),
			'in_progress' => array(
				'label' => esc_html__( 'In Progress', 'tutor' ),
				'value' => $distribution['inprogress'] ?? 0,
			),
			'inactive'    => array(
				'label' => esc_html__( 'Inactive', 'tutor' ),
				'value' => $distribution['inactive'] ?? 0,
			),
			'cancelled'   => array(
				'label' => esc_html__( 'Cancelled', 'tutor' ),
				'value' => $distribution['cancelled'] ?? 0,
			),
		);
	}

	/**
	 * Adapt Top Performing Courses data.
	 *
	 * @since 4.0.9
	 *
	 * @param int    $user_id    Instructor user ID.
	 * @param string $type       Sort type ('revenue' or 'student').
	 * @param string $start_date Start date (Y-m-d).
	 * @param string $end_date   End date (Y-m-d).
	 *
	 * @return array
	 */
	public static function get_top_performing_courses( int $user_id, string $type = 'revenue', string $start_date = '', string $end_date = '' ): array {
		$args = array(
			'start_date' => $start_date,
			'end_date'   => $end_date,
			'order_by'   => in_array( $type, array( 'revenue', 'student' ), true ) ? $type : 'revenue',
		);

		return Instructor::format_instructor_top_performing_courses(
			Instructor::get_top_performing_courses_by_instructor( $user_id, $args )
		);
	}

	/**
	 * Adapt Upcoming Tasks data.
	 *
	 * @since 4.0.9
	 *
	 * @param int $user_id Instructor user ID.
	 *
	 * @return array
	 */
	public static function get_upcoming_tasks( int $user_id ): array {
		$tutor_pro_enabled = tutor_utils()->is_plugin_active( 'tutor-pro/tutor-pro.php' );
		if ( ! $tutor_pro_enabled ) {
			return array();
		}

		return Instructor::format_instructor_upcoming_live_tasks(
			Instructor::get_instructor_upcoming_live_tasks( $user_id )
		);
	}

	/**
	 * Adapt Recent Reviews data.
	 *
	 * @since 4.0.9
	 *
	 * @param int    $user_id    Instructor user ID.
	 * @param int    $limit      Max count.
	 * @param string $start_date Start date (Y-m-d).
	 * @param string $end_date   End date (Y-m-d).
	 *
	 * @return array
	 */
	public static function get_recent_reviews( int $user_id, int $limit = 3, string $start_date = '', string $end_date = '' ): array {
		$review_args = array( 'comment_approved' => 'approved' );
		if ( ! empty( $start_date ) && ! empty( $end_date ) ) {
			$review_args = self::date_range( $start_date, $end_date );
		}

		$reviews = tutor_utils()->get_reviews_by_instructor( $user_id, 0, $limit, '', '', $review_args );
		return Instructor::format_instructor_recent_reviews( $reviews->results ?? array() );
	}
}
