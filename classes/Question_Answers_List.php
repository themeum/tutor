<?php
namespace TUTOR;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Question_Answers_List {

	use Backend_Page_Trait;

	const Question_Answer_PAGE = 'question_answer';

	function __construct($register_hook=true) {
		if(!$register_hook) {
			return;
		}

	}


	function get_items($args=array()) {
		$per_page = tutor_utils()->get_option( 'pagination_per_page' );

		$search_term = '';
		if (isset($args['search'])){
			$search_term = sanitize_text_field($args['search']);
		}

		$current_page 	= isset( $_REQUEST['paged'] ) ? absint( $_REQUEST['paged'] ) : 1;

		$question_status= !empty($args['tab']) ? $args['tab'] : null;
		$items 			= tutor_utils()->get_qa_questions(($current_page-1)*$per_page, $per_page, $search_term, null, null, null, $question_status, false, $args);
		$total_items 	= tutor_utils()->get_qa_questions(($current_page-1)*$per_page, $per_page, $search_term, null, null, null, $question_status, true, $args);

		return array(
			'items' => $items,
			'pagination' => array(
				'total_items' => $total_items,
				'per_page'    => $per_page,
				'paged'		  => $current_page
			)
		);
	}

	public function get_bulk_actions() {
		return array(
			$this->bulk_action_default(),
			$this->bulk_action_delete()
		);
	}

	/**
	 * Get answer list by question id & type
	 *
	 * @since v2.0.2
	 *
	 * @param integer $question_id
	 * @param string $question_type
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
