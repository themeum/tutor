<?php
/**
 * Quiz Question Answers Table.
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
 * Class QuizQuestionAnswersTable
 *
 * @since 4.3.0
 */
class QuizQuestionAnswersTable extends Table {

	/**
	 * Base table name.
	 *
	 * @var string
	 */
	protected static $table = 'tutor_quiz_question_answers';

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
			answer_id bigint(20) NOT NULL AUTO_INCREMENT,
			belongs_question_id bigint(20) DEFAULT NULL,
			belongs_question_type varchar(250) DEFAULT NULL,
			answer_title text,
			is_correct tinyint(4) DEFAULT NULL,
			image_id bigint(20) DEFAULT NULL,
			answer_two_gap_match text,
			answer_view_format varchar(250) DEFAULT NULL,
			answer_settings text,
			answer_order int(11) DEFAULT '0',
			PRIMARY KEY (answer_id)
		) {$collate};";
	}
}

