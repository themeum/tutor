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

use TUTOR\User;
use Tutor\Cache\QuizAttempts;
use Tutor\Components\Badge;
use Tutor\Components\Button;
use Tutor\Components\Constants\Size;
use Tutor\Components\Constants\Positions;
use Tutor\Components\Constants\Variant;
use Tutor\Components\Popover;
use Tutor\Helpers\UrlHelper;
use Tutor\Models\QuizModel;
use Tutor\Components\SvgIcon;
use Tutor\Components\Progress;

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
	 * @param int    $quiz_attempts_count the quiz attempt count.
	 * @param string $url the url.
	 * @param string $result_filter the current result state.
	 * @param string $search_filter the search filter.
	 * @param string $course_filter the course id filter.
	 * @param string $start_date range start date (Y-m-d).
	 * @param string $end_date   range end date (Y-m-d).
	 * @param string $order_filter the order filter.
	 * @param array  $all_quizzes all quiz data for student.
	 *
	 * @return array
	 */
	public function get_quiz_attempts_nav_data( $quiz_attempts_count = 0, $url = '', $result_filter = '', $search_filter = '', $course_filter = 0, $start_date = '', $end_date = '', $order_filter = 'DESC', $all_quizzes = array() ): array {
		$quiz_model = new QuizModel();

		if ( tutor_utils()->count( $all_quizzes ) ) {
			$results             = isset( $all_quizzes['results'] ) ? $all_quizzes['results'] : array();
			$all_attempts        = isset( $all_quizzes['total_count'] ) ? $all_quizzes['total_count'] : 0;
			$quiz_attempts_count = $all_attempts;
			$passed_attempts     = count( $quiz_model->get_formatted_quiz_attempt_list_by_quiz_id( $results, QuizModel::RESULT_PASS ) );
			$failed_attempts     = count( $quiz_model->get_formatted_quiz_attempt_list_by_quiz_id( $results, QuizModel::RESULT_FAIL ) );
			$pending_attempts    = count( $quiz_model->get_formatted_quiz_attempt_list_by_quiz_id( $results, QuizModel::RESULT_PENDING ) );
		} else {
			$all_attempts     = QuizModel::get_quiz_attempts( 0, 0, $search_filter, $course_filter > 0 ? $course_filter : '', $start_date, $end_date, $order_filter, '', true, true );
			$pending_attempts = QuizModel::get_quiz_attempts( 0, 0, $search_filter, $course_filter > 0 ? $course_filter : '', $start_date, $end_date, $order_filter, QuizModel::RESULT_PENDING, true, true );
			$passed_attempts  = QuizModel::get_quiz_attempts( 0, 0, $search_filter, $course_filter > 0 ? $course_filter : '', $start_date, $end_date, $order_filter, QuizModel::RESULT_PASS, true, true );
			$failed_attempts  = QuizModel::get_quiz_attempts( 0, 0, $search_filter, $course_filter > 0 ? $course_filter : '', $start_date, $end_date, $order_filter, QuizModel::RESULT_FAIL, true, true );
		}

		$filter_url = remove_query_arg( 'current_page', $url );

		$nav_links = array(
			'type'    => 'dropdown',
			'active'  => true,
			'count'   => $quiz_attempts_count,
			'options' => array(
				array(
					'label'  => __( 'All', 'tutor' ),
					'count'  => $all_attempts,
					'url'    => remove_query_arg( 'result', $filter_url ),
					'active' => '' === $result_filter,
				),
				array(
					'label'  => __( 'Pending', 'tutor' ),
					'url'    => UrlHelper::add_query_params( $filter_url, array( 'result' => QuizModel::RESULT_PENDING ) ),
					'count'  => $pending_attempts,
					'active' => QuizModel::RESULT_PENDING === $result_filter,
				),
				array(
					'label'  => __( 'Failed', 'tutor' ),
					'url'    => UrlHelper::add_query_params( $filter_url, array( 'result' => QuizModel::RESULT_FAIL ) ),
					'count'  => $failed_attempts,
					'active' => QuizModel::RESULT_FAIL === $result_filter,
				),
				array(
					'label'  => __( 'Passed', 'tutor' ),
					'url'    => UrlHelper::add_query_params( $filter_url, array( 'result' => QuizModel::RESULT_PASS ) ),
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
	 * Check if attempt details are hidden.
	 *
	 * @since 4.0.0
	 *
	 * @return bool
	 */
	public static function is_attempt_details_hidden(): bool {
		$is_student_view        = User::is_student_view();
		$is_quiz_details_hidden = $is_student_view && tutor_utils()->get_option( 'hide_quiz_details' );
		return $is_quiz_details_hidden;
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
	 * @param array $attempt Quiz attempt.
	 * @param array $query_param Query param to add with the URL.
	 *
	 * @return string
	 */
	public function get_review_url( $attempt = array(), $query_param = array() ): string {
		$default = array( 'attempt_id' => $attempt['attempt_id'] ?? 0 );
		$params  = wp_parse_args( $query_param, $default );

		return UrlHelper::add_query_params( get_pagenum_link(), $params );
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
		$quiz_settings          = tutor_utils()->get_quiz_option( $quiz_id, '', array() );
		$limit_attempts_allowed = '1' === (string) ( $quiz_settings['limit_attempts_allowed'] ?? '0' );
		$attempts_allowed       = (int) ( $quiz_settings['attempts_allowed'] ?? 0 );
		$can_retry              = Quiz::can_retry_quiz( $limit_attempts_allowed, $attempts_allowed, $attempts_count );

		if ( User::is_student_view() && $can_retry ) {
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
	 * Render quiz attempt details button.
	 *
	 * @since 4.0.0
	 *
	 * @param array $attempt the quiz attempt.
	 *
	 * @return void
	 */
	public function render_details_button( $attempt ) {
		if ( User::is_student_view() ) {
			Button::make()
				->label( __( 'Details', 'tutor' ) )
				->icon( Icon::RESOURCES, 'left', 20 )
				->size( Size::MEDIUM )
				->tag( 'a' )
				->attr( 'href', $this->get_review_url( $attempt ) )
				->variant( 'primary' )
				->render();
		}
	}

	/**
	 * Get kebab button for quiz attempt popover.
	 *
	 * @since 4.0.0
	 *
	 * @param string $size the size of the button.
	 *
	 * @return string
	 */
	private function get_kebab_button( $size = Size::X_SMALL ) {
		$kebab_button = Button::make()
				->label( __( 'More options', 'tutor' ) )
				->icon( Icon::ELLIPSES )
				->icon_only()
				->attr( 'x-ref', 'trigger' )
				->attr( '@click', 'toggle()' )
				->attr( 'class', 'tutor-quiz-item-result-more' )
				->variant( Variant::SECONDARY )
				->size( $size )
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
		$query_param = array( 'action' => 'view_details' );

		$url = $this->get_review_url( $attempt, $query_param );

		$details_item = array(
			'tag'     => 'a',
			'content' => __( 'Details', 'tutor' ),
			'icon'    => SvgIcon::make()->name( Icon::RESOURCES )->size( 20 )->get(),
			'attr'    => array( 'href' => $url ),
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
	 * @param bool    $is_learning_area is learning area list item.
	 * @param bool    $show_details whether to show details.
	 *
	 * @return void
	 */
	public function render_student_attempt_popover( $attempt = array(), $attempts_count = 0, $quiz_id = 0, $is_learning_area = false, $show_details = true ) {
		$is_quiz_details_hidden = $this->is_attempt_details_hidden();
		$quiz_settings          = tutor_utils()->get_quiz_option( $quiz_id, '', array() );
		$limit_attempts_allowed = '1' === (string) ( $quiz_settings['limit_attempts_allowed'] ?? '0' );
		$attempts_allowed       = (int) ( $quiz_settings['attempts_allowed'] ?? 0 );

		$can_retry  = ! $is_learning_area && Quiz::can_retry_quiz( $limit_attempts_allowed, $attempts_allowed, $attempts_count );
		$show_retry = $can_retry && $attempts_count > 0;

		if ( ! $show_retry && $is_quiz_details_hidden ) {
			return;
		}

		if ( $is_learning_area && ! $is_quiz_details_hidden ) {

			$query_param = array( 'action' => 'view_details' );

			$url = $this->get_review_url( $attempt, $query_param );

			$button_html = Button::make()
				->tag( 'a' )
				->label( __( 'Details', 'tutor' ) )
				->size( Size::X_SMALL )
				->variant( Variant::PRIMARY )
				->attr( 'href', $url )
				->attr( 'class', 'tutor-quiz-item-result-more tutor-quiz-details-btn' )
				->get();

			echo '<div class="tutor-flex">' . wp_kses_post( $button_html ) . '</div>';
			return;
		}

		$popover = Popover::make()
			->trigger( $this->get_kebab_button( $show_details ? Size::X_SMALL : Size::MEDIUM ) )
			->placement( 'bottom' )
			->menu_min_width( '110px' );

		if ( $show_retry ) {
			$popover->menu_item(
				array(
					'tag'     => 'button',
					'content' => __( 'Retry', 'tutor' ),
					'icon'    => SvgIcon::make()->name( Icon::RELOAD_3 )->size( 20 )->get(),
					'attr'    => array(
						'@click' => $this->get_retry_attribute( $quiz_id ),
					),
				)
			);
		}

		if ( ! $is_quiz_details_hidden && $show_details ) {
			$popover->menu_item( $this->get_details_item( $attempt ) );
		}

		$popover->render();
	}

	/**
	 * Render quiz attempt marks percentage.
	 *
	 * @since 4.0.0
	 *
	 * @param string $attempt_result the quiz attempt `QuizModel::RESULT_PASS | QuizModel::RESULT_PENDING | QuizModel::RESULT_FAIL`.
	 * @param int    $earned_percentage the earned percentage.
	 * @param string $size the size of the component.
	 * @param string $wrapper_class the wrapper class of the component.
	 *
	 * @return void
	 */
	public static function render_quiz_attempt_marks_percentage( $attempt_result = '', $earned_percentage = 0, $size = 'small', $wrapper_class = '' ) {
		$statics_stroke_color = 'var(--tutor-icon-critical)';

		if ( QuizModel::RESULT_PASS === $attempt_result ) {
			$statics_stroke_color = 'var(--tutor-icon-success-secondary)';

			if ( 100 === (int) $earned_percentage ) {
				$statics_stroke_color = 'var(--tutor-icon-success-primary)';
			}
		} elseif ( QuizModel::RESULT_PENDING === $attempt_result ) {
			$statics_stroke_color = 'var(--tutor-icon-warning-secondary)';
		}

		Progress::make()
			->type( 'circle' )
			->value( $earned_percentage )
			->size( $size )
			->stroke_color( 'var(--tutor-border-idle2)' )
			->progress_stroke_color( $statics_stroke_color )
			->animated()
			->attr( 'class', $wrapper_class )
			->render();
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
			->icon( Icon::RESOURCES, 'left', 20 )
			->size( Size::MEDIUM )
			->tag( 'a' )
			->attr( 'href', $this->get_review_url( $attempt ) )
			->variant( 'primary' )
			->render();

		Button::make()
			->label( __( 'Delete', 'tutor' ) )
			->icon( Icon::DELETE_2, 'left', 20 )
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
			->placement( Positions::BOTTOM )
			->menu_min_width( '110px' )
			->menu_item( $this->get_details_item( $attempt ) )
			->menu_item(
				array(
					'tag'     => 'button',
					'content' => __( 'Delete', 'tutor' ),
					'icon'    => SvgIcon::make()->name( Icon::DELETE_2 )->size( 20 )->get(),
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
