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
use Tutor\Models\QuizModel;

$questions    = isset( $questions ) && is_array( $questions ) ? $questions : array();
$attempt_data = isset( $attempt_data ) && is_object( $attempt_data ) ? $attempt_data : null;
$back_url     = isset( $back_url ) ? (string) $back_url : '';
$context      = isset( $context ) ? (string) $context : '';

$attempt_answers_map = array();
if ( $attempt_data && ! empty( $attempt_data->attempt_id ) ) {
	$attempt_answers = QuizModel::get_quiz_answers_by_attempt_id( (int) $attempt_data->attempt_id );
	if ( is_array( $attempt_answers ) ) {
		foreach ( $attempt_answers as $attempt_answer ) {
			$qid = (int) ( $attempt_answer->question_id ?? 0 );
			if ( $qid > 0 ) {
				$attempt_answers_map[ $qid ] = $attempt_answer;
			}
		}
	}
}
?>

<div class="tutor-quiz tutor-quiz-questions">
	<?php foreach ( $questions as $index => $question ) : ?>
		<?php
		$question_type     = $question->question_type ?? '';
		$question_id       = (int) ( $question->question_id ?? 0 );
		$is_dnd_review     = in_array( $question_type, array( 'image_answering', 'ordering', 'matching', 'image_matching' ), true );
		$is_tf_review      = 'true_false' === $question_type;
		$is_mc_review      = in_array( $question_type, array( 'single_choice', 'multiple_choice' ), true );
		$is_oe_review      = in_array( $question_type, array( 'open_ended', 'short_answer' ), true );
		$is_fib_review     = 'fill_in_the_blank' === $question_type;
		$is_pin_review     = 'pin_image' === $question_type;
		$attempt_answer    = $attempt_answers_map[ $question_id ] ?? null;
		$question_template = '';

		if ( $is_dnd_review ) {
			$question_template = 'review-answer-dnd';
		} elseif ( $is_tf_review ) {
			$question_template = 'true-false';
		} elseif ( $is_mc_review ) {
			$question_template = 'multiple-choice';
		} elseif ( $is_oe_review ) {
			$question_template = 'open-ended';
		} elseif ( $is_fib_review ) {
			$question_template = 'fill-in-the-blank';
		} elseif ( $is_pin_review ) {
			$question_template = 'pin-image';
		}
		?>

		<div id="question-<?php echo esc_attr( $question_id ); ?>">
			<?php if ( ! empty( $question_template ) ) : ?>
				<?php
				tutor_load_template(
					'shared.components.quiz.attempt-details.question',
					array(
						'question'             => $question,
						'attempt_answer'       => $attempt_answer,
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
				?>
			<?php endif; ?>
		</div>
	<?php endforeach; ?>
</div>
