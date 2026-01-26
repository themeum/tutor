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

use Tutor\Components\ConfirmationModal;
use Tutor\Components\Constants\Positions;
use Tutor\Components\Constants\Size;
use Tutor\Components\Constants\Variant;
use Tutor\Components\DateFilter;
use Tutor\Components\DropdownFilter;
use Tutor\Components\EmptyState;
use Tutor\Components\Pagination;
use Tutor\Components\SearchFilter;
use Tutor\Components\Sorting;
use TUTOR\Icon;
use TUTOR\Input;
use Tutor\Models\QuizModel;
use TUTOR\Quiz_Attempts_List;

if ( isset( $_GET['view_quiz_attempt_id'] ) ) {
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
$order_filter  = Input::get( 'order', 'DESC' );
$date_filter   = Input::get( 'date', '' );
$result_filter = Input::get( 'result', '' );
$search_filter = Input::get( 'search', '' );


$quiz_attempts           = QuizModel::get_quiz_attempts( 0, 0, $search_filter, '', $date_filter, $order_filter, null, false, true );
$quiz_attempts_formatted = QuizModel::format_quiz_attempts( $quiz_attempts, $result_filter );
$quiz_attempts_list      = array_slice( $quiz_attempts_formatted, $offset, $item_per_page, true );
$quiz_attempts_count     = (int) count( $quiz_attempts_formatted );

$nav_links = $quiz_attempt_obj->get_quiz_attempts_nav_data( $quiz_attempts, $quiz_attempts_count, get_pagenum_link(), $result_filter );

?>

<div class="tutor-dashboard-quiz-attempts-wrapper" x-data="tutorQuizAttempts()">
	<h4 class="tutor-quiz-attempts-mobile-heading tutor-h4 tutor-pt-3 tutor-pb-3">
	<?php esc_html_e( 'Quiz Attempts', 'tutor' ); ?>
	</h4>
	<div class="tutor-dashboard-page-card">
		<?php if ( $quiz_attempts_count ) : ?>
		<div class="tutor-quiz-attempts">
			<div class="tutor-quiz-attempts-filter">
				<div class="tutor-quiz-attempts-filter-item">
					<?php
						DropdownFilter::make()
							->options( $nav_links['options'] )
							->query_param( 'result' )
							->variant( Variant::PRIMARY_SOFT )
							->render();
					?>
				</div>
				<div class="tutor-quiz-attempts-filter-item">
						<?php
							SearchFilter::make()
								->form_id( 'tutor-quiz-attempt-search-form' )
								->hidden_inputs( array( 'result' => $result_filter ) )
								->placeholder( __( 'Search quizzes...', 'tutor' ) )
								->size( Size::SMALL )
								->render();
						?>
				</div>
				<div class="tutor-quiz-attempts-filter-item">
					<?php
						DateFilter::make()
							->type( DateFilter::TYPE_SINGLE )
							->placement( Positions::BOTTOM_START )
							->trigger_size( Size::X_SMALL )
							->icon_size( 15 )
							->render();
					?>
				</div>
				<div class="tutor-quiz-attempts-filter-item">
					<?php Sorting::make()->order( $order_filter )->render(); ?>
				</div>
			</div>
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
							'quiz_id'      => $quiz_index,
							'quiz_title'   => $quiz_attempt['quiz_title'],
							'course_title' => $quiz_attempt['course_title'],
							'attempts'     => $attempts,
							'course_id'    => $quiz_attempt['course_id'],
						)
					);
				}
				?>
			</div>
		</div>
		<?php else : ?>
			<?php
			EmptyState::make()
				->title( __( 'No Quiz Attempts Found', 'tutor' ) )
				->render();
			?>
		<?php endif; ?>
	</div>
	<div class="tutor-pt-6">
		<?php
		if ( $quiz_attempts_count > $item_per_page ) {
			Pagination::make()
			->current( $current_page )
			->total( $quiz_attempts_count )
			->limit( $item_per_page )
			->prev( tutor_utils()->get_svg_icon( Icon::CHEVRON_LEFT_2 ) )
			->next( tutor_utils()->get_svg_icon( Icon::CHEVRON_RIGHT_2 ) )
			->render();
		}
		?>
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
