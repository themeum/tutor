<?php
/**
 * Attempt details Scale (read-only).
 *
 * @package Tutor\Templates
 * @subpackage LearningArea\Quiz\AttemptDetails
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use Tutor\Models\QuizModel;

if ( ! isset( $question ) || ! is_object( $question ) ) {
	return;
}

$attempt_answer = isset( $attempt_answer ) && is_object( $attempt_answer ) ? $attempt_answer : null;
$question_id    = (int) ( $question->question_id ?? 0 );

// Resolve student-selected value.
$student_value = null;
if ( $attempt_answer ) {
	$given_raw = $attempt_answer->given_answer ?? '';
	if ( is_string( $given_raw ) && '' !== $given_raw ) {
		$student_data = json_decode( stripslashes( $given_raw ), true );
		if ( is_array( $student_data ) && isset( $student_data['value'] ) ) {
			$student_value = (float) $student_data['value'];
		}
	}
}

// Resolve correct value from question answers.
$correct_value = null;
if ( $question_id > 0 ) {
	$answers = QuizModel::get_question_answers( $question_id, 'scale' );
	if ( ! empty( $answers ) && ! empty( $answers[0]->answer_two_gap_match ) ) {
		$target_json = $answers[0]->answer_two_gap_match;
		$target      = json_decode( stripslashes( (string) $target_json ), true );
		if ( is_array( $target ) && isset( $target['value'] ) ) {
			$correct_value = (float) $target['value'];
		}
	}
}
?>

<div class="tutor-quiz-question-options">
	<div class="tutor-scale-answer-summary">
		<p class="tutor-fs-7 tutor-color-secondary tutor-mb-4">
			<strong><?php esc_html_e( 'Selected value:', 'tutor' ); ?></strong>
			<?php
			if ( null !== $student_value ) {
				echo esc_html( number_format( $student_value, 2 ) );
			} else {
				esc_html_e( 'No answer provided', 'tutor' );
			}
			?>
		</p>
		<?php if ( null !== $correct_value ) : ?>
			<p class="tutor-fs-7 tutor-color-secondary tutor-mb-0">
				<strong><?php esc_html_e( 'Correct value:', 'tutor' ); ?></strong>
				<?php echo esc_html( number_format( $correct_value, 2 ) ); ?>
			</p>
		<?php endif; ?>
	</div>
</div>

