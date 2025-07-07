<?php
/**
 * Quiz attempt list management
 *
 * @package Tutor\QuestionAnswer
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.0.0
 */

namespace TUTOR;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Tutor\Cache\QuizAttempts;
use Tutor\Models\QuizModel;

/**
 * Quiz attempt class
 *
 * @since 1.0.0
 */
class Quiz_Attempts_List {

	const QUIZ_ATTEMPT_PAGE = 'tutor_quiz_attempts';

	/**
	 * Trait for utilities
	 *
	 * @var $page_title
	 */

	use Backend_Page_Trait;

	/**
	 * Bulk Action
	 *
	 * @var $bulk_action
	 */
	public $bulk_action = true;

	/**
	 * Handle dependencies
	 *
	 * @since 1.0.0
	 *
	 * @param boolean $register_hook should register hook or not.
	 */
	public function __construct( $register_hook = true ) {
		if ( ! $register_hook ) {
			return;
		}

		/**
		 * Handle bulk action
		 *
		 * @since 2.0.0
		 */
		add_action( 'wp_ajax_tutor_quiz_attempts_bulk_action', array( $this, 'quiz_attempts_bulk_action' ) );
		add_action( 'wp_ajax_tutor_quiz_attempts_count', array( $this, 'get_quiz_attempts_stat' ) );

		/**
		 * Delete quiz attempt cache
		 *
		 * @since 2.1.0
		 */
		add_action( 'tutor_quiz/attempt_ended', array( new QuizAttempts(), 'delete_cache' ) );
		add_action( 'tutor_quiz/attempt_deleted', array( new QuizAttempts(), 'delete_cache' ) );
		add_action( 'tutor_quiz/answer/review/after', array( new QuizAttempts(), 'delete_cache' ) );
	}

	/**
	 * Page title fallback
	 *
	 * @since 3.5.0
	 *
	 * @param string $name Property name.
	 *
	 * @return string
	 */
	public function __get( $name ) {
		if ( 'page_title' === $name ) {
			return esc_html__( 'Quiz Attempts', 'tutor' );
		}
	}

	/**
	 * Get the attempts stat from specific instructor context
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	public function get_quiz_attempts_stat() {
		global $wpdb;

		if ( wp_doing_ajax() ) {
			tutor_utils()->checking_nonce();
		}

		/**
		 * Parse `passing_grade` value from `attempt_info` serialized data.
		 *
		 * Data Format     : "passing_grade";s:2:"80" or "passing_grade";s:3:"100"
		 * Expected Output : 80 or 100
		 *
		 * TODO: Optimize SQL query.
		 */
		$pass_mark = "((( SUBSTRING_INDEX(
			SUBSTRING_INDEX(
			  attempt_info,
			  CONCAT(
				  '\"passing_grade\";s:',
				  SUBSTRING_INDEX(SUBSTRING_INDEX(attempt_info, '\"passing_grade\";s:', -1), ':\"', 1),
				  ':\"'
			  ),
			  -1
			), 
			'\"', 
			1
  		))/100) * quiz_attempts.total_marks)";

		$pending_count  = "(SELECT COUNT(DISTINCT attempt_answer_id) FROM {$wpdb->prefix}tutor_quiz_attempt_answers WHERE quiz_attempt_id=quiz_attempts.attempt_id AND is_correct IS NULL)";
		$pass_clause    = " AND quiz_attempts.earned_marks >= {$pass_mark}  ";
		$fail_clause    = " AND quiz_attempts.earned_marks < {$pass_mark} AND {$pending_count} < 1 ";
		$pending_clause = " AND {$pending_count} > 0 ";

		$user_id     = get_current_user_id();
		$user_clause = '';
		if ( ! current_user_can( 'administrator' ) ) {
			$user_clause = "AND quiz.post_author = {$user_id}";
		}

		$count_obj = (object) array(
			'pass'    => 0,
			'fail'    => 0,
			'pending' => 0,
		);

		$is_ajax_action = 'tutor_quiz_attempts_count' === Input::post( 'action' );
		$course_id      = Input::post( 'course_id', '' );
		$date           = Input::post( 'date', '' );
		$search         = Input::post( 'search', '' );

		$course_filter   = '' !== $course_id ? $wpdb->prepare( ' AND quiz_attempts.course_id = %d', $course_id ) : '';
		$date_filter     = '' !== $date ? $wpdb->prepare( ' AND DATE(quiz_attempts.attempt_started_at) = %s ', $date ) : '';
		$search_term_raw = $search;
		$search_filter   = '%' . $wpdb->esc_like( $search ) . '%';

		if ( $is_ajax_action ) {
			$current_params = compact( 'course_id', 'date', 'search' );
			$attempt_cache  = new QuizAttempts( $current_params );

			$cached_attempts = $attempt_cache->get_cache();
			if ( $attempt_cache->has_cache() && $attempt_cache->is_same_query() && isset( $cached_attempts['result'] ) ) {
				$count_obj = $cached_attempts['result'];
			} else {
				$select_stmt = "SELECT COUNT( DISTINCT attempt_id)
								FROM {$wpdb->prefix}tutor_quiz_attempts quiz_attempts
								INNER JOIN {$wpdb->posts} quiz ON quiz_attempts.quiz_id = quiz.ID
								INNER JOIN {$wpdb->users} AS users ON quiz_attempts.user_id = users.ID
								INNER JOIN {$wpdb->posts} AS course ON course.ID = quiz_attempts.course_id";

				$count_obj->pass = (int) $wpdb->get_var(
					$wpdb->prepare(
						"{$select_stmt}		
						WHERE attempt_status != %s
						AND (
							users.user_email = %s
							OR users.display_name LIKE %s
							OR quiz.post_title LIKE %s
							OR course.post_title LIKE %s
						)
							{$pass_clause}
							{$user_clause}
							{$course_filter}
							{$date_filter}
							",
						'attempt_started',
						$search_term_raw,
						$search_filter,
						$search_filter,
						$search_filter
					)
				);

				$count_obj->fail = (int) $wpdb->get_var(
					$wpdb->prepare(
						"{$select_stmt}		
						WHERE attempt_status != %s
						AND (
							users.user_email = %s
							OR users.display_name LIKE %s
							OR quiz.post_title LIKE %s
							OR course.post_title LIKE %s
						)
							{$fail_clause}
							{$user_clause}
							{$course_filter}
							{$date_filter}
							",
						'attempt_started',
						$search_term_raw,
						$search_filter,
						$search_filter,
						$search_filter
					)
				);

				$count_obj->pending = (int) $wpdb->get_var(
					$wpdb->prepare(
						"{$select_stmt}		
						WHERE attempt_status != %s
						AND (
							users.user_email = %s
							OR users.display_name LIKE %s
							OR quiz.post_title LIKE %s
							OR course.post_title LIKE %s
						)
							{$pending_clause}
							{$user_clause}
							{$course_filter}
							{$date_filter}
							",
						'attempt_started',
						$search_term_raw,
						$search_filter,
						$search_filter,
						$search_filter
					)
				);

				$attempt_cache->data = $count_obj;
				$attempt_cache->set_cache();
			}
		}

		$all      = $count_obj->pass + $count_obj->fail + $count_obj->pending;
		$pass     = $count_obj->pass;
		$fail     = $count_obj->fail;
		$pending  = $count_obj->pending;
		$response = compact( 'all', 'pass', 'fail', 'pending' );

		return $is_ajax_action ? wp_send_json_success( $response ) : $response;
	}

	/**
	 * Available tabs that will visible on the right side of page navbar
	 *
	 * @since 2.0.0
	 *
	 * @param string $user_id selected quiz_attempts id | optional.
	 * @param int    $course_id selected quiz_attempts id | optional.
	 * @param string $date selected date | optional.
	 * @param string $search search by user name or email | optional.
	 *
	 * @return array
	 */
	public function tabs_key_value( $user_id, $course_id, $date, $search ): array {
		$url   = apply_filters( 'tutor_data_tab_base_url', get_pagenum_link() );
		$stats = $this->get_quiz_attempts_stat();

		$tabs = array(
			array(
				'key'   => '',
				'title' => __( 'All', 'tutor' ),
				'value' => $stats['all'],
				'url'   => $url . '&data=all',
			),
			array(
				'key'   => 'pass',
				'title' => __( 'Pass', 'tutor' ),
				'value' => $stats['pass'],
				'url'   => $url . '&data=pass',
			),
			array(
				'key'   => 'fail',
				'title' => __( 'Fail', 'tutor' ),
				'value' => $stats['fail'],
				'url'   => $url . '&data=fail',
			),
			array(
				'key'   => 'pending',
				'title' => __( 'Pending', 'tutor' ),
				'value' => $stats['pending'],
				'url'   => $url . '&data=pending',
			),
		);

		return $tabs;
	}

	/**
	 * Prepare bulk actions that will show on dropdown options
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	public function prpare_bulk_actions(): array {
		$actions = array(
			$this->bulk_action_default(),
			$this->bulk_action_delete(),
		);
		return $actions;
	}


	/**
	 * Handle bulk action for instructor delete
	 *
	 * @since 2.0.0
	 *
	 * @return void send wp_json response
	 */
	public function quiz_attempts_bulk_action() {
		// check nonce.
		tutor_utils()->checking_nonce();

		// Check if user is privileged.
		if ( ! User::has_any_role( array( User::ADMIN, User::INSTRUCTOR ) ) ) {
			wp_send_json_error( tutor_utils()->error_message() );
		}

		$bulk_action = Input::post( 'bulk-action', '' );
		$bulk_ids    = Input::post( 'bulk-ids', '' );
		$bulk_ids    = explode( ',', $bulk_ids );
		$bulk_ids    = array_map(
			function( $id ) {
				return (int) trim( $id );
			},
			$bulk_ids
		);

		// prevent instructor to remove quiz attempt from admin.
		$bulk_ids = array_filter(
			$bulk_ids,
			function ( $attempt_id ) {
				$attempt   = tutor_utils()->get_attempt( $attempt_id );
				$user_id   = get_current_user_id();
				$course_id = $attempt && is_object( $attempt ) ? $attempt->course_id : 0;
				return $course_id && tutor_utils()->can_user_edit_course( $user_id, $course_id );
			}
		);

		switch ( $bulk_action ) {
			case 'delete':
				QuizModel::delete_quiz_attempt( $bulk_ids );
				break;
		}

		wp_send_json_success();
	}

	/**
	 * Get bulk action as an array
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		$actions = array(
			'delete' => 'Delete',
		);
		return $actions;
	}
}
