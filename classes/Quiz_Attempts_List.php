<?php
namespace TUTOR;

if ( ! defined( 'ABSPATH' ) )
	exit;

if (! class_exists('Tutor_List_Table')){
	include_once tutor()->path.'classes/Tutor_List_Table.php';
}

class Quiz_Attempts_List extends \Tutor_List_Table {

	const QUIZ_ATTEMPT_PAGE = 'tutor_quiz_attempts';

	/**
	 * Trait for utilities
	 *
	 * @var $page_title
	 */

	use Backend_Page_Trait;
	/**
	 * Page Title
	 *
	 * @var $page_title
	 */
	public $page_title;

	/**
	 * Bulk Action
	 *
	 * @var $bulk_action
	 */
	public $bulk_action = true;

	/**
	 * Handle dependencies
	 */
	public function __construct() {
		$this->page_title = __( 'Quiz Attempts', 'tutor' );
		/**
		 * Handle bulk action
		 *
		 * @since v2.0.0
		 */
		add_action( 'wp_ajax_tutor_quiz_attempts_bulk_action', array( $this, 'quiz_attempts_bulk_action' ) );
	}

	/**
	 * Available tabs that will visible on the right side of page navbar
	 *
	 * @param string $user_id selected quiz_attempts id | optional.
	 * @param string $date selected date | optional.
	 * @param string $search search by user name or email | optional.
	 * @return array
	 * @since v2.0.0
	 */
	public function tabs_key_value( $user_id, $course_id, $date, $search ): array {
		$url       = get_pagenum_link();
		$pass  = self::get_quiz_attempt_number( 'approved', $user_id, $course_id, $date, $search );
		$fail = self::get_quiz_attempt_number( 'pending', $user_id, $course_id, $date, $search );
		$pending = self::get_quiz_attempt_number( 'blocked', $user_id, $course_id, $date, $search );
		$tabs      = array(
			array(
				'key'   => 'all',
				'title' => __( 'All', 'tutor-pro' ),
				'value' => $pass + $fail + $pending,
				'url'   => $url . '&data=all',
			),
			array(
				'key'   => 'pass',
				'title' => __( 'Pass', 'tutor-pro' ),
				'value' => $pass,
				'url'   => $url . '&data=pass',
			),
			array(
				'key'   => 'fail',
				'title' => __( 'Fail', 'tutor-pro' ),
				'value' => $fail,
				'url'   => $url . '&data=fail',
			),
			array(
				'key'   => 'pending',
				'title' => __( 'Pending', 'tutor-pro' ),
				'value' => $pending,
				'url'   => $url . '&data=pending',
			),
		);
		return $tabs;
	}

	/**
	 * Prepare bulk actions that will show on dropdown options
	 *
	 * @return array
	 * @since v2.0.0
	 */
	public function prpare_bulk_actions(): array {
		$actions = array(
			$this->bulk_action_default(),
			$this->bulk_action_delete(),
		);
		return $actions;
	}

	/**
	 * Count enrolled number by status & filters
	 * Count all enrollment | approved | cancelled
	 *
	 * @param string $status | required.
	 * @param string $user_id selected user id | optional.
	 * @param string $date selected date | optional.
	 * @param string $search_term search by user name or email | optional.
	 * @return int
	 * @since v2.0.0
	 */
	protected static function get_instructor_number( $status = '', $user_id = '', $course_id = '', $attempt_id = '', $date = '', $search_term = ''  ): int {
		global $wpdb;
		$status      = sanitize_text_field( $status );
		$course_id   = sanitize_text_field( $course_id );
		$user_id   = sanitize_text_field( $user_id );
		$attempt_id   = sanitize_text_field( $attempt_id );
		$date        = sanitize_text_field( $date );
		$search_term = sanitize_text_field( $search_term );

		$search_term = '%' . $wpdb->esc_like( $search_term ) . '%';

		// add user id in where clause.
		$user_query = '';
		if ( '' !== $user_id ) {
			$user_query = "AND user.ID = $user_id";
		}

		// add quiz id in where clause.
		$quiz_query = '';
		if ( '' !== $quiz_id ) {
			$quiz_query = "AND quiz.ID = $user_id";
		}

		$count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(attempt_id)
				FROM 	{$wpdb->prefix}tutor_quiz_attempts quiz_attempts
					   INNER JOIN {$wpdb->posts} quiz
							   ON quiz_attempts.quiz_id = quiz.ID
					   INNER JOIN {$wpdb->users}
							   ON quiz_attempts.user_id = {$wpdb->users}.ID
			   WHERE 	attempt_status != %s
					   AND ( user_email LIKE %s OR display_name LIKE %s OR post_title LIKE %s )
			   ",
			   'attempt_started',
				$status,
				$search_term,
				$search_term,
				$search_term,
				$search_term
			)
		);
		return $count ? $count : 0;
	}

	/**
	 * Handle bulk action for instructor delete
	 *
	 * @return string JSON response.
	 * @since v2.0.0
	 */
	public function quiz_attempts_bulk_action() {
		// check nonce.
		tutor_utils()->checking_nonce();
		$status   = isset( $_POST['bulk-action'] ) ? sanitize_text_field( $_POST['bulk-action'] ) : '';
		$bulk_ids = isset( $_POST['bulk-ids'] ) ? sanitize_text_field( $_POST['bulk-ids'] ) : array();
		$update   = self::update_quiz_attempts( $status, $bulk_ids );
		return true === $update ? wp_send_json_success() : wp_send_json_error();
		exit;
	}

	/**
	 * Execute bulk action for enrollments ex: complete | cancel
	 *
	 * @param string $status hold status for updating.
	 * @param string $users_ids ids that need to update.
	 * @return bool
	 * @since v2.0.0
	 */
	public static function update_quiz_attempts( $status, $user_ids ): bool {
		global $wpdb;
		$quiz_attempts_table = $wpdb->tutor_quiz_attempts;
		$update     = $wpdb->query(
			$wpdb->prepare(
				" UPDATE {$quiz_attempts_table}
				SET 	attempt_status = %s 
				WHERE ID IN ($user_ids)
			",
				$status
			)
		);
		return false === $update ? false : true;
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
		/*
		$actions = array();

		$actions['answer'] = sprintf('<a href="?page=%s&sub_page=%s&attempt_id=%s">'.__('Review', 'tutor').'</a>',$_REQUEST['page'],'view_attempt',$item->attempt_id);
		//$actions['delete'] = sprintf('<a href="?page=%s&action=%s&attempt_id=%s">Delete</a>',$_REQUEST['page'],'delete',$item->attempt_id);

		$quiz_title = "<p><strong>{$item->display_name}</strong></p>";
		$quiz_title .= "<p>{$item->user_email}</p>";
		//@since 1.9.5 instead of showing time ago showing original date time 
		if ($item->attempt_ended_at){
			$ended_ago_time = human_time_diff(strtotime($item->attempt_ended_at), tutor_time()).__(' ago', 'tutor');
			$attempt_started_at = date( get_option( 'date_format'), strtotime($item->attempt_started_at) );
			$quiz_title .= "<span>{$attempt_started_at}</span>";
		}

		//Return the title contents
		return sprintf('%1$s <span style="color:silver">(id:%2$s)</span>%3$s',
			$quiz_title,
			$item->attempt_id,
			$this->row_actions($actions)
		);*/
		return $item->attempt_ended_at;
		
	}

	function column_student_info($item){
		return $item->display_name;
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

	function column_total_correct_answer($item) {
		echo $item->total_correct_answer;
	}

	function column_earned_marks($item){

	    /*if ($item->attempt_status === 'review_required'){
            $output = '<span class="result-review-required">' . __('Under Review', 'tutor') . '</span>';
        }else {

            $pass_mark_percent = tutor_utils()->get_quiz_option($item->quiz_id, 'passing_grade', 0);
            $earned_percentage = $item->earned_marks > 0 ? (number_format(($item->earned_marks * 100) / $item->total_marks)) : 0;

            $output = $item->earned_marks .__( ' out of ', 'tutor' ). " {$item->total_marks} <br />";
            $output .= "({$earned_percentage}%) ".__( ' pass ', 'tutor' )." ({$pass_mark_percent}%) <br />";

            if ($earned_percentage >= $pass_mark_percent) {
                $output .= '<span class="result-pass">' . __('Pass', 'tutor') . '</span>';
            } else {
                $output .= '<span class="result-fail">' . __('Fail', 'tutor') . '</span>';
            }
        }
		return $output;*/

		return $item->total_marks;
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
			//'attempt_status'      => __('Attempt Status', 'tutor'),
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
			$attempt_ids = array_map( 'absint', $attempt_ids );

			tutor_utils()->delete_quiz_attempt( $attempt_ids );
		}
	}

	function prepare_items( $search_filter = '', $course_filter = '', $date_filter = '', $order_filter = '' ) {
		global $wpdb;

		$per_page = 20;

		$columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array($columns, $hidden, $sortable);
		$this->process_bulk_action();

		$current_page = $this->get_pagenum();

		$total_items = 0;
		$this->items = array();

		if ( current_user_can( 'administrator' ) ) {
			
			$this->items = tutor_utils()->get_quiz_attempts( ( $current_page - 1 ) * $per_page, $per_page, $search_filter, $course_filter, $date_filter, $order_filter );

			$total_items = tutor_utils()->get_total_quiz_attempts(); 

		} elseif ( current_user_can( 'tutor_instructor' ) ){
			/**
			 * Instructors course specific quiz attempts
			 */
			$user_id = get_current_user_id();
			$get_assigned_courses_ids = $wpdb->get_col($wpdb->prepare("SELECT meta_value from {$wpdb->usermeta} WHERE meta_key = '_tutor_instructor_course_id' AND user_id = %d", $user_id));

			$custom_author_query = "AND {$wpdb->posts}.post_author = {$user_id}";
			if (is_array($get_assigned_courses_ids) && count($get_assigned_courses_ids)){
				$in_query_pre = implode(',', $get_assigned_courses_ids);
				$custom_author_query = "  AND ( {$wpdb->posts}.post_author = {$user_id} OR {$wpdb->posts}.ID IN({$in_query_pre}) ) ";
			}
			$course_post_type = tutor()->course_post_type;
			$get_course_ids = $wpdb->get_col("SELECT ID from {$wpdb->posts} where post_type = '{$course_post_type}' $custom_author_query ; ");

			if (is_array($get_course_ids) && count($get_course_ids)){

				$this->items = tutor_utils()->get_quiz_attempts_by_course_ids(( $current_page - 1 ) * $per_page, $per_page, $get_course_ids, $search_filter, $course_filter, $date_filter, $order_filter );
				
				$total_items = tutor_utils()->get_total_quiz_attempts_by_course_ids($get_course_ids);
			}

		}

		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'per_page'    => $per_page,
			'total_pages' => ceil($total_items/$per_page)
		) );
	}
}