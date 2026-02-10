<?php
/**
 * Matching
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use TUTOR\Icon;
use Tutor\Components\Button;
use Tutor\Components\Constants\Size;
use Tutor\Components\Constants\Variant;

global $tutor_is_started_quiz;

$default_question = array(
	'index'                => 1,
	'question_id'          => 0,
	'question_title'       => '',
	'question_description' => '',
	'question_type'        => 'matching',
	'answer_required'      => true,
	'question_mark'        => 10,
	'answer_explanation'   => '',
	'question_settings'    => array(
		'answer_required'    => '0',
		'question_mark'      => '1',
		'question_type'      => 'matching',
		'randomize_question' => '0',
		'show_question_mark' => '1',
		'is_image_matching'  => '0',
	),
);

$question           = wp_parse_args( $question, $default_question );
$answer_field_name  = sprintf(
	'attempt[%d][quiz_question][%d][answers][]',
	$tutor_is_started_quiz->attempt_id,
	$question['question_id']
);
$answer_is_required = isset( $question['question_settings']['answer_required'] ) && '1' === $question['question_settings']['answer_required'];
$required_message   = __( 'The answer for this question is required', 'tutor' );
$register_rules     = '';
if ( $answer_is_required ) {
	$register_rules = ", { validate: (value) => Array.isArray(value) && value.every((item) => item) || '" . esc_js( $required_message ) . "' }";
}
$register_attr = "register('{$answer_field_name}'{$register_rules})";

?>

<div
	x-data="tutorQuestionMatching({
		questionId: 'question-<?php echo esc_attr( $question['question_id'] ); ?>',
		onDrop: (values) => setValue('<?php echo esc_attr( $answer_field_name ); ?>', values, { shouldDirty: true }),
		onClear: (values) => setValue('<?php echo esc_attr( $answer_field_name ); ?>', values, { shouldDirty: true }),
	})"
	class='tutor-flex tutor-flex-column tutor-gap-7 tutor-sm-gap-5'
>
	<div
		class="tutor-quiz-question-options"
		data-image-matching="<?php echo esc_attr( $question['question_settings']['is_image_matching'] ); ?>"
	>
		<?php foreach ( $question['question_answers'] as $answer ) : ?>
			<div class="tutor-quiz-question-option">
				<?php if ( $question['question_settings']['is_image_matching'] && ! empty( $answer['image_id'] ) ) : ?>
					<img src="<?php echo esc_url( wp_get_attachment_image_url( $answer['image_id'], 'full' ) ); ?>" alt="<?php echo esc_attr( $answer['answer_title'] ); ?>">
				<?php else : ?>
					<div data-title>
						<div class="tutor-quiz-question-option-number">
							<?php echo esc_html( $answer['answer_order'] ); ?>
						</div>
						<?php echo esc_html( $answer['answer_title'] ); ?>
					</div>
				<?php endif; ?>
				<div
					class="tutor-quiz-question-option-drop-zone"
					data-drop-placeholder-text="<?php echo esc_attr__( 'Drop here', 'tutor' ); ?>"
				>
					<input
						class="tutor-hidden"
						name="<?php echo esc_attr( $answer_field_name ); ?>"
						x-bind="<?php echo esc_attr( $register_attr ); ?>"
					>
					<span data-drop-placeholder class="tutor-text-subdued">
						<?php esc_html_e( 'Drop here', 'tutor' ); ?>
					</span>
					<?php
						Button::make()
							->variant( Variant::GHOST )
							->size( Size::X_SMALL )
							->icon( Icon::CROSS )
							->icon_only()
							->attrs(
								array(
									'class'          => 'tutor-hidden',
									'@click.prevent' => 'clearDropZone',
								)
							)
							->render();
					?>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
	<div
		class="tutor-quiz-questions-error"
		x-cloak
		x-show="errors?.['<?php echo esc_attr( $answer_field_name ); ?>']?.message"
		x-text="errors?.['<?php echo esc_attr( $answer_field_name ); ?>']?.message"
	></div>

	<div class="tutor-quiz-question-draggable">
		<div class="tutor-quiz-question-draggable-header">
			<?php tutor_utils()->render_svg_icon( Icon::DRAG, 20, 20, array( 'class' => 'tutor-icon-secondary' ) ); ?>
			<span class="tutor-text-small tutor-font-medium"><?php esc_html_e( 'Drag from here', 'tutor' ); ?></span>
		</div>
		<div class="tutor-quiz-question-options">
			<?php foreach ( $question['question_answers'] as $answer ) : ?>
				<div
					class="tutor-quiz-question-option"
					data-option="draggable"
					data-id="<?php echo esc_attr( $answer['answer_id'] ); ?>"
				>
					<div data-title>
						<?php echo esc_html( $answer['answer_two_gap_match'] ); ?>
					</div>
					<button type="button" data-grab-handle>
						<?php tutor_utils()->render_svg_icon( Icon::GRAB_HANDLE, 24, 24 ); ?>
					</button>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</div>
