<?php
/**
 * Quiz Attempt Answers Table.
 *
 * @package Tutor\Database\Tables
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.3.0
 */

namespace Tutor\Database\Tables;

defined( 'ABSPATH' ) || exit;

use Tutor\Core\Table;

/**
 * Class QuizAttemptAnswersTable
 *
 * @since 4.3.0
 */
class QuizAttemptAnswersTable extends Table {

	/**
	 * Base table name.
	 *
	 * @since 4.3.0
	 *
	 * @var string
	 */
	protected static $table = 'tutor_quiz_attempt_answers';

	/**
	 * Get CREATE TABLE SQL DDL schema.
	 *
	 * @since 4.3.0
	 *
	 * @param bool $include_constraints Whether to include foreign key constraints.
	 *
	 * @return string
	 */
	public static function get_schema( bool $include_constraints = true ): string {
		$table   = static::get_name();
		$collate = static::get_charset_collate();

		return "CREATE TABLE {$table} (
			attempt_answer_id bigint(20) NOT NULL AUTO_INCREMENT,
			user_id bigint(20) DEFAULT NULL,
			quiz_id bigint(20) DEFAULT NULL,
			question_id bigint(20) DEFAULT NULL,
			quiz_attempt_id bigint(20) DEFAULT NULL,
			given_answer longtext,
			question_mark decimal(8,2) DEFAULT NULL,
			achieved_mark decimal(8,2) DEFAULT NULL,
			minus_mark decimal(8,2) DEFAULT NULL,
			is_correct tinyint(4) DEFAULT NULL,
			PRIMARY KEY  (attempt_answer_id)
		) {$collate};";
	}
}

