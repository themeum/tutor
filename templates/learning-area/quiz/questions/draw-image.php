<?php
/**
 * Draw on Image (learning-area quiz attempt).
 *
 * Mirrors Tutor Pro draw-image markup and POST field names so existing
 * grading and draw-image-question.js work unchanged. Reveal-mode reference
 * uses the same CSS-mask tint pattern as Pin on Image (feat/quiz-type-pin-image).
 *
 * @package Tutor\Templates
 * @subpackage LearningArea\Quiz
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use TUTOR\Quiz as QuizClass;
use Tutor\Models\QuizModel;

global $tutor_is_started_quiz;

$question_id = (int) ( $question['question_id'] ?? 0 );
if ( $question_id > 0 ) {
	$GLOBALS['tutor_learning_area_draw_image_rendered'][ $question_id ] = true;
}

$answers = isset( $question['question_answers'] ) && is_array( $question['question_answers'] ) ? $question['question_answers'] : array();
$answer  = ! empty( $answers ) ? reset( $answers ) : null;

if ( ! $answer ) {
	return;
}

$answer_obj = is_array( $answer ) ? (object) $answer : $answer;

$bg_image_url = QuizModel::get_answer_image_url( $answer_obj );

$quiz_id_attempt = isset( $tutor_is_started_quiz->quiz_id ) ? (int) $tutor_is_started_quiz->quiz_id : 0;
$is_reveal_mode  = $quiz_id_attempt > 0 && QuizClass::QUIZ_FEEDBACK_MODE_REVEAL === tutor_utils()->get_quiz_option( $quiz_id_attempt, 'feedback_mode', '' );

$instructor_mask         = ! empty( $answer_obj->answer_two_gap_match ) ? (string) $answer_obj->answer_two_gap_match : '';
$instructor_mask         = trim( $instructor_mask );
$instructor_mask_is_url  = false !== wp_http_validate_url( $instructor_mask );
$instructor_mask_is_data =
	0 === strpos( $instructor_mask, 'data:image/' ) &&
	false !== strpos( $instructor_mask, ';base64,' );
$instructor_has_mask     = $instructor_mask_is_url || $instructor_mask_is_data;
$instructor_mask_css     = $instructor_mask_is_url ? esc_url_raw( $instructor_mask ) : $instructor_mask;

$question_type = 'draw_image';

$wrapper_id      = 'tutor-draw-image-question-' . $question_id;
$image_id        = 'tutor-draw-image-bg-' . $question_id;
$canvas_id       = 'tutor-draw-image-canvas-' . $question_id;
$hidden_input_id = 'tutor-draw-image-mask-' . $question_id;

$field_name          = ( $question_field_name_base ?? '' ) . '[answers][mask]';
$register_rules      = '';
$required_message_js = isset( $required_message ) ? (string) $required_message : __( 'The answer for this question is required', 'tutor' );
if ( $answer_is_required ) {
	$register_rules = ", { required: '" . esc_js( $required_message_js ) . "' }";
}
$register_attr = "register('{$field_name}'{$register_rules})";

/**
 * Fires when the learning-area draw-image template is rendered; Tutor Pro
 * hooks this to enqueue draw-image-question.js.
 *
 * @since 4.0.0
 */
do_action( 'tutor_enqueue_draw_image_question_script' );
?>

<div
	id="<?php echo esc_attr( $wrapper_id ); ?>"
	class="quiz-question-ans-choice-area tutor-mt-40 tutor-draw-image-question question-type-<?php echo esc_attr( $question_type ); ?> <?php echo $answer_is_required ? esc_attr( 'quiz-answer-required' ) : ''; ?>"
	data-question-type="<?php echo esc_attr( $question_type ); ?>"
>
	<?php if ( $bg_image_url ) : ?>
		<div class="tutor-draw-image-wrapper">
			<img
				id="<?php echo esc_attr( $image_id ); ?>"
				src="<?php echo esc_url( $bg_image_url ); ?>"
				alt="<?php esc_attr_e( 'Draw on image question', 'tutor' ); ?>"
			/>
			<canvas
				id="<?php echo esc_attr( $canvas_id ); ?>"
				class="tutor-draw-image-canvas"
			></canvas>
		</div>
		<?php if ( $is_reveal_mode && $instructor_has_mask ) : ?>
		<div class="tutor-draw-image-reference-wrapper tutor-d-none tutor-mt-24" aria-hidden="true">
			<p class="tutor-fs-7 tutor-fw-medium tutor-color-black tutor-mb-12">
				<?php esc_html_e( 'Reference (correct answer zone):', 'tutor' ); ?>
			</p>
			<div class="tutor-draw-image-reference-inner">
				<img
					class="tutor-draw-image-reference-bg"
					src="<?php echo esc_url( $bg_image_url ); ?>"
					alt="<?php esc_attr_e( 'Reference background', 'tutor' ); ?>"
				/>
				<span
					class="tutor-draw-image-reference-mask tutor-draw-image-reference-mask--tint"
					style="<?php echo esc_attr( '--tutor-draw-mask-url: url("' . $instructor_mask_css . '"); --tutor-draw-mask-bg: #04C98633;' ); ?>"
					role="presentation"
				></span>
			</div>
		</div>
		<?php endif; ?>
	<?php else : ?>
		<p class="tutor-fs-7 tutor-color-secondary">
			<?php esc_html_e( 'No background image configured for this Draw on Image question.', 'tutor' ); ?>
		</p>
	<?php endif; ?>

	<input
		type="hidden"
		id="<?php echo esc_attr( $hidden_input_id ); ?>"
		name="<?php echo esc_attr( $field_name ); ?>"
		value=""
		x-bind="<?php echo esc_attr( $register_attr ); ?>"
	/>

	<p class="tutor-fs-7 tutor-color-secondary tutor-mt-12">
		<?php esc_html_e( 'Draw a lasso around your answer: on desktop, move the pointer over the image first, then click and drag; on touch devices, draw directly. Your answer is saved when you submit the quiz.', 'tutor' ); ?>
	</p>
</div>

<div
	class="tutor-quiz-questions-error"
	x-cloak
	x-show="errors?.['<?php echo esc_attr( $field_name ); ?>']?.message"
	x-text="errors?.['<?php echo esc_attr( $field_name ); ?>']?.message"
></div>

<style>
	#<?php echo esc_html( $wrapper_id ); ?> .tutor-draw-image-wrapper {
		position: relative;
		display: inline-block;
	}

	#<?php echo esc_html( $wrapper_id ); ?> .tutor-draw-image-wrapper img {
		display: block;
		max-width: 100%;
		height: auto;
	}

	#<?php echo esc_html( $wrapper_id ); ?> .tutor-draw-image-canvas {
		position: absolute;
		top: 0;
		left: 0;
	}

	#<?php echo esc_html( $wrapper_id ); ?> .tutor-draw-image-reference-inner {
		position: relative;
		display: inline-block;
		overflow: hidden;
	}

	#<?php echo esc_html( $wrapper_id ); ?> .tutor-draw-image-reference-bg {
		display: block;
		max-width: 100%;
		height: auto;
	}

	#<?php echo esc_html( $wrapper_id ); ?> .tutor-draw-image-reference-mask {
		position: absolute;
		inset: 0;
		width: 100%;
		height: 100%;
	}

	#<?php echo esc_html( $wrapper_id ); ?> .tutor-draw-image-reference-mask--tint {
		display: block;
		background: var(--tutor-draw-mask-bg, #04C98633);
		-webkit-mask-image: var(--tutor-draw-mask-url);
		-webkit-mask-repeat: no-repeat;
		-webkit-mask-size: 100% 100%;
		mask-image: var(--tutor-draw-mask-url);
		mask-repeat: no-repeat;
		mask-size: 100% 100%;
		filter:
			drop-shadow(1px 0 0 #53B96A)
			drop-shadow(-1px 0 0 #53B96A)
			drop-shadow(0 1px 0 #53B96A)
			drop-shadow(0 -1px 0 #53B96A);
	}
</style>
