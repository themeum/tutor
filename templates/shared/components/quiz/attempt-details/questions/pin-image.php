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

$background_url = $correct_answer ? QuizModel::get_answer_image_url( $correct_answer ) : '';
$reference_mask = $correct_answer && ! empty( $correct_answer->answer_two_gap_match ) ? (string) $correct_answer->answer_two_gap_match : '';
$has_reference  = false !== wp_http_validate_url( $reference_mask );

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

<div class="tutor-quiz-question-options">
	<div class="tutor-pin-image-given-answer">
		<p class="tutor-fs-7 tutor-fw-medium tutor-color-black tutor-mb-8">
			<?php esc_html_e( 'Submitted pin vs correct zone:', 'tutor' ); ?>
		</p>

		<?php if ( $background_url ) : ?>
			<div class="tutor-pin-image-layered">
				<img src="<?php echo esc_url( $background_url ); ?>" alt="" class="tutor-pin-image-bg" />
				<?php if ( $has_reference ) : ?>
					<img src="<?php echo esc_url( $reference_mask ); ?>" alt="" role="presentation" class="tutor-pin-image-overlay" />
				<?php endif; ?>
				<?php if ( $coords ) : ?>
					<span
						class="tutor-pin-image-marker"
						style="--tutor-pin-x: <?php echo esc_attr( $coords['x'] * 100 ); ?>%; --tutor-pin-y: <?php echo esc_attr( $coords['y'] * 100 ); ?>%;"
					></span>
				<?php endif; ?>
			</div>
			<div class="tutor-fs-7 tutor-color-secondary tutor-mt-8">
				<?php if ( ! $coords ) : ?>
					<?php esc_html_e( 'No pin submitted.', 'tutor' ); ?>
				<?php elseif ( ! $has_reference ) : ?>
					<?php esc_html_e( 'Correct answer zone is not available.', 'tutor' ); ?>
				<?php else : ?>
					<?php esc_html_e( 'The marker shows your submitted pin and the overlay shows the correct zone.', 'tutor' ); ?>
				<?php endif; ?>
			</div>
		<?php elseif ( $has_reference ) : ?>
			<img src="<?php echo esc_url( $reference_mask ); ?>" alt="" class="tutor-pin-image-single" />
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
