<?php
/**
 * Attempt details Multiple Choice/Single Choice (read-only).
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

$question_settings           = maybe_unserialize( $question->question_settings );
$question_settings           = is_array( $question_settings ) ? $question_settings : array();
$question_answers            = QuizModel::get_answers_by_quiz_question( (int) $question->question_id );
$question_answers            = is_array( $question_answers ) ? $question_answers : array();
$has_multiple_correct_answer = isset( $question_settings['has_multiple_correct_answer'] ) && '1' === (string) $question_settings['has_multiple_correct_answer'];

$given_ids = array();
if ( isset( $question->given_answer ) ) {
	$given_value = maybe_unserialize( $question->given_answer );
	$given_ids   = is_array( $given_value ) ? array_values( $given_value ) : array( $given_value );
}
$given_ids = array_map( 'intval', array_filter( $given_ids ) );
?>

<div class="tutor-quiz-question-options">
	<?php foreach ( $question_answers as $index => $answer ) : ?>
		<?php
		$is_selected = in_array( (int) $answer->answer_id, $given_ids, true );
		$is_correct  = (bool) ( $answer->is_correct ?? false );
		$option_attr = '';
		if ( $is_selected && $is_correct ) {
			$option_attr = 'correct';
		} elseif ( $is_selected && ! $is_correct ) {
			$option_attr = 'incorrect';
		} elseif ( ! $is_selected && $is_correct ) {
			$option_attr = 'correct';
		}
		?>
		<div class="tutor-quiz-question-option" data-option="<?php echo esc_attr( $option_attr ); ?>" data-readonly="true">
			<?php if ( ! empty( $answer->image_id ) ) : ?>
				<img src="<?php echo esc_url( wp_get_attachment_image_url( $answer->image_id, 'full' ) ); ?>" alt="<?php echo esc_attr( $answer->answer_title ?? '' ); ?>">
				<div data-title><?php echo esc_html( $answer->answer_title ?? '' ); ?></div>
			<?php else : ?>
				<div class="tutor-input-field">
					<div class="tutor-input-wrapper">
						<input
							type="<?php echo esc_attr( $has_multiple_correct_answer ? 'checkbox' : 'radio' ); ?>"
							class="<?php echo esc_attr( $has_multiple_correct_answer ? 'tutor-checkbox' : 'tutor-radio' ); ?>"
							id="<?php echo esc_attr( 'attempt-review-' . $question->question_id . '-' . $index ); ?>"
							<?php checked( $is_selected ); ?>
							disabled
						>
						<label class="tutor-label" for="<?php echo esc_attr( 'attempt-review-' . $question->question_id . '-' . $index ); ?>">
							<?php echo esc_html( $answer->answer_title ?? '' ); ?>
						</label>
					</div>
				</div>
			<?php endif; ?>
		</div>
	<?php endforeach; ?>
</div>
