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

$field_name     = $question_field_name_base ?? '';
$register_rules = '';
if ( $answer_is_required ) {
	$register_rules = ", { required: '" . esc_js( $required_message ) . "' }";
}
$register_attr = "register('{$field_name}'{$register_rules})";

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
				name="<?php echo esc_attr( $field_name ); ?>"
				value="<?php echo esc_attr( $answer['answer_id'] ); ?>"
				x-bind="<?php echo esc_attr( $register_attr ); ?>"
				<?php if ( $show_correct_answers ) : ?>
					checked
				<?php endif; ?>
			>
			<?php tutor_utils()->render_svg_icon( $answer['is_correct'] ? Icon::CHECK_2 : Icon::CROSS, 20, 20 ); ?>
			<?php echo esc_html( $answer['answer_title'] ); ?>
		</label>
	<?php endforeach; ?>
</div>

<div
	class="tutor-quiz-questions-error"
	x-cloak
	x-show="errors?.['<?php echo esc_attr( $field_name ); ?>']?.message"
	x-text="errors?.['<?php echo esc_attr( $field_name ); ?>']?.message"
></div>
