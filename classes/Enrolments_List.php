<?php
namespace TUTOR;

if ( ! defined( 'ABSPATH' ) )
	exit;

if (! class_exists('Tutor_List_Table')){
	include_once tutor()->path.'classes/Tutor_List_Table.php';
}

class Enrolments_List extends \Tutor_List_Table {

	function __construct(){
		global $status, $page;

		//Set parent defaults
		parent::__construct( array(
			'singular'  => 'enrolment',     //singular name of the listed records
			'plural'    => 'enrolments',    //plural name of the listed records
			'ajax'      => false        //does this table support ajax?
		) );
	}

	function column_default($item, $column_name){
		switch($column_name){
			case 'user_email':
			case 'display_name':
				return $item->$column_name;
			default:
				return print_r($item,true); //Show the whole array for troubleshooting purposes
		}
	}

	function column_cb($item){
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			/*$1%s*/ $this->_args['singular'],  //Let's simply repurpose the table's singular label ("enrolment")
			/*$2%s*/ $item->enrol_id                //The value of the checkbox should be the record's id
		);
	}

	function column_student($item){
		$student_url = tutils()->profile_url($item->student_id);
		$student = "<a href='{$student_url}' target='_blank'>{$item->display_name}</a> <span style='color:silver'>(enrol_id:{$item->enrol_id})</span>  <br /> <small>{$item->user_email}</small>";

		$actions = array();
		if ($item->status === 'completed'){
			$actions['cancel'] = sprintf('<a href="?page=%s&action=%s&enrol_id=%s">Cancel</a>',$_REQUEST['page'],'cancel',$item->enrol_id);
		}else{
			$actions['complete'] = sprintf('<a href="?page=%s&action=%s&enrol_id=%s">Complete</a>',$_REQUEST['page'],'complete',$item->enrol_id);
		}
		$actions['delete'] = sprintf('<a href="?page=%s&action=%s&enrol_id=%s">Delete</a>',$_REQUEST['page'],'delete',$item->enrol_id);

		$student .= $this->row_actions($actions);


		return $student;
	}

	function column_course($item){
		$student = "<strong><a href='".get_permalink($item->course_id)."' target='_blank'>{$item->course_title}</a> </strong> <br />";
		$student .= sprintf(__('Date : %s', 'tutor'), date(get_option('date_format').' '.get_option('time_format')), strtotime($item->enrol_date));

		return $student;
	}

	function column_order($item){
		$order_id = get_post_meta($item->enrol_id, '_tutor_enrolled_by_order_id', true);
		if ($order_id){
			$order_edit_url = admin_url("post.php?post={$order_id}&action=edit");
			$order = "<a href='{$order_edit_url}' target='_blank'> #{$order_id} </a> ";
			return $order;
		}
		return '';
	}

	function column_status($item){
		return "<span class='tutor-status-context tutor-status-{$item->status}'>{$item->status}</span>";
	}

	function get_columns(){
		$columns = array(
			//'cb'                => '<input type="checkbox" />', //Render a checkbox instead of text
			'student'      => __('Student', 'tutor'),
			'course'      => __('Course', 'tutor'),
			'order'      => __('Order', 'tutor'),
			'status'      => __('Status', 'tutor'),
		);
		return $columns;
	}

	function get_bulk_actions() {
		$actions = array(
			//'delete'    => 'Delete'
		);
		return $actions;
	}

	function process_bulk_action() {
		global $wpdb;

		$enrol_id = tutils()->array_get('enrol_id', $_REQUEST);
		//Detect when a bulk action is being triggered...
		if( 'delete' === $this->current_action() ) {
			$wpdb->delete($wpdb->posts, array('ID' => $enrol_id, 'post_type' => 'tutor_enrolled' ));

			delete_post_meta($enrol_id, '_tutor_enrolled_by_order_id');
			delete_post_meta($enrol_id, '_tutor_enrolled_by_product_id');
		}

		if( 'complete' === $this->current_action() ) {
			$wpdb->update($wpdb->posts, array('post_status' => 'completed'), array('ID' => $enrol_id));
		}
		if( 'cancel' === $this->current_action() ) {
			$wpdb->update($wpdb->posts, array('post_status' => 'cancel'), array('ID' => $enrol_id));
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

		$this->_column_headers = array($columns, $hidden);
		$this->process_bulk_action();

		$current_page = $this->get_pagenum();

		$total_items = tutor_utils()->get_total_enrolments($search_term);
		$this->items = tutor_utils()->get_enrolments(($current_page-1)*$per_page, $per_page, $search_term);

		$this->set_pagination_args( array(
			'total_items' => $total_items,                  //WE have to calculate the total number of items
			'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
			'total_pages' => ceil($total_items/$per_page)   //WE have to calculate the total number of pages
		) );
	}
}