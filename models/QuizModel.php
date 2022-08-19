<?php
namespace Tutor\Models;

/**
 * Class QuizModel
 *
 * @since 2.0.10
 */
class QuizModel {

	/**
	 * Get all of the attempts by an user of a quiz
	 *
	 * @param int $quiz_id
	 * @param int $user_id
	 *
	 * @return array|bool|null|object
	 *
	 * @since 1.0.0
	 */

	public function quiz_attempts( $quiz_id = 0, $user_id = 0 ) {
		global $wpdb;

		$quiz_id = tutor_utils()->get_post_id( $quiz_id );
		$user_id = tutor_utils()->get_user_id( $user_id );

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

		if ( is_array( $attempts ) && count( $attempts ) ) {
			return $attempts;
		}

		return false;
	}

	/**
	 * Get Quiz question by question id
	 *
	 * @param int $question_id
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
	 * @param int $quiz_id
	 * @param int $user_id
	 *
	 * @return array|bool|null|object
	 *
	 * @since 1.4.1
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
	 * @param $quiz_id
	 * @return int
	 *
	 * @since 1.0.0
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
	 * @param $quiz_id
	 *
	 * @return int
	 * @since 1.0.0
	 */
	public static function quiz_next_question_id() {
		global $wpdb;

		$last_order = (int) $wpdb->get_var( "SELECT MAX(question_id) FROM {$wpdb->prefix}tutor_quiz_questions;" );
		return $last_order + 1;
	}
}
