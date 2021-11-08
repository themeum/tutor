<?php
namespace TUTOR;

if ( ! defined( 'ABSPATH' ) )
	exit;

if (! class_exists('Tutor_List_Table')){
	include_once tutor()->path.'classes/Tutor_List_Table.php';
}

class Question_Answers_List extends \Tutor_List_Table {

	const Question_Answer_PAGE = 'question_answer';

	function process_bulk_action() {
		global $wpdb;

		//Detect when a bulk action is being triggered...
		if( 'delete' === $this->current_action() ) {
			if ( empty($_GET['question']) || ! is_array($_GET['question'])){
				return;
			}

			$question_ids = array_map('sanitize_text_field', $_GET['question']);
			$question_ids = implode( ',', array_map( 'absint', $question_ids ) );

			//Deleting question (comment), child question and question meta (comment meta)
			$wpdb->query( "DELETE FROM {$wpdb->comments} WHERE {$wpdb->comments}.comment_ID IN($question_ids)" );
			$wpdb->query( "DELETE FROM {$wpdb->comments} WHERE {$wpdb->comments}.comment_parent IN($question_ids)" );
			$wpdb->query( "DELETE FROM {$wpdb->commentmeta} WHERE {$wpdb->commentmeta}.comment_id IN($question_ids)" );
		}
	}

	function get_items() {
		$per_page = 20;

		$search_term = '';
		if (isset($_REQUEST['s'])){
			$search_term = sanitize_text_field($_REQUEST['s']);
		}

		// $this->process_bulk_action();

		$current_page = $this->get_pagenum();

		$total_items = tutor_utils()->get_total_qa_question($search_term);
		$items = tutor_utils()->get_qa_questions(($current_page-1)*$per_page, $per_page, $search_term);

		return array(
			'items' => $items,
			'pagination' => array(
				'total_items' => $total_items,
				'per_page'    => $per_page,
				'total_pages' => ceil($total_items/$per_page)
			)
		);
	}
}