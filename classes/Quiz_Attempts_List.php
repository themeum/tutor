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
use Tutor\Components\Badge;
use Tutor\Components\Button;
use Tutor\Components\Constants\Size;
use Tutor\Components\Popover;
use Tutor\Helpers\UrlHelper;
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
	 * @since 3.8.0 refactor and query optimize.
	 *
	 * @return array
	 */
	public function get_quiz_attempts_stat() {
		global $wpdb;

		if ( wp_doing_ajax() ) {
			tutor_utils()->checking_nonce();
		}

		$count_obj = (object) array(
			'pass'    => 0,
			'fail'    => 0,
			'pending' => 0,
		);

		$is_ajax_action = 'tutor_quiz_attempts_count' === Input::post( 'action' );
		$user_id        = get_current_user_id();
		$course_id      = Input::post( 'course_id', 0, Input::TYPE_INT );
		$date           = Input::post( 'date', '' );
		$search         = Input::post( 'search', '' );

		if ( $is_ajax_action ) {
			$current_params = compact( 'course_id', 'date', 'search' );
			$attempt_cache  = new QuizAttempts( $current_params );

			$cached_attempts = $attempt_cache->get_cache();
			if ( $attempt_cache->has_cache() && $attempt_cache->is_same_query() && isset( $cached_attempts['result'] ) ) {
				$count_obj = $cached_attempts['result'];
			} else {

				$course_filter = $course_id ? $wpdb->prepare( ' AND quiz_attempts.course_id = %d', $course_id ) : '';
				$date_filter   = empty( $date ) ? '' : $wpdb->prepare( ' AND DATE(quiz_attempts.attempt_started_at) = %s ', $date );
				$user_clause   = User::is_admin() ? '' : $wpdb->prepare( ' AND quiz.post_author = %d', $user_id );

				$search_term_raw = $search;
				$search_filter   = '%' . $wpdb->esc_like( $search ) . '%';

				//phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$results = $wpdb->get_results(
					$wpdb->prepare(
						"SELECT result, COUNT( DISTINCT attempt_id) AS total
								FROM {$wpdb->prefix}tutor_quiz_attempts quiz_attempts
								INNER JOIN {$wpdb->posts} quiz ON quiz_attempts.quiz_id = quiz.ID
								INNER JOIN {$wpdb->users} AS users ON quiz_attempts.user_id = users.ID
								INNER JOIN {$wpdb->posts} AS course ON course.ID = quiz_attempts.course_id
						WHERE result IS NOT NULL
						AND (
							users.user_email = %s
							OR users.display_name LIKE %s
							OR quiz.post_title LIKE %s
							OR course.post_title LIKE %s
						)
						{$user_clause}
						{$course_filter}
						{$date_filter}
						GROUP BY result",
						$search_term_raw,
						$search_filter,
						$search_filter,
						$search_filter
					)
				);
				//phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

				foreach ( $results as $row ) {
					if ( isset( $count_obj->{$row->result} ) ) {
						$count_obj->{$row->result} = (int) $row->total;
					}
				}

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
	 * Obtain nav data for quiz attempts.
	 *
	 * @since 4.0.0
	 *
	 * @param array   $quiz_attempts the quiz attempts list.
	 * @param integer $quiz_attempts_count the quiz attempts count.
	 * @param string  $url the page url.
	 * @param string  $result_filter filter to filter out results.
	 *
	 * @return array
	 */
	public function get_quiz_attempts_nav_data( array $quiz_attempts = array(), int $quiz_attempts_count = 0, string $url = '', string $result_filter = '' ): array {
		$all_attempts     = count( QuizModel::format_quiz_attempts( $quiz_attempts ) );
		$pending_attempts = count( QuizModel::format_quiz_attempts( $quiz_attempts, QuizModel::RESULT_PENDING ) );
		$passed_attempts  = count( QuizModel::format_quiz_attempts( $quiz_attempts, QuizModel::RESULT_PASS ) );
		$failed_attempts  = count( QuizModel::format_quiz_attempts( $quiz_attempts, QuizModel::RESULT_FAIL ) );

		$nav_links = array(
			'type'    => 'dropdown',
			'active'  => true,
			'count'   => $quiz_attempts_count,
			'options' => array(
				array(
					'label'  => __( 'All', 'tutor' ),
					'count'  => $all_attempts,
					'url'    => remove_query_arg( 'result' ),
					'active' => '' === $result_filter,
				),
				array(
					'label'  => __( 'Pending', 'tutor' ),
					'url'    => add_query_arg( array( 'result' => QuizModel::RESULT_PENDING ), $url ),
					'count'  => $pending_attempts,
					'active' => QuizModel::RESULT_PENDING === $result_filter,
				),
				array(
					'label'  => __( 'Failed', 'tutor' ),
					'url'    => add_query_arg( array( 'result' => QuizModel::RESULT_FAIL ), $url ),
					'count'  => $failed_attempts,
					'active' => QuizModel::RESULT_FAIL === $result_filter,
				),
				array(
					'label'  => __( 'Passed', 'tutor' ),
					'url'    => add_query_arg( array( 'result' => QuizModel::RESULT_PASS ), $url ),
					'count'  => $passed_attempts,
					'active' => QuizModel::RESULT_PASS === $result_filter,
				),
			),
		);

		return $nav_links;
	}

	/**
	 * Prepare bulk actions that will show on dropdown options
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	public function prepare_bulk_actions(): array {
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
			function ( $id ) {
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

	/**
	 * Get attempt row template for quiz attempts.
	 *
	 * @since 4.0.0
	 *
	 * @param integer $course_id the course id.
	 *
	 * @return string
	 */
	public function get_quiz_attempt_row_template( $course_id = 0 ): string {
		$view_mode  = User::VIEW_AS_STUDENT === User::get_current_view_mode();
		$is_student = ( User::is_student( get_current_user_id() ) && tutor_utils()->is_enrolled( $course_id, get_current_user_id(), false ) ) || $view_mode;
		$template   = $is_student ? 'dashboard.components.student-quiz-attempt-row' : 'dashboard.components.quiz-attempt-row';
		return $template;
	}

	/**
	 * Get retry button attributes.
	 *
	 * @since 4.0.0
	 *
	 * @param integer $quiz_id the quiz id.
	 *
	 * @return string
	 */
	private function get_retry_attribute( $quiz_id = 0 ): string {
		$retry_attr = sprintf(
			'TutorCore.modal.showModal("tutor-retry-modal", { data: %s });',
			wp_json_encode(
				array(
					'quizID'      => $quiz_id,
					'redirectURL' => get_post_permalink( $quiz_id ),
				)
			)
		);

		return $retry_attr;
	}

	/**
	 * Get the quiz attempt review url.
	 *
	 * @since 4.0.0
	 *
	 * @param array $attempt the quiz attempt.
	 *
	 * @return string
	 */
	public function get_review_url( $attempt = array() ): string {
		return UrlHelper::add_query_params( get_pagenum_link(), array( 'view_quiz_attempt_id' => $attempt['attempt_id'] ?? 0 ) );
	}

	/**
	 * Render student quiz attempt retry button.
	 *
	 * @since 4.0.0
	 *
	 * @param integer $course_id the course id.
	 * @param integer $quiz_id the quiz id.
	 * @param array   $attempt the quiz attempt.
	 * @param integer $attempts_count the quiz attempt count.
	 *
	 * @return void
	 */
	public function render_retry_button( $course_id = 0, $quiz_id = 0, $attempt = array(), $attempts_count = 0 ) {
		$view_mode  = User::VIEW_AS_STUDENT === User::get_current_view_mode();
		$is_student = ( User::is_student( get_current_user_id() ) && tutor_utils()->is_enrolled( $course_id, get_current_user_id(), false ) ) || $view_mode;

		if ( $is_student && $this->should_retry( $attempt, $attempts_count ) ) {
			Button::make()
			->label( __( 'Retry', 'tutor' ) )
			->icon( Icon::RELOAD )
			->size( Size::MEDIUM )
			->variant( 'primary' )
			->attr( '@click', $this->get_retry_attribute( $quiz_id ) )
			->render();
		}
	}

	/**
	 * Whether student can retry attempt or not.
	 *
	 * @since 4.0.0
	 *
	 * @param array   $attempt the quiz attempt.
	 * @param integer $attempts_count the quiz attempt count.
	 *
	 * @return boolean
	 */
	private function should_retry( $attempt = array(), $attempts_count = 0 ): bool {
		$attempt_info = $attempt['attempt_info'] ?? array();

		$should_retry = false;

		if ( tutor_utils()->count( $attempt_info ) ) {
			$allowed_attempts = (int) $attempt_info['attempts_allowed'] ?? 0;
			$feedback_mode    = $attempt_info['feedback_mode'] ?? '';
			$should_retry     = 'retry' === $feedback_mode && $attempts_count < $allowed_attempts;
		}

		return $should_retry;
	}

	/**
	 * Get kebab button for quiz attempt popover.
	 *
	 * @since 4.0.0
	 *
	 * @return string
	 */
	private function get_kebab_button() {
		$kebab_button = Button::make()
				->icon( Icon::THREE_DOTS_VERTICAL )
				->attr( 'x-ref', 'trigger' )
				->attr( '@click', 'toggle()' )
				->attr( 'class', 'tutor-quiz-item-result-more' )
				->variant( 'secondary' )
				->size( Size::X_SMALL )
				->get();
		return $kebab_button;
	}

	/**
	 * Get quiz detail item for quiz attempt popover.
	 *
	 * @since 4.0.0
	 *
	 * @param array $attempt the quiz attempt.
	 *
	 * @return array
	 */
	private function get_details_item( $attempt = array() ) {
		$details_item = array(
			'tag'     => 'a',
			'content' => __( 'Details', 'tutor' ),
			'icon'    => tutor_utils()->get_svg_icon( Icon::RESOURCES ),
			'attr'    => array( 'href' => $this->get_review_url( $attempt ) ),
		);
		return $details_item;
	}

	/**
	 * Render student quiz attempt popover.
	 *
	 * @since 4.0.0
	 *
	 * @param array   $attempt the quiz attempt.
	 * @param integer $attempts_count the quiz attempt count.
	 * @param integer $quiz_id the quiz id.
	 *
	 * @return void
	 */
	public function render_student_attempt_popover( $attempt = array(), $attempts_count = 0, $quiz_id = 0 ) {
		// Only add retry option to the first attempt.
		if ( ! $this->should_retry( $attempt, $attempts_count ) || ! $attempts_count ) {
			Popover::make()
			->trigger( $this->get_kebab_button() )
			->placement( 'bottom' )
			->menu_item( $this->get_details_item( $attempt ) )
			->render();
		} else {
			Popover::make()
			->trigger( $this->get_kebab_button() )
			->placement( 'bottom' )
			->menu_item(
				array(
					'tag'     => 'button',
					'content' => __( 'Retry', 'tutor' ),
					'icon'    => tutor_utils()->get_svg_icon( Icon::RELOAD ),
					'attr'    => array(
						'@click' => $this->get_retry_attribute( $quiz_id ),
					),
				)
			)
			->menu_item( $this->get_details_item( $attempt ) )
			->render();
		}
	}

	/**
	 * Render List Badge for quiz attempts.
	 *
	 * @since 4.0.0
	 *
	 * @param array $attempt the quiz attempt.
	 *
	 * @return void
	 */
	public function render_quiz_attempt_list_badge( $attempt = array() ) {
		if ( QuizModel::RESULT_PASS === $attempt['result'] ) {
			Badge::make()->label( __( 'Passed', 'tutor' ) )->variant( Badge::SUCCESS )->rounded()->render();
		} elseif ( QuizModel::RESULT_PENDING === $attempt['result'] ) {
			Badge::make()->label( __( 'Pending', 'tutor' ) )->variant( Badge::WARNING )->rounded()->render();
		} else {
			Badge::make()->label( 'Failed' )->variant( Badge::ERROR )->rounded()->render();
		}
	}

	/**
	 * Render quiz attempt mobile view buttons.
	 *
	 * @since 4.0.0
	 *
	 * @param array $attempt the quiz attempt.
	 *
	 * @return void
	 */
	public function render_quiz_attempt_buttons( $attempt = array() ) {
		Button::make()
			->label( __( 'Details', 'tutor' ) )
			->icon( Icon::RESOURCES, 'left', 20, 20 )
			->size( Size::MEDIUM )
			->tag( 'a' )
			->attr( 'href', $this->get_review_url( $attempt ) )
			->variant( 'primary' )
			->render();

		Button::make()
			->label( __( 'Delete', 'tutor' ) )
			->icon( Icon::DELETE_2, 'left', 20, 20 )
			->size( Size::MEDIUM )
			->attr( '@click', sprintf( 'TutorCore.modal.showModal("tutor-quiz-attempt-delete-modal", { attemptID: %d });', $attempt['attempt_id'] ?? 0 ) )
			->variant( 'secondary' )
			->render();
	}

	/**
	 * Render quiz attempt popover for instructor quiz attempt list.
	 *
	 * @since 4.0.0
	 *
	 * @param array $attempt the quiz attempt.
	 *
	 * @return void
	 */
	public function render_quiz_attempt_popover( $attempt = array() ) {
		Popover::make()
			->trigger( $this->get_kebab_button() )
			->placement( 'bottom' )
			->menu_item( $this->get_details_item( $attempt ) )
			->menu_item(
				array(
					'tag'     => 'button',
					'content' => __( 'Delete', 'tutor' ),
					'icon'    => tutor_utils()->get_svg_icon( Icon::DELETE_2 ),
					'attr'    => array(
						'@click' => sprintf(
							'hide(); TutorCore.modal.showModal("tutor-quiz-attempt-delete-modal", { attemptID: %d });',
							$attempt['attempt_id'] ?? 0
						),
					),
				)
			)
			->render();
	}
}
