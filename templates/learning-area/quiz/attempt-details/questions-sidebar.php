<?php
/**
 * Tutor quiz attempt details sidebar.
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use Tutor\Models\QuizModel;

if ( ! isset( $quiz_id ) ) {
	return;
}

$quiz_id = (int) $quiz_id;

if ( ! $quiz_id ) {
	return;
}

$questions           = tutor_utils()->get_questions_by_quiz( $quiz_id );
$question_status_map = array();

if ( isset( $attempt_data ) && is_object( $attempt_data ) && ! empty( $attempt_data->attempt_id ) ) {
	$attempt_answers = QuizModel::get_quiz_answers_by_attempt_id( (int) $attempt_data->attempt_id );

	if ( is_array( $attempt_answers ) ) {
		foreach ( $attempt_answers as $answer_row ) {
			$question_id = (int) ( $answer_row->question_id ?? 0 );
			if ( ! $question_id ) {
				continue;
			}

			if ( ! isset( $question_status_map[ $question_id ] ) ) {
				$question_status_map[ $question_id ] = array(
					'has_pending'   => false,
					'has_incorrect' => false,
					'has_correct'   => false,
				);
			}

			if ( null === $answer_row->is_correct ) {
				$question_status_map[ $question_id ]['has_pending'] = true;
			} elseif ( (bool) $answer_row->is_correct ) {
				$question_status_map[ $question_id ]['has_correct'] = true;
			} else {
				$question_status_map[ $question_id ]['has_incorrect'] = true;
			}
		}
	}
}
?>

<div class="tutor-quiz-sidebar-questions">
	<?php if ( is_array( $questions ) ) : ?>
		<?php foreach ( $questions as $index => $question ) : ?>
			<?php
			$question_id       = (int) ( $question->question_id ?? 0 );
			$item_status_class = '';

			if ( isset( $question_status_map[ $question_id ] ) ) {
				$question_status_data = $question_status_map[ $question_id ];
				if ( ! empty( $question_status_data['has_pending'] ) ) {
					$item_status_class = 'pending';
				} elseif ( ! empty( $question_status_data['has_incorrect'] ) ) {
					$item_status_class = 'incorrect';
				} elseif ( ! empty( $question_status_data['has_correct'] ) ) {
					$item_status_class = 'correct';
				}
			}

			$classes = array( 'tutor-quiz-sidebar-question-item' );
			if ( 0 === (int) $index ) {
				$classes[] = 'active';
			}
			if ( $item_status_class ) {
				$classes[] = $item_status_class;
			}
			?>
			<a href="#" class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
				<div class="tutor-question-number"><?php echo esc_html( (int) $index + 1 ); ?>.</div>
				<div class="tutor-question-content">
					<?php echo esc_html( wp_strip_all_tags( (string) ( $question->question_title ?? '' ) ) ); ?>
				</div>
			</a>
		<?php endforeach; ?>
	<?php endif; ?>
</div>
