<?php
/**
 * Question answer list management
 *
 * @package Tutor\QuestionAnswer
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.0.0
 */

namespace TUTOR;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Question_Answers_List
 *
 * @since 1.0.0
 */
class Question_Answers_List {

	/**
	 * Backend Page trait
	 */
	use Backend_Page_Trait;

	/**
	 * Page slug
	 *
	 * @var string
	 */
	const QUESTION_ANSWER_PAGE = 'question_answer';

	/**
	 * Register hooks & dependencies
	 *
	 * @since 1.0.0
	 *
	 * @param boolean $register_hook weather to register hook or not.
	 *
	 * @return void
	 */
	public function __construct( $register_hook = true ) {
		if ( ! $register_hook ) {
			return;
		}
	}

	/**
	 * Get question answers
	 *
	 * @since 1.0.0
	 *
	 * @param array $args query args.
	 *
	 * @return array
	 */
	public function get_items( $args = array() ) {
		$per_page = tutor_utils()->get_option( 'pagination_per_page' );

		$search_term = '';
		if ( isset( $args['search'] ) ) {
			$search_term = sanitize_text_field( $args['search'] );
		}

		$current_page    = absint( Input::sanitize_request_data( 'paged', 1 ) );
		$question_status = ! empty( $args['tab'] ) ? $args['tab'] : null;
		$items           = tutor_utils()->get_qa_questions( ( $current_page - 1 ) * $per_page, $per_page, $search_term, null, null, null, $question_status, false, $args );
		$total_items     = tutor_utils()->get_qa_questions( ( $current_page - 1 ) * $per_page, $per_page, $search_term, null, null, null, $question_status, true, $args );

		return array(
			'items'      => $items,
			'pagination' => array(
				'total_items' => $total_items,
				'per_page'    => $per_page,
				'paged'       => $current_page,
			),
		);
	}

	/**
	 * Get bulk action as an array
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		return array(
			$this->bulk_action_default(),
			$this->bulk_action_delete(),
		);
	}

	/**
	 * Get answer list by question id & type
	 *
	 * @since 2.0.2
	 *
	 * @param integer $question_id question id.
	 * @param string  $question_type question type.
	 *
	 * @return array list of answer
	 */
	public static function answer_list_by_question( int $question_id, string $question_type ): array {
		global $wpdb;
		$answers = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}tutor_quiz_question_answers 
				where belongs_question_id = %d 
					AND belongs_question_type = %s 
				order by answer_order asc ;",
				$question_id,
				$question_type
			)
		);
		return is_array( $answers ) && count( $answers ) ? $answers : array();
	}
}
