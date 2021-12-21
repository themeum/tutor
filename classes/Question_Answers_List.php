<?php
namespace TUTOR;

if ( ! defined( 'ABSPATH' ) )
	exit;

if (! class_exists('Tutor_List_Table')){
	include_once tutor()->path.'classes/Tutor_List_Table.php';
}

class Question_Answers_List extends \Tutor_List_Table {

	use Backend_Page_Trait;

	const Question_Answer_PAGE = 'question_answer';

	function __construct($register_hook=true) {
		if(!$register_hook) {
			return;
		}

	}


	function get_items($args=array()) {
		$per_page = 15;

		$search_term = '';
		if (isset($args['search'])){
			$search_term = sanitize_text_field($args['search']);
		}

		$current_page 	= $this->get_pagenum();
		
		$question_status= !empty($_GET['data']) ? $_GET['data'] : null;
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