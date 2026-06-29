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
$radio_required_message      = __( 'Please select an option to answer', 'tutor' );
$checkbox_required_message   = __( 'Please select at least one option to answer.', 'tutor' );
$field_name_base             = $question_field_name_base ?? '';
$field_name                  = $field_name_base . ( $has_multiple_correct_answer ? '[]' : '' );
$register_rules              = '';
if ( $answer_is_required ) {
	if ( $has_multiple_correct_answer ) {
		$register_rules = ", { validate: (value) => Array.isArray(value) && value.length > 0 || '" . esc_js( $checkbox_required_message ) . "' }";
	} else {
		$register_rules = ", { required: '" . esc_js( $radio_required_message ) . "' }";
	}
}
$register_attr = "register('{$field_name}'{$register_rules})";

?>

<div class="tutor-quiz-question-options">
	<?php foreach ( $question['question_answers'] as $index => $answer ) : ?>
		<label 
			class="tutor-quiz-question-option"
			tabindex="0"
			@keydown.space.prevent="$el.querySelector('input').click()"
			@keydown.enter.prevent="$el.querySelector('input').click()"
		>
			<div class="tutor-input-field <?php echo $has_image( $answer ) ? 'tutor-hidden' : ''; ?>">
				<div class="tutor-input-wrapper">
					<input 
						type="<?php echo esc_attr( $has_multiple_correct_answer ? 'checkbox' : 'radio' ); ?>"
						class="<?php echo esc_attr( $has_multiple_correct_answer ? 'tutor-checkbox' : 'tutor-radio' ); ?>"
						id="<?php echo esc_attr( $question['question_id'] ) . esc_attr( $index ); ?>"
						name="<?php echo esc_attr( $field_name ); ?>"
						value="<?php echo esc_attr( $answer['answer_id'] ); ?>"
						tabindex="-1"
						x-bind="<?php echo esc_attr( $register_attr ); ?>"
					>
					<label 
						class="tutor-label"
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
