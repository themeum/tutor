<?php
/**
 * Attempt details question wrapper.
 *
 * @package Tutor\Templates
 * @subpackage LearningArea\Quiz\AttemptDetails
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use Tutor\Models\QuizModel;

if ( ! isset( $question ) || ! is_object( $question ) || empty( $question_template ) ) {
	return;
}

$index                = (int) ( $index ?? 1 );
$attempt_id           = (int) ( $attempt_id ?? 0 );
$back_url             = (string) ( $back_url ?? '' );
$context              = (string) ( $context ?? '' );
$is_instructor_review    = ! empty( $is_instructor_review );
$is_overridden           = ! empty( $is_overridden );
$review_field_name       = (string) ( $review_field_name ?? '' );
$has_partial_or_negative = ! empty( $has_partial_or_negative );
$question_settings       = maybe_unserialize( $question->question_settings );
$question_settings    = is_array( $question_settings ) ? $question_settings : array();
$question_type        = (string) ( $question->question_type ?? '' );

if ( 'image_matching' === $question_type ) {
	$question_type = 'matching';
}

if ( 'single_choice' === $question_type ) {
	$question_type = 'multiple_choice';
}

$is_skipped    = QuizModel::is_attempt_answer_skipped( $question );
$review_status = $question ? QuizModel::get_attempt_answer_status( $question ) : 'skipped';
$answer_status = $review_status;

$question_wrapper_classes = array( 'tutor-quiz-question' );
if ( 'review-answer-dnd' === $question_template ) {
	$question_wrapper_classes[] = 'tutor-quiz-review-dnd';
}

?>

<div class="<?php echo esc_attr( implode( ' ', $question_wrapper_classes ) ); ?>" data-question="<?php echo esc_attr( $question_type ); ?>">
	<?php
	tutor_load_template(
		'shared.components.quiz.attempt-details.question-header',
		array(
			'question'             => $question,
			'index'                => $index,
			'question_title'       => (string) ( $question->question_title ?? '' ),
			'question_description' => (string) ( $question->question_description ?? '' ),
			'question_mark'        => (string) ( $question->question_mark ?? '' ),
			'show_question_mark'   => '1' === (string) ( $question_settings['show_question_mark'] ?? '1' ),
			'answer_status'        => $answer_status,
			'attempt_id'           => $attempt_id,
			'attempt_answer_id'    => (int) ( $question->attempt_answer_id ?? 0 ),
			'is_skipped'           => $is_skipped,
			'back_url'             => $back_url,
			'context'              => $context,
			'is_instructor_review' => $is_instructor_review,
			'review_field_name'    => $review_field_name,
			'is_overridden'        => $is_overridden,
		)
	);

	tutor_load_template(
		'shared.components.quiz.attempt-details.questions.' . $question_template,
		array(
			'question'             => $question,
			'index'                => $index,
			'is_instructor_review' => $is_instructor_review,
			'is_skipped'           => $is_skipped,
			'review_status'        => $review_status,
			'manual_mark_field'    => "manual_marks[{$question->question_id}]",
			'question_feedback'    => (string) ( $question_feedback ?? '' ),
			'attempt_id'           => $attempt_id,
			'attempt_answer_id'    => (int) ( $question->attempt_answer_id ?? 0 ),
		)
	);

	do_action( 'tutor_quiz_attempt_details_after_question_template', $question, $question_template, $index );

	if ( is_object( $question ) ) {
		do_action( 'tutor_quiz_attempt_details_loop_after_row', $question, $answer_status, array() );
		do_action( 'tutor_quiz_attempt_details_mark_breakdown', $question, $answer_status, $is_instructor_review, $has_partial_or_negative );
	}
	?>
</div>
