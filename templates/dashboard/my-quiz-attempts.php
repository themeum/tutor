<?php
/**
 * My Quiz Attempts
 *
 * @package Tutor\Templates
 * @subpackage Dashboard
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.1.2
 */

defined( 'ABSPATH' ) || exit;

use Tutor\Components\Button;
use Tutor\Components\ConfirmationModal;
use Tutor\Components\Constants\Variant;
use Tutor\Components\DropdownFilter;
use Tutor\Components\EmptyState;
use Tutor\Components\Pagination;
use Tutor\Components\Sorting;
use TUTOR\Input;
use Tutor\Models\QuizModel;
use TUTOR\Quiz_Attempts_List;
use Tutor\Helpers\UrlHelper;

if ( Input::has( 'attempt_id', Input::GET_REQUEST ) ) {
	// Load single attempt details if ID provided.
	include __DIR__ . '/my-quiz-attempts/attempts-details.php';
	return;
}

$url              = get_pagenum_link();
$item_per_page    = tutor_utils()->get_option( 'pagination_per_page' );
$current_page     = max( 1, Input::get( 'current_page', 1, Input::TYPE_INT ) );
$offset           = ( $current_page - 1 ) * $item_per_page;
$quiz_attempt_obj = new Quiz_Attempts_List( false );
$quiz_model       = new QuizModel();


// Filter params.
$order_filter  = Input::get( 'order', 'DESC' );
$course_id     = isset( $course_id ) ? $course_id : array();
$result_filter = Input::get( 'result', '' );

$quiz_ids_query     = QuizModel::get_quiz_id_by_user_quiz_attempts( get_current_user_id(), $course_id, $offset, $item_per_page, $order_filter, $result_filter );
$all_quiz_ids_query = QuizModel::get_quiz_id_by_user_quiz_attempts( get_current_user_id(), $course_id, 0, 0, $order_filter );

$quiz_attempts_list  = array();
$quiz_attempts_count = 0;
$nav_links           = array();

if ( tutor_utils()->count( $quiz_ids_query ) ) {
	$quiz_attempts_count = isset( $quiz_ids_query['total_count'] ) ? $quiz_ids_query['total_count'] : 0;
	$quiz_ids            = isset( $quiz_ids_query['results'] ) ? $quiz_ids_query['results'] : array();
	$quiz_attempts_list  = $quiz_model->get_formatted_quiz_attempt_list_by_quiz_id( $quiz_ids, $result_filter );
}

if ( tutor_utils()->count( $all_quiz_ids_query ) ) {
	$ids                    = isset( $all_quiz_ids_query['results'] ) ? $all_quiz_ids_query['results'] : array();
	$total_count            = isset( $all_quiz_ids_query['total_count'] ) ? $all_quiz_ids_query['total_count'] : 0;
	$passed_attempts_count  = count( $quiz_model->get_formatted_quiz_attempt_list_by_quiz_id( $ids, QuizModel::RESULT_PASS ) );
	$fail_attempts_count    = count( $quiz_model->get_formatted_quiz_attempt_list_by_quiz_id( $ids, QuizModel::RESULT_FAIL ) );
	$pending_attempts_count = count( $quiz_model->get_formatted_quiz_attempt_list_by_quiz_id( $ids, QuizModel::RESULT_PENDING ) );

	$nav_links = $quiz_attempt_obj->get_quiz_attempts_nav_data( $total_count, $pending_attempts_count, $fail_attempts_count, $passed_attempts_count, $total_count, $url, $result_filter );
}

?>
<div class="tutor-my-quiz-attempts-wrapper" x-data="tutorQuizAttempts()">
		<div class="tutor-quiz-attempts">
			<div class="tutor-quiz-students-attempts-filter tutor-flex tutor-justify-between tutor-px-6 tutor-py-5 tutor-sm-p-5 tutor-items-center tutor-border-b">
				<div class="tutor-quiz-students-attempts-filter-item">
				<?php
				if ( isset( $nav_links['options'] ) ) {
					DropdownFilter::make()
						->options( $nav_links['options'] )
						->query_param( 'result' )
						->render();
				}
				?>
				</div>
				<div class="tutor-quiz-students-attempts-filter-item tutor-flex tutor-items-center tutor-gap-4">
					<?php

					if ( Input::has_any( array( 'result', 'order' ), Input::GET_REQUEST ) ) {
						Button::make()
							->tag( 'a' )
							->attr( 'href', tutor_utils()->tutor_dashboard_url( 'courses/my-quiz-attempts' ) )
							->attr( 'class', 'tutor-text-brand' )
							->label( __( 'Clear all', 'tutor' ) )
							->variant( Variant::LINK )
							->render();
					}
					Sorting::make()->order( $order_filter )->render();

					?>
				</div>
			</div>
			<?php if ( $quiz_attempts_count ) : ?>
			<div class="tutor-quiz-attempts-header">
				<div class="tutor-quiz-attempts-header-item"><?php esc_html_e( 'Quiz info', 'tutor' ); ?></div>
				<div class="tutor-quiz-attempts-header-item"><?php esc_html_e( 'Marks', 'tutor' ); ?></div>
				<div class="tutor-quiz-attempts-header-item"><?php esc_html_e( 'Time', 'tutor' ); ?></div>
				<div class="tutor-quiz-attempts-header-item"><?php esc_html_e( 'Result', 'tutor' ); ?></div>
			</div>
			<div class="tutor-quiz-attempts-list">
				<?php
				foreach ( $quiz_attempts_list as $quiz_index => $quiz_attempt ) {
					$attempts       = $quiz_attempt['attempts'];
					$attempts_count = count( $attempts );

					tutor_load_template(
						'dashboard.components.quiz-attempts-group',
						array(
							'quiz_id'          => $quiz_index,
							'quiz_title'       => $quiz_attempt['quiz_title'],
							'course_title'     => $quiz_attempt['course_title'],
							'attempts'         => $attempts,
							'course_id'        => $quiz_attempt['course_id'],
							'quiz_attempt_obj' => $quiz_attempt_obj,
							'attempts_count'   => $attempts_count,
						)
					);
				}
				?>
			</div>
		<?php else : ?>
			<?php
			EmptyState::make()
				->title( __( 'No Quiz Attempts Found', 'tutor' ) )
				->render();
			?>
		<?php endif; ?>
			<?php
			Pagination::make()
				->current( $current_page )
				->total( $quiz_attempts_count )
				->limit( $item_per_page )
				->attr( 'class', 'tutor-p-6' )
				->render();
			?>

			<div x-data="tutorQuizRetryAttempt()" x-init="init()">
				<?php
				ConfirmationModal::make()
					->id( 'tutor-retry-modal' )
					->title( __( 'Retry This Quiz Attempt?', 'tutor' ) )
					->icon( UrlHelper::themed_asset( 'images/illustrations/quiz-retry.webp' ) )
					->message( __( 'Retrying this quiz will reset your current attempt. Your answers and score from this attempt will be lost.', 'tutor' ) )
					->confirm_handler( 'retryMutation?.mutate({...payload?.data})' )
					->confirm_text( __( 'Retry Quiz', 'tutor' ) )
					->mutation_state( 'retryMutation' )
					->render();
				?>
			</div>
</div>
