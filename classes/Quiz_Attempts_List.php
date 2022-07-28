<?php
namespace TUTOR;

use Tutor\Cache\QuizAttempts;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Quiz_Attempts_List {

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
	public function __construct($register_hook=true) {
		$this->page_title = __( 'Quiz Attempts', 'tutor' );
		if(!$register_hook) {
			return;
		}
		
		/**
		 * Handle bulk action
		 *
		 * @since v2.0.0
		 */
		add_action( 'wp_ajax_tutor_quiz_attempts_bulk_action', array( $this, 'quiz_attempts_bulk_action' ) );
		add_action( 'wp_ajax_tutor_quiz_attempts_count', array( $this, 'get_quiz_attempts_stat' ) );
	}

	/**
	 * @return array
	 *
	 *
	 * Get the attempts stat from specific instructor context
	 *
	 * @since 2.0.0
	 */
	public function get_quiz_attempts_stat() {
		global $wpdb;
		// Set query based on action tab.
		$pass_mark      = "(((SUBSTRING_INDEX(SUBSTRING_INDEX(quiz_attempts.attempt_info, '\"passing_grade\";s:2:\"', -1), '\"', 1))/100)*quiz_attempts.total_marks)";
		$pending_count  = "(SELECT COUNT(DISTINCT attempt_answer_id) FROM {$wpdb->prefix}tutor_quiz_attempt_answers WHERE quiz_attempt_id=quiz_attempts.attempt_id AND is_correct IS NULL)";

		$pass_clause = " AND quiz_attempts.earned_marks >= {$pass_mark}  ";

		$fail_clause = " AND quiz_attempts.earned_marks < {$pass_mark} ";

		$pending_clause = " AND {$pending_count} > 0 ";

		$count 			= array();
		$is_ajax_action = isset( $_POST['action'] ) && 'tutor_quiz_attempts_count' === $_POST['action'];
		if ( $is_ajax_action ) {
			$attempt_cache = new QuizAttempts();
			if ( $attempt_cache->has_cache() ) {
				$count = $attempt_cache->get_cache();
			} else {
				$count = $wpdb->get_col(
					$wpdb->prepare(
						"SELECT COUNT( DISTINCT attempt_id)
							 FROM 	{$wpdb->prefix}tutor_quiz_attempts quiz_attempts
									INNER JOIN {$wpdb->posts} quiz
										ON quiz_attempts.quiz_id = quiz.ID
									INNER JOIN {$wpdb->prefix}tutor_quiz_attempt_answers AS ans 
										ON quiz_attempts.attempt_id = ans.quiz_attempt_id		
		
							WHERE 	attempt_status != %s
								{$pass_clause}
		
							UNION 
		
							SELECT COUNT( DISTINCT attempt_id)
								 FROM 	{$wpdb->prefix}tutor_quiz_attempts quiz_attempts
										INNER JOIN {$wpdb->posts} quiz
											ON quiz_attempts.quiz_id = quiz.ID
										INNER JOIN {$wpdb->prefix}tutor_quiz_attempt_answers AS ans 
											ON quiz_attempts.attempt_id = ans.quiz_attempt_id		
		
								WHERE 	attempt_status != %s
									{$fail_clause}
		
							UNION
		
							SELECT COUNT( DISTINCT attempt_id)
								 FROM 	{$wpdb->prefix}tutor_quiz_attempts quiz_attempts
										INNER JOIN {$wpdb->posts} quiz
											ON quiz_attempts.quiz_id = quiz.ID
										INNER JOIN {$wpdb->prefix}tutor_quiz_attempt_answers AS ans 
											ON quiz_attempts.attempt_id = ans.quiz_attempt_id		
		
								WHERE 	attempt_status != %s
									{$pending_clause}
		
					",
						'attempt_started',
						'attempt_started',
						'attempt_started'
					)
				);
				$attempt_cache->data = array(
					$count[0] ?? 0, //pass
					$count[1] ?? 0, //fail
					$count[2] ?? 0, //pending
				);
				$attempt_cache->set_cache();
			}

		}

		$count_pass 	= $count[0] ?? 0;
		$count_fail 	= $count[1] ?? 0;
		$count_pending  = $count[2] ?? 0;

		$all 	 = $count_pass + $count_fail + $count_pending;
		$pass 	 = $count_pass;
		$fail 	 = $count_fail;
		$pending = $count_pending;
		$response = compact('all', 'pass', 'fail', 'pending');
		return $is_ajax_action ? wp_send_json_success( $response ) : $response;
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
		$url     = get_pagenum_link();
		$stats 	 = $this->get_quiz_attempts_stat(get_current_user_id());
		
		$tabs 	 = array(
			array(
				'key'   => 'all',
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
	 * Count all enrolment | approved | cancelled
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
					   INNER JOIN {$wpdb->tutor_quiz_attempt_answers} quiz
							   ON quiz_attempts.quiz_id = quiz.ID
					   INNER JOIN {$wpdb->users}
							   ON quiz_attempts.user_id = {$wpdb->users}.ID
			   WHERE 	attempt_status != %s
					   AND ( user_email = %s OR display_name LIKE %s OR post_title LIKE %s )
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

		$bulk_action = isset( $_POST['bulk-action'] ) ? sanitize_text_field( $_POST['bulk-action'] ) : '';
		$bulk_ids = isset( $_POST['bulk-ids'] ) ? sanitize_text_field( $_POST['bulk-ids'] ) :'';
		$bulk_ids = explode(',', $bulk_ids);
		$bulk_ids = array_map(function($id){return (int)trim($id);}, $bulk_ids);
		
		switch($bulk_action) {
			case 'delete' :
				tutor_utils()->delete_quiz_attempt( $bulk_ids );
				break;
		}
		
		wp_send_json_success();
	}

	function get_bulk_actions() {
		$actions = array(
			'delete' => 'Delete',
		);
		return $actions;
	}
}
