<?php
/**
 * Tutor MCP quiz attempt tools.
 *
 * @package Tutor\Mcp
 * @author  Themeum <support@themeum.com>
 * @link    https://themeum.com
 * @since   4.3.0
 */

namespace Tutor\Mcp\Abilities;

use Tutor\Helpers\QueryHelper;
use Tutor\Models\QuizModel;
use Tutor\Mcp\Registrar;
use Tutor\Mcp\Support\AbilityFactory;
use Tutor\Mcp\Support\Pagination;
use Tutor\Mcp\Support\Permissions;
use TUTOR\Quiz;

defined( 'ABSPATH' ) || exit;

/**
 * Quiz attempt tool registration and execute handlers.
 *
 * @since 4.3.0
 */
class QuizAttemptAbilities {

	/**
	 * Register quiz attempt abilities.
	 *
	 * @since 4.3.0
	 *
	 * @return void
	 */
	public function register(): void {
		AbilityFactory::tool(
			'tutor-mcp/list-quiz-attempts',
			'tutor-mcp-quiz-attempts',
			__( 'List Quiz Attempts', 'tutor' ),
			__( 'Paginated list of quiz attempts. Filter by course_id or quiz_id.', 'tutor' ),
			array(
				'type'       => 'object',
				'properties' => array(
					'course_id' => array(
						'type'    => 'integer',
						'minimum' => 1,
					),
					'quiz_id'   => array(
						'type'    => 'integer',
						'minimum' => 1,
					),
					'page'      => array(
						'type'    => 'integer',
						'minimum' => 1,
						'default' => 1,
					),
					'per_page'  => array(
						'type'    => 'integer',
						'minimum' => 1,
						'maximum' => 50,
						'default' => 20,
					),
				),
			),
			static function ( array $input ): bool {
				if ( ! Permissions::can_manage_courses() ) {
					return false;
				}
				$course_id = absint( $input['course_id'] ?? 0 );
				if ( $course_id ) {
					return Permissions::can_edit_course( $course_id );
				}
				$quiz_id = absint( $input['quiz_id'] ?? 0 );
				if ( $quiz_id ) {
					$quiz_course = (int) tutor_utils()->get_course_id_by( 'quiz', $quiz_id );
					return $quiz_course && Permissions::can_edit_course( $quiz_course );
				}
				return true;
			},
			array( $this, 'list_quiz_attempts' )
		);

		AbilityFactory::tool(
			'tutor-mcp/get-quiz-attempt',
			'tutor-mcp-quiz-attempts',
			__( 'Get Quiz Attempt', 'tutor' ),
			__( 'Get detailed quiz attempt with answers.', 'tutor' ),
			array(
				'type'       => 'object',
				'required'   => array( 'attempt_id' ),
				'properties' => array(
					'attempt_id' => array(
						'type'    => 'integer',
						'minimum' => 1,
					),
				),
			),
			static function ( array $input ): bool {
				$attempt_id = absint( $input['attempt_id'] ?? 0 );
				return $attempt_id && tutor_utils()->can_user_manage( 'attempt', $attempt_id );
			},
			array( $this, 'get_quiz_attempt' )
		);

		AbilityFactory::tool(
			'tutor-mcp/review-quiz-attempt',
			'tutor-mcp-quiz-attempts',
			__( 'Review Quiz Attempt', 'tutor' ),
			__( 'Mark open-ended/essay answers as correct or incorrect with optional manual marks.', 'tutor' ),
			array(
				'type'       => 'object',
				'required'   => array( 'attempt_id', 'review_statuses' ),
				'properties' => array(
					'attempt_id'      => array(
						'type'    => 'integer',
						'minimum' => 1,
					),
					'review_statuses' => array(
						'type'        => 'object',
						'description' => 'Object keyed by question_id with values "correct" or "incorrect".',
					),
					'manual_marks'    => array(
						'type'        => 'object',
						'description' => 'Object keyed by question_id with numeric mark values.',
					),
					'feedback'        => array( 'type' => 'string' ),
				),
			),
			static function ( array $input ): bool {
				if ( Registrar::is_read_only() ) {
					return false;
				}
				$attempt_id = absint( $input['attempt_id'] ?? 0 );
				return $attempt_id && tutor_utils()->can_user_manage( 'attempt', $attempt_id );
			},
			array( $this, 'review_quiz_attempt' ),
			AbilityFactory::WRITE_HINTS
		);

		AbilityFactory::tool(
			'tutor-mcp/delete-quiz-attempt',
			'tutor-mcp-quiz-attempts',
			__( 'Delete Quiz Attempt', 'tutor' ),
			__( 'Delete a quiz attempt. Requires confirm=true.', 'tutor' ),
			array(
				'type'       => 'object',
				'required'   => array( 'attempt_id', 'confirm' ),
				'properties' => array(
					'attempt_id' => array(
						'type'    => 'integer',
						'minimum' => 1,
					),
					'confirm'    => array(
						'type'    => 'boolean',
						'default' => false,
					),
				),
			),
			static function ( array $input ): bool {
				if ( Registrar::is_read_only() ) {
					return false;
				}
				$attempt_id = absint( $input['attempt_id'] ?? 0 );
				return $attempt_id && tutor_utils()->can_user_manage( 'attempt', $attempt_id );
			},
			array( $this, 'delete_quiz_attempt' ),
			AbilityFactory::DESTRUCTIVE_HINTS
		);
	}

	/**
	 * List quiz attempts.
	 *
	 * Uses QuizModel::get_quiz_attempts with instructor_id_check — same as
	 * dashboard quiz-attempts.php (instructors only see their courses).
	 *
	 * @since 4.3.0
	 *
	 * @param array $input Input.
	 * @return array
	 */
	public function list_quiz_attempts( array $input ): array {
		$bounds        = Pagination::bounds( $input );
		$course_filter = '';

		if ( ! empty( $input['quiz_id'] ) ) {
			$course_id = (int) tutor_utils()->get_course_id_by( 'quiz', absint( $input['quiz_id'] ) );
			if ( $course_id ) {
				$course_filter = (string) $course_id;
			}
		} elseif ( ! empty( $input['course_id'] ) ) {
			$course_filter = (string) absint( $input['course_id'] );
		}

		$attempts = QuizModel::get_quiz_attempts(
			$bounds['offset'],
			$bounds['per_page'],
			'',
			$course_filter,
			'',
			'',
			'DESC',
			null,
			false,
			true
		);
		$total    = (int) QuizModel::get_quiz_attempts(
			$bounds['offset'],
			$bounds['per_page'],
			'',
			$course_filter,
			'',
			'',
			'DESC',
			null,
			true,
			true
		);

		$rows = array();
		if ( is_array( $attempts ) ) {
			foreach ( $attempts as $attempt ) {
				if ( ! empty( $input['quiz_id'] ) && absint( $input['quiz_id'] ) !== (int) $attempt->quiz_id ) {
					continue;
				}
				$rows[] = array(
					'attempt_id'         => (int) $attempt->attempt_id,
					'quiz_id'            => (int) $attempt->quiz_id,
					'course_id'          => (int) $attempt->course_id,
					'user_id'            => (int) $attempt->user_id,
					'total_marks'        => (float) $attempt->total_marks,
					'earned_marks'       => (float) $attempt->earned_marks,
					'attempt_status'     => $attempt->attempt_status,
					'attempt_started_at' => $attempt->attempt_started_at,
					'attempt_ended_at'   => $attempt->attempt_ended_at ?? null,
				);
			}
		}

		return array(
			'attempts' => $rows,
			'total'    => $total,
			'page'     => $bounds['page'],
			'per_page' => $bounds['per_page'],
		);
	}

	/**
	 * Get quiz attempt details.
	 *
	 * @since 4.3.0
	 *
	 * @param array $input Input.
	 * @return array|\WP_Error
	 */
	public function get_quiz_attempt( array $input ) {
		$attempt_id = absint( $input['attempt_id'] ?? 0 );
		$attempt    = tutor_utils()->get_attempt( $attempt_id );
		if ( ! is_object( $attempt ) ) {
			return new \WP_Error( 'tutor_mcp_not_found', __( 'Attempt not found.', 'tutor' ) );
		}

		$details = QuizModel::quiz_attempt_details( $attempt_id );

		return array(
			'attempt_id'         => (int) $attempt->attempt_id,
			'quiz_id'            => (int) $attempt->quiz_id,
			'course_id'          => (int) $attempt->course_id,
			'user_id'            => (int) $attempt->user_id,
			'total_marks'        => (float) $attempt->total_marks,
			'earned_marks'       => (float) $attempt->earned_marks,
			'attempt_status'     => $attempt->attempt_status,
			'attempt_started_at' => $attempt->attempt_started_at,
			'attempt_ended_at'   => $attempt->attempt_ended_at ?? null,
			'details'            => is_array( $details ) ? $details : array(),
		);
	}

	/**
	 * Review quiz attempt answers.
	 *
	 * Mirrors dashboard grading for manual-review question types: sets
	 * achieved_mark, clamps manual marks, fires review/graded hooks, and
	 * updates attempt totals.
	 *
	 * @since 4.3.0
	 *
	 * @param array $input Input.
	 * @return array|\WP_Error
	 */
	public function review_quiz_attempt( array $input ) {
		$deny = Permissions::allow_write();
		if ( is_wp_error( $deny ) ) {
			return $deny;
		}

		$attempt_id      = absint( $input['attempt_id'] ?? 0 );
		$review_statuses = isset( $input['review_statuses'] ) && is_array( $input['review_statuses'] )
			? $input['review_statuses']
			: array();
		$manual_marks    = isset( $input['manual_marks'] ) && is_array( $input['manual_marks'] )
			? $input['manual_marks']
			: array();
		$feedback        = array_key_exists( 'feedback', $input )
			? wp_kses_post( (string) $input['feedback'] )
			: null;

		$attempt = tutor_utils()->get_attempt( $attempt_id );
		if ( ! is_object( $attempt ) ) {
			return new \WP_Error( 'tutor_mcp_not_found', __( 'Attempt not found.', 'tutor' ) );
		}

		$attempt_answers = QuizModel::get_quiz_answers_by_attempt_id( $attempt_id );
		$answers_map     = array();
		if ( is_array( $attempt_answers ) ) {
			foreach ( $attempt_answers as $attempt_answer ) {
				$question_id = (int) ( $attempt_answer->question_id ?? 0 );
				if ( $question_id > 0 ) {
					$answers_map[ $question_id ] = $attempt_answer;
				}
			}
		}

		$manual_types       = QuizModel::get_manual_review_types();
		$has_review_updates = false;
		$course_id          = (int) $attempt->course_id;
		$student_id         = (int) $attempt->user_id;

		foreach ( $review_statuses as $question_id => $mark_as ) {
			$question_id = (int) $question_id;
			$mark_as     = (string) $mark_as;
			if ( ! in_array( $mark_as, array( 'correct', 'incorrect' ), true ) ) {
				continue;
			}

			$attempt_answer = $answers_map[ $question_id ] ?? null;
			if ( ! $attempt_answer ) {
				continue;
			}

			$question_type = (string) ( $attempt_answer->question_type ?? '' );
			if ( ! in_array( $question_type, $manual_types, true ) ) {
				continue;
			}

			$target_is_correct = ( 'correct' === $mark_as ) ? QuizModel::ATTEMPT_ANSWER_CORRECT : QuizModel::ATTEMPT_ANSWER_INCORRECT;
			$prev_is_correct   = null !== $attempt_answer->is_correct ? (int) $attempt_answer->is_correct : null;
			if ( $prev_is_correct === $target_is_correct ) {
				continue;
			}

			$question = QuizModel::get_quiz_question_by_id( $question_id );
			if ( ! is_object( $question ) ) {
				continue;
			}

			$attempt_answer_id = (int) $attempt_answer->attempt_answer_id;

			/**
			 * Same hooks as Quiz::apply_quiz_answer_review.
			 */
			do_action( 'tutor_quiz_review_answer_before', $attempt_answer_id, $attempt_id, $mark_as );
			$mark_as = apply_filters( 'tutor_quiz_review_mark_as', $mark_as, $attempt_answer_id, $attempt_id, $question );

			$question_mark = (float) ( $attempt_answer->question_mark ?? $question->question_mark ?? 0.0 );
			$default_marks = array(
				'achieved_mark' => 'correct' === $mark_as ? $question_mark : 0.0,
				'minus_mark'    => 0,
			);
			$review_marks  = apply_filters(
				'tutor_quiz_review_answer_marks',
				$default_marks,
				$mark_as,
				$attempt_answer,
				$question,
				$attempt
			);

			$answer_updated = QueryHelper::update(
				'tutor_quiz_attempt_answers',
				array(
					'achieved_mark' => (float) ( $review_marks['achieved_mark'] ?? $default_marks['achieved_mark'] ),
					'minus_mark'    => (float) ( $review_marks['minus_mark'] ?? $default_marks['minus_mark'] ),
					'is_correct'    => 'correct' === $mark_as ? QuizModel::ATTEMPT_ANSWER_CORRECT : QuizModel::ATTEMPT_ANSWER_INCORRECT,
				),
				array( 'attempt_answer_id' => $attempt_answer_id )
			);

			if ( false === $answer_updated ) {
				continue;
			}

			$attempt_answer->is_correct    = 'correct' === $mark_as ? QuizModel::ATTEMPT_ANSWER_CORRECT : QuizModel::ATTEMPT_ANSWER_INCORRECT;
			$attempt_answer->achieved_mark = (float) ( $review_marks['achieved_mark'] ?? $default_marks['achieved_mark'] );
			$has_review_updates            = true;

			do_action( 'tutor_quiz_review_answer_after', $attempt_answer_id, $attempt_id, $mark_as );
			do_action( 'tutor_quiz/answer/review/after', $attempt_answer_id, $course_id, $student_id );
		}

		$has_manual_mark_updates = false;
		foreach ( $manual_marks as $question_id => $mark ) {
			if ( '' === $mark || null === $mark || ! is_numeric( $mark ) ) {
				continue;
			}

			$question_id    = (int) $question_id;
			$attempt_answer = $answers_map[ $question_id ] ?? null;
			if ( ! $attempt_answer ) {
				continue;
			}

			$question_type = (string) ( $attempt_answer->question_type ?? '' );
			if ( ! in_array( $question_type, $manual_types, true ) ) {
				continue;
			}

			$question_mark = (float) ( $attempt_answer->question_mark ?? 0.0 );
			if ( $question_mark <= 0.0 ) {
				$question      = QuizModel::get_quiz_question_by_id( $question_id );
				$question_mark = (float) ( $question->question_mark ?? 0.0 );
			}

			$new_mark       = min( max( 0, (float) $mark ), $question_mark );
			$answer_updated = QueryHelper::update(
				'tutor_quiz_attempt_answers',
				array(
					'achieved_mark' => $new_mark,
					'is_correct'    => QuizModel::ATTEMPT_ANSWER_MANUAL_GRADED,
				),
				array( 'attempt_answer_id' => (int) $attempt_answer->attempt_answer_id )
			);

			if ( false !== $answer_updated ) {
				$has_manual_mark_updates = true;
			}
		}

		$updated = $has_review_updates || $has_manual_mark_updates;

		if ( $updated ) {
			$stats = QuizModel::get_attempt_answers_stats( $attempt_id );
			QueryHelper::update(
				'tutor_quiz_attempts',
				array(
					'earned_marks'         => (float) $stats->total_earned_marks,
					'is_manually_reviewed' => 1,
					'manually_reviewed_at' => gmdate( 'Y-m-d H:i:s', tutor_time() ),
					'attempt_status'       => QuizModel::ATTEMPT_ENDED,
				),
				array( 'attempt_id' => $attempt_id )
			);
			QuizModel::update_attempt_result( $attempt_id );

			do_action( 'tutor_quiz/attempt/submitted/feedback', $attempt_id );
			do_action( 'tutor_quiz/attempt/graded', $attempt_id );
		}

		$feedback_updated = false;
		if ( null !== $feedback ) {
			$feedback_updated = $this->save_instructor_feedback( $attempt_id, $feedback );
			if ( $feedback_updated && ! $updated ) {
				do_action( 'tutor_quiz/attempt/submitted/feedback', $attempt_id );
				do_action( 'tutor_quiz/attempt/graded', $attempt_id );
			}
		}

		return array(
			'attempt_id' => $attempt_id,
			'reviewed'   => $updated || $feedback_updated,
		);
	}

	/**
	 * Store instructor feedback in attempt_info.
	 *
	 * @since 4.3.0
	 *
	 * @param int    $attempt_id Attempt ID.
	 * @param string $feedback   Feedback text.
	 * @return bool
	 */
	private function save_instructor_feedback( int $attempt_id, string $feedback ): bool {
		$attempt_details = Quiz::attempt_details( $attempt_id );
		if ( ! $attempt_details || empty( $attempt_details->attempt_info ) ) {
			return false;
		}

		$attempt_info = maybe_unserialize( $attempt_details->attempt_info );
		if ( ! is_array( $attempt_info ) ) {
			return false;
		}

		$attempt_info['instructor_feedback'] = $feedback;

		return (bool) Quiz::update_attempt_info( $attempt_id, maybe_serialize( $attempt_info ) );
	}

	/**
	 * Delete a quiz attempt.
	 *
	 * @since 4.3.0
	 *
	 * @param array $input Input.
	 * @return array|\WP_Error
	 */
	public function delete_quiz_attempt( array $input ) {
		$deny = Permissions::allow_write();
		if ( is_wp_error( $deny ) ) {
			return $deny;
		}

		if ( empty( $input['confirm'] ) ) {
			return new \WP_Error( 'tutor_mcp_confirm_required', __( 'Deleting requires confirm=true.', 'tutor' ) );
		}

		$attempt_id = absint( $input['attempt_id'] ?? 0 );
		$attempt    = tutor_utils()->get_attempt( $attempt_id );
		if ( ! is_object( $attempt ) ) {
			return new \WP_Error( 'tutor_mcp_not_found', __( 'Attempt not found.', 'tutor' ) );
		}

		QuizModel::delete_quiz_attempt( $attempt_id );

		return array(
			'attempt_id' => $attempt_id,
			'deleted'    => true,
		);
	}
}
