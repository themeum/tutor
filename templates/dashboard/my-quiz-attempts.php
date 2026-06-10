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
use Tutor\Helpers\QueryHelper;
use TUTOR\Input;
use Tutor\Models\QuizModel;
use TUTOR\Quiz_Attempts_List;

if ( Input::has( 'attempt_id', Input::GET_REQUEST ) ) {
	// Load single attempt details if ID provided.
	include __DIR__ . '/my-quiz-attempts/attempts-details.php';
	return;
}

$url              = get_pagenum_link( 1, false );
$item_per_page    = tutor_utils()->get_option( 'pagination_per_page' );
$current_page     = max( 1, Input::get( 'current_page', 1, Input::TYPE_INT ) );
$current_user_id  = get_current_user_id();
$offset           = ( $current_page - 1 ) * $item_per_page;
$quiz_attempt_obj = new Quiz_Attempts_List( false );
$quiz_model       = new QuizModel();


// Filter params.
$order_filter  = QueryHelper::get_valid_sort_order( Input::get( 'order', 'DESC' ) );
$course_id     = isset( $course_id ) ? $course_id : array();
$result_filter = Input::get( 'result', '' );

$quizzes     = QuizModel::get_attempted_quizzes( $current_user_id, $course_id, $offset, $item_per_page, $order_filter, array( 'status' => $result_filter ) );
$all_quizzes = QuizModel::get_attempted_quizzes( $current_user_id, $course_id, 0, 0, $order_filter );

$quiz_attempts_list  = array();
$quiz_attempts_count = 0;
$nav_links           = array();

if ( tutor_utils()->count( $quizzes ) ) {
	$quiz_attempts_count = isset( $quizzes['total_count'] ) ? $quizzes['total_count'] : 0;
	$results             = isset( $quizzes['results'] ) ? $quizzes['results'] : array();
	$quiz_attempts_list  = $quiz_model->get_formatted_quiz_attempt_list_by_quiz_id( $results, $result_filter );
}

if ( tutor_utils()->count( $all_quizzes ) ) {
	$nav_links = $quiz_attempt_obj->get_quiz_attempts_nav_data( $quiz_attempts_count, $url, $result_filter, '', 0, '', '', $order_filter, $all_quizzes );
}

?>
<div class="tutor-my-quiz-attempts-wrapper" x-data="tutorQuizAttempts()">
	<div class="tutor-quiz-attempts">
		<div class="tutor-quiz-students-attempts-filter tutor-flex tutor-justify-between tutor-surface-l1 tutor-px-6 tutor-py-5 tutor-sm-p-5 tutor-items-center tutor-border-b">
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
		foreach ( $quiz_attempts_list as $quiz_index => $quiz_attempt ) :
			$attempts           = $quiz_attempt['attempts'];
			$attempts_count     = count( $attempts );
			$quiz_id            = $quiz_attempt['quiz_id'] ?? 0;
			$course_id          = $quiz_attempt['course_id'] ?? 0;
			$first_attempt      = $attempts[0];
			$remaining_attempts = array_slice( $attempts, 1 );
			?>
		<div x-data="{ expanded: false }" class="tutor-quiz-attempts-item-wrapper" :class="{ 'tutor-quiz-previous-attempts': expanded }">
			<!-- First Attempt (Always Visible with Quiz Title & Expand Button) -->
			<?php
			tutor_load_template(
				'shared.components.student-quiz-attempt-row',
				array(
					'attempt'          => $first_attempt,
					'quiz_title'       => $quiz_attempt['quiz_title'] ?? '',
					'course_title'     => $quiz_attempt['course_title'] ?? '',
					'course_id'        => $course_id,
					'show_quiz_title'  => true,
					'show_course'      => true,
					'quiz_id'          => $quiz_id,
					'attempts_count'   => $attempts_count,
					'attempt_id'       => $first_attempt['attempt_id'] ?? 0,
					'quiz_attempt_obj' => $quiz_attempt_obj,
				)
			);
			?>

			<!-- Additional Attempts (Collapsible) -->
			<?php if ( ! empty( $remaining_attempts ) ) : ?>
				<div x-show="expanded" x-collapse x-cloak class="tutor-quiz-previous-attempts">
					<div class="tutor-text-tiny tutor-text-subdued tutor-py-4 tutor-px-6 tutor-quiz-previous-attempts-title">
						<?php esc_html_e( 'Previous Attempts', 'tutor' ); ?>
					</div>
					<?php foreach ( $remaining_attempts as $key => $attempt ) : ?>
						<?php
						tutor_load_template(
							'shared.components.student-quiz-attempt-row',
							array(
								'attempt'          => $attempt,
								'attempt_number'   => count( $remaining_attempts ) - $key,
								'quiz_id'          => $quiz_id,
								'attempt_id'       => $attempt['attempt_id'] ?? 0,
								'course_id'        => $course_id,
								'quiz_attempt_obj' => $quiz_attempt_obj,
								'is_previous'      => true,
							)
						);
						?>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<div class="tutor-quiz-item-actions" x-show="!expanded" x-cloak>
				<?php
				$quiz_attempt_obj->render_details_button( $first_attempt );
				$quiz_attempt_obj->render_student_attempt_popover( $first_attempt, $attempts_count, $quiz_id, false, false );
				?>
			</div>
		</div>
		<?php endforeach; ?>
		<?php
		Pagination::make()
			->current( $current_page )
			->total( $quiz_attempts_count )
			->limit( $item_per_page )
			->attr( 'class', 'tutor-p-6' )
			->render();
		?>
	</div>
	<?php else : ?>
		<?php
		EmptyState::make()
			->title( __( 'No Quiz Attempts Found', 'tutor' ) )
				->icon( tutor_utils()->get_themed_svg( 'images/illustrations/quiz-empty.svg' ) )
			->render();
		?>
	<?php endif; ?>

	<div x-data="tutorQuizRetryAttempt()">
		<?php
		ConfirmationModal::make()
			->id( 'tutor-retry-modal' )
			->title( __( 'Retake Quiz?', 'tutor' ) )
			->icon( tutor_utils()->get_themed_svg( 'images/illustrations/quiz-retry.svg' ), 80, 80, ConfirmationModal::ICON_TYPE_HTML )
			->message( __( 'Retrying this quiz will reset your current attempt. Your answers and score from this attempt will be lost.', 'tutor' ) )
			->confirm_handler( 'retryMutation?.mutate({...payload?.data})' )
			->confirm_text( __( 'Retry Quiz', 'tutor' ) )
			->mutation_state( 'retryMutation' )
			->render();
		?>
	</div>
</div>
