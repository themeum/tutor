<?php
/**
 * Attempt details Open-ended / Short Answer (read-only).
 *
 * @package Tutor\Templates
 * @subpackage LearningArea\Quiz\AttemptDetails
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use TUTOR\Icon;
use Tutor\Components\SvgIcon;

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

$is_instructor_review       = ! empty( $is_instructor_review );
$is_skipped                 = ! empty( $is_skipped );
$review_status              = (string) ( $review_status ?? '' );
$manual_mark_field          = (string) ( $manual_mark_field ?? '' );
$question_feedback          = (string) ( $question_feedback ?? '' );
$feedback_attempt_id        = (int) ( $attempt_id ?? 0 );
$feedback_attempt_answer_id = (int) ( $attempt_answer_id ?? 0 );
$question_id                = (int) ( $question->question_id ?? 0 );

$qmark_raw       = (float) ( $question->question_mark ?? 0 );
$qmark_formatted = ( floor( $qmark_raw ) === $qmark_raw ) ? (string) (int) $qmark_raw : (string) round( $qmark_raw, 2 );
$achieved_raw    = isset( $question->achieved_mark ) && null !== $question->achieved_mark && '' !== $question->achieved_mark ? (float) $question->achieved_mark : null;
$is_unscored     = 'pending' === $review_status && ( null === $achieved_raw || 0.0 === (float) $achieved_raw );
$achieved_val    = ( null !== $achieved_raw && ! $is_unscored )
	? ( ( floor( $achieved_raw ) === $achieved_raw ) ? (string) (int) $achieved_raw : (string) round( $achieved_raw, 2 ) )
	: '';
?>

<div class="tutor-quiz-question-options">
	<div class="tutor-quiz-open-ended-answer<?php echo '' === trim( $given_answer ) ? ' is-empty' : ''; ?>">
		<?php echo '' !== trim( $given_answer ) ? nl2br( esc_html( $given_answer ) ) : esc_html__( 'No answer submitted', 'tutor' ); ?>
	</div>

	<?php if ( $is_instructor_review && ! $is_skipped && $manual_mark_field ) : ?>
		<div class="tutor-quiz-manual-review-wrap">
			<div class="tutor-quiz-obtained-marks-group">
				<label class="tutor-quiz-obtained-marks-label" for="<?php echo esc_attr( 'tutor-' . $manual_mark_field ); ?>">
					<?php esc_html_e( 'Obtained marks', 'tutor' ); ?>
				</label>
				<div class="tutor-quiz-obtained-marks-row">
					<input
						id="<?php echo esc_attr( 'tutor-' . $manual_mark_field ); ?>"
						class="tutor-quiz-obtained-marks-control"
						type="number"
						min="0"
						max="<?php echo esc_attr( $qmark_formatted ); ?>"
						step="any"
						placeholder="—"
						name="<?php echo esc_attr( $manual_mark_field ); ?>"
						value="<?php echo esc_attr( $achieved_val ); ?>"
						x-bind="register('<?php echo esc_attr( $manual_mark_field ); ?>', {
							required: '<?php echo esc_js( __( 'Mark is required', 'tutor' ) ); ?>',
							min: {
								value: 0,
								message: '<?php echo esc_js( __( 'Mark cannot be negative', 'tutor' ) ); ?>'
							},
							max: {
								value: <?php echo esc_js( $qmark_formatted ); ?>,
								message: '<?php echo esc_js( sprintf( /* translators: %s: maximum mark */ __( 'Mark cannot exceed %s', 'tutor' ), $qmark_formatted ) ); ?>'
							}
						})"
						:class="{ 'is-invalid': errors?.['<?php echo esc_attr( $manual_mark_field ); ?>'] }"
						@input="trigger('<?php echo esc_attr( $manual_mark_field ); ?>')"
					/>
					<span class="tutor-quiz-obtained-marks-total">/ <?php echo esc_html( $qmark_formatted ); ?></span>
				</div>
				<div
					class="tutor-quiz-manual-review-error"
					x-cloak
					x-show="errors?.['<?php echo esc_attr( $manual_mark_field ); ?>']?.message"
					x-text="errors?.['<?php echo esc_attr( $manual_mark_field ); ?>']?.message"
				></div>
			</div>

			<?php if ( $feedback_attempt_id && $feedback_attempt_answer_id ) : ?>
				<div
					class="tutor-question-feedback"
					x-data="tutorQuestionFeedback({
						attemptId: <?php echo esc_attr( $feedback_attempt_id ); ?>,
						attemptAnswerId: <?php echo esc_attr( $feedback_attempt_answer_id ); ?>,
						feedback: <?php echo wp_json_encode( $question_feedback ); ?>
					})"
				>
					<template x-if="!expanded">
						<button
							type="button"
							class="tutor-quiz-add-feedback-btn"
							@click="toggle()"
						>
							<?php SvgIcon::make()->name( Icon::COMMENTS )->size( 16 )->render(); ?>
							<span x-show="!feedback"><?php esc_html_e( 'Add Feedback', 'tutor' ); ?></span>
							<span x-show="feedback"><?php esc_html_e( 'Show Feedback', 'tutor' ); ?></span>
						</button>
					</template>

					<template x-if="expanded">
						<div class="tutor-quiz-feedback-panel">
							<div class="tutor-quiz-feedback-panel-header">
								<span class="tutor-quiz-feedback-panel-title">
									<?php esc_html_e( 'Feedback from instructor', 'tutor' ); ?>
								</span>
								<span class="tutor-quiz-feedback-panel-status" x-show="feedback"><?php esc_html_e( 'Editing', 'tutor' ); ?></span>
							</div>

							<p x-show="feedback && !editing" class="tutor-quiz-feedback-panel-content" x-text="feedback"></p>

							<template x-if="!feedback || editing">
								<textarea
									class="tutor-quiz-feedback-textarea"
									rows="3"
									name="question_feedback[<?php echo esc_attr( $feedback_attempt_answer_id ); ?>]"
									placeholder="<?php esc_attr_e( 'Write feedback for the student...', 'tutor' ); ?>"
									x-model="draft"
								></textarea>
							</template>

							<div class="tutor-quiz-feedback-actions">
								<button
									type="button"
									class="tutor-btn tutor-btn-outline-primary tutor-btn-sm"
									@click="toggle()"
								>
									<?php esc_html_e( 'Cancel', 'tutor' ); ?>
								</button>
								<button
									type="button"
									class="tutor-btn tutor-btn-destructive tutor-btn-sm"
									@click="del()"
									x-show="feedback"
								>
									<?php esc_html_e( 'Delete', 'tutor' ); ?>
								</button>
								<button
									type="button"
									class="tutor-btn tutor-btn-primary tutor-btn-sm"
									@click="save()"
									:disabled="saving"
									x-text="saving ? '<?php esc_html_e( 'Saving...', 'tutor' ); ?>' : '<?php esc_html_e( 'Save', 'tutor' ); ?>'"
								></button>
							</div>
						</div>
					</template>
				</div>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<?php if ( ! $is_instructor_review && '' !== trim( $question_feedback ) ) : ?>
		<div class="tutor-quiz-question-feedback-wrap">
			<div class="tutor-quiz-question-feedback-title">
				<?php esc_html_e( 'Feedback from instructor', 'tutor' ); ?>
			</div>
			<div class="tutor-quiz-question-feedback-card">
				<?php echo nl2br( esc_html( $question_feedback ) ); ?>
			</div>
		</div>
	<?php endif; ?>
</div>
