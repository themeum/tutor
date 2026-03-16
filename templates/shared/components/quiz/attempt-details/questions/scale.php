<?php
/**
 * Attempt details Scale (read-only) with visual scale.
 *
 * @package Tutor\Templates
 * @subpackage LearningArea\Quiz\AttemptDetails
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use Tutor\Models\QuizModel;

if ( ! isset( $question ) || ! is_object( $question ) ) {
	return;
}

// Allow Tutor Pro to enqueue the interactive scale script when available.
do_action( 'tutor_enqueue_scale_question_script' );

$attempt_answer = isset( $attempt_answer ) && is_object( $attempt_answer ) ? $attempt_answer : null;
$question_id    = (int) ( $question->question_id ?? 0 );

// Resolve student-selected value.
$student_value = null;
if ( $attempt_answer ) {
	$given_raw = $attempt_answer->given_answer ?? '';
	if ( is_string( $given_raw ) && '' !== $given_raw ) {
		$student_data = json_decode( stripslashes( $given_raw ), true );
		if ( is_array( $student_data ) && isset( $student_data['value'] ) ) {
			$student_value = (float) $student_data['value'];
		}
	}
}

// Resolve correct value and scale configuration from question answers.
$correct_value = null;
$scale_config  = array();

if ( $question_id > 0 ) {
	$answers = QuizModel::get_question_answers( $question_id, 'scale' );
	if ( ! empty( $answers ) && ! empty( $answers[0]->answer_two_gap_match ) ) {
		$target_json = $answers[0]->answer_two_gap_match;
		$target      = json_decode( stripslashes( (string) $target_json ), true );
		if ( is_array( $target ) ) {
			if ( isset( $target['value'] ) ) {
				$correct_value = (float) $target['value'];
			}
			if ( isset( $target['config'] ) && is_array( $target['config'] ) ) {
				$scale_config = $target['config'];
			}
		}
	}
}

// Mirror defaults from Pro scale-question.php for consistent UI.
$min_value        = isset( $scale_config['min'] ) ? (float) $scale_config['min'] : 0;
$max_value        = isset( $scale_config['max'] ) ? (float) $scale_config['max'] : 100;
$step             = isset( $scale_config['step'] ) ? (float) $scale_config['step'] : 1;
$default_val      = null !== $correct_value ? $correct_value : ( ( null !== $student_value ? $student_value : ( $min_value + $max_value ) / 2 ) );
$px_per_unit      = isset( $scale_config['pxPerUnit'] ) ? (float) $scale_config['pxPerUnit'] : 10;
$label_every      = isset( $scale_config['labelEvery'] ) ? (float) $scale_config['labelEvery'] : max( 1, ( $max_value - $min_value ) / 10 );
$minor_tick_every = isset( $scale_config['minorTickEvery'] ) ? (float) $scale_config['minorTickEvery'] : max( 1, ( $max_value - $min_value ) / 50 );
$precision        = isset( $scale_config['precision'] ) ? (int) $scale_config['precision'] : ( ( $step < 1 ) ? 2 : 0 );

// Determine correctness status for styling (Tutor Pro sets status for scale).
$status_cls = 'tutor-scale-question--summary-wrong';
if ( $attempt_answer ) {
	$answer_status = QuizModel::get_attempt_answer_status( $attempt_answer );
	if ( 'correct' === $answer_status ) {
		$status_cls = 'tutor-scale-question--summary-correct';
	}
}

// Unique DOM IDs for this question instance.
$wrapper_id   = 'tutor-scale-question-summary-core-' . $question_id;
$container_id = 'tutor-scale-container-summary-core-' . $question_id;
$scale_id     = 'tutor-scale-summary-core-' . $question_id;
$bubble_id    = 'tutor-scale-bubble-summary-core-' . $question_id;
$input_id     = 'tutor-scale-value-' . $question_id;
?>

<div class="tutor-quiz-question-options">
	<div class="tutor-quiz-review-scale-wrapper">
		<p class="tutor-quiz-review-col-title">
			<?php esc_html_e( 'Scale answer:', 'tutor' ); ?>
		</p>

		<div
			id="<?php echo esc_attr( $wrapper_id ); ?>"
			class="tutor-scale-question tutor-scale-question--summary <?php echo esc_attr( $status_cls ); ?>"
			data-question-type="scale"
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
						'readOnly'       => true,
						'summaryMode'    => true,
						// Bubble shows correct value; selectedValue marks student choice tick; correctValue marks reference tick.
						'selectedValue'  => null !== $student_value ? (float) $student_value : null,
						'correctValue'   => null !== $correct_value ? (float) $correct_value : null,
					)
				)
			);
			?>
			"
		>
			<div class="tutor-scale-slider-wrapper">
				<div class="tutor-scale-bubble" id="<?php echo esc_attr( $bubble_id ); ?>">
					<div class="tutor-scale-bubble-value">
						<?php
						if ( null !== $student_value ) {
							echo esc_html( (string) $student_value );
						} else {
							esc_html_e( '—', 'tutor' );
						}
						?>
					</div>
					<div class="tutor-scale-bubble-pointer"></div>
				</div>

				<div class="tutor-scale-container" id="<?php echo esc_attr( $container_id ); ?>" aria-hidden="true">
					<div class="tutor-scale" id="<?php echo esc_attr( $scale_id ); ?>">
						<!-- Ticks will be generated by JavaScript -->
					</div>
				</div>
			</div>

			<input type="hidden" id="<?php echo esc_attr( $input_id ); ?>" value="" />
		</div>
	</div>
</div>

