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
		if(isset($args['tab']) && 'all' === $args['tab']){
			$args['no_archive'] = false;
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
}
