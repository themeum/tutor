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
use Tutor\Components\SvgIcon;
use Tutor\Components\Constants\Color;
use Tutor\Components\Button;
use Tutor\Components\Constants\Size;
use Tutor\Components\Constants\Variant;

$answer_field_name = ( $question_field_name_base ?? '' ) . '[answers][]';
$register_rules    = '';
$question          = $question ?? array();
$draggable_answers = $question['question_randomized_answers'] ?? $question['question_answers'] ?? array();
$is_image_matching = isset( $question['question_settings']['is_image_matching'] ) && '1' === (string) $question['question_settings']['is_image_matching'];
if ( $answer_is_required ?? false ) {
	$register_rules = ", { validate: (value) => Array.isArray(value) && value.every((item) => item) || '" . esc_js( $required_message ?? '' ) . "' }";
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
		data-image-matching="<?php echo esc_attr( $is_image_matching ? '1' : '0' ); ?>"
	>
		<?php foreach ( $question['question_answers'] as $answer ) : ?>
			<div class="tutor-quiz-question-option">
				<?php if ( $is_image_matching && ! empty( $answer['image_id'] ) ) : ?>
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
					tabindex="0"
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
									'class'          => 'tutor-force-hidden',
									'@click.prevent' => 'clearDropZone',
									'aria-label'     => __( 'Clear matched option', 'tutor' ),
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
			<?php SvgIcon::make()->name( Icon::DRAG )->size( 20 )->color( Color::SECONDARY )->render(); ?>
			<span class="tutor-text-small tutor-font-medium"><?php esc_html_e( 'Drag from here', 'tutor' ); ?></span>
		</div>
		<div class="tutor-quiz-question-options">
			<?php foreach ( $draggable_answers as $answer ) : ?>
				<?php
				$draggable_title = $is_image_matching
					? $answer['answer_title'] : $answer['answer_two_gap_match'];
				?>
				<div
					class="tutor-quiz-question-option"
					data-option="draggable"
					data-id="<?php echo esc_attr( $answer['answer_id'] ); ?>"
				>
					<div data-title>
						<?php echo esc_html( $draggable_title ); ?>
					</div>
					<button type="button" data-grab-handle tabindex="-1" aria-label="<?php esc_attr_e( 'Drag matching option', 'tutor' ); ?>">
						<?php SvgIcon::make()->name( Icon::GRAB_HANDLE )->size( 24 )->render(); ?>
					</button>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</div>

