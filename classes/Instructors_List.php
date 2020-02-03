<?php
namespace TUTOR;

if ( ! defined( 'ABSPATH' ) )
	exit;

if (! class_exists('Tutor_List_Table')){
	include_once tutor()->path.'classes/Tutor_List_Table.php';
}

class Instructors_List extends \Tutor_List_Table {
	function __construct(){
		global $status, $page;

		//Set parent defaults
		parent::__construct( array(
			'singular'  => 'instructor',     //singular name of the listed records
			'plural'    => 'instructors',    //plural name of the listed records
			'ajax'      => false        //does this table support ajax?
		) );

		//$this->process_bulk_action();
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

	function column_total_course($item){
		global $wpdb;
		$course_post_type = tutor()->course_post_type;

		$total_course = (int) $wpdb->get_var("select count(ID) from {$wpdb->posts} WHERE post_author={$item->ID} AND post_type='{$course_post_type}' ");

		echo $total_course;
	}

	/**
	 * @param $item
	 *
	 * Completed Course by User
	 */
	function column_status($item){
		$status = tutor_utils()->instructor_status($item->ID, false);
		$status_name = tutor_utils()->instructor_status($item->ID);
		echo "<span class='tutor-status-context tutor-status-{$status}-context'>{$status_name}</span>";
	}

	function column_display_name($item){
		//Build row actions
		$actions = array(
			//'edit'      => sprintf('<a href="?page=%s&action=%s&instructor=%s">Edit</a>',$_REQUEST['page'],'edit',$item->ID),
			//'delete'    => sprintf('<a href="?page=%s&action=%s&instructor=%s">Delete</a>',$_REQUEST['page'],'delete',$item->ID),
		);

		$status = tutor_utils()->instructor_status($item->ID, false);

		switch ($status){
			case 'pending':
				$actions['approved'] = sprintf('<a class="instructor-action" data-action="approve" data-instructor-id="'.$item->ID.'" href="?page=%s&action=%s&instructor=%s">Approve</a>', $_REQUEST['page'],'approve',$item->ID);
				break;
			case 'approved':
				$actions['blocked'] = sprintf('<a class="instructor-action" data-action="blocked" data-instructor-id="'.$item->ID.'" href="?page=%s&action=%s&instructor=%s">Block</a>', $_REQUEST['page'],'blocked',$item->ID);
				break;
			case 'blocked':
				$actions['approved'] = sprintf('<a class="instructor-action" data-action="approve" data-instructor-id="'.$item->ID.'" href="?page=%s&action=%s&instructor=%s">Un Block</a>',$_REQUEST['page'],'approve',$item->ID);
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
			/*$1%s*/ $this->_args['singular'],  //Let's simply repurpose the table's singular label ("instructor")
			/*$2%s*/ $item->ID                //The value of the checkbox should be the record's id
		);
	}

	function get_columns(){
		$columns = array(
			//'cb'                => '<input type="checkbox" />', //Render a checkbox instead of text
			'display_name'      => __('Name', 'tutor'),
			'user_email'        => __('E-Mail', 'tutor'),
			'total_course'      => __('Total Course', 'tutor'),
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
			$instructor_id = (int) sanitize_text_field($_GET['instructor']);

			do_action('tutor_before_approved_instructor', $instructor_id);

			update_user_meta($instructor_id, '_tutor_instructor_status', 'approved');
			update_user_meta($instructor_id, '_tutor_instructor_approved', tutor_time());

			$instructor = new \WP_User($instructor_id);
			$instructor->add_role(tutor()->instructor_role);

			//TODO: send E-Mail to this user about instructor approval, should via hook
			do_action('tutor_after_approved_instructor', $instructor_id);
		}

		if( 'blocked' === $this->current_action() ) {
			$instructor_id = (int) sanitize_text_field($_GET['instructor']);

			do_action('tutor_before_blocked_instructor', $instructor_id);
			update_user_meta($instructor_id, '_tutor_instructor_status', 'blocked');

			$instructor = new \WP_User($instructor_id);
			$instructor->remove_role(tutor()->instructor_role);
			do_action('tutor_after_blocked_instructor', $instructor_id);

			//TODO: send E-Mail to this user about instructor blocked, should via hook
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

		$current_page = $this->get_pagenum();

		$total_items = tutor_utils()->get_total_instructors($search_term);
		$this->items = tutor_utils()->get_instructors(($current_page-1)*$per_page, $per_page, $search_term);

		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'per_page'    => $per_page,
			'total_pages' => ceil($total_items/$per_page)
		) );
	}
}