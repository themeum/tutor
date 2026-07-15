<?php
/**
 * Quiz question wrapper template.
 *
 * Renders the outer question div, header, answer template, and after-answers actions.
 *
 * @package Tutor\Templates
 * @subpackage LearningArea\Quiz
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 *
 * @var object $question          The question object.
 * @var array  $question_settings The question settings array.
 * @var string $question_type     The question type template name (e.g. 'true-false').
 */

defined( 'ABSPATH' ) || exit;

global $tutor_is_started_quiz;
global $post;

$default_question = array(
	'index'                       => 1,
	'question_id'                 => 0,
	'question_title'              => '',
	'question_description'        => '',
	'question_type'               => 'true_false',
	'answer_required'             => true,
	'question_mark'               => 10,
	'answer_explanation'          => '',
	'question_settings'           => array(
		'answer_required'    => '0',
		'is_image_matching'  => '0',
		'question_mark'      => '1',
		'question_type'      => 'true_false',
		'randomize_question' => '1',
		'show_question_mark' => '1',
	),
	'question_answers'            => array(),
	'question_randomized_answers' => array(),
);

$quiz_id            = $tutor_is_started_quiz->quiz_id ?? 0;
$quiz               = $post instanceof WP_Post ? $post : get_post( $quiz_id );
$quiz_settings      = tutor_utils()->get_quiz_option( $quiz_id, '', array() );
$show_question_mark = $question_settings['show_question_mark'] ?? '0';
$attempt_id         = (int) ( $tutor_is_started_quiz->attempt_id ?? 0 );
$question_id        = (int) ( $question->question_id ?? 0 );
$field_name_base    = sprintf( 'attempt[%d][quiz_question][%d]', $attempt_id, $question_id );

$answer_is_required = isset( $question_settings['answer_required'] ) && '1' === $question_settings['answer_required'];
$required_message   = __( 'The answer for this question is required', 'tutor' );
?>

<div
	class="tutor-quiz-question"
	id="<?php echo esc_attr( $question_id ); ?>"
	data-question="<?php echo esc_attr( $question->question_type ); ?>"
	data-answer-required="<?php echo esc_attr( $answer_is_required ); ?>"
>
	<?php
	// Render question header.
	tutor_load_template(
		'learning-area.quiz.question-header',
		array(
			'question'           => $question,
			'show_question_mark' => $show_question_mark,
		)
	);

	// Render the question type specific answers template.
	tutor_load_template(
		apply_filters( 'tutor_filter_quiz_question_template', 'learning-area.quiz.questions.' . $question_type, $question_type ),
		array(
			'question'                 => wp_parse_args( (array) $question, $default_question ),
			'quiz_settings'            => $quiz_settings,
			'answer_is_required'       => $answer_is_required,
			'required_message'         => $required_message,
			'question_field_name_base' => $field_name_base,
		)
	);

	// Fire after-answers actions.
	do_action( 'tutor_quiz_question_after_answers', $quiz, $quiz_settings, $question );
	do_action( 'tutor_require_question_answer_file', $question_type, $tutor_is_started_quiz, $question );

	?>
</div>
