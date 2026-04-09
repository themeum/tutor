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
use TUTOR\User;

if ( Input::has( 'attempt_id', Input::GET_REQUEST ) ) {
	// Load single attempt details if ID provided.
	include __DIR__ . '/quiz-attempts/quiz-reviews.php';
	return;
}

$url              = get_pagenum_link();
$item_per_page    = tutor_utils()->get_option( 'pagination_per_page' );
$current_page     = max( 1, Input::get( 'current_page', 1, Input::TYPE_INT ) );
$offset           = ( $current_page - 1 ) * $item_per_page;
$quiz_attempt_obj = new Quiz_Attempts_List( false );

// Filter params.
$course_id     = Input::get( 'course-id', 0, Input::TYPE_INT );
$order_filter  = Input::get( 'order', 'DESC' );
$date_filter   = Input::get( 'date', '' );
$result_filter = Input::get( 'result', '' );
$search_filter = Input::get( 'search', '' );

$is_student_view         = User::VIEW_AS_STUDENT === User::get_current_view_mode();
$quiz_attempts           = QuizModel::get_quiz_attempts( 0, 0, $search_filter, $course_id > 0 ? $course_id : '', $date_filter, $order_filter, null, false, true );
$quiz_attempts_formatted = QuizModel::format_quiz_attempts( $quiz_attempts, $result_filter, $is_student_view );
$quiz_attempts_count     = count( $quiz_attempts_formatted );

if ( Input::has( 'date', Input::GET_REQUEST ) && $quiz_attempts_count <= $offset ) {
	$offset = 0;
}

$quiz_attempts_list = array_slice( $quiz_attempts_formatted, $offset, $item_per_page, true );
$nav_links          = $quiz_attempt_obj->get_quiz_attempts_nav_data( $quiz_attempts, $quiz_attempts_count, get_pagenum_link(), $result_filter );

?>

<div class="tutor-dashboard-quiz-attempts-wrapper" x-data="tutorQuizAttempts()">
	<h4 class="tutor-quiz-attempts-mobile-heading tutor-h4 tutor-mb-5">
		<?php esc_html_e( 'Quiz Attempts', 'tutor' ); ?>
	</h4>
	<div class="tutor-dashboard-page-card">
		<div class="tutor-quiz-attempts tutor-instructor-quiz-attempts">
			<div class="tutor-quiz-attempts-filter">
				<div class="tutor-quiz-attempts-filter-item">
					<?php
						DropdownFilter::make()
							->size( Size::SMALL )
							->options( $nav_links['options'] )
							->query_param( 'result' )
							->variant( Variant::PRIMARY_SOFT )
							->render();
					?>
				</div>
				<div class="tutor-quiz-attempts-filter-item">
					<?php
					$query_items = array( 'course-id', 'search', 'date', 'result', 'order' );
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
					?>
				</div>
				<div class="tutor-quiz-attempts-filter-item">
					<?php
						DateFilter::make()
							->type( DateFilter::TYPE_SINGLE )
							->placement( Positions::BOTTOM_END )
							->trigger_size( Size::SMALL )
							->icon_size( Size::SIZE_16 )
							->render();
					?>
				</div>
				<div class="tutor-quiz-attempts-filter-item">
					<?php Sorting::make()->size( Size::SMALL )->order( $order_filter )->render(); ?>
				</div>
			</div>
			<div class="tutor-p-6 tutor-flex tutor-justify-between">
				<?php
				SearchFilter::make()
					->form_id( 'tutor-quiz-attempt-search-form' )
					->hidden_inputs( array( 'result' => $result_filter ) )
					->placeholder( __( 'Search quizzes...', 'tutor' ) )
					->size( Size::SMALL )
					->render();

				CourseFilter::make()
					->size( Size::SMALL )
					->button_class( 'tutor-btn tutor-btn-outline tutor-gap-2 tutor-btn-small' )
					->render();
				?>
			</div>
			<div class="tutor-quiz-attempts-header">
				<div class="tutor-quiz-attempts-header-item"><?php esc_html_e( 'Quiz info', 'tutor' ); ?></div>
				<div class="tutor-quiz-attempts-header-item"><?php esc_html_e( 'Marks', 'tutor' ); ?></div>
				<div class="tutor-quiz-attempts-header-item"><?php esc_html_e( 'Time', 'tutor' ); ?></div>
				<div class="tutor-quiz-attempts-header-item"><?php esc_html_e( 'Result', 'tutor' ); ?></div>
			</div>
			<?php if ( $quiz_attempts_count ) : ?>
			<div class="tutor-quiz-attempts-list">
				<?php
				foreach ( $quiz_attempts_list as $quiz_attempt ) {
					$attempts       = $quiz_attempt['attempts'];
					$attempts_count = count( $attempts );

					tutor_load_template(
						'dashboard.components.quiz-attempts-group',
						array(
							'quiz_id'          => $quiz_attempt['quiz_id'],
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
				<?php EmptyState::make()->title( __( 'No Quiz Attempts Found', 'tutor' ) )->render(); ?>
			<?php endif; ?>
		</div>
	</div>
	<?php
		Pagination::make()
		->current( $current_page )
		->total( $quiz_attempts_count )
		->limit( $item_per_page )
		->attr( 'class', 'tutor-pt-6' )
		->render();

	ConfirmationModal::make()
		->id( 'tutor-quiz-attempt-delete-modal' )
		->title( __( 'Do You Want to Delete This?', 'tutor' ) )
		->message( __( 'Would you like to delete Quiz Attempt permanently? We suggest you proceed with caution.', 'tutor' ) )
		->confirm_handler( 'handleDeleteAttempt(payload?.attemptID)' )
		->mutation_state( 'deleteMutation' )
		->render();
	?>
</div>
