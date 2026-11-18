<?php
/**
 * Quiz attempt test class.
 *
 * @package Tutor\Tests
 */

namespace TutorTest;

use Tutor\Models\QuizModel;
use TUTOR\Quiz;

/**
 * Quiz attempt test.
 */
class QuizAttemptTest extends \WP_UnitTestCase {

	/**
	 * Abandoning without posted answers should still end the attempt.
	 *
	 * Covers the legacy-mode case where no supported question is rendered,
	 * so the submitted attempt array is empty.
	 *
	 * @return void
	 */
	public function test_abandon_without_posted_answers_ends_attempt() {
		global $wpdb;

		$user_id = self::factory()->user->create();

		$wpdb->insert(
			$wpdb->prefix . 'tutor_quiz_attempts',
			array(
				'course_id'                => 0,
				'quiz_id'                  => 0,
				'user_id'                  => $user_id,
				'total_questions'          => 0,
				'total_answered_questions' => 0,
				'total_marks'              => 0,
				'earned_marks'             => 0,
				'attempt_info'             => maybe_serialize( array( 'passing_grade' => 50 ) ),
				'attempt_status'           => QuizModel::ATTEMPT_STARTED,
				'attempt_started_at'       => current_time( 'mysql' ),
			)
		);

		$attempt_id = (int) $wpdb->insert_id;
		$attempt    = tutor_utils()->get_attempt( $attempt_id );

		$ended_count = 0;
		$listener    = function ( $ended_attempt_id ) use ( &$ended_count, $attempt_id ) {
			if ( (int) $ended_attempt_id === $attempt_id ) {
				++$ended_count;
			}
		};
		add_action( 'tutor_quiz/attempt_ended', $listener );

		Quiz::manage_attempt_answers( array(), $attempt, $attempt_id, 0, $user_id );

		remove_action( 'tutor_quiz/attempt_ended', $listener );

		$updated_attempt = tutor_utils()->get_attempt( $attempt_id );

		$this->assertSame( QuizModel::ATTEMPT_ENDED, $updated_attempt->attempt_status );
		$this->assertSame( 1, $ended_count );
	}

	/**
	 * Test has_supported_questions returns false when unsupported questions filter excludes all questions.
	 *
	 * @return void
	 */
	public function test_has_supported_questions() {
		global $wpdb;

		$quiz_id = self::factory()->post->create( array( 'post_type' => tutor()->quiz_post_type ) );

		$wpdb->delete( $wpdb->prefix . 'tutor_quiz_questions', array( 'quiz_id' => $quiz_id ) );

		$wpdb->insert(
			$wpdb->prefix . 'tutor_quiz_questions',
			array(
				'quiz_id'        => $quiz_id,
				'question_title' => 'Question',
				'question_type'  => QuizModel::QUESTION_TYPE_SINGLE_CHOICE,
				'question_mark'  => 1,
			)
		);

		$this->assertTrue( Quiz::has_supported_questions( $quiz_id ) );

		$exclude_single_choice = function ( $types ) {
			$types[] = QuizModel::QUESTION_TYPE_SINGLE_CHOICE;
			return $types;
		};
		add_filter( 'tutor_filter_unsupported_quiz_question_types', $exclude_single_choice );

		$this->assertFalse( Quiz::has_supported_questions( $quiz_id ) );

		remove_filter( 'tutor_filter_unsupported_quiz_question_types', $exclude_single_choice );
	}
}
