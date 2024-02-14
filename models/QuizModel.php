<?php
/**
 * Quiz Model
 *
 * @package Tutor\Models
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.10
 */

namespace Tutor\Models;

use Tutor\Cache\TutorCache;
use Tutor\Helpers\QueryHelper;

/**
 * Class QuizModel
 *
 * @since 2.0.10
 */
class QuizModel {

	const ATTEMPT_STARTED = 'attempt_started';
	const ATTEMPT_ENDED   = 'attempt_ended';
	const REVIEW_REQUIRED = 'review_required';

	/**
	 * Get quiz table name
	 *
	 * @since 2.1.0
	 *
	 * @return string
	 */
	public function get_table(): string {
		global $wpdb;
		return $wpdb->prefix . 'tutor_quiz_attempts';
	}

	/**
	 * Get total number of quiz
	 *
	 * @since 2.0.2
	 *
	 * @return int
	 */
	public static function get_total_quiz() {
		global $wpdb;

		$sql = "SELECT COUNT(DISTINCT quiz.ID) 
			FROM {$wpdb->posts} quiz
				INNER JOIN {$wpdb->posts} topic ON quiz.post_parent=topic.ID 
				INNER JOIN {$wpdb->posts} course ON topic.post_parent=course.ID 
			WHERE course.post_type=%s
				AND quiz.post_type='tutor_quiz'";

		//phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		return $wpdb->get_var( $wpdb->prepare( $sql, tutor()->course_post_type ) );
	}

	/**
	 * Get Attempt row by grade method settings
	 *
	 * @since 1.4.2
	 *
	 * @param int $quiz_id quiz id.
	 * @param int $user_id user id.
	 *
	 * @return array|bool|null|object
	 */
	public function get_quiz_attempt( $quiz_id = 0, $user_id = 0 ) {
		global $wpdb;

		$quiz_id = tutils()->get_post_id( $quiz_id );
		$user_id = tutils()->get_user_id( $user_id );

		$attempt = false;

		$quiz_grade_method = get_tutor_option( 'quiz_grade_method', 'highest_grade' );
		$from_string       = "FROM {$wpdb->tutor_quiz_attempts} WHERE quiz_id = %d AND user_id = %d AND attempt_status != 'attempt_started' ";

		//phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		if ( 'highest_grade' === $quiz_grade_method ) {
			$attempt = $wpdb->get_row( $wpdb->prepare( "SELECT * {$from_string} ORDER BY earned_marks DESC LIMIT 1; ", $quiz_id, $user_id ) );
		} elseif ( 'average_grade' === $quiz_grade_method ) {

			$attempt = $wpdb->get_row(
				$wpdb->prepare(
					"SELECT {$wpdb->tutor_quiz_attempts}.*,
						COUNT(attempt_id) AS attempt_count,
						AVG(total_marks) AS total_marks,
						AVG(earned_marks) AS earned_marks {$from_string}
				",
					$quiz_id,
					$user_id
				)
			);
		} elseif ( 'first_attempt' === $quiz_grade_method ) {

			$attempt = $wpdb->get_row( $wpdb->prepare( "SELECT * {$from_string} ORDER BY attempt_id ASC LIMIT 1; ", $quiz_id, $user_id ) );
		} elseif ( 'last_attempt' === $quiz_grade_method ) {

			$attempt = $wpdb->get_row( $wpdb->prepare( "SELECT * {$from_string} ORDER BY attempt_id DESC LIMIT 1; ", $quiz_id, $user_id ) );
		}
		//phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		return $attempt;
	}

	/**
	 * Get all of the attempts by an user of a quiz
	 *
	 * @since 1.0.0
	 *
	 * @param int $quiz_id quiz ID.
	 * @param int $user_id user ID.
	 *
	 * @return array|bool|null|object
	 */
	public function quiz_attempts( $quiz_id = 0, $user_id = 0 ) {
		global $wpdb;

		$quiz_id = tutor_utils()->get_post_id( $quiz_id );
		$user_id = tutor_utils()->get_user_id( $user_id );

		$cache_key = "tutor_quiz_attempts_for_{$user_id}_{$quiz_id}";
		$attempts  = TutorCache::get( $cache_key );

		if ( false === $attempts ) {
			$attempts = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT *
				FROM 	{$wpdb->prefix}tutor_quiz_attempts
				WHERE 	quiz_id = %d
						AND user_id = %d
						ORDER BY attempt_id  DESC
				",
					$quiz_id,
					$user_id
				)
			);
			TutorCache::set( $cache_key, $attempts );
		}

		if ( is_array( $attempts ) && count( $attempts ) ) {
			return $attempts;
		}

		return false;
	}

	/**
	 * Get Quiz question by question id
	 *
	 * @since 1.0.0
	 *
	 * @param int $question_id question ID.
	 *
	 * @return array|bool|object|void|null
	 */
	public static function get_quiz_question_by_id( $question_id = 0 ) {
		global $wpdb;

		if ( $question_id ) {
			$question = $wpdb->get_row(
				$wpdb->prepare(
					"SELECT *
				FROM 	{$wpdb->prefix}tutor_quiz_questions
				WHERE 	question_id = %d
				LIMIT 0, 1;
				",
					$question_id
				)
			);

			return $question;
		}

		return false;
	}

	/**
	 * Get all ended attempts by an user of a quiz
	 *
	 * @since 1.4.1
	 *
	 * @param int $quiz_id quiz ID.
	 * @param int $user_id user ID.
	 *
	 * @return array|bool|null|object
	 */
	public function quiz_ended_attempts( $quiz_id = 0, $user_id = 0 ) {
		global $wpdb;

		$quiz_id = tutor_utils()->get_post_id( $quiz_id );
		$user_id = tutor_utils()->get_user_id( $user_id );

		$attempts = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT *
			FROM 	{$wpdb->prefix}tutor_quiz_attempts
			WHERE 	quiz_id = %d
					AND user_id = %d
					AND attempt_status != %s
			",
				$quiz_id,
				$user_id,
				'attempt_started'
			)
		);

		if ( is_array( $attempts ) && count( $attempts ) ) {
			return $attempts;
		}

		return false;
	}

	/**
	 * Get the next question order ID
	 *
	 * @since 1.0.0
	 *
	 * @param integer $quiz_id quiz ID.
	 *
	 * @return int
	 */
	public static function quiz_next_question_order_id( $quiz_id ) {
		global $wpdb;

		$last_order = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT MAX(question_order)
			FROM 	{$wpdb->prefix}tutor_quiz_questions
			WHERE 	quiz_id = %d ;
			",
				$quiz_id
			)
		);

		return $last_order + 1;
	}

	/**
	 * Get next quiz question ID
	 *
	 * @since 1.0.0
	 *
	 * @return int
	 */
	public static function quiz_next_question_id() {
		global $wpdb;

		$last_order = (int) $wpdb->get_var( "SELECT MAX(question_id) FROM {$wpdb->prefix}tutor_quiz_questions;" );
		return $last_order + 1;
	}

	/**
	 * Total number of quiz attempts
	 *
	 * @since 1.0.0
	 *
	 * @param string  $search_term search term.
	 * @param integer $course_id course ID.
	 * @param string  $tab tab.
	 * @param string  $date_filter date filter.
	 *
	 * @return int
	 */
	public static function get_total_quiz_attempts( $search_term = '', int $course_id = 0, string $tab = '', $date_filter = '' ) {
		global $wpdb;

		if ( '' !== $search_term ) {
			$search_term = '%' . $wpdb->esc_like( $search_term ) . '%';
		}

		// Set query based on action tab.
		$pass_mark     = "((( SUBSTRING_INDEX(
			SUBSTRING_INDEX(
			  attempt_info,
			  CONCAT(
				  '\"passing_grade\";s:',
				  SUBSTRING_INDEX(SUBSTRING_INDEX(attempt_info, '\"passing_grade\";s:', -1), ':\"', 1),
				  ':\"'
			  ),
			  -1
			), 
			'\"', 
			1
  		))/100) * quiz_attempts.total_marks)";
		$pending_count = "(SELECT COUNT(DISTINCT attempt_answer_id) FROM {$wpdb->prefix}tutor_quiz_attempt_answers WHERE quiz_attempt_id=quiz_attempts.attempt_id AND is_correct IS NULL)";

		$tab_join   = '';
		$tab_clause = '';
		if ( '' !== $tab ) {
			$tab_join = "INNER JOIN {$wpdb->prefix}tutor_quiz_attempt_answers AS ans ON quiz_attempts.attempt_id = ans.quiz_attempt_id";
		}
		switch ( $tab ) {
			case 'pass':
				// Just check if the earned mark is greater than pass mark.
				// It doesn't matter if there is any pending or failed question.
				$tab_clause = " AND quiz_attempts.earned_marks >= {$pass_mark}  ";
				break;

			case 'fail':
				// Check if earned marks is less than pass mark and there is no pending question.
				$tab_clause = " AND quiz_attempts.earned_marks < {$pass_mark} AND {$pending_count} < 1 ";
				break;
			case 'pending':
				$tab_clause = " AND {$pending_count} > 0 ";
				break;
		}

		$course_join   = '';
		$course_clause = '';
		if ( $course_id || '' !== $search_term ) {
			$course_join = "INNER JOIN {$wpdb->posts} AS course ON course.ID = quiz_attempts.course_id";
		}
		if ( $course_id ) {
			$course_clause = " AND quiz_attempts.course_id = $course_id";
		}

		$user_join    = '';
		$user_clause  = '';
		$search_term1 = sanitize_text_field( $search_term );
		$search_term2 = sanitize_text_field( $search_term );
		$search_term3 = sanitize_text_field( $search_term );
		if ( '' !== $search_term ) {
			$user_join = "INNER JOIN {$wpdb->users}
			ON quiz_attempts.user_id = {$wpdb->users}.ID";

			$user_clause = "AND ( user_email LIKE '%$search_term1%' OR display_name LIKE '%$search_term2%' OR course.post_title LIKE '%$search_term3%' )";
		}

		if ( '' !== $date_filter ) {
			$date_filter = '' != $date_filter ? tutor_get_formated_date( 'Y-m-d', $date_filter ) : '';
			$date_filter = '' != $date_filter ? " AND  DATE(quiz_attempts.attempt_started_at) = '$date_filter' " : '';
		}

		//phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT( DISTINCT attempt_id)
		 	FROM 	{$wpdb->prefix}tutor_quiz_attempts quiz_attempts
					INNER JOIN {$wpdb->posts} quiz
							ON quiz_attempts.quiz_id = quiz.ID
					{$user_join}
					{$course_join}
					{$tab_join}
			WHERE 	attempt_status != %s
				{$user_clause}
				{$course_clause}
				{$tab_clause}
				{$date_filter}
			",
				'attempt_started'
			)
		);

		//phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		return (int) $count;
	}

	/**
	 * Get the all quiz attempts
	 *
	 * @since 1.0.0
	 * @since 1.9.5 sorting paramas added
	 *
	 * @param integer $start start.
	 * @param integer $limit limit.
	 * @param string  $search_filter search filter.
	 * @param string  $course_filter course filter.
	 * @param string  $date_filter date filter.
	 * @param string  $order_filter order filter.
	 * @param mixed   $result_state result state.
	 * @param boolean $count_only count only or not.
	 * @param boolean $instructor_id_check need instructor id check or not.
	 *
	 * @return mixed
	 */
	public static function get_quiz_attempts( $start = 0, $limit = 10, $search_filter = '', $course_filter = array(), $date_filter = '', $order_filter = 'DESC', $result_state = null, $count_only = false, $instructor_id_check = false ) {
		global $wpdb;

		$start         = sanitize_text_field( $start );
		$limit         = sanitize_text_field( $limit );
		$search_filter = sanitize_text_field( $search_filter );
		$course_filter = sanitize_text_field( $course_filter );
		$date_filter   = sanitize_text_field( $date_filter );
		$order_filter  = sanitize_sql_orderby( $order_filter );

		$search_term_raw = $search_filter;
		$search_filter   = '%' . $wpdb->esc_like( $search_filter ) . '%';

		// Filter by course.
		if ( '' != $course_filter ) {
			! is_array( $course_filter ) ? $course_filter = array( $course_filter ) : 0;
			$course_ids                                   = implode( ',', array_map( 'intval', $course_filter ) );
			$course_filter                                = " AND quiz_attempts.course_id IN ($course_ids) ";
		}

		// Filter by date.
		$date_filter = '' != $date_filter ? tutor_get_formated_date( 'Y-m-d', $date_filter ) : '';
		$date_filter = '' != $date_filter ? " AND  DATE(quiz_attempts.attempt_started_at) = '$date_filter' " : '';

		$result_clause  = '';
		$select_columns = $count_only ? 'COUNT(DISTINCT quiz_attempts.attempt_id)' : 'DISTINCT quiz_attempts.*, quiz.post_title, users.user_email, users.user_login, users.display_name';
		$limit_offset   = $count_only ? '' : ' LIMIT ' . $limit . ' OFFSET ' . $start;

		$pass_mark     = "((( SUBSTRING_INDEX(
			SUBSTRING_INDEX(
			  attempt_info,
			  CONCAT(
				  '\"passing_grade\";s:',
				  SUBSTRING_INDEX(SUBSTRING_INDEX(attempt_info, '\"passing_grade\";s:', -1), ':\"', 1),
				  ':\"'
			  ),
			  -1
			), 
			'\"', 
			1
  		))/100) * quiz_attempts.total_marks)";
		$pending_count = "(SELECT COUNT(DISTINCT attempt_answer_id) FROM {$wpdb->prefix}tutor_quiz_attempt_answers WHERE quiz_attempt_id=quiz_attempts.attempt_id AND is_correct IS NULL)";

		// Get attempts by instructor ID.
		$instructor_clause = '';
		$instructor_join   = '';
		if ( $instructor_id_check ) {
			$current_user_id = get_current_user_id();
			$instructor_id   = tutor_utils()->has_user_role( 'administrator', $current_user_id ) ? null : $current_user_id;

			if ( $instructor_id ) {
				// $instructor_clause = " AND (instructor_meta.meta_key='_tutor_instructor_course_id' AND instructor_meta.user_id=$instructor_id)";
				$instructor_clause = " INNER JOIN {$wpdb->prefix}usermeta AS instructor_meta ON course.ID = instructor_meta.meta_value AND (instructor_meta.meta_key='_tutor_instructor_course_id' AND instructor_meta.user_id=$instructor_id) ";
			}
		}

		// Switc hthrough result state and assign meta clause.
		switch ( $result_state ) {
			case 'pass':
				// Just check if the earned mark is greater than pass mark.
				// It doesn't matter if there is any pending or failed question.
				$result_clause = " AND quiz_attempts.earned_marks>={$pass_mark}  ";
				break;

			case 'fail':
				// Check if earned marks is less than pass mark and there is no pending question.
				$result_clause = " AND quiz_attempts.earned_marks<{$pass_mark} AND {$pending_count} < 1 ";
				break;

			case 'pending':
				$result_clause = " AND {$pending_count}>0 ";
				break;
		}

		//phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$query = $wpdb->prepare(
			"SELECT {$select_columns}
		 	FROM {$wpdb->prefix}tutor_quiz_attempts quiz_attempts
					INNER JOIN {$wpdb->posts} quiz ON quiz_attempts.quiz_id = quiz.ID
					INNER JOIN {$wpdb->users} AS users ON quiz_attempts.user_id = users.ID
					INNER JOIN {$wpdb->posts} AS course ON course.ID = quiz_attempts.course_id
					INNER JOIN {$wpdb->prefix}tutor_quiz_attempt_answers AS ans ON quiz_attempts.attempt_id = ans.quiz_attempt_id
					{$instructor_clause}
			WHERE 	quiz_attempts.attempt_ended_at IS NOT NULL
					AND (
							users.user_email = %s
							OR users.display_name LIKE %s
							OR quiz.post_title LIKE %s
							OR course.post_title LIKE %s
						)
					AND quiz_attempts.attempt_ended_at IS NOT NULL
					{$result_clause}
					{$course_filter}
					{$date_filter}
			ORDER 	BY quiz_attempts.attempt_ended_at {$order_filter} {$limit_offset}",
			$search_term_raw,
			$search_filter,
			$search_filter,
			$search_filter
		);

		//phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		//phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		return $count_only ? $wpdb->get_var( $query ) : $wpdb->get_results( $query );
	}

	/**
	 * Delete quizattempt for user
	 *
	 * @since 1.9.5
	 *
	 * @param mixed $attempt_ids attempt ids.
	 *
	 * @return void
	 */
	public static function delete_quiz_attempt( $attempt_ids ) {
		global $wpdb;

		// Singlular to array.
		! is_array( $attempt_ids ) ? $attempt_ids = array( $attempt_ids ) : 0;

		if ( count( $attempt_ids ) ) {
			$attempt_ids = implode( ',', $attempt_ids );

			//phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			// Deleting attempt (comment), child attempt and attempt meta (comment meta).
			$wpdb->query( "DELETE FROM {$wpdb->prefix}tutor_quiz_attempts WHERE attempt_id IN($attempt_ids)" );
			$wpdb->query( "DELETE FROM {$wpdb->prefix}tutor_quiz_attempt_answers WHERE quiz_attempt_id IN($attempt_ids)" );
			//phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

			do_action( 'tutor_quiz/attempt_deleted', $attempt_ids );
		}
	}

	/**
	 * Sorting params added on quiz attempt
	 *
	 * @since 1.9.5
	 *
	 * @param integer $start start.
	 * @param integer $limit limit.
	 * @param array   $course_ids course ids.
	 * @param string  $search_filter search filter.
	 * @param string  $course_filter course filter.
	 * @param string  $date_filter date filter.
	 * @param string  $order_filter order filter.
	 * @param mixed   $user_id user id.
	 * @param boolean $count_only is only count or not.
	 * @param boolean $all_attempt need all atempt or not.
	 *
	 * @return mixed
	 */
	public static function get_quiz_attempts_by_course_ids( $start = 0, $limit = 10, $course_ids = array(), $search_filter = '', $course_filter = '', $date_filter = '', $order_filter = '', $user_id = null, $count_only = false, $all_attempt = false ) {
		global $wpdb;
		$search_filter = sanitize_text_field( $search_filter );
		$course_filter = (int) sanitize_text_field( $course_filter );
		$date_filter   = sanitize_text_field( $date_filter );
		$order_filter  = sanitize_sql_orderby( $order_filter );

		$course_ids = array_map(
			function ( $id ) {
				return "'" . esc_sql( $id ) . "'";
			},
			$course_ids
		);

		$course_ids_in = count( $course_ids ) ? ' AND quiz_attempts.course_id IN (' . implode( ', ', $course_ids ) . ') ' : '';

		$search_filter   = $search_filter ? '%' . $wpdb->esc_like( $search_filter ) . '%' : '';
		$search_term_raw = $search_filter;
		$search_filter   = $search_filter ? "AND ( users.user_email = '{$search_term_raw}' OR users.display_name LIKE {$search_filter} OR quiz.post_title LIKE {$search_filter} OR course.post_title LIKE {$search_filter} )" : '';

		$course_filter = 0 !== $course_filter ? " AND quiz_attempts.course_id = $course_filter " : '';
		$date_filter   = '' != $date_filter ? tutor_get_formated_date( 'Y-m-d', $date_filter ) : '';
		$date_filter   = '' != $date_filter ? " AND  DATE(quiz_attempts.attempt_started_at) = '$date_filter' " : '';
		$user_filter   = $user_id ? ' AND user_id=\'' . esc_sql( $user_id ) . '\' ' : '';

		$limit_offset = $count_only ? '' : " LIMIT 	{$start}, {$limit} ";
		$select_col   = $count_only ? ' COUNT(DISTINCT quiz_attempts.attempt_id) ' : ' quiz_attempts.*, users.*, quiz.* ';

		$attempt_type = $all_attempt ? '' : " AND quiz_attempts.attempt_status != 'attempt_started' ";

		$query = "SELECT {$select_col}
			FROM	{$wpdb->prefix}tutor_quiz_attempts AS quiz_attempts
					INNER JOIN {$wpdb->posts} AS quiz
							ON quiz_attempts.quiz_id = quiz.ID
					INNER JOIN {$wpdb->users} AS users
							ON quiz_attempts.user_id = users.ID
					INNER JOIN {$wpdb->posts} AS course
							ON course.ID = quiz_attempts.course_id
			WHERE 	1=1
					{$attempt_type}
					{$course_ids_in}
					{$search_filter}
					{$course_filter}
					{$date_filter}
					{$user_filter}
			ORDER 	BY quiz_attempts.attempt_id {$order_filter} {$limit_offset};";

		//phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		return $count_only ? $wpdb->get_var( $query ) : $wpdb->get_results( $query );
	}

	/**
	 * Get answers list by quiz question
	 *
	 * @since 1.0.0
	 *
	 * @param int  $question_id question ID.
	 * @param bool $rand rand.
	 *
	 * @return array|bool|null|object
	 */
	public static function get_answers_by_quiz_question( $question_id, $rand = false ) {
		global $wpdb;

		$question = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT *
			FROM	{$wpdb->prefix}tutor_quiz_questions
			WHERE	question_id = %d;
			",
				$question_id
			)
		);

		if ( ! $question ) {
			return false;
		}

		$order = ' answer_order ASC ';
		if ( 'ordering' === $question->question_type ) {
			$order = ' RAND() ';
		}

		if ( $rand ) {
			$order = ' RAND() ';
		}

		//phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$answers = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT *
			FROM 	{$wpdb->prefix}tutor_quiz_question_answers
			WHERE 	belongs_question_id = %d
					AND belongs_question_type = %s
			ORDER BY {$order}
			",
				$question_id,
				$question->question_type
			)
		);
		//phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		return $answers;
	}

	/**
	 * Get quiz answers by attempt id
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $attempt_id attempt ID.
	 * @param bool  $add_index need index or not.
	 *
	 * @return array|null|object
	 */
	public static function get_quiz_answers_by_attempt_id( $attempt_id, $add_index = false ) {
		global $wpdb;

		$ids    = is_array( $attempt_id ) ? $attempt_id : array( $attempt_id );
		$ids_in = implode( ',', $ids );

		if ( empty( $ids_in ) ) {
			// Prevent empty.
			return array();
		}

		//phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$results = $wpdb->get_results(
			"SELECT answers.*,
					question.*
			FROM 	{$wpdb->prefix}tutor_quiz_attempt_answers answers
					LEFT JOIN {$wpdb->prefix}tutor_quiz_questions question
						   ON answers.question_id = question.question_id
			WHERE 	answers.quiz_attempt_id IN ({$ids_in})
			ORDER BY attempt_answer_id ASC;"
		);
		//phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		if ( $add_index ) {
			$new_array = array();

			foreach ( $results as $result ) {
				! isset( $new_array[ $result->quiz_attempt_id ] ) ? $new_array[ $result->quiz_attempt_id ] = array() : 0;
				$new_array[ $result->quiz_attempt_id ][] = $result;
			}

			return $new_array;
		}

		return $results;
	}

	/**
	 * Get single answer by answer_id
	 *
	 * @since 1.0.0
	 *
	 * @param array|init $answer_id answer id.
	 *
	 * @return array|null|object
	 */
	public static function get_answer_by_id( $answer_id ) {
		global $wpdb;

		! is_array( $answer_id ) ? $answer_id = array( $answer_id ) : 0;

		$answer_id = array_map(
			function ( $id ) {
				return "'" . esc_sql( $id ) . "'";
			},
			$answer_id
		);

		$in_ids_string = implode( ', ', $answer_id );

		//phpcs:disable WordPress.DB.PreparedSQL.NotPrepared
		$answer = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT answer.*,
					question.question_title,
					question.question_type
			FROM 	{$wpdb->prefix}tutor_quiz_question_answers answer
					LEFT JOIN {$wpdb->prefix}tutor_quiz_questions question
						   ON answer.belongs_question_id = question.question_id
			WHERE 	answer.answer_id IN (" . $in_ids_string . ')
					AND 1 = %d;
			',
				1
			)
		);
		//phpcs:enable WordPress.DB.PreparedSQL.NotPrepared

		return $answer;
	}

	/**
	 * Get quiz attempt timing
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $attempt_data attempt data.
	 * @return array
	 */
	public static function get_quiz_attempt_timing( $attempt_data ) {
		$attempt_duration       = '';
		$attempt_duration_taken = '';
		$attempt_info           = @unserialize( $attempt_data->attempt_info );
		if ( is_array( $attempt_info ) ) {
			// Allowed duration.
			if ( isset( $attempt_info['time_limit'] ) ) {
				//phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText
				$time_type        = __( ucwords( tutor_utils()->array_get( 'time_limit.time_type', $attempt_info, 'minutes' ) ), 'tutor' );
				$time_value       = tutor_utils()->array_get( 'time_limit.time_value', $attempt_info, 0 );
				$attempt_duration = $time_value . ' ' . $time_type;
			}

			// Taken duration.
			$seconds                = strtotime( $attempt_data->attempt_ended_at ) - strtotime( $attempt_data->attempt_started_at );
			$attempt_duration_taken = tutor_utils()->seconds_to_time( $seconds );
		}

		return compact( 'attempt_duration', 'attempt_duration_taken' );
	}

	/**
	 * Check student is passed in a quiz or not.
	 * Quiz retry mode: student required at least one quiz passed in attempts
	 *
	 * @since 2.1.0
	 *
	 * @param int $quiz_id quiz ID.
	 * @param int $user_id user ID.
	 *
	 * @return boolean
	 */
	public static function is_quiz_passed( $quiz_id, $user_id = 0 ) {
		global $wpdb;

		$user_id             = tutor_utils()->get_user_id( $user_id );
		$attempts            = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}tutor_quiz_attempts WHERE user_id=%d AND quiz_id=%d", $user_id, $quiz_id ) );
		$required_percentage = tutor_utils()->get_quiz_option( $quiz_id, 'passing_grade', 0 );

		foreach ( $attempts as $attempt ) {
			$earned_percentage = $attempt->earned_marks > 0 ? ( ( $attempt->earned_marks * 100 ) / $attempt->total_marks ) : 0;
			if ( $earned_percentage >= $required_percentage ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get all question type for a quiz
	 *
	 * @since 2.1.0
	 *
	 * @param integer $quiz_id quiz ID.
	 *
	 * @return array
	 */
	public static function get_quiz_question_types( int $quiz_id ) {
		global $wpdb;
		$types = $wpdb->get_col(
			$wpdb->prepare( "SELECT DISTINCT question_type FROM {$wpdb->prefix}tutor_quiz_questions WHERE quiz_id=%d", $quiz_id )
		);

		return $types;
	}

	/**
	 * Check a quiz attempt need manual review or not
	 *
	 * @since 2.1.0
	 *
	 * @param int $quiz_id quiz ID.
	 *
	 * @return boolean
	 */
	public static function is_manual_review_required( $quiz_id ) {
		$required              = false;
		$review_question_types = array( 'open_ended', 'short_answer' );
		$question_types        = self::get_quiz_question_types( $quiz_id );

		foreach ( $review_question_types as $type ) {
			if ( in_array( $type, $question_types, true ) ) {
				$required = true;
				break;
			}
		}

		return $required;
	}

	/**
	 * Get last or first quiz attempt
	 *
	 * @since 2.1.0
	 * @since 2.1.3   user_id param added.
	 *
	 * @param integer $quiz_id  quiz id to get attempt of.
	 * @param integer $user_id  user ID who attempt the quiz.
	 * @param string  $order  ASC or DESC, default is DESC
	 *                pass ASC to get first attempt.
	 *
	 * @return mixed  object on success, null on failure
	 */
	public function get_first_or_last_attempt( int $quiz_id, int $user_id = 0, string $order = 'DESC' ) {
		$attempt = QueryHelper::get_row(
			$this->get_table(),
			array(
				'quiz_id' => $quiz_id,
				'user_id' => tutor_utils()->get_user_id( $user_id ),
			),
			'attempt_id',
			$order
		);
		return $attempt;
	}

	/**
	 * Get total number of quizzes by course id
	 *
	 * @since 2.2.0
	 *
	 * @param int|array $course_id Course id or array of course ids.
	 *
	 * @return int
	 */
	public static function get_quiz_count_by_course( $course_id ) {
		global $wpdb;

		$and_clause = is_array( $course_id ) && count( $course_id ) ? ' AND post_parent IN (' . QueryHelper::prepare_in_clause( $course_id ) . ')' : "AND post_parent = $course_id";

		//phpcs:disable
		$count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT
					COUNT(ID) 
				FROM {$wpdb->posts}
				WHERE post_parent IN 
					(SELECT
						ID 
					FROM {$wpdb->posts} 
						WHERE post_type = %s
						{$and_clause}
						AND post_status = %s
					)
					AND post_type = %s 
					AND post_status = %s",
				'topics',
				'publish',
				'tutor_quiz',
				'publish'
			)
		);
		//phpcs:enable
		return $count ? $count : 0;
	}

	/**
	 * Get final quiz result depending on all attempts.
	 *
	 * @since 2.4.0
	 *
	 * @param int $quiz_id quiz id.
	 * @param int $user_id user id.
	 *
	 * @return string pass, fail, pending
	 */
	public static function get_quiz_result( $quiz_id, $user_id = 0 ) {
		global $wpdb;

		$all_pending = true;
		$result      = 'pending';

		$user_id      = tutor_utils()->get_user_id( $user_id );
		$attempt_list = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}tutor_quiz_attempts WHERE user_id=%d AND quiz_id=%d", $user_id, $quiz_id ) );

		$total_pending_attempt = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(quiz_attempt_id) total_pending_attempt
				FROM (
					SELECT qa.quiz_attempt_id, COUNT(*) AS total_pending
					FROM {$wpdb->prefix}tutor_quiz_attempt_answers qa
					WHERE qa.quiz_id = %d AND qa.user_id=%d AND qa.is_correct IS NULL
					GROUP BY qa.quiz_attempt_id
				) a
				",
				$quiz_id,
				$user_id
			)
		);

		if ( count( $attempt_list ) !== $total_pending_attempt ) {
			$all_pending = false;
		}

		if ( false === $all_pending ) {
			$required_percentage = tutor_utils()->get_quiz_option( $quiz_id, 'passing_grade', 0 );
			foreach ( $attempt_list as $attempt ) {
				$earned_percentage = $attempt->earned_marks > 0 ? ( ( $attempt->earned_marks * 100 ) / $attempt->total_marks ) : 0;
				if ( $earned_percentage >= $required_percentage ) {
					// If at least one attempt passed then quiz passed.
					$result = 'pass';
					break;
				} else {
					$result = 'fail';
				}
			}
		}

		return $result;
	}

	/**
	 * Get quiz attempt details
	 *
	 * @since 2.6.1
	 *
	 * @param integer $attempt_id attempt id.
	 *
	 * @return mixed
	 */
	public static function quiz_attempt_details( int $attempt_id ) {
		global $wpdb;

		$table_quiz_attempt_answers  = $wpdb->prefix . 'tutor_quiz_attempt_answers';
		$table_quiz_questions        = $wpdb->prefix . 'tutor_quiz_questions';
		$table_quiz_attempts         = $wpdb->prefix . 'tutor_quiz_attempts';
		$table_quiz_question_answers = $wpdb->prefix . 'tutor_quiz_question_answers';

		$query = "SELECT 
				ques.question_id, 
				ques.question_title, 
				ques.question_type, 
				(
				SELECT 
					GROUP_CONCAT(answer_title) 
				FROM 
					{$table_quiz_question_answers} 
				WHERE 
					belongs_question_id = ques.question_id 
					AND is_correct = 1
				) AS correct_answers,
				
				(
			
				SELECT
			
				CASE
					WHEN CHAR_LENGTH(att_ans.given_answer) = 1 AND att_ans.given_answer REGEXP '^[0-9]$' THEN
					-- If given_answer is a single digit integer
					(
						SELECT
						answer_title
						FROM
						{$table_quiz_question_answers}
						WHERE
						answer_id = CAST(att_ans.given_answer AS UNSIGNED)
					)
					WHEN CHAR_LENGTH(att_ans.given_answer) > 1 AND SUBSTRING(att_ans.given_answer, 1, 2) = 'a:' THEN
					-- If given_answer is serialized array
					(
						att_ans.given_answer
					)
					ELSE
					-- If given_answer is a serialized string
					att_ans.given_answer
				END
				) AS given_answer, 
				att_ans.question_mark, 
				att_ans.achieved_mark, 
				att_ans.is_correct,
				(
					SELECT 
						attempt_info
					FROM {$table_quiz_attempts}
					WHERE attempt_id = {$attempt_id}
					LIMIT 1
				) AS attempt_info
			FROM 
				{$table_quiz_attempt_answers} AS att_ans 
				JOIN {$table_quiz_questions} AS ques ON ques.question_id = att_ans.question_id 
				JOIN {$table_quiz_question_answers} AS ans ON ans.answer_id = att_ans.attempt_answer_id 
			WHERE 
				quiz_attempt_id = %d
			LIMIT 
				50		
		";

		$result = $wpdb->get_results( $wpdb->prepare( $query, $attempt_id ) );

		// If array and count result then loop with each result and prepare given answer.
		if ( is_array( $result ) && count( $result ) ) {
			foreach ( $result as $key => $value ) {
				// Check if given answer is a serialized string.
				if ( is_serialized( $value->given_answer ) ) {
					$given_answers                = tutor_utils()->get_answer_by_id( maybe_unserialize( $value->given_answer ) );
					$result[ $key ]->given_answer = array_column( $given_answers, 'answer_title' );
				} elseif ( is_numeric( $value->given_answer ) ) {
					$given_answers                = tutor_utils()->get_answer_by_id( maybe_unserialize( $value->given_answer ) );
					$result[ $key ]->given_answer = array_column( $given_answers, 'answer_title' );
				}
			}
		}

		return $result;
	}
}
