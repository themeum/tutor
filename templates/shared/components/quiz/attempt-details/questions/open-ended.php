<?php
/**
 * Attempt details Open-ended / Short Answer (read-only).
 *
 * @package Tutor\Templates
 * @subpackage LearningArea\Quiz\AttemptDetails
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! isset( $question ) || ! is_object( $question ) ) {
	return;
}

$given_answer = '';

if ( isset( $question->given_answer ) ) {
	$given_raw = maybe_unserialize( $question->given_answer );
	if ( is_array( $given_raw ) ) {
		$given_answer = implode( ', ', array_map( 'strval', $given_raw ) );
	} else {
		$given_answer = (string) $given_raw;
	}
}
?>

<div class="tutor-quiz-question-options">
	<div class="tutor-input-field">
		<div class="tutor-input-wrapper">
			<textarea
				class="tutor-input tutor-text-area tutor-input-content-clear"
				placeholder="<?php esc_attr_e( 'No answer submitted', 'tutor' ); ?>"
				readonly
				disabled
			><?php echo esc_textarea( $given_answer ); ?></textarea>
		</div>
	</div>
</div>
