<?php
/**
 * Tutor quiz attempt details sidebar.
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use Tutor\Models\QuizModel;

if ( ! isset( $quiz_id ) ) {
	return;
}

$quiz_id = (int) $quiz_id;

if ( ! $quiz_id ) {
	return;
}

$question_status_map = array();
$default_item_status = ( isset( $attempt_data ) && is_object( $attempt_data ) ) ? 'incorrect' : '';
$status_priority     = array(
	'correct'   => 1,
	'incorrect' => 2,
	'pending'   => 3,
);

if ( isset( $attempt_data ) && is_object( $attempt_data ) && ! empty( $attempt_data->attempt_id ) ) {
	$questions = isset( $questions ) ? $questions : QuizModel::get_quiz_answers_by_attempt_id( (int) $attempt_data->attempt_id );
	$questions = QuizModel::filter_attempt_answers_for_details( $questions, ! empty( $is_instructor_review ) );

	foreach ( $questions as $answer_row ) {
		$question_id = (int) ( $answer_row->question_id ?? 0 );
		if ( ! $question_id ) {
			continue;
		}

		$answer_status = QuizModel::get_attempt_answer_status( $answer_row );
		$item_status   = 'correct' === $answer_status ? 'correct' : ( 'pending' === $answer_status ? 'pending' : 'incorrect' );
		$current       = $question_status_map[ $question_id ] ?? '';

		if ( ! $current || $status_priority[ $item_status ] > $status_priority[ $current ] ) {
			$question_status_map[ $question_id ] = $item_status;
		}
	}
} else {
	$questions = tutor_utils()->get_questions_by_quiz( $quiz_id );
}

$first_question_id = is_array( $questions ) && ! empty( $questions ) ? (int) ( $questions[0]->question_id ?? 0 ) : 0;
$first_question_id = $first_question_id > 0 ? $first_question_id : '';
?>

<div
	class="tutor-quiz-summary-sidebar"
	x-data="tutorQuizSummarySidebar({
		firstQuestionId: '<?php echo esc_js( (string) $first_question_id ); ?>'
	})"
>
	<h3 class="tutor-h3 tutor-mb-10">
		<?php esc_html_e( 'Quiz questions', 'tutor' ); ?>
	</h3>

	<div class="tutor-quiz-sidebar-questions">
		<?php if ( is_array( $questions ) ) : ?>
			<?php foreach ( $questions as $index => $question ) : ?>
				<?php
				$question_id       = (int) ( $question->question_id ?? 0 );
				$item_status_class = '';

				$item_status_class = $question_status_map[ $question_id ] ?? $default_item_status;

				$classes = array( 'tutor-quiz-sidebar-question-item' );
				if ( $item_status_class ) {
					$classes[] = $item_status_class;
				}
				?>
				<a
					href="#question-<?php echo esc_attr( $question_id ); ?>"
					class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>"
					data-question-id="<?php echo esc_attr( $question_id ); ?>"
					:class="{ 'active': String(activeQuestionId) === '<?php echo esc_attr( $question_id ); ?>' }"
					@click.prevent="setActiveQuestion('<?php echo esc_attr( $question_id ); ?>')"
				>
					<div class="tutor-question-number"><?php echo esc_html( (int) $index + 1 ); ?>.</div>
					<div class="tutor-question-content">
						<?php echo esc_html( wp_strip_all_tags( wp_unslash( (string) ( $question->question_title ?? '' ) ) ) ); ?>
					</div>
				</a>
			<?php endforeach; ?>
		<?php endif; ?>
	</div>
</div>
