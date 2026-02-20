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

$default_question = array(
	'index'                => 1,
	'question_id'          => 0,
	'question_title'       => '',
	'question_description' => '',
	'question_type'        => 'true_false',
	'answer_required'      => true,
	'question_mark'        => 10,
	'answer_explanation'   => '',
	'question_settings'    => array(
		'answer_required'    => '0',
		'question_mark'      => '1',
		'question_type'      => 'true_false',
		'randomize_question' => '1',
		'show_question_mark' => '1',
	),
);

$quiz_id            = $tutor_is_started_quiz->quiz_id ?? 0;
$quiz_settings      = tutor_utils()->get_quiz_option( $quiz_id, 'quiz_settings', array() );
$show_question_mark = $question_settings['show_question_mark'] ?? '0';

$answer_is_required = isset( $question_settings['answer_required'] ) && '1' === $question_settings['answer_required'];
$required_message   = __( 'The answer for this question is required', 'tutor' );
?>

<div
	class="tutor-quiz-question"
	data-question="<?php echo esc_attr( $question->question_type ); ?>"
	data-answer-required="<?php echo esc_attr( $answer_is_required ); ?>"
>
	<?php
	// Render question header.
	tutor_load_template(
		'learning-area.quiz.question-header',
		array(
			'index'                => $question->index,
			'question_title'       => $question->question_title,
			'question_description' => $question->question_description ?? '',
			'question_mark'        => $question->question_mark,
			'show_question_mark'   => $show_question_mark,
		)
	);

	// Render the question type specific answers template.
	tutor_load_template(
		'learning-area.quiz.questions.' . $question_type,
		array(
			'question'           => wp_parse_args( (array) $question, $default_question ),
			'quiz_settings'      => $quiz_settings,
			'answer_is_required' => $answer_is_required,
			'required_message'   => $required_message,
		)
	);

	// Fire after-answers actions.
	do_action( 'tutor_quiz_question_after_answers', $quiz_settings, $question );
	do_action( 'tutor_require_question_answer_file', $question_type, $tutor_is_started_quiz, $question );

	?>
</div>
