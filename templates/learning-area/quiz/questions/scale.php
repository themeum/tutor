<?php
/**
 * Scale Question
 *
 * New learning-area frontend for the Scale quiz type.
 * The backend grading and data contract are handled by Tutor Pro.
 *
 * @package Tutor\Templates
 * @subpackage LearningArea\Quiz\Questions
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use TUTOR\Quiz as QuizClass;

global $tutor_is_started_quiz;

// Mark that the new learning-area scale template has rendered for this question
// so Tutor Pro can skip its legacy renderer when appropriate.
$question_id = isset( $question['question_id'] ) ? (int) $question['question_id'] : 0;
if ( $question_id > 0 ) {
	if ( ! isset( $GLOBALS['tutor_learning_area_scale_rendered'] ) || ! is_array( $GLOBALS['tutor_learning_area_scale_rendered'] ) ) {
		$GLOBALS['tutor_learning_area_scale_rendered'] = array();
	}
	$GLOBALS['tutor_learning_area_scale_rendered'][ $question_id ] = true;
}

$attempt_id = ( is_object( $tutor_is_started_quiz ) && isset( $tutor_is_started_quiz->attempt_id ) ) ? (int) $tutor_is_started_quiz->attempt_id : 0;

// Field name used by TutorCore form + PHP submit handler.
$field_name_base = $question_field_name_base ?? '';
$field_name      = $field_name_base . '[answers][scale][value]';
$register_attr   = "register('{$field_name}')";

// Compatibility: $question here is an array prepared by Quiz::render_question.
$question_type = isset( $question['question_type'] ) ? (string) $question['question_type'] : 'scale';
$answers       = isset( $question['question_answers'] ) && is_array( $question['question_answers'] ) ? $question['question_answers'] : array();

$answer = ! empty( $answers ) ? (object) $answers[0] : null;

if ( ! $answer ) {
	return;
}

// Determine quiz feedback mode for reveal behaviour.
$quiz_id        = ( is_object( $tutor_is_started_quiz ) && isset( $tutor_is_started_quiz->quiz_id ) ) ? (int) $tutor_is_started_quiz->quiz_id : 0;
$is_reveal_mode = $quiz_id > 0 && QuizClass::QUIZ_FEEDBACK_MODE_REVEAL === tutor_utils()->get_quiz_option( $quiz_id, 'feedback_mode', '' );

$target_json = ! empty( $answer->answer_two_gap_match ) ? $answer->answer_two_gap_match : '';
$target      = is_string( $target_json ) ? json_decode( stripslashes( $target_json ), true ) : null;
$has_correct = is_array( $target ) && isset( $target['value'] );

// Get scale configuration from answer settings or use defaults.
$scale_config     = is_array( $target ) && isset( $target['config'] ) ? $target['config'] : array();
$min_value        = isset( $scale_config['min'] ) ? (float) $scale_config['min'] : 0;
$max_value        = isset( $scale_config['max'] ) ? (float) $scale_config['max'] : 100;
$step             = isset( $scale_config['step'] ) ? (float) $scale_config['step'] : 1;
$default_val      = isset( $scale_config['defaultValue'] ) ? (float) $scale_config['defaultValue'] : ( $min_value + $max_value ) / 2;
$px_per_unit      = isset( $scale_config['pxPerUnit'] ) ? (float) $scale_config['pxPerUnit'] : 10;
$label_every      = isset( $scale_config['labelEvery'] ) ? (float) $scale_config['labelEvery'] : max( 1, ( $max_value - $min_value ) / 10 );
$minor_tick_every = isset( $scale_config['minorTickEvery'] ) ? (float) $scale_config['minorTickEvery'] : max( 1, ( $max_value - $min_value ) / 50 );
$precision        = isset( $scale_config['precision'] ) ? (int) $scale_config['precision'] : ( ( $step < 1 ) ? 2 : 0 );

// Unique DOM IDs for this question instance.
$wrapper_id   = 'tutor-scale-question-' . $question_id;
$container_id = 'tutor-scale-container-' . $question_id;
$scale_id     = 'tutor-scale-' . $question_id;
$bubble_id    = 'tutor-scale-bubble-' . $question_id;
$input_id     = 'tutor-scale-value-' . $question_id;

/**
 * Allow Tutor Pro to enqueue the scale-question script for the new frontend.
 *
 * @since 4.0.0
 */
do_action( 'tutor_enqueue_scale_question_script' );
?>

<div
	id="<?php echo esc_attr( $wrapper_id ); ?>"
	class="quiz-question-ans-choice-area tutor-mt-40 tutor-scale-question question-type-<?php echo esc_attr( $question_type ); ?> <?php echo esc_html( $answer_is_required ? 'quiz-answer-required' : '' ); ?>"
	data-question-type="<?php echo esc_attr( $question_type ); ?>"
	data-question-id="<?php echo esc_attr( (string) $question_id ); ?>"
	data-scale-config="
		<?php
		echo esc_attr(
			wp_json_encode(
				array(
					'min'            => $min_value,
					'max'            => $max_value,
					'step'           => $step,
					'defaultValue'   => $default_val,
					'pxPerUnit'      => $px_per_unit,
					'labelEvery'     => $label_every,
					'minorTickEvery' => $minor_tick_every,
					'precision'      => $precision,
				)
			)
		);
		?>
		"
>
	<div class="tutor-scale-slider-wrapper">
		<div class="tutor-scale-bubble" id="<?php echo esc_attr( $bubble_id ); ?>">
			<div class="tutor-scale-bubble-value"><?php echo esc_html( $default_val ); ?></div>
			<div class="tutor-scale-bubble-pointer"></div>
		</div>

		<div class="tutor-scale-container" id="<?php echo esc_attr( $container_id ); ?>" aria-label="<?php esc_attr_e( 'Interactive scale: drag to select your answer value.', 'tutor' ); ?>">
			<div class="tutor-scale" id="<?php echo esc_attr( $scale_id ); ?>">
				<!-- Ticks will be generated by JavaScript -->
			</div>
		</div>

		<div class="tutor-scale-instructions">
			<div class="tutor-scale-hand-icon" aria-hidden="true">
				<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M9 11V6C9 5.44772 9.44772 5 10 5C10.5523 5 11 5.44772 11 6V11M9 11H11M9 11V10M11 11V10M7 13V8C7 7.44772 7.44772 7 8 7C8.55228 7 9 7.44772 9 8V10M13 11V7C13 6.44772 13.4477 6 14 6C14.5523 6 15 6.44772 15 7V11M15 11V13C15 13.5304 15.2107 14.0391 15.5858 14.4142L17.2929 16.1213C17.9229 16.7513 18 17.2 18 18C18 19.1046 17.1046 20 16 20H11.382C10.5346 20 9.70694 19.7416 9.01155 19.2596L6 17.5V13.5C6 12.6716 6.67157 12 7.5 12C8.32843 12 9 12.6716 9 13.5V10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
				</svg>
			</div>
			<span class="tutor-scale-instruction-text"><?php esc_html_e( 'Drag the scale to select your answer', 'tutor' ); ?></span>
		</div>
	</div>

	<?php if ( $is_reveal_mode && $has_correct ) : ?>
		<?php $correct_value = (float) $target['value']; ?>
		<div class="tutor-scale-reference-wrapper tutor-d-none tutor-mt-24" aria-hidden="true">
			<p class="tutor-fs-7 tutor-fw-medium tutor-color-black tutor-mb-12">
				<?php esc_html_e( 'Reference (correct answer):', 'tutor' ); ?>
			</p>
			<p class="tutor-fs-7 tutor-color-secondary">
				<?php
				/* translators: %s: correct scale value. */
				$correct_value_text = sprintf(
					// phpcs:ignore WordPress.WP.I18n.MissingTranslatorsComment
					esc_html__( 'Correct value: %s', 'tutor' ),
					$correct_value
				);
				echo esc_html( $correct_value_text );
				?>
			</p>
		</div>
	<?php endif; ?>

	<input
		type="hidden"
		id="<?php echo esc_attr( $input_id ); ?>"
		name="<?php echo esc_attr( $field_name ); ?>"
		x-bind="<?php echo esc_attr( $register_attr ); ?>"
		value=""
	/>

	<p class="tutor-fs-7 tutor-color-secondary tutor-mt-12">
		<?php esc_html_e( 'Drag the scale to select your answer. Your selection will be saved when you submit the quiz.', 'tutor' ); ?>
	</p>
</div>

