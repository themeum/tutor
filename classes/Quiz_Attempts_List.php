<?php
namespace TUTOR;

if ( ! defined( 'ABSPATH' ) )
	exit;

if (! class_exists('Tutor_List_Table')){
	include_once tutor()->path.'classes/Tutor_List_Table.php';
}

class Quiz_Attempts_List extends \Tutor_List_Table {

	function __construct(){
		global $status, $page;

		//Set parent defaults
		parent::__construct( array(
			'singular'  => 'attempt',     //singular name of the listed records
			'plural'    => 'attempts',    //plural name of the listed records
			'ajax'      => false        //does this table support ajax?
		) );
	}

	function column_default($item, $column_name){
		switch($column_name){
			case 'unknown_col':
				return $item->$column_name;
			default:
				//return print_r($item,true); //Show the whole array for troubleshooting purposes
		}
	}

	function column_student($item){
		$actions = array();

		$actions['answer'] = sprintf('<a href="?page=%s&sub_page=%s&attempt_id=%s">View</a>',$_REQUEST['page'],'view_attempt',$item->comment_ID);
		//$actions['delete'] = sprintf('<a href="?page=%s&action=%s&attempt_id=%s">Delete</a>',$_REQUEST['page'],'delete',$item->comment_ID);

		$quiz_title = '<strong>'.$item->display_name.'</strong> <br />'.$item->user_email.'<br /><br />'. human_time_diff(strtotime
			($item->comment_date)).__(' ago', 'tutor');

		//Return the title contents
		return sprintf('%1$s <span style="color:silver">(id:%2$s)</span>%3$s',
			$quiz_title,
			$item->comment_ID,
			$this->row_actions($actions)
		);
	}

	function column_quiz($item){
		return $item->post_title;
	}

	function column_cb($item){
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			/*$1%s*/ $this->_args['singular'],  //Let's simply repurpose the table's singular label ("teacher")
			/*$2%s*/ $item->comment_ID                //The value of the checkbox should be the record's id
		);
	}

	function column_course($item) {
		$quiz = tutor_utils()->get_course_by_quiz($item->comment_post_ID);
		$title = get_the_title($quiz->ID);

		return "<a href='".admin_url("post.php?post={$quiz->ID}&action=edit")."'>{$title}</a>";
	}


	function column_total_questions($item) {
		$attempt_info = maybe_unserialize($item->quiz_attempt_info);

		echo tutor_utils()->avalue_dot('total_question', $attempt_info);
	}

	function column_earned_marks($item){
		$attempt_info = maybe_unserialize($item->quiz_attempt_info);

		$answers_mark = wp_list_pluck(tutor_utils()->avalue_dot('answers', $attempt_info), 'question_mark' );
		$total_marks = array_sum($answers_mark);

		$marks_earned = tutor_utils()->avalue_dot('marks_earned', $attempt_info);
		$earned_percentage = $marks_earned > 0 ? ( number_format(($marks_earned * 100) / $total_marks)) : 0;

		$pass_mark_percent = tutor_utils()->avalue_dot('pass_mark_percent', $attempt_info);

		$output = $marks_earned." out of {$total_marks} ({$earned_percentage}%)  ";
		if ($earned_percentage >= $pass_mark_percent){
			$output .= '<span class="result-pass">'.__('Pass', 'tutor').'</span>';
		}else{
			$output .= '<span class="result-fail">'.__('Fail', 'tutor').'</span>';
		}

		return $output;
	}

	function column_attempt_status($item){
		$status = ucwords(str_replace('quiz_', '', $item->attempt_status));
		return  "<span class='tutor-status-context {$item->attempt_status}'>{$status}</span>";
	}


	function get_columns(){
		$columns = array(
			'cb'                => '<input type="checkbox" />', //Render a checkbox instead of text
			'student'           => __('Students', 'tutor'),
			'quiz'              => __('Quiz', 'tutor'),
			'course'            => __('Course', 'tutor'),
			'total_questions'   => __('Total Questions', 'tutor'),
			'earned_marks'      => __('Earned Mark', 'tutor'),
			'attempt_status'      => __('Earned Mark', 'tutor'),
		);
		return $columns;
	}

	function get_sortable_columns() {
		$sortable_columns = array(
			//'display_name'     => array('title',false),     //true means it's already sorted
		);
		return $sortable_columns;
	}

	function get_bulk_actions() {
		$actions = array(
			'delete'    => 'Delete'
		);
		return $actions;
	}

	function process_bulk_action() {
		global $wpdb;

		//Detect when a bulk action is being triggered...
		if( 'delete' === $this->current_action() ) {
			if ( empty($_GET['attempt']) || ! is_array($_GET['attempt'])){
				return;
			}

			$attempt_ids = array_map('sanitize_text_field', $_GET['attempt']);
			$attempt_ids = implode( ',', array_map( 'absint', $attempt_ids ) );

			//Deleting attempt (comment), child attempt and attempt meta (comment meta)
			$wpdb->query( "DELETE FROM {$wpdb->comments} WHERE {$wpdb->comments}.comment_ID IN($attempt_ids)" );
			$wpdb->query( "DELETE FROM {$wpdb->commentmeta} WHERE {$wpdb->commentmeta}.comment_id IN($attempt_ids)" );
		}
	}

	function prepare_items() {
		$per_page = 20;

		$search_term = '';
		if (isset($_REQUEST['s'])){
			$search_term = sanitize_text_field($_REQUEST['s']);
		}

		$columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array($columns, $hidden, $sortable);
		$this->process_bulk_action();

		$current_page = $this->get_pagenum();

		$total_items = tutor_utils()->get_total_quiz_attempts($search_term);
		$this->items = tutor_utils()->get_quiz_attempts(($current_page-1)*$per_page, $per_page, $search_term);

		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'per_page'    => $per_page,
			'total_pages' => ceil($total_items/$per_page)
		) );
	}
}