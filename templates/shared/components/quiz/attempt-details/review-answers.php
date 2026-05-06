<?php
/**
 * Tutor learning area attempt details review answers list.
 *
 * @package Tutor\Templates
 * @subpackage LearningArea\Quiz\AttemptDetails
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use Tutor\Quiz;

$questions    = isset( $questions ) && is_array( $questions ) ? $questions : array();
$attempt_data = isset( $attempt_data ) && is_object( $attempt_data ) ? $attempt_data : null;
$back_url     = isset( $back_url ) ? (string) $back_url : '';
$context      = isset( $context ) ? (string) $context : '';
?>

<div class="tutor-quiz tutor-quiz-questions">
	<?php foreach ( $questions as $index => $question ) : ?>
		<?php
		$question_type = $question->question_type ?? '';

		$question_id = (int) ( $question->question_id ?? 0 );

		$is_dnd                = in_array( $question_type, array( 'image_answering', 'ordering', 'matching', 'image_matching' ), true );
		$is_true_false         = 'true_false' === $question_type;
		$is_multiple_choice    = in_array( $question_type, array( 'single_choice', 'multiple_choice' ), true );
		$is_open_ended         = in_array( $question_type, array( 'open_ended', 'short_answer' ), true );
		$is_fill_in_the_blanks = 'fill_in_the_blank' === $question_type;
		$is_draw_image         = 'draw_image' === $question_type;
		$is_pin                = 'pin_image' === $question_type;
		$is_scale              = 'scale' === $question_type;
		$is_coordinates        = 'coordinates' === $question_type;
		$is_puzzle             = 'puzzle' === $question_type;

		$question_template = '';

		if ( $is_dnd ) {
			$question_template = 'review-answer-dnd';
		} elseif ( $is_true_false ) {
			$question_template = 'true-false';
		} elseif ( $is_multiple_choice ) {
			$question_template = 'multiple-choice';
		} elseif ( $is_open_ended ) {
			$question_template = 'open-ended';
		} elseif ( $is_fill_in_the_blanks ) {
			$question_template = 'fill-in-the-blank';
		} elseif ( $is_scale ) {
			$question_template = 'scale';
		} elseif ( $is_pin ) {
			$question_template = 'pin-image';
		} elseif ( $is_draw_image ) {
			$question_template = 'draw-image';
		} elseif ( $is_coordinates ) {
			$question_template = 'coordinates';
		} elseif ( $is_puzzle ) {
			$question_template = 'puzzle';
		}
		?>

		<div id="question-<?php echo esc_attr( $question_id ); ?>">
			<?php
			if ( ! empty( $question_template ) ) {
				tutor_load_template(
					'shared.components.quiz.attempt-details.question',
					array(
						'question'             => $question,
						'index'                => (int) $index + 1,
						'question_template'    => $question_template,
						'attempt_id'           => (int) ( $attempt_data->attempt_id ?? 0 ),
						'is_manually_reviewed' => ! empty( $attempt_data->is_manually_reviewed ),
						'back_url'             => $back_url,
						'context'              => $context,
						'is_instructor_review' => $is_instructor_review,
						'review_field_name'    => "review_statuses[{$question_id}]",
					)
				);
			}
			do_action( 'tutor_review_answer_after_question_template', $question, $index, $is_instructor_review );
			?>
		</div>
	<?php endforeach; ?>
</div>
