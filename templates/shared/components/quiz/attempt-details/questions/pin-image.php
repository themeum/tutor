<?php
/**
 * Attempt details Pin on Image (read-only).
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

$attempt_answer = isset( $attempt_answer ) && is_object( $attempt_answer ) ? $attempt_answer : null;
$pin_answers    = QuizModel::get_answers_by_quiz_question( (int) $question->question_id, false );
$pin_answers    = is_array( $pin_answers ) ? $pin_answers : array();
$correct_answer = ! empty( $pin_answers ) ? reset( $pin_answers ) : null;

$background_url     = $correct_answer ? QuizModel::get_answer_image_url( $correct_answer ) : '';
$reference_mask     = $correct_answer && ! empty( $correct_answer->answer_two_gap_match ) ? (string) $correct_answer->answer_two_gap_match : '';
$reference_mask     = trim( $reference_mask );
$reference_is_url   = false !== wp_http_validate_url( $reference_mask );
$reference_is_data  =
	0 === strpos( $reference_mask, 'data:image/' ) &&
	false !== strpos( $reference_mask, ';base64,' );
$has_reference      = $reference_is_url || $reference_is_data;
$reference_mask_css = $reference_is_url ? esc_url_raw( $reference_mask ) : $reference_mask;
$wrapper_id         = 'tutor-pin-image-attempt-' . (int) $question->question_id;

$coords = null;
if ( $attempt_answer && ! empty( $attempt_answer->given_answer ) ) {
	$given_answer = maybe_unserialize( $attempt_answer->given_answer );
	$decoded      = null;

	if ( is_array( $given_answer ) ) {
		$decoded = $given_answer;
	} elseif ( is_string( $given_answer ) ) {
		$decoded = json_decode( stripslashes( $given_answer ), true );
		if ( ! is_array( $decoded ) ) {
			$decoded = json_decode( $given_answer, true );
		}
	}

	if ( is_array( $decoded ) && isset( $decoded['pin'] ) && is_array( $decoded['pin'] ) ) {
		$decoded = $decoded['pin'];
	}

	if ( is_array( $decoded ) && isset( $decoded['x'], $decoded['y'] ) ) {
		$coords = array(
			'x' => max( 0.0, min( 1.0, (float) $decoded['x'] ) ),
			'y' => max( 0.0, min( 1.0, (float) $decoded['y'] ) ),
		);
	}
}
?>

<div id="<?php echo esc_attr( $wrapper_id ); ?>" class="tutor-quiz-question-options">
	<div class="tutor-pin-image-given-answer">

		<?php if ( $background_url ) : ?>
			<div class="tutor-pin-image-layered">
				<img src="<?php echo esc_url( $background_url ); ?>" alt="" class="tutor-pin-image-bg" />
				<?php if ( $has_reference ) : ?>
					<span
						class="tutor-pin-image-overlay tutor-pin-image-overlay--tint"
						style="<?php echo esc_attr( '--tutor-pin-mask-url: url("' . $reference_mask_css . '"); --tutor-pin-mask-bg: #04C98633;' ); ?>"
						role="presentation"
					></span>
				<?php endif; ?>
				<?php if ( $coords ) : ?>
					<span
						class="tutor-pin-image-marker"
						style="--tutor-pin-x: <?php echo esc_attr( $coords['x'] * 100 ); ?>%; --tutor-pin-y: <?php echo esc_attr( $coords['y'] * 100 ); ?>%;"
					></span>
				<?php endif; ?>
			</div>
		<?php elseif ( $has_reference ) : ?>
			<div class="tutor-pin-image-single tutor-pin-image-single--tint">
				<span
					class="tutor-pin-image-single-mask"
					style="<?php echo esc_attr( '--tutor-pin-mask-url: url("' . $reference_mask_css . '"); --tutor-pin-mask-bg: #04C98633;' ); ?>"
					role="presentation"
				></span>
			</div>
			<div class="tutor-fs-7 tutor-color-secondary tutor-mt-8">
				<?php esc_html_e( 'Background image not available; showing only the correct zone mask.', 'tutor' ); ?>
			</div>
		<?php elseif ( $coords ) : ?>
			<div class="tutor-fs-7 tutor-color-secondary">
				<?php esc_html_e( 'Pin submitted, but no background image found.', 'tutor' ); ?>
			</div>
		<?php else : ?>
			<div class="tutor-fs-7 tutor-color-secondary">
				<?php esc_html_e( 'No pin submitted.', 'tutor' ); ?>
			</div>
		<?php endif; ?>
	</div>
</div>
