<?php
/**
 * True False
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use TUTOR\Icon;
use Tutor\Quiz;

global $tutor_is_started_quiz;

$default_question = array(
	'index'                => 1,
	'question_id'          => 0,
	'question_title'       => '',
	'question_description' => '',
	'question_type'        => 'true_false',
	'answer_required'      => true,
	'question_mark'        => 10,
	'question_settings'    => array(
		'answer_required'    => '0',
		'question_mark'      => '1',
		'question_type'      => 'true_false',
		'randomize_question' => '1',
		'show_question_mark' => '1',
	),
);

$question = wp_parse_args( $question, $default_question );

/*
 * Check if current answer is correct
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
};

$show_correct_answers = Quiz::show_correct_answers( $tutor_is_started_quiz->attempt_status );

?>

<div class="tutor-quiz-question" data-question="<?php echo esc_attr( $question['question_type'] ); ?>">
	<?php
	tutor_load_template(
		'learning-area.quiz.question-header',
		array(
			'index'                => $question['index'],
			'question_title'       => $question['question_title'],
			'question_description' => $question['question_description'],
			'question_mark'        => $question['question_mark'],
			'show_question_mark'   => $question['question_settings']['show_question_mark'],
		)
	);
	?>

	<div class="tutor-quiz-question-options">
		<?php foreach ( $question['question_answers'] as $answer ) : ?>
			<label 
				class="tutor-quiz-question-option" 
				<?php if ( $show_correct_answers ) : ?>
					data-option="<?php echo esc_attr( $is_correct( $answer ) ); ?>"
				<?php endif; ?>
			>
				<input
					class="tutor-hidden"
					type="radio"
					name="attempt[<?php echo esc_attr( $tutor_is_started_quiz->attempt_id ); ?>][quiz_question][<?php echo esc_attr( $question['question_id'] ); ?>]"
					value="<?php echo esc_attr( $answer->answer_id ); ?>"
					<?php if ( $show_correct_answers ) : ?>
						checked
					<?php endif; ?>
				>
				<?php tutor_utils()->render_svg_icon( $answer['is_correct'] ? Icon::CHECK_2 : Icon::CROSS, 20, 20 ); ?>
				<?php echo esc_html( $answer['answer_title'] ); ?>
			</label>
		<?php endforeach; ?>
	</div>
</div>
