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
use Tutor\Components\SvgIcon;

$question          = $question ?? array();
$answer_field_name = ( $question_field_name_base ?? '' ) . '[answers][]';
$register_rules    = '';
if ( $answer_is_required ?? false ) {
	$register_rules = ", { validate: (value) => Array.isArray(value) && value.length > 0 || '" . esc_js( $required_message ?? '' ) . "' }";
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
		<?php foreach ( $question['question_answers'] as $index => $answer ) : ?>
			<div
				class="tutor-quiz-question-option"
				data-option="draggable"
				data-id="<?php echo esc_attr( $answer['answer_id'] ); ?>"
			>
				<div data-option-order="<?php echo esc_attr( $index + 1 ); ?>">
					<?php echo esc_html( $index + 1 ); ?>
				</div>
				<div data-title>
					<?php if ( ! empty( $answer['image_id'] ) ) : ?>
						<img src="<?php echo esc_url( wp_get_attachment_image_url( $answer['image_id'], 'full' ) ); ?>" alt="<?php echo esc_attr( $answer['answer_title'] ); ?>">
					<?php endif; ?>
					<?php echo esc_html( $answer['answer_title'] ); ?>
				</div>

				<button type="button" data-grab-handle tabindex="-1" aria-label="<?php esc_attr_e( 'Reorder item', 'tutor' ); ?>">
					<?php SvgIcon::make()->name( Icon::GRAB_HANDLE )->size( 40 )->render(); ?>
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
