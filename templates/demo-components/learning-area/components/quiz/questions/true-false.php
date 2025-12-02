<?php
/**
 * True False
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use TUTOR\Icon;

$question = array(
	'index'             => 1,
	'question_id'       => 1,
	'question_title'    => __( 'Is the Earth round?', 'tutor' ),
	'question_description' => __( 'This is a description of the question.', 'tutor' ),
	'question_type'     => 'true_false',
	'answer_required'   => true,
	'question_mark'     => 10,
	'question_settings' => array(
		'answer_required'    => '0',
		'question_mark'      => '1',
		'question_type'      => 'true_false',
		'randomize_question' => '0',
		'show_question_mark' => '1',
	),
	'question_answers'  => array(
		array(
			'answer_title' => __( 'True', 'tutor' ),
			'is_correct'   => true,
		),
		array(
			'answer_title' => __( 'False', 'tutor' ),
			'is_correct'   => false,
		),
	),
);

/*
Check if current answer is correct
 *
 * @param array $answer Answer data.
 *
 * @return string Correct, Incorrect or Empty string.
 */
$is_correct = function ( $answer ) {
	if ( ! array_key_exists( 'is_correct', $answer ) ) {
		return '';
	}

	$value = $answer['is_correct'];

	// values that should return an empty string.
	$empty_values = array( null, '' );

	if ( in_array( $value, $empty_values, true ) ) {
		return '';
	}

	// map boolean values to their labels.
	$map = array(
		true  => 'correct',
		false => 'incorrect',
	);

	return $map[ $value ] ?? '';
}

?>

<div class="tutor-quiz-question" data-question="<?php echo esc_attr( $question['question_type'] ); ?>">
	<?php
	tutor_load_template(
		'demo-components.learning-area.components.quiz.question-header',
		array(
			'index'              => $question['index'],
			'question_title'     => $question['question_title'],
			'question_description' => $question['question_description'],
			'question_mark'      => $question['question_mark'],
			'show_question_mark' => $question['question_settings']['show_question_mark'],
		)
	);
	?>

	<div class="tutor-quiz-question-options">
		<?php foreach ( $question['question_answers'] as $answer ) : ?>
			<div class="tutor-quiz-question-option" data-option="<?php echo esc_attr( $is_correct( $answer ) ); ?>">
				<?php tutor_utils()->render_svg_icon( $answer['is_correct'] ? Icon::CHECK_2 : Icon::CROSS, 20, 20 ); ?>
				<?php echo esc_html( $answer['answer_title'] ); ?>
			</div>
		<?php endforeach; ?>
	</div>
</div>
