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

if ( ! isset( $question ) || ! is_object( $question ) || empty( $question_template ) ) {
	return;
}

$index             = (int) ( $index ?? 1 );
$attempt_answer    = isset( $attempt_answer ) && is_object( $attempt_answer ) ? $attempt_answer : null;
$question_settings = maybe_unserialize( $question->question_settings );
$question_settings = is_array( $question_settings ) ? $question_settings : array();
$question_type     = (string) ( $question->question_type ?? '' );

if ( 'image_matching' === $question_type ) {
	$question_type = 'matching';
}

$status_label   = __( 'Incorrect', 'tutor' );
$status_variant = Badge::ERROR;

if ( $attempt_answer && (bool) $attempt_answer->is_correct ) {
	$status_label   = __( 'Correct', 'tutor' );
	$status_variant = Badge::SUCCESS;
} elseif (
	$attempt_answer &&
	null === $attempt_answer->is_correct &&
	in_array( $question_type, array( 'open_ended', 'short_answer', 'image_answering' ), true )
) {
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
			'index'                => $index,
			'question_title'       => (string) ( $question->question_title ?? '' ),
			'question_description' => (string) ( $question->question_description ?? '' ),
			'question_mark'        => (string) ( $question->question_mark ?? '' ),
			'show_question_mark'   => '1' === (string) ( $question_settings['show_question_mark'] ?? '1' ),
			'status_label'         => $status_label,
			'status_variant'       => $status_variant,
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
	?>
</div>
