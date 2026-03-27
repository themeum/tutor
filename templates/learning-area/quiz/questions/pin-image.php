<?php
/**
 * Tutor quiz Pin on Image question (learning area).
 *
 * @package Tutor\Templates
 * @subpackage LearningArea\Quiz\Questions
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use Tutor\Models\QuizModel;

if ( ! isset( $question ) || ! is_array( $question ) ) {
	return;
}

$question_id = (int) ( $question['question_id'] ?? 0 );
if ( $question_id <= 0 ) {
	return;
}

global $tutor_is_started_quiz;

$attempt_id = 0;
$quiz_id    = 0;
if ( is_object( $tutor_is_started_quiz ) ) {
	$attempt_id = isset( $tutor_is_started_quiz->attempt_id ) ? (int) $tutor_is_started_quiz->attempt_id : 0;
	$quiz_id    = isset( $tutor_is_started_quiz->quiz_id ) ? (int) $tutor_is_started_quiz->quiz_id : 0;
}

$answers = isset( $question['question_answers'] ) && is_array( $question['question_answers'] ) ? $question['question_answers'] : array();
$answer  = ! empty( $answers ) ? reset( $answers ) : null;

if ( ! is_array( $answer ) ) {
	return;
}

// Signal Pro fallback renderer to skip duplicate output for this question.
if ( ! isset( $GLOBALS['tutor_learning_area_pin_image_rendered'] ) || ! is_array( $GLOBALS['tutor_learning_area_pin_image_rendered'] ) ) {
	$GLOBALS['tutor_learning_area_pin_image_rendered'] = array();
}
$GLOBALS['tutor_learning_area_pin_image_rendered'][ $question_id ] = true;

// Request script enqueue from Pro so existing asset/hook controls remain centralized.
do_action( 'tutor_enqueue_pin_image_question_script' );

$bg_image_url = '';
if ( isset( $answer['image_id'] ) ) {
	$bg_image_url = QuizModel::get_answer_image_url( (object) $answer );
}

$question_type          = (string) ( $question['question_type'] ?? 'pin_image' );
$question_settings      = isset( $question['question_settings'] ) && is_array( $question['question_settings'] ) ? $question['question_settings'] : array();
$answer_is_required     = isset( $question_settings['answer_required'] ) && '1' === (string) $question_settings['answer_required'];
$is_reveal_mode         = 'reveal' === tutor_utils()->get_quiz_option( $quiz_id, 'feedback_mode', '' );
$instructor_mask        = ! empty( $answer['answer_two_gap_match'] ) ? (string) $answer['answer_two_gap_match'] : '';
$instructor_mask_is_url = false !== wp_http_validate_url( $instructor_mask );

$wrapper_id     = 'tutor-pin-image-question-' . $question_id;
$image_id       = 'tutor-pin-image-bg-' . $question_id;
$pin_x_input_id = 'tutor-pin-image-x-' . $question_id;
$pin_y_input_id = 'tutor-pin-image-y-' . $question_id;

$pin_x_field_name = sprintf( '%s[answers][pin][x]', $question_field_name_base ?? '' );
$pin_y_field_name = sprintf( '%s[answers][pin][y]', $question_field_name_base ?? '' );
$register_rules   = '';
if ( $answer_is_required ) {
	$register_rules = ", { required: '" . esc_js( $required_message ) . "' }";
}
$pin_x_register_attr = "register('{$pin_x_field_name}'{$register_rules})";
$pin_y_register_attr = "register('{$pin_y_field_name}'{$register_rules})";
?>

<div
	id="<?php echo esc_attr( $wrapper_id ); ?>"
	class="quiz-question-ans-choice-area tutor-mt-40 tutor-pin-image-question question-type-<?php echo esc_attr( $question_type ); ?> <?php echo esc_attr( $answer_is_required ? 'quiz-answer-required' : '' ); ?>"
	data-question-type="<?php echo esc_attr( $question_type ); ?>"
>
	<?php if ( $bg_image_url ) : ?>
		<div class="tutor-pin-image-wrapper">
			<img
				id="<?php echo esc_attr( $image_id ); ?>"
				src="<?php echo esc_url( $bg_image_url ); ?>"
				alt="<?php esc_attr_e( 'Pin on image question', 'tutor' ); ?>"
			/>
			<span class="tutor-pin-image-marker" aria-hidden="true"></span>
		</div>
		<?php if ( $is_reveal_mode && $instructor_mask_is_url ) : ?>
			<div class="tutor-pin-image-reference-wrapper tutor-d-none tutor-mt-24" aria-hidden="true">
				<p class="tutor-fs-7 tutor-fw-medium tutor-color-black tutor-mb-12">
					<?php esc_html_e( 'Reference (correct answer zone):', 'tutor' ); ?>
				</p>
				<div class="tutor-pin-image-reference-inner">
					<img
						class="tutor-pin-image-reference-bg"
						src="<?php echo esc_url( $bg_image_url ); ?>"
						alt="<?php esc_attr_e( 'Reference background', 'tutor' ); ?>"
					/>
					<img
						class="tutor-pin-image-reference-mask"
						src="<?php echo esc_url( $instructor_mask ); ?>"
						alt=""
						role="presentation"
					/>
				</div>
			</div>
		<?php endif; ?>
	<?php else : ?>
		<p class="tutor-fs-7 tutor-color-secondary">
			<?php esc_html_e( 'No background image configured for this Pin on Image question.', 'tutor' ); ?>
		</p>
	<?php endif; ?>

	<input
		type="hidden"
		id="<?php echo esc_attr( $pin_x_input_id ); ?>"
		name="<?php echo esc_attr( $pin_x_field_name ); ?>"
		value=""
		x-bind="<?php echo esc_attr( $pin_x_register_attr ); ?>"
	/>
	<input
		type="hidden"
		id="<?php echo esc_attr( $pin_y_input_id ); ?>"
		name="<?php echo esc_attr( $pin_y_field_name ); ?>"
		value=""
		x-bind="<?php echo esc_attr( $pin_y_register_attr ); ?>"
	/>

	<p class="tutor-fs-7 tutor-color-secondary tutor-mt-12">
		<?php esc_html_e( 'Click on the image to place your pin. You can change it by clicking again. Your answer will be saved when you submit the quiz.', 'tutor' ); ?>
	</p>
</div>
