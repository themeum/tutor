<?php
/**
 * Attempt details question wrapper.
 *
 * @package Tutor\Templates
 * @subpackage LearningArea\Quiz\AttemptDetails
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use Tutor\Components\Badge;
use Tutor\Models\QuizModel;

if ( ! isset( $question ) || ! is_object( $question ) || empty( $question_template ) ) {
	return;
}

$index                = (int) ( $index ?? 1 );
$attempt_answer       = isset( $attempt_answer ) && is_object( $attempt_answer ) ? $attempt_answer : null;
$attempt_id           = (int) ( $attempt_id ?? 0 );
$back_url             = (string) ( $back_url ?? '' );
$context              = (string) ( $context ?? '' );
$is_instructor_review = ! empty( $is_instructor_review );
$review_field_name    = (string) ( $review_field_name ?? '' );
$question_settings    = maybe_unserialize( $question->question_settings );
$question_settings    = is_array( $question_settings ) ? $question_settings : array();
$question_type        = (string) ( $question->question_type ?? '' );

if ( 'image_matching' === $question_type ) {
	$question_type = 'matching';
}

if ( 'single_choice' === $question_type ) {
	$question_type = 'multiple_choice';
}

$status_label   = __( 'Incorrect', 'tutor' );
$status_variant = Badge::ERROR;
$answer_status  = $attempt_answer ? QuizModel::get_attempt_answer_status( $attempt_answer ) : 'wrong';

if ( 'correct' === $answer_status ) {
	$status_label   = __( 'Correct', 'tutor' );
	$status_variant = Badge::SUCCESS;
} elseif ( 'pending' === $answer_status ) {
	$status_label   = __( 'Pending', 'tutor' );
	$status_variant = Badge::WARNING;
}


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
			'status_label'         => $status_label,
			'status_variant'       => $status_variant,
			'answer_status'        => $answer_status,
			'attempt_id'           => $attempt_id,
			'attempt_answer_id'    => (int) ( $attempt_answer->attempt_answer_id ?? 0 ),
			'back_url'             => $back_url,
			'context'              => $context,
			'is_instructor_review' => $is_instructor_review,
			'review_field_name'    => $review_field_name,
		)
	);

	tutor_load_template(
		'shared.components.quiz.attempt-details.questions.' . $question_template,
		array(
			'question'       => $question,
			'attempt_answer' => $attempt_answer,
			'index'          => $index,
		)
	);

	if ( is_object( $attempt_answer ) ) {
		do_action( 'tutor_quiz_attempt_details_loop_after_row', $attempt_answer, $answer_status, array() );
	}
	?>
</div>
