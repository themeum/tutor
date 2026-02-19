<?php
/**
 * Ordering
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use TUTOR\Icon;

global $tutor_is_started_quiz;

$default_question = array(
	'index'                => 1,
	'question_id'          => 1,
	'question_title'       => '',
	'question_description' => '',
	'question_type'        => 'ordering',
	'answer_required'      => true,
	'question_mark'        => 10,
	'answer_explanation'   => '',
	'question_settings'    => array(
		'answer_required'    => '0',
		'question_mark'      => '1',
		'question_type'      => 'ordering',
		'randomize_question' => '0',
		'show_question_mark' => '1',
	),
);

$question          = wp_parse_args( $question, $default_question );
$answer_field_name = sprintf(
	'attempt[%d][quiz_question][%d][answers][]',
	$tutor_is_started_quiz->attempt_id,
	$question['question_id']
);
$register_rules    = '';
if ( $answer_is_required ) {
	$register_rules = ", { validate: (value) => Array.isArray(value) && value.length > 0 || '" . esc_js( $required_message ) . "' }";
}
$register_attr = "register('{$answer_field_name}'{$register_rules})";

?>

<div
	data-question-id="question-<?php echo esc_attr( $question['question_id'] ); ?>"
	x-data="tutorQuestionOrdering({
		questionId: 'question-<?php echo esc_attr( $question['question_id'] ); ?>',
		onOrder: (values) => setValue('<?php echo esc_attr( $answer_field_name ); ?>', values, { shouldDirty: true }),
	})"
>
	<div class="tutor-quiz-question-options">
		<input
			type="hidden"
			name="<?php echo esc_attr( $answer_field_name ); ?>"
			x-bind="<?php echo esc_attr( $register_attr ); ?>"
		>
		<?php foreach ( $question['question_answers'] as $answer ) : ?>
			<div
				class="tutor-quiz-question-option"
				data-option="draggable"
				data-id="<?php echo esc_attr( $answer['answer_id'] ); ?>"
			>
				<div data-option-order="<?php echo esc_attr( $answer['answer_order'] ); ?>">
					<?php echo esc_html( $answer['answer_order'] ); ?>
				</div>
				<div data-title>
					<?php if ( ! empty( $answer['image_id'] ) ) : ?>
						<img src="<?php echo esc_url( wp_get_attachment_image_url( $answer['image_id'], 'full' ) ); ?>" alt="<?php echo esc_attr( $answer['answer_title'] ); ?>">
					<?php endif; ?>
					<?php echo esc_html( $answer['answer_title'] ); ?>
				</div>

				<button type="button" data-grab-handle>
					<?php tutor_utils()->render_svg_icon( Icon::GRAB_HANDLE, 40, 40 ); ?>
				</button>
			</div>
		<?php endforeach; ?>
	</div>
	
	<div
		class="tutor-quiz-questions-error"
		x-cloak
		x-show="errors?.['<?php echo esc_attr( $answer_field_name ); ?>']?.message"
		x-text="errors?.['<?php echo esc_attr( $answer_field_name ); ?>']?.message"
	></div>
</div>
