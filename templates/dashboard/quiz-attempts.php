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

use Tutor\Components\Button;
use Tutor\Components\Constants\Size;
use Tutor\Components\Constants\Variant;
use Tutor\Components\EmptyState;
use Tutor\Components\Modal;
use Tutor\Components\Nav;
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
	<div class="tutor-dashboard-page-card">
		<?php if ( $quiz_attempts_count ) : ?>
		<div class="tutor-quiz-attempts">
			<div class="tutor-quiz-attempts-filter">
				<div class="tutor-quiz-attempts-filter-item ">
					<?php
						Nav::make()
						->items( array( $nav_links ) )
						->size( Size::SMALL )
						->render();
					?>
				</div>
				<div class="tutor-flex tutor-gap-3">
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
						Button::make()
								->icon( tutor_utils()->get_svg_icon( Icon::CALENDAR_2 ) )
								->size( Size::X_SMALL )
								->variant( Variant::OUTLINE )
								->render();
					?>
					</div>
					<div class="tutor-quiz-attempts-filter-item">
					<?php Sorting::make()->order( $order_filter )->render(); ?>
					</div>
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
							'course_data'  => $quiz_attempt['course_data'],
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
		Pagination::make()
		->current( $current_page )
		->total( $quiz_attempts_count )
		->limit( $item_per_page )
		->prev( tutor_utils()->get_svg_icon( Icon::CHEVRON_LEFT_2 ) )
		->next( tutor_utils()->get_svg_icon( Icon::CHEVRON_RIGHT_2 ) )
		->render();
		?>
	</div>
</div>
