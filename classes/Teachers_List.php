<?php
namespace TUTOR;

if ( ! defined( 'ABSPATH' ) )
	exit;

if (! class_exists('Tutor_List_Table')){
	include_once tutor()->path.'classes/Tutor_List_Table.php';
}

class Teachers_List extends \Tutor_List_Table {

	function __construct(){
		global $status, $page;

		//Set parent defaults
		parent::__construct( array(
			'singular'  => 'teacher',     //singular name of the listed records
			'plural'    => 'teachers',    //plural name of the listed records
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

	/**
	 * @param $item
	 *
	 * Completed Course by User
	 */
	function column_status($item){
		$status = tutor_utils()->teacher_status($item->ID, false);
		$status_name = tutor_utils()->teacher_status($item->ID);
		echo "<span class='tutor-status-context tutor-status-{$status}-context'>{$status_name}</span>";
	}

	function column_display_name($item){
		//Build row actions
		$actions = array(
			//'edit'      => sprintf('<a href="?page=%s&action=%s&teacher=%s">Edit</a>',$_REQUEST['page'],'edit',$item->ID),
			//'delete'    => sprintf('<a href="?page=%s&action=%s&teacher=%s">Delete</a>',$_REQUEST['page'],'delete',$item->ID),
		);

		$status = tutor_utils()->teacher_status($item->ID, false);

		switch ($status){
			case 'pending':
				$actions['approved'] = sprintf('<a href="?page=%s&action=%s&teacher=%s">Approve</a>',$_REQUEST['page'],'approve',$item->ID);
				break;
			case 'approved':
				$actions['blocked'] = sprintf('<a href="?page=%s&action=%s&teacher=%s">Blocked</a>',$_REQUEST['page'],'blocked',$item->ID);
				break;
			case 'blocked':
				$actions['approved'] = sprintf('<a href="?page=%s&action=%s&teacher=%s">Un Block</a>',$_REQUEST['page'],'approve',$item->ID);
				break;
		}
		//Return the title contents
		return sprintf('%1$s <span style="color:silver">(id:%2$s)</span>%3$s',
			$item->display_name,
			$item->ID,
			$this->row_actions($actions)
		);
	}

	function column_cb($item){
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			/*$1%s*/ $this->_args['singular'],  //Let's simply repurpose the table's singular label ("teacher")
			/*$2%s*/ $item->ID                //The value of the checkbox should be the record's id
		);
	}

	function get_columns(){
		$columns = array(
			'cb'                => '<input type="checkbox" />', //Render a checkbox instead of text
			'display_name'      => __('Name', 'tutor'),
			'user_email'        => __('E-Mail', 'tutor'),
			'status'            => __('Status', 'tutor'),
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
			//'delete'    => 'Delete'
		);
		return $actions;
	}

	function process_bulk_action() {
		if( 'approve' === $this->current_action() ) {
			$teacher_id = (int) sanitize_text_field($_GET['teacher']);

			do_action('tutor_before_approved_teacher', $teacher_id);

			update_user_meta($teacher_id, '_tutor_teacher_status', 'approved');
			update_user_meta($teacher_id, '_tutor_teacher_approved', time());

			$teacher = new \WP_User($teacher_id);
			$teacher->set_role(tutor()->teacher_role);

			//TODO: send E-Mail to this user about teacher approval, should via hook
			do_action('tutor_after_approved_teacher', $teacher_id);

			wp_redirect(wp_get_referer());
		}

		if( 'blocked' === $this->current_action() ) {
			$teacher_id = (int) sanitize_text_field($_GET['teacher']);

			do_action('tutor_before_blocked_teacher', $teacher_id);
			update_user_meta($teacher_id, '_tutor_teacher_status', 'blocked');

			$teacher = new \WP_User($teacher_id);
			$teacher->remove_role(tutor()->teacher_role);
			do_action('tutor_after_blocked_teacher', $teacher_id);

			//TODO: send E-Mail to this user about teacher blocked, should via hook
			wp_redirect(wp_get_referer());
		}

		//Detect when a bulk action is being triggered...
		if( 'delete'===$this->current_action() ) {
			wp_die('Items deleted (or they would be if we had items to delete)!');
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

		$total_items = tutor_utils()->get_total_teachers($search_term);
		$this->items = tutor_utils()->get_teachers(($current_page-1)*$per_page, $per_page, $search_term);

		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'per_page'    => $per_page,
			'total_pages' => ceil($total_items/$per_page)
		) );
	}
}