<?php
/**
 * Tutor quiz image answering.
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use Tutor\Components\InputField;

global $tutor_is_started_quiz;

$default_question = array(
	'index'                => 1,
	'question_id'          => 0,
	'content_id'           => null,
	'quiz_id'              => 0,
	'question_title'       => '',
	'question_description' => '',
	'answer_explanation'   => '',
	'question_type'        => 'image_answering',
	'question_mark'        => '1.00',
	'question_settings'    => array(
		'answer_required'    => '0',
		'question_mark'      => '1',
		'question_type'      => 'image_answering',
		'randomize_question' => '0',
		'show_question_mark' => '0',
	),
);

$question           = wp_parse_args( $question, $default_question );
$answer_is_required = isset( $question['question_settings']['answer_required'] ) && '1' === $question['question_settings']['answer_required'];
$required_message   = __( 'The answer for this question is required', 'tutor' );
$field_name         = '';
$register_rules     = '';
if ( $answer_is_required ) {
	$register_rules = ", { required: '" . esc_js( $required_message ) . "' }";
}

?>

<div class="tutor-quiz-question-options">
	<?php foreach ( $question['question_answers'] as $index => $answer ) : ?>
		<div class="tutor-quiz-question-option">
			<img src="<?php echo esc_url( wp_get_attachment_image_url( $answer['image_id'], 'full' ) ); ?>" alt="<?php echo esc_attr( $answer['answer_title'] ); ?>">
			<?php
			$input_name    = 'attempt[' . $tutor_is_started_quiz->attempt_id . '][quiz_question][' . $question['question_id'] . '][answer_id][' . $answer['answer_id'] . ']';
			$rules_suffix  = $register_rules;
			$register_attr = "register('{$input_name}'{$rules_suffix})";

			if ( 0 === $index ) {
				$field_name = $input_name;
			}

			InputField::make()
				->name( $input_name )
				->clearable()
				->attr( 'x-bind', $register_attr )
				->placeholder( __( 'Write your answer here', 'tutor' ) )
				->render();
			?>
		</div>
	<?php endforeach; ?>
</div>
