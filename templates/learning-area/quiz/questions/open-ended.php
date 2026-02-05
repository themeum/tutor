<?php
/**
 * Openended Short Answer
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use Tutor\Components\InputField;
use Tutor\Components\Constants\InputType;

global $tutor_is_started_quiz;

$default_question = array(
	'index'             => 1,
	'question_id'       => 1,
	'question_title'    => '',
	'question_type'     => 'open_ended',
	'answer_required'   => true,
	'question_mark'     => 10,
	'question_settings' => array(
		'answer_required'    => '0',
		'question_mark'      => '1',
		'question_type'      => 'open_ended',
		'randomize_question' => '0',
		'show_question_mark' => '1',
	),
);

$question      = wp_parse_args( $question, $default_question );
$question_type = $question['question_settings']['question_type'] ?? 'open_ended';
$quiz_id       = $tutor_is_started_quiz->quiz_id ?? 0;

$quiz_options = $quiz_id ? tutor_utils()->get_quiz_option( $quiz_id ) : array();
$limit_key    = 'short_answer' === $question_type ? 'short_answer_characters_limit' : 'open_ended_answer_characters_limit';

$characters_limit    = (int) ( $quiz_options[ $limit_key ] ?? 0 );
$answer_is_required  = isset( $question['question_settings']['answer_required'] ) && '1' === $question['question_settings']['answer_required'];
$required_message    = __( 'The answer for this question is required', 'tutor' );
$field_name          = 'attempt[' . $tutor_is_started_quiz->attempt_id . '][quiz_question][' . $question['question_id'] . ']';
$register_rule_parts = array();
if ( $answer_is_required ) {
	$register_rule_parts[] = "required: '" . esc_js( $required_message ) . "'";
}
if ( $characters_limit > 0 ) {
	$max_length_message    = __( 'The answer exceeds the allowed character limit', 'tutor' );
	$register_rule_parts[] = "maxLength: { value: {$characters_limit}, message: '" . esc_js( $max_length_message ) . "' }";
}
$register_rules = '';
if ( ! empty( $register_rule_parts ) ) {
	$register_rules = ', { ' . implode( ', ', $register_rule_parts ) . ' }';
}
$register_attr = "register('{$field_name}'{$register_rules})";

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

	<?php $input_name = $field_name; ?>
	<div
		class="tutor-quiz-question-options"
		<?php if ( $characters_limit > 0 ) : ?>
			x-data="{ max: <?php echo esc_attr( $characters_limit ); ?>, remaining: <?php echo esc_attr( $characters_limit ); ?> }"
			x-effect="remaining = Math.max(0, max - (values?.['<?php echo esc_attr( $input_name ); ?>'] || '').length)"
		<?php endif; ?>
	>
		<?php
			$input_field = InputField::make()
				->type( InputType::TEXTAREA )
				->name( $input_name )
				->clearable()
				->attr( 'x-bind', $register_attr )
				->placeholder( __( 'Type your answer here', 'tutor' ) );

			$input_field->render();
		?>
		<?php if ( $characters_limit > 0 ) : ?>
			<p class="tutor-small tutor-text-subdued">
				<?php esc_html_e( 'Character Remaining: ', 'tutor' ); ?>
				<span x-text="remaining"></span>
			</p>
		<?php endif; ?>
	</div>
</div>
