<?php
/**
 * Quiz Questions Table.
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
 * Class QuizQuestionsTable
 *
 * @since 4.3.0
 */
class QuizQuestionsTable extends Table {

	/**
	 * Base table name.
	 *
	 * @since 4.3.0
	 *
	 * @var string
	 */
	protected static $table = 'tutor_quiz_questions';

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
			question_id bigint(20) NOT NULL AUTO_INCREMENT,
			quiz_id bigint(20) DEFAULT NULL,
			question_title text,
			question_description longtext,
			answer_explanation longtext DEFAULT '',
			question_type varchar(50) DEFAULT NULL,
			question_mark decimal(9,2) DEFAULT NULL,
			question_settings longtext,
			question_order int(11) DEFAULT NULL,
			PRIMARY KEY (question_id)
		) {$collate};";
	}
}

