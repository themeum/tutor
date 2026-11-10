<?php
/**
 * Quiz Attempts Table.
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
 * Class QuizAttemptsTable
 *
 * @since 4.3.0
 */
class QuizAttemptsTable extends Table {

	/**
	 * Base table name.
	 *
	 * @var string
	 */
	protected static $table = 'tutor_quiz_attempts';

	/**
	 * Get CREATE TABLE SQL DDL schema.
	 *
	 * @since 4.3.0
	 *
	 * @param bool $include_constraints Whether to include foreign key constraints.
	 * @return string
	 */
	public static function get_schema( bool $include_constraints = true ): string {
		$table   = static::get_name();
		$collate = static::get_charset_collate();

		return "CREATE TABLE {$table} (
			attempt_id bigint(20) NOT NULL AUTO_INCREMENT,
			course_id bigint(20) DEFAULT NULL,
			quiz_id bigint(20) DEFAULT NULL,
			user_id bigint(20) DEFAULT NULL,
			total_questions int(11) DEFAULT NULL,
			total_answered_questions int(11) DEFAULT NULL,
			total_marks decimal(9,2) DEFAULT NULL,
			earned_marks decimal(9,2) DEFAULT NULL,
			attempt_info text,
			attempt_status varchar(50) DEFAULT NULL,
			attempt_ip varchar(250) DEFAULT NULL,
			attempt_started_at datetime DEFAULT NULL,
			attempt_ended_at datetime DEFAULT NULL,
			is_manually_reviewed int(1) DEFAULT NULL,
			manually_reviewed_at datetime DEFAULT NULL,
			result varchar(10) DEFAULT NULL,
			PRIMARY KEY  (attempt_id),
			INDEX (course_id),
			INDEX (quiz_id),
			INDEX (user_id),
			INDEX (result)
		) {$collate};";
	}
}

