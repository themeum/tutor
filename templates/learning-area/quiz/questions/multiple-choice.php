<?php
/**
 * Multiple Choice
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use Tutor\Quiz;

global $tutor_is_started_quiz;

$default_question = array(
	'index'             => 1,
	'question_id'       => 0,
	'question_title'    => '',
	'question_type'     => 'multiple_choice',
	'answer_required'   => true,
	'question_mark'     => 10,
	'answer_explanation' => '',
	'question_settings' => array(
		'answer_required'             => '0',
		'question_mark'               => '1',
		'question_type'               => 'multiple_choice',
		'randomize_question'          => '0',
		'has_multiple_correct_answer' => '1',
		'show_question_mark'          => '1',
	),
);

$question = wp_parse_args( $question, $default_question );

/** Check if current answer is correct
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

/**
 * Check if current answer has a thumb
 *
 * @param array $answer Answer data.
 *
 * @return bool
 */
$has_image = function ( $answer ) {
	return array_key_exists( 'image_id', $answer ) && ! empty( $answer['image_id'] );
};

$has_multiple_correct_answer = isset( $question['question_settings']['has_multiple_correct_answer'] ) && '1' === $question['question_settings']['has_multiple_correct_answer'];
$answer_is_required          = isset( $question['question_settings']['answer_required'] ) && '1' === $question['question_settings']['answer_required'];
$radio_required_message      = __( 'Please select an option to answer', 'tutor' );
$checkbox_required_message   = __( 'Please select at least one option to answer.', 'tutor' );
$field_name                  = 'attempt[' . $tutor_is_started_quiz->attempt_id . '][quiz_question][' . $question['question_id'] . ']' . ( $has_multiple_correct_answer ? '[]' : '' );
$register_rules              = '';
if ( $answer_is_required ) {
	if ( $has_multiple_correct_answer ) {
		$register_rules = ", { validate: (value) => Array.isArray(value) && value.length > 0 || '" . esc_js( $checkbox_required_message ) . "' }";
	} else {
		$register_rules = ", { required: '" . esc_js( $radio_required_message ) . "' }";
	}
}
$register_attr = "register('{$field_name}'{$register_rules})";

$show_correct_answers = Quiz::show_correct_answers( $tutor_is_started_quiz->attempt_status );

?>

<div 
	class="tutor-quiz-question" 
	data-question="<?php echo esc_attr( $question['question_type'] ); ?>"
	data-answer-required="<?php echo esc_attr( $question['question_settings']['answer_required'] ?? '0' ); ?>"
>
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
		<?php foreach ( $question['question_answers'] as $index => $answer ) : ?>
			<label
				class="tutor-quiz-question-option"
				<?php if ( $show_correct_answers ) : ?>
					data-option="<?php echo esc_attr( $is_correct( $answer ) ); ?>"
				<?php endif; ?>
			>
				<div class="tutor-input-field <?php echo $has_image( $answer ) ? 'tutor-hidden' : ''; ?>">
					<div class="tutor-input-wrapper">
						<!-- @TODO: Disable checkbox when viewing quiz attempt -->
						<input 
							type="<?php echo esc_attr( $has_multiple_correct_answer ? 'checkbox' : 'radio' ); ?>"
							class="<?php echo esc_attr( $has_multiple_correct_answer ? 'tutor-checkbox' : 'tutor-radio' ); ?>"
							id="<?php echo esc_attr( $question['question_id'] ) . esc_attr( $index ); ?>"
							name="<?php echo esc_attr( $field_name ); ?>"
							value="<?php echo esc_attr( $answer['answer_id'] ); ?>"
							x-bind="<?php echo esc_attr( $register_attr ); ?>"
							<?php if ( $show_correct_answers ) : ?>
								disabled
							<?php endif; ?>
						>
						<label 
							for="<?php echo esc_attr( $question['question_id'] ) . esc_attr( $index ); ?>"
						>
							<?php echo esc_html( $answer['answer_title'] ); ?>
						</label>
					</div>
				</div>
				<?php if ( $has_image( $answer ) ) : ?>
					<img src="<?php echo esc_url( wp_get_attachment_image_url( $answer['image_id'], 'full' ) ); ?>" alt="<?php echo esc_attr( $answer['answer_title'] ); ?>">
					<div data-title>
						<?php echo esc_html( $answer['answer_title'] ); ?>
					</div>
				<?php endif; ?>
			</label>
		<?php endforeach; ?>
	</div>
	<div
		class="tutor-quiz-questions-error"
		x-cloak
	x-show="errors?.['<?php echo esc_attr( $field_name ); ?>']?.message"
	x-text="errors?.['<?php echo esc_attr( $field_name ); ?>']?.message"
	></div>

	<?php
	tutor_load_template(
		'learning-area.quiz.question-explanation',
		array(
			'answer_explanation' => $question['answer_explanation'],
			'question_id'        => $question['question_id'],
		)
	);
	?>
</div>
