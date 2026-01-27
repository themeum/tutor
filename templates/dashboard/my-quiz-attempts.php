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

use Tutor\Components\ConfirmationModal;
use Tutor\Components\DropdownFilter;
use Tutor\Components\EmptyState;
use Tutor\Components\Pagination;
use Tutor\Components\Sorting;
use TUTOR\Icon;
use TUTOR\Input;
use Tutor\Models\QuizModel;
use TUTOR\Quiz_Attempts_List;

if ( Input::has( 'view_quiz_attempt_id' ) ) {
	// Load single attempt details if ID provided.
	include __DIR__ . '/my-quiz-attempts/attempts-details.php';
	return;
}

$item_per_page    = tutor_utils()->get_option( 'pagination_per_page' );
$current_page     = max( 1, Input::get( 'current_page', 1, Input::TYPE_INT ) );
$offset           = ( $current_page - 1 ) * $item_per_page;
$quiz_attempt_obj = new Quiz_Attempts_List( false );


// Filter params.
$order_filter  = Input::get( 'order', 'DESC' );
$course_id     = isset( $course_id ) ? $course_id : array();
$result_filter = Input::get( 'result', '' );

$quiz_attempts           = QuizModel::get_quiz_attempts_by_course_ids( 0, 0, $course_id, '', '', '', $order_filter, get_current_user_id() );
$quiz_attempts_formatted = QuizModel::format_quiz_attempts( $quiz_attempts, $result_filter );
$quiz_attempts_list      = array_slice( $quiz_attempts_formatted, $offset, $item_per_page, true );
$quiz_attempts_count     = (int) count( $quiz_attempts_formatted );

$nav_links = $quiz_attempt_obj->get_quiz_attempts_nav_data( $quiz_attempts, $quiz_attempts_count, get_pagenum_link(), $result_filter );

?>
<div class="tutor-my-quiz-attempts-wrapper" x-data="tutorQuizAttempts()">
	<?php if ( $quiz_attempts_count ) : ?>
		<div class="tutor-quiz-attempts">
			<div class="tutor-quiz-students-attempts-filter tutor-flex tutor-justify-between tutor-sm-p-5 tutor-p-6 tutor-items-center tutor-sm-border-b">
				<div class="tutor-quiz-students-attempts-filter-item">
				<?php
					DropdownFilter::make()
						->options( $nav_links['options'] )
						->query_param( 'result' )
						->render();
				?>
				</div>
				<div class="tutor-quiz-students-attempts-filter-item">
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
		<div class="tutor-p-6">
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
			->id( 'tutor-retry-modal' )
			->title( __( 'Retry This Quiz Attempt?', 'tutor' ) )
			->message( __( 'Retrying this quiz will reset your current attempt. Your answers and score from this attempt will be lost.', 'tutor' ))
			->confirm_handler( 'handleRetryAttempt({...payload?.data})' )
			->confirm_text( __( 'Retry Quiz', 'tutor' ))
			->mutation_state( 'retryMutation' )
			->render();
	?>
</div>