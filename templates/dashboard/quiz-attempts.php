<?php
/**
 * Frontend Students Quiz Attempts
 *
 * @package Tutor\Templates
 * @subpackage Dashboard
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.4.0
 */

defined( 'ABSPATH' ) || exit;

use Tutor\Components\Button;
use Tutor\Components\ConfirmationModal;
use Tutor\Components\Constants\Positions;
use Tutor\Components\Constants\Size;
use Tutor\Components\Constants\Variant;
use Tutor\Components\CourseFilter;
use Tutor\Components\DateFilter;
use Tutor\Components\DropdownFilter;
use Tutor\Components\EmptyState;
use Tutor\Components\Pagination;
use Tutor\Components\SearchFilter;
use Tutor\Components\Sorting;
use TUTOR\Input;
use Tutor\Models\QuizModel;
use TUTOR\Quiz_Attempts_List;

if ( Input::has( 'attempt_id', Input::GET_REQUEST ) ) {
	// Load single attempt details if ID provided.
	include __DIR__ . '/quiz-attempts/quiz-reviews.php';
	return;
}

$url              = get_pagenum_link( 1, false );
$item_per_page    = tutor_utils()->get_option( 'pagination_per_page' );
$current_page     = max( 1, Input::get( 'current_page', 1, Input::TYPE_INT ) );
$offset           = ( $current_page - 1 ) * $item_per_page;
$quiz_attempt_obj = new Quiz_Attempts_List( false );

// Filter params.
$course_id     = Input::get( 'course-id', 0, Input::TYPE_INT );
$order_filter  = Input::get( 'order', 'DESC' );
$start_date    = Input::get( 'start_date', '' );
$end_date      = Input::get( 'end_date', '' );
$result_filter = Input::get( 'result', '' );
$search_filter = Input::get( 'search', '' );

$quiz_attempts       = QuizModel::get_quiz_attempts( $offset, $item_per_page, $search_filter, $course_id > 0 ? $course_id : '', $start_date, $end_date, $order_filter, $result_filter, false, true );
$quiz_attempts_list  = QuizModel::format_quiz_attempts( $quiz_attempts, $result_filter );
$quiz_attempts_count = QuizModel::get_quiz_attempts( $offset, $item_per_page, $search_filter, $course_id > 0 ? $course_id : '', $start_date, $end_date, $order_filter, $result_filter, true, true );


$date_params_present = Input::has( 'start_date', Input::GET_REQUEST ) || Input::has( 'end_date', Input::GET_REQUEST );
if ( $date_params_present && $quiz_attempts_count <= $offset ) {
	$offset = 0;
}


$nav_links = $quiz_attempt_obj->get_quiz_attempts_nav_data(
	$quiz_attempts_count,
	$url,
	$result_filter,
	$search_filter,
	$course_id,
	$start_date,
	$end_date,
	$order_filter,
	array()
);

$hidden_inputs = array(
	'order'      => $order_filter,
	'start_date' => $start_date,
	'end_date'   => $end_date,
	'result'     => $result_filter,
	'course-id'  => $course_id,
)

?>

<div class="tutor-dashboard-quiz-attempts-wrapper" x-data="tutorQuizAttempts()">
	<h4 class="tutor-quiz-attempts-mobile-heading tutor-h4 tutor-mb-5">
		<?php esc_html_e( 'Quiz Attempts', 'tutor' ); ?>
	</h4>
	<div class="tutor-quiz-attempts tutor-instructor-quiz-attempts tutor-surface-l1 tutor-border tutor-rounded-2xl tutor-overflow-hidden">
		<div class="tutor-quiz-attempts-filter">
			<?php
			CourseFilter::make()
				->size( Size::SMALL )
				->variant( Variant::PRIMARY_SOFT )
				->render();

			DropdownFilter::make()
				->size( Size::SMALL )
				->options( $nav_links['options'] )
				->query_param( 'result' )
				->variant( Variant::OUTLINE )
				->position( Positions::BOTTOM_END )
				->render();
			?>
		</div>
		<div class="tutor-px-6 tutor-py-5 tutor-flex tutor-gap-3 tutor-justify-between tutor-border-b">
			<?php
			SearchFilter::make()
				->form_id( 'tutor-quiz-attempt-search-form' )
				->hidden_inputs( $hidden_inputs )
				->placeholder( __( 'Search quizzes...', 'tutor' ) )
				->size( Size::SMALL )
				->render();
			?>

			<div class="tutor-flex tutor-gap-3">
				<?php
				$query_items = array( 'course-id', 'search', 'start_date', 'end_date', 'result', 'order' );
				if ( Input::has_any( $query_items, Input::GET_REQUEST ) ) {
					Button::make()
						->tag( 'a' )
						->size( Size::SMALL )
						->attr( 'href', tutor_utils()->tutor_dashboard_url( 'quiz-attempts' ) )
						->attr( 'class', 'tutor-text-brand' )
						->label( __( 'Clear all', 'tutor' ) )
						->variant( Variant::LINK )
						->render();
				}

				DateFilter::make()
					->type( DateFilter::TYPE_RANGE )
					->placement( Positions::BOTTOM_END )
					->hide_initial_label()
					->render();

				Sorting::make()->size( Size::SMALL )->order( $order_filter )->render();
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
			<?php foreach ( $quiz_attempts_list as $quiz_attempt ) : ?>
			<div class="tutor-quiz-attempts-item-wrapper">
				<?php
					tutor_load_template(
						'dashboard.components.quiz-attempt-row',
						array(
							'attempt'          => $quiz_attempt,
							'quiz_title'       => $quiz_attempt['quiz_title'],
							'course_title'     => $quiz_attempt['course_title'],
							'course_id'        => $quiz_attempt['course_id'],
							'show_quiz_title'  => true,
							'show_course'      => true,
							'quiz_id'          => $quiz_attempt['quiz_id'],
							'attempts_count'   => 1,
							'attempt_id'       => $quiz_attempt['attempt_id'] ?? 0,
							'quiz_attempt_obj' => $quiz_attempt_obj,
						)
					);

				?>
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
	</div>
	<?php
	ConfirmationModal::make()
		->id( 'tutor-quiz-attempt-delete-modal' )
		->title( __( 'Do You Want to Delete This?', 'tutor' ) )
		->message( __( 'Would you like to delete Quiz Attempt permanently? We suggest you proceed with caution.', 'tutor' ) )
		->confirm_handler( 'handleDeleteAttempt(payload?.attemptID)' )
		->mutation_state( 'deleteMutation' )
		->render();
	?>
</div>
