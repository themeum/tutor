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
use Tutor\Components\Constants\InputType;
use Tutor\Components\Constants\Size;
use Tutor\Components\Constants\Variant;
use Tutor\Components\InputField;
use Tutor\Components\Nav;
use Tutor\Components\Pagination;
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
$course_filter = Input::get( 'course-id', '' );
$order_filter  = Input::get( 'order', 'DESC' );
$date_filter   = Input::get( 'date', '' );
$result_filter = Input::get( 'result', '' );
$quiz_filter   = Input::get( 'quiz-name', '' );


$quiz_attempts           = QuizModel::get_quiz_attempts( 0, 0, '', $course_filter, $date_filter, $order_filter, null, false, true );
$quiz_attempts_formatted = QuizModel::format_quiz_attempts( $quiz_attempts, $result_filter );
$quiz_attempts_list      = array_slice( $quiz_attempts_formatted, $offset, $item_per_page, true );
$quiz_attempts_count     = (int) count( $quiz_attempts_formatted );

$nav_links = $quiz_attempt_obj->get_quiz_attempts_nav_data( $quiz_attempts, $quiz_attempts_count, get_pagenum_link(), $result_filter );
?>


<div class="tutor-dashboard-page-card">
	<div class="tutor-quiz-attempts">
		<div class="tutor-quiz-attempts-filter">
			<div class="tutor-quiz-attempts-filter-item ">
				<?php
					Nav::make()
					->items( array( $nav_links ) )
					->render();
				?>
			</div>
			<div class="tutor-flex tutor-gap-3">
				<div class="tutor-quiz-attempts-filter-item">
					<?php
						InputField::make()
							->type( InputType::TEXT )
							->name( 'quiz-name' )
							->placeholder( __( 'Search quizzes...', 'tutor' ) )
							->left_icon( tutor_utils()->get_svg_icon( Icon::SEARCH, 20, 20 ) )
							->searchable()
							->clearable()
							->render();

					?>

				</div>
				<div class="tutor-quiz-attempts-filter-item">
				<?php
					Button::make()
							->icon( tutor_utils()->get_svg_icon( Icon::CALENDAR_2, 16, 16 ) )
							->variant( Variant::OUTLINE )
							->render();
				?>
				</div>
				<div class="tutor-quiz-attempts-filter-item">
				<?php
					Button::make()
							->icon( tutor_utils()->get_svg_icon( Icon::SORT, 16, 16 ) )
							->variant( Variant::OUTLINE )
							->render();
				?>
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
</div>
<div class="quiz-attempts-pagination tutor-pt-6">
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