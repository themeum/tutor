<?php
/**
 * REST API for quiz.
 *
 * @package Tutor\RestAPI
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.7.1
 */

namespace TUTOR;

use Tutor\Helpers\QueryHelper;
use Tutor\Models\QuizModel;
use WP_REST_Request;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class REST_Quiz
 */
class REST_Quiz {

	use REST_Response;

	/**
	 * Quiz post type
	 *
	 * @var string The post type for quizzes.
	 */
	private $post_type = 'tutor_quiz';

	/**
	 * Post parent ID
	 *
	 * @var int|null The post parent ID.
	 */
	private $post_parent;

	/**
	 * Quiz questions table name
	 *
	 * @var string The table name for quiz questions.
	 */
	private $t_quiz_question = 'tutor_quiz_questions';

	/**
	 * Quiz question answers table name
	 *
	 * @var string The table name for quiz question answers.
	 */
	private $t_quiz_ques_ans = 'tutor_quiz_question_answers';

	/**
	 * Quiz question answer options table name
	 *
	 * @var string The table name for quiz attempts.
	 */
	private $t_quiz_attempt = 'tutor_quiz_attempts';

	/**
	 * Quiz attempt answers table name
	 *
	 * @var string The table name for quiz attempt answers.
	 */
	private $t_quiz_attempt_ans = 'tutor_quiz_attempt_answers';

	/**
	 * Obtain quiz detail for a single quiz.
	 *
	 * @since 2.7.1
	 *
	 * @param WP_REST_Request $request REST request object.
	 *
	 * @return mixed
	 */
	public function get_quiz( WP_REST_Request $request ) {
		global $wpdb;

		$quiz_id   = Input::sanitize( $request->get_param( 'id' ), 0, Input::TYPE_INT );
		$quiz_post = get_post( $quiz_id );

		if ( ! $quiz_post || $this->post_type !== $quiz_post->post_type || 'publish' !== $quiz_post->post_status ) {
			return $this->response(
				'tutor_read_quiz',
				__( 'Quiz not found for given ID', 'tutor' ),
				array(),
				$this->not_found_code
			);
		}

		$quiz = REST_Posts::to_public_post_dto( $quiz_post );

		$wpdb->q_t = $wpdb->prefix . $this->t_quiz_question; // Question table.

		$quiz->quiz_settings = get_post_meta( $quiz->ID, 'tutor_quiz_option', false );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom tutor_quiz_questions table; no WP API.
		$questions = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT
				question_id,
				question_title,
				question_description,
				question_type,
				question_mark,
				question_settings FROM {$wpdb->q_t}
				WHERE quiz_id = %d
				",
				$quiz->ID
			)
		);

		foreach ( $questions as $question ) {
			if ( isset( $quiz->question_settings ) ) {
				$question->question_settings = maybe_unserialize( $quiz->question_settings );
			}

			$question->question_answers = QuizModel::get_question_answers( $question->question_id, $question->question_type );
		}

		if ( ! RestAuth::can_reveal_quiz_answers( $quiz_id ) ) {
			$questions = self::redact_answers_for_student( $questions );
		}

		$quiz->quiz_questions = $questions;

		return $this->response(
			'tutor_read_quiz',
			__( 'Quiz retrieved successfully', 'tutor' ),
			$quiz,
			$this->success_code
		);
	}

	/**
	 * Get quiz with settings.
	 *
	 * @since 1.7.1
	 *
	 * @param WP_REST_Request $request REST request object.
	 *
	 * @return mixed
	 */
	public function quiz_with_settings( WP_REST_Request $request ) {
		$this->post_parent = $request->get_param( 'topic_id' );

		$data = REST_Posts::get_published_child_posts( $this->post_type, $this->post_parent );

		if ( count( $data ) > 0 ) {
			foreach ( $data as $quiz ) {
				$quiz->quiz_settings = get_post_meta( $quiz->ID, 'tutor_quiz_option', false );
			}

			return $this->response(
				'tutor_read_quiz',
				__( 'Quiz retrieved successfully', 'tutor' ),
				$data,
				$this->success_code
			);
		}

		return $this->response(
			'tutor_read_quiz',
			__( 'Quiz not found for given ID', 'tutor' ),
			$data,
			$this->not_found_code
		);
	}

	/**
	 * Get quiz question and answers.
	 *
	 * @param WP_REST_Request $request REST request object.
	 *
	 * @return mixed
	 */
	public function quiz_question_ans( WP_REST_Request $request ) {
		global $wpdb;

		$this->post_parent = $request->get_param( 'id' );

		$wpdb->q_t = $wpdb->prefix . $this->t_quiz_question; // Question table.

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom tutor_quiz_questions table; no WP API.
		$quizs = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT
				question_id,
				question_title,
				question_description,
				question_type,
				question_mark,
				question_settings FROM {$wpdb->q_t}
				WHERE quiz_id = %d
				",
				$this->post_parent
			)
		);
		$data  = array();

		if ( count( $quizs ) > 0 ) {
			// Get question ans by question_id.
			foreach ( $quizs as $quiz ) {
				// Un-serialized question settings.
				$quiz->question_settings = maybe_unserialize( $quiz->question_settings );

				// Question options (redacted later when answers must stay hidden).
				$quiz->question_answers = QuizModel::get_question_answers( $quiz->question_id, $quiz->question_type );

				array_push( $data, $quiz );
			}

			if ( ! RestAuth::can_reveal_quiz_answers( (int) $this->post_parent ) ) {
				$data = self::redact_answers_for_student( $data );
			}

			return $this->response(
				'tutor_read_quiz',
				__( 'Question retrieved successfully', 'tutor' ),
				$data,
				$this->success_code
			);
		}

		return $this->response(
			'tutor_read_quiz',
			__( 'Question not found for given ID', 'tutor' ),
			array(),
			$this->not_found_code
		);
	}

	/**
	 * Get quiz attempt details.
	 *
	 * @since 1.7.1
	 *
	 * @param WP_REST_Request $request REST request object.
	 *
	 * @return mixed
	 */
	public function quiz_attempt_details( WP_REST_Request $request ) {
		global $wpdb;

		$quiz_id = Input::sanitize( $request->get_param( 'id' ), 0, Input::TYPE_INT );

		$wpdb->quiz_attempt = $wpdb->prefix . $this->t_quiz_attempt;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom tutor_quiz_attempts table; no WP API.
		$attempts = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT
				att.attempt_id,
				att.user_id,
				att.total_questions,
				att.total_answered_questions,
				att.total_marks,
				att.earned_marks,
				att.attempt_info,
				att.attempt_status,
				att.attempt_started_at,
				att.attempt_ended_at,
				att.is_manually_reviewed,
				att.manually_reviewed_at
			FROM {$wpdb->quiz_attempt} att
				WHERE att.quiz_id = %d
			",
				$quiz_id
			)
		);

		if ( count( $attempts ) > 0 ) {
			$user_id      = get_current_user_id();
			$course_id    = (int) tutor_utils()->get_course_id_by( 'quiz', $quiz_id );
			$can_view_all = $course_id && tutor_utils()->has_user_course_content_access( $user_id, $course_id );

			// unserialize each attempt info.
			foreach ( $attempts as $key => $attempt ) {
				if ( ! $can_view_all && (int) $attempt->user_id !== (int) $user_id ) {
					unset( $attempts[ $key ] );
					continue;
				}

				$attempt->attempt_info = maybe_unserialize( $attempt->attempt_info );
				// Attach answers for this attempt only.
				$answers = $this->get_quiz_attempt_ans( (int) $attempt->attempt_id );

				if ( false !== $answers ) {
					$attempt->attempts_answer = $answers;
				} else {
					$attempt->attempts_answer = array();
				}
			}

			$attempts = array_values( $attempts );

			return $this->response(
				'tutor_read_quiz',
				__( 'Quiz attempts retrieved successfully', 'tutor' ),
				$attempts,
				$this->success_code
			);
		}

		return $this->response(
			'tutor_read_quiz',
			__( 'Quiz attempts not found for given ID', 'tutor' ),
			array(),
			$this->not_found_code
		);
	}

	/**
	 * Get quiz attempt answers for a single attempt.
	 *
	 * @since 1.7.1
	 * @since 4.2.0 Scope by quiz_attempt_id to prevent cross-user answer leaks.
	 *
	 * @param int $attempt_id quiz attempt id.
	 *
	 * @return mixed
	 */
	protected function get_quiz_attempt_ans( $attempt_id ) {
		global $wpdb;

		$attempt_id = absint( $attempt_id );
		if ( ! $attempt_id ) {
			return false;
		}

		$wpdb->quiz_attempt_ans = $wpdb->prefix . $this->t_quiz_attempt_ans;
		$wpdb->quiz_question    = $wpdb->prefix . $this->t_quiz_question;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom attempt/question tables; no WP API.
		$answers = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT
				q.question_title,
				att_ans.given_answer,
				att_ans.question_mark,
				att_ans.achieved_mark,
				att_ans.minus_mark,
				att_ans.is_correct FROM {$wpdb->quiz_attempt_ans} as att_ans
			JOIN {$wpdb->quiz_question} q ON q.question_id = att_ans.question_id
			WHERE att_ans.quiz_attempt_id = %d
			",
				$attempt_id
			)
		);

		if ( count( $answers ) > 0 ) {
			// unserialize each given answer.
			foreach ( $answers as $key => $answer ) {
				$answer->given_answer = maybe_unserialize( $answer->given_answer );

				if ( is_numeric( $answer->given_answer ) || is_array( $answer->given_answer ) ) {
					$ids                  = $answer->given_answer;
					$ans_title            = $this->answer_titles_by_id( $ids );
					$answer->given_answer = $ans_title;
				}
			}

			return $answers;
		}
		return false;
	}

	/**
	 * Get answer titles by id.
	 *
	 * @since 1.7.1
	 *
	 * @param int|int[] $id answer id or array of answer id.
	 *
	 * @return mixed
	 */
	protected function answer_titles_by_id( $id ) {
		global $wpdb;
		$wpdb->t_quiz_ques_ans = $wpdb->prefix . $this->t_quiz_ques_ans;

		$ids = array_filter( array_map( 'absint', (array) $id ) );
		if ( empty( $ids ) ) {
			return array();
		}

		$array = QueryHelper::prepare_in_clause( $ids );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom answers table; no WP API.
		$results = $wpdb->get_results(
			"SELECT
				answer_title
			FROM {$wpdb->t_quiz_ques_ans}
			WHERE
			answer_id IN ({$array})" // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- IDs prepared via QueryHelper::prepare_in_clause.
		);

		return $results;
	}

	/**
	 * Redact quiz solution fields for students who cannot reveal answers.
	 *
	 * Choice types keep option titles but drop correctness markers. Types where
	 * the answer row itself is the key (FIB, matching, ordering, etc.) return
	 * an empty answers list.
	 *
	 * @since 4.2.0
	 *
	 * @param array $questions Questions with answers.
	 *
	 * @return array
	 */
	private static function redact_answers_for_student( $questions ) {
		$choice_types = array(
			QuizModel::QUESTION_TYPE_TRUE_FALSE,
			QuizModel::QUESTION_TYPE_SINGLE_CHOICE,
			QuizModel::QUESTION_TYPE_MULTIPLE_CHOICE,
		);

		foreach ( $questions as $question ) {
			$type = isset( $question->question_type ) ? (string) $question->question_type : '';

			if ( ! in_array( $type, $choice_types, true ) ) {
				$question->question_answers = array();
				continue;
			}

			if ( empty( $question->question_answers ) || ! is_array( $question->question_answers ) ) {
				continue;
			}

			foreach ( $question->question_answers as $answer ) {
				if ( ! is_object( $answer ) ) {
					continue;
				}
				unset( $answer->is_correct, $answer->answer_two_gap_match );
			}
		}

		return $questions;
	}
}
