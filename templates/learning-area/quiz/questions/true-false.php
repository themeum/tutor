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
use Tutor\Components\SvgIcon;

$field_name     = $question_field_name_base ?? '';
$register_rules = '';
if ( $answer_is_required ) {
	$register_rules = ", { required: '" . esc_js( $required_message ) . "' }";
}
$register_attr = "register('{$field_name}'{$register_rules})";

?>

<div class="tutor-quiz-question-options">
	<?php foreach ( $question['question_answers'] as $answer ) : ?>
		<label 
			class="tutor-quiz-question-option"
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
			>
			<?php SvgIcon::make()->name( $answer['is_correct'] ? Icon::CHECK_2 : Icon::CROSS )->size( 20 )->render(); ?>
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
