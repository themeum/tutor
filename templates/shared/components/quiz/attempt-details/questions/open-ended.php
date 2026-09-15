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

$is_instructor_review = ! empty( $is_instructor_review );
$is_skipped           = ! empty( $is_skipped );
$review_status        = (string) ( $review_status ?? '' );
$manual_mark_field    = (string) ( $manual_mark_field ?? '' );
$question_feedback    = (string) ( $question_feedback ?? '' );
$feedback_attempt_id       = (int) ( $attempt_id ?? 0 );
$feedback_attempt_answer_id = (int) ( $attempt_answer_id ?? 0 );
$question_id          = (int) ( $question->question_id ?? 0 );
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
	<?php if ( $is_instructor_review && ! $is_skipped && $manual_mark_field ) : ?>
		<div class="tutor-flex tutor-items-center tutor-gap-2 tutor-mt-4">
			<label class="tutor-form-label" for="<?php echo esc_attr( 'tutor-' . $manual_mark_field ); ?>">
				<?php esc_html_e( 'Obtained marks', 'tutor' ); ?>
			</label>
			<input
				id="<?php echo esc_attr( 'tutor-' . $manual_mark_field ); ?>"
				class="tutor-form-control tutor-w-20"
				type="number"
				min="0"
				max="<?php echo esc_attr( (string) $question->question_mark ); ?>"
				step="0.01"
				name="<?php echo esc_attr( $manual_mark_field ); ?>"
				value="<?php echo esc_attr( (string) ( $question->achieved_mark ?? 0 ) ); ?>"
				x-bind="register('<?php echo esc_attr( $manual_mark_field ); ?>')"
			/>
			<span>/ <?php echo esc_html( $question->question_mark ); ?></span>
		</div>
	<?php elseif ( ! $is_skipped && 'graded' === $review_status ) : ?>
		<p class="tutor-text-secondary tutor-mt-4">
			<?php
			printf(
				/* translators: 1: achieved marks, 2: available marks. */
				esc_html__( 'Score: %1$s / %2$s', 'tutor' ),
				esc_html( (string) ( $question->achieved_mark ?? 0 ) ),
				esc_html( (string) $question->question_mark )
			);
			?>
		</p>
	<?php endif; ?>

	<?php if ( $is_instructor_review && ! $is_skipped && $feedback_attempt_id && $feedback_attempt_answer_id ) : ?>
		<?php
		$feedback_id = 'tutor-question-feedback-' . esc_attr( $feedback_attempt_answer_id );
		?>
		<div
			class="tutor-question-feedback tutor-mt-4"
			x-data="tutorQuestionFeedback({
				attemptId: <?php echo esc_attr( $feedback_attempt_id ); ?>,
				attemptAnswerId: <?php echo esc_attr( $feedback_attempt_answer_id ); ?>,
				feedback: <?php echo wp_json_encode( $question_feedback ); ?>
			})"
		>
			<template x-if="!expanded">
				<button
					type="button"
					class="tutor-btn tutor-btn-ghost tutor-btn-sm tutor-mt-2 tutor-gap-2"
					@click="toggle()"
				>
					<span x-show="!feedback" x-text="'<?php echo esc_html__( 'Add Feedback', 'tutor' ); ?>'"></span>
					<span x-show="feedback" x-text="'<?php echo esc_html__( 'Show Feedback', 'tutor' ); ?>'"></span>
				</button>
			</template>

			<template x-if="expanded">
				<div class="tutor-question-feedback-panel tutor-bg-primary tutor-rounded-lg tutor-p-4 tutor-mt-2">
					<div class="tutor-question-feedback-panel-header tutor-flex tutor-items-center tutor-justify-between tutor-gap-2 tutor-mb-2">
						<span class="tutor-fs-7 tutor-fw-medium tutor-text-white">
							<?php esc_html_e( 'Feedback from instructor', 'tutor' ); ?>
						</span>
						<span class="tutor-text-white tutor-text-secondary" x-show="feedback"><?php esc_html_e( 'Editing', 'tutor' ); ?></span>
					</div>

					<p x-show="feedback && !editing" class="tutor-text-white tutor-fs-7 tutor-mb-3" x-text="feedback"></p>

					<template x-if="!feedback || editing">
						<textarea
							class="tutor-form-control tutor-text-area tutor-mb-3"
							rows="3"
							name="question_feedback[<?php echo esc_attr( $feedback_attempt_answer_id ); ?>]"
							placeholder="<?php esc_attr_e( 'Write feedback for the student...', 'tutor' ); ?>"
							x-model="draft"
						></textarea>
					</template>

					<div class="tutor-flex tutor-items-center tutor-gap-2">
						<button
							type="button"
							class="tutor-btn tutor-btn-outline-primary tutor-btn-sm"
							@click="toggle()"
						>
							<?php esc_html_e( 'Cancel', 'tutor' ); ?>
						</button>
						<button
							type="button"
							class="tutor-btn tutor-btn-secondary tutor-btn-sm"
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

	<?php if ( ! $is_instructor_review && '' !== trim( $question_feedback ) ) : ?>
		<div class="tutor-question-feedback student-view tutor-bg-primary tutor-rounded-lg tutor-p-4 tutor-mt-4">
			<div class="tutor-fs-7 tutor-fw-medium tutor-text-white tutor-mb-1">
				<?php esc_html_e( 'Feedback from instructor', 'tutor' ); ?>
			</div>
			<p class="tutor-fs-7 tutor-text-white tutor-mb-0"><?php echo esc_html( $question_feedback ); ?></p>
		</div>
	<?php endif; ?>
</div>
