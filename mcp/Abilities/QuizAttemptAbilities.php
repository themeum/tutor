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
use TUTOR\User;

defined( 'ABSPATH' ) || exit;

/**
 * Quiz attempt tool registration and execute handlers.
 *
 * @since 4.3.0
 */
class QuizAttemptAbilities {

	/**
	 * Write annotations.
	 *
	 * @var array<string, bool>
	 */
	private const WRITE_HINTS = array(
		'readOnlyHint'    => false,
		'destructiveHint' => false,
	);

	/**
	 * Destructive annotations.
	 *
	 * @var array<string, bool>
	 */
	private const DESTRUCTIVE_HINTS = array(
		'readOnlyHint'    => false,
		'destructiveHint' => true,
	);

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
					'attempt_id'     => array(
						'type'    => 'integer',
						'minimum' => 1,
					),
					'review_statuses' => array(
						'type'        => 'object',
						'description' => 'Object keyed by question_id with values "correct" or "incorrect".',
					),
					'manual_marks'   => array(
						'type'        => 'object',
						'description' => 'Object keyed by question_id with numeric mark values.',
					),
					'feedback'       => array( 'type' => 'string' ),
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
			self::WRITE_HINTS
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
			self::DESTRUCTIVE_HINTS
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
		$bounds       = Pagination::bounds( $input );
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
	 * Uses Quiz::review_quiz_answers_bulk internal path via direct DB updates.
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

		$attempt_id     = absint( $input['attempt_id'] ?? 0 );
		$review_statuses = isset( $input['review_statuses'] ) && is_array( $input['review_statuses'] )
			? $input['review_statuses']
			: array();
		$manual_marks   = isset( $input['manual_marks'] ) && is_array( $input['manual_marks'] )
			? $input['manual_marks']
			: array();
		$feedback       = isset( $input['feedback'] ) ? sanitize_textarea_field( (string) $input['feedback'] ) : null;

		$attempt = tutor_utils()->get_attempt( $attempt_id );
		if ( ! is_object( $attempt ) ) {
			return new \WP_Error( 'tutor_mcp_not_found', __( 'Attempt not found.', 'tutor' ) );
		}

		$attempt_answers = QuizModel::get_quiz_answers_by_attempt_id( $attempt_id );
		$answers_map     = array();
		if ( is_array( $attempt_answers ) ) {
			foreach ( $attempt_answers as $aa ) {
				$qid = (int) ( $aa->question_id ?? 0 );
				if ( $qid > 0 ) {
					$answers_map[ $qid ] = $aa;
				}
			}
		}

		$updated = false;
		foreach ( $review_statuses as $question_id => $mark_as ) {
			$question_id = (int) $question_id;
			$mark_as     = (string) $mark_as;
			if ( ! in_array( $mark_as, array( 'correct', 'incorrect' ), true ) ) {
				continue;
			}

			$aa = $answers_map[ $question_id ] ?? null;
			if ( ! $aa ) {
				continue;
			}

			$is_correct = 'correct' === $mark_as ? 1 : 0;
			QueryHelper::update(
				'tutor_quiz_attempt_answers',
				array( 'is_correct' => $is_correct ),
				array( 'attempt_answer_id' => (int) $aa->attempt_answer_id )
			);

			if ( isset( $manual_marks[ $question_id ] ) && is_numeric( $manual_marks[ $question_id ] ) ) {
				QueryHelper::update(
					'tutor_quiz_attempt_answers',
					array( 'achieved_mark' => (float) $manual_marks[ $question_id ] ),
					array( 'attempt_answer_id' => (int) $aa->attempt_answer_id )
				);
			}

			$updated = true;
		}

		if ( $updated ) {
			$stats       = QuizModel::get_attempt_answers_stats( $attempt_id );
			$update_data = array(
				'earned_marks'         => $stats->total_earned_marks,
				'is_manually_reviewed' => 1,
				'manually_reviewed_at' => gmdate( 'Y-m-d H:i:s', tutor_time() ),
			);

			if ( null !== $feedback ) {
				$update_data['instructor_feedback'] = $feedback;
			}

			QueryHelper::update(
				'tutor_quiz_attempts',
				$update_data,
				array( 'attempt_id' => $attempt_id )
			);
			QuizModel::update_attempt_result( $attempt_id );
		}

		return array(
			'attempt_id' => $attempt_id,
			'reviewed'   => $updated,
		);
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
