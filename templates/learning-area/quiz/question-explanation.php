<?php
/**
 * Quiz answer explanation.
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use TUTOR\Icon;

global $tutor_is_started_quiz;

$answer_explanation = $answer_explanation ?? '';
$answer_explanation = is_string( $answer_explanation ) ? trim( $answer_explanation ) : '';
$question_id        = $question_id ?? 0;
$panel_id           = 'tutor-quiz-explanation-panel-' . (int) $question_id;
$trigger_id         = 'tutor-quiz-explanation-trigger-' . (int) $question_id;
$quiz_id            = $tutor_is_started_quiz->quiz_id ?? 0;
$feedback_mode      = $quiz_id ? tutor_utils()->get_quiz_option( $quiz_id, 'feedback_mode', '' ) : '';
$is_reveal_mode     = 'reveal' === $feedback_mode;

if ( '' === $answer_explanation || ! $is_reveal_mode ) {
	return;
}

?>

<div
	class="tutor-quiz-explanation"
	data-quiz-explanation
	x-data="{ open: false }"
	:class="{ 'is-open': open }"
>
	<button
		id="<?php echo esc_attr( $trigger_id ); ?>"
		type="button"
		class="tutor-quiz-explanation-trigger"
		data-quiz-explanation-toggle
		:aria-expanded="open"
		aria-controls="<?php echo esc_attr( $panel_id ); ?>"
		@click="open = !open"
	>
		<span class="tutor-quiz-explanation-icon">
			<?php tutor_utils()->render_svg_icon( Icon::BULB_2, 24, 24 ); ?>
		</span>
		<span class="tutor-quiz-explanation-title">
			<?php esc_html_e( 'Answer Explanation', 'tutor' ); ?>
		</span>
		<span class="tutor-quiz-explanation-chevron" aria-hidden="true">
			<?php tutor_utils()->render_svg_icon( Icon::CHEVRON_DOWN, 20, 20 ); ?>
		</span>
	</button>
	<div
		id="<?php echo esc_attr( $panel_id ); ?>"
		class="tutor-quiz-explanation-panel"
		role="region"
		aria-labelledby="<?php echo esc_attr( $trigger_id ); ?>"
		x-show="open"
		x-collapse.duration.300ms
	>
		<div class="tutor-quiz-explanation-body">
			<?php echo wp_kses_post( $answer_explanation ); ?>
		</div>
	</div>
</div>
