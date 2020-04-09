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

		$actions['answer'] = sprintf('<a href="?page=%s&sub_page=%s&attempt_id=%s">'.__('Review', 'tutor').'</a>',$_REQUEST['page'],'view_attempt',$item->attempt_id);
		//$actions['delete'] = sprintf('<a href="?page=%s&action=%s&attempt_id=%s">Delete</a>',$_REQUEST['page'],'delete',$item->attempt_id);

		$quiz_title = "<p><strong>{$item->display_name}</strong></p>";
		$quiz_title .= "<p>{$item->user_email}</p>";

		if ($item->attempt_ended_at){
			$ended_ago_time = human_time_diff(strtotime($item->attempt_ended_at), tutor_time()).__(' ago', 'tutor');
			$quiz_title .= "<span>{$ended_ago_time}</span>";
		}

		//Return the title contents
		return sprintf('%1$s <span style="color:silver">(id:%2$s)</span>%3$s',
			$quiz_title,
			$item->attempt_id,
			$this->row_actions($actions)
		);
	}

	function column_quiz($item){
		return $item->post_title;
	}

	function column_cb($item){
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			/*$1%s*/ $this->_args['singular'],  //Let's simply repurpose the table's singular label ("instructor")
			/*$2%s*/ $item->attempt_id                //The value of the checkbox should be the record's id
		);
	}

	function column_course($item) {
		$quiz = tutor_utils()->get_course_by_quiz($item->quiz_id);

		if ($quiz) {
			$title = get_the_title( $quiz->ID );
			return "<a href='" . admin_url( "post.php?post={$quiz->ID}&action=edit" ) . "'>{$title}</a>";
		}
	}

	function column_total_questions($item) {
		echo $item->total_questions;
	}

	function column_earned_marks($item){
		$pass_mark_percent = tutor_utils()->get_quiz_option($item->quiz_id, 'passing_grade', 0);
		$earned_percentage = $item->earned_marks > 0 ? ( number_format(($item->earned_marks * 100) / $item->total_marks)) : 0;

		$output = $item->earned_marks." out of {$item->total_marks} <br />";
		$output .= "({$earned_percentage}%) pass ({$pass_mark_percent}%) <br />";

		if ($earned_percentage >= $pass_mark_percent){
			$output .= '<span class="result-pass">'.__('Pass', 'tutor').'</span>';
		}else{
			$output .= '<span class="result-fail">'.__('Fail', 'tutor').'</span>';
		}

		return $output;
	}

	function column_attempt_status($item){
		$status = ucwords(str_replace('quiz_', '', $item->attempt_status));

		return "<span class='attempt-status-{$item->attempt_status}'>{$status}</span>";
	}

	function get_columns(){
		$columns = array(
			'cb'                => '<input type="checkbox" />', //Render a checkbox instead of text
			'student'           => __('Students', 'tutor'),
			'quiz'              => __('Quiz', 'tutor'),
			'course'            => __('Course', 'tutor'),
			'total_questions'   => __('Total Questions', 'tutor'),
			'earned_marks'      => __('Earned Points', 'tutor'),
			'attempt_status'      => __('Attempt Status', 'tutor'),
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
			$wpdb->query( "DELETE FROM {$wpdb->prefix}tutor_quiz_attempts WHERE attempt_id IN($attempt_ids)" );
			$wpdb->query( "DELETE FROM {$wpdb->prefix}tutor_quiz_attempt_answers WHERE quiz_attempt_id IN($attempt_ids)" );
		}
	}

	function prepare_items() {
		global $wpdb;

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

		$total_items = 0;
		$this->items = array();

		if (current_user_can('administrator')) {
			$total_items = tutor_utils()->get_total_quiz_attempts( $search_term );
			$this->items = tutor_utils()->get_quiz_attempts( ( $current_page - 1 ) * $per_page, $per_page, $search_term );
		}elseif (current_user_can('tutor_instructor')){
			/**
			 * Instructors course specific quiz attempts
			 */
			$user_id = get_current_user_id();
			$get_assigned_courses_ids = $wpdb->get_col("SELECT meta_value from {$wpdb->usermeta} WHERE meta_key = '_tutor_instructor_course_id' AND user_id = {$user_id}  ");

			$custom_author_query = "AND {$wpdb->posts}.post_author = {$user_id}";
			if (is_array($get_assigned_courses_ids) && count($get_assigned_courses_ids)){
				$in_query_pre = implode($get_assigned_courses_ids, ',');
				$custom_author_query = "  AND ( {$wpdb->posts}.post_author = {$user_id} OR {$wpdb->posts}.ID IN({$in_query_pre}) ) ";
			}
			$course_post_type = tutor()->course_post_type;
			$get_course_ids = $wpdb->get_col("SELECT ID from {$wpdb->posts} where post_type = '{$course_post_type}' $custom_author_query ; ");

			if (is_array($get_course_ids) && count($get_course_ids)){
				$total_items = tutor_utils()->get_total_quiz_attempts_by_course_ids($get_course_ids, $search_term );
				$this->items = tutor_utils()->get_quiz_attempts_by_course_ids(( $current_page - 1 ) * $per_page, $per_page, $get_course_ids, $search_term );
			}

		}

		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'per_page'    => $per_page,
			'total_pages' => ceil($total_items/$per_page)
		) );
	}
}