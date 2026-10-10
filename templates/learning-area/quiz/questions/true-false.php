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

use TUTOR\Quiz;
use TUTOR\Icon;

$field_name         = $question_field_name_base ?? '';
$register_rules     = '';
$question           = (array) ( $question ?? array() );
$answer_is_required = $answer_is_required ?? false;
$required_message   = $required_message ?? '';
if ( $answer_is_required ) {
	$register_rules = ", { required: '" . esc_js( $required_message ) . "' }";
}
$register_attr = "register('{$field_name}'{$register_rules})";

$committed_answer = $committed_answer ?? null;
$is_committed     = ! empty( $committed_answer );
$given_answers    = array();
if ( $is_committed ) {
	$unserialized  = maybe_unserialize( $committed_answer->given_answer );
	$given_answers = is_array( $unserialized ) ? array_map( 'strval', $unserialized ) : array( (string) $unserialized );
}

?>

<div class="tutor-quiz-question-options">
	<?php if ( tutor_utils()->count( $question['question_answers'] ) ) : ?>
		<?php foreach ( $question['question_answers'] ?? array() as $answer ) : ?>
			<?php
			$answer_id_str = (string) ( $answer['answer_id'] ?? '' );
			$is_checked    = $is_committed && in_array( $answer_id_str, $given_answers, true );
			$option_state  = '';
			if ( $is_committed && ! empty( $quiz_settings['enable_answer_reveal'] ) ) {
				if ( ! empty( $answer['is_correct'] ) ) {
					$option_state = 'correct';
				} elseif ( $is_checked ) {
					$option_state = 'incorrect';
				}
			}
			?>
			<label 
				class="tutor-quiz-question-option"
				<?php if ( $option_state ) : ?>
					data-option="<?php echo esc_attr( $option_state ); ?>"
				<?php endif; ?>
				tabindex="0"
				@keydown.space.prevent="$el.querySelector('input').click()"
				@keydown.enter.prevent="$el.querySelector('input').click()"
			>
				<input
					class="tutor-hidden"
					type="radio"
					tabindex="-1"
					name="<?php echo esc_attr( $field_name ); ?>"
					value="<?php echo esc_attr( $answer['answer_id'] ); ?>"
					x-bind="<?php echo esc_attr( $register_attr ); ?>"
					<?php if ( $is_committed ) : ?>
						disabled="disabled"
						<?php echo $is_checked ? 'checked="checked"' : ''; ?>
					<?php endif; ?>
				>
				<?php echo esc_html( Quiz::sanitize_quiz_content( $answer['answer_title'] ?? '' ) ); ?>
			</label>
		<?php endforeach; ?>
	<?php endif; ?>
</div>


<div
	class="tutor-quiz-questions-error"
	x-cloak
	x-show="errors?.['<?php echo esc_attr( $field_name ); ?>']?.message"
	x-text="errors?.['<?php echo esc_attr( $field_name ); ?>']?.message"
></div>
