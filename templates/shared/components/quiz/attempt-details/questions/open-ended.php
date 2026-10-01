<?php
/**
 * Attempt details Open-ended / Short Answer (read-only).
 *
 * @package Tutor\Templates
 * @subpackage LearningArea\Quiz\AttemptDetails
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use Tutor\Components\Button;
use Tutor\Components\Constants\Size;
use Tutor\Components\Constants\Variant;
use Tutor\Components\InputField;
use Tutor\Components\SvgIcon;
use TUTOR\Icon;
use Tutor\Models\QuizModel;

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
$feedback_attempt_id        = (int) ( $attempt_id ?? ( $question->quiz_attempt_id ?? 0 ) );
$feedback_attempt_answer_id = (int) ( $attempt_answer_id ?? ( $question->attempt_answer_id ?? ( $question->question_id ?? 0 ) ) );
$question_id                = (int) ( $question->question_id ?? 0 );

if ( ! $feedback_attempt_answer_id && $question_id > 0 ) {
	$feedback_attempt_answer_id = $question_id;
}

if ( '' === $question_feedback && isset( $attempt_data->attempt_info ) ) {
	$question_feedback_map = QuizModel::get_attempt_feedback_map( $attempt_data->attempt_info );
	$question_feedback     = (string) ( $question_feedback_map[ $feedback_attempt_answer_id ] ?? ( $question_feedback_map[ $question_id ] ?? '' ) );
}

$qmark_formatted = (string) round( (float) ( $question->question_mark ?? 0 ), 2 );
$achieved_raw    = isset( $question->achieved_mark ) && null !== $question->achieved_mark && '' !== $question->achieved_mark ? (float) $question->achieved_mark : null;
$is_unscored     = 'pending' === $review_status && ( null === $achieved_raw || 0.0 === (float) $achieved_raw );
$achieved_val    = ( null !== $achieved_raw && ! $is_unscored ) ? (string) round( $achieved_raw, 2 ) : '';

$is_graded             = 'graded' === $review_status;
$mark_validation_rules = array(
	'min' => array(
		'value'   => 0,
		'message' => __( 'Mark cannot be negative', 'tutor' ),
	),
	'max' => array(
		'value'   => (float) $qmark_formatted,
		'message' => sprintf(
			/* translators: %s: maximum mark */
			__( 'Mark cannot exceed %s', 'tutor' ),
			$qmark_formatted
		),
	),
);

if ( $is_graded ) {
	$mark_validation_rules['required'] = __( 'Mark is required', 'tutor' );
}
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
					<?php
					InputField::make()
						->type( 'number' )
						->name( $manual_mark_field )
						->id( 'tutor-' . $manual_mark_field )
						->value( $achieved_val )
						->placeholder( '—' )
						->attr( 'min', '0' )
						->attr( 'max', $qmark_formatted )
						->attr( 'step', 'any' )
						->attr(
							'x-bind',
							'register(' . wp_json_encode( $manual_mark_field ) . ', ' . wp_json_encode( $mark_validation_rules ) . ')'
						)
						->render();
					?>
					<span class="tutor-quiz-obtained-marks-total">/ <?php echo esc_html( $qmark_formatted ); ?></span>
				</div>
			</div>

			<?php if ( $feedback_attempt_answer_id > 0 ) : ?>
				<div
					class="tutor-question-feedback"
					x-data='tutorQuestionFeedback({
						initialFeedback: <?php echo wp_json_encode( (string) $question_feedback ); ?>,
						fieldName: <?php echo wp_json_encode( "question_feedback[{$feedback_attempt_answer_id}]" ); ?>,
						formId: <?php echo wp_json_encode( $form_id ?? 'quiz-attempt-review-form' ); ?>
					})'
				>
					<?php
					Button::make()
						->label( __( 'Add Feedback', 'tutor' ) )
						->icon( Icon::COMMENT_OUTLINE, 'left', 20, 20 )
						->flip_rtl()
						->variant( Variant::LINK )
						->size( Size::SM )
						->attr( 'type', 'button' )
						->attr( 'class', 'tutor-quiz-add-feedback-btn' )
						->attr( 'x-show', '!expanded && !feedback' )
						->attr( 'x-cloak', true )
						->attr( 'x-collapse', true )
						->attr( '@click', 'toggle()' )
						->render();

					Button::make()
						->label( __( 'Show Feedback', 'tutor' ) )
						->icon( Icon::EYE_LINE )
						->variant( Variant::LINK )
						->size( Size::SM )
						->attr( 'type', 'button' )
						->attr( 'class', 'tutor-quiz-add-feedback-btn' )
						->attr( 'x-show', '!expanded && feedback' )
						->attr( 'x-cloak', true )
						->attr( 'x-collapse', true )
						->attr( '@click', 'toggle()' )
						->render();
					?>

					<div
						class="tutor-quiz-feedback-panel"
						x-show="expanded"
						x-collapse
						x-cloak
					>
						<div class="tutor-quiz-feedback-panel-header">
							<span class="tutor-quiz-feedback-panel-title" x-text="feedback ? <?php echo esc_attr( __( 'Edit Feedback', 'tutor' ) ); ?> : <?php echo esc_attr( __( 'Write feedback', 'tutor' ) ); ?>"></span>
						</div>

						<?php
						InputField::make()
							->type( 'textarea' )
							->name( "question_feedback[{$feedback_attempt_answer_id}]" )
							->placeholder( __( 'Write feedback for the student...', 'tutor' ) )
							->attr( 'rows', '3' )
							->attr(
								'x-bind',
								'register(' . wp_json_encode( "question_feedback[{$feedback_attempt_answer_id}]" ) . ')'
							)
							->render();
						?>

						<div class="tutor-quiz-feedback-actions tutor-flex tutor-justify-between tutor-items-center">
							<div class="tutor-quiz-feedback-actions-start">
								<?php
								Button::make()
									->label( __( 'Delete', 'tutor' ) )
									->variant( Variant::DESTRUCTIVE )
									->size( Size::SM )
									->attr( 'type', 'button' )
									->attr( '@click.prevent', 'del()' )
									->attr( 'x-show', 'feedback' )
									->attr( 'x-cloak', true )
									->render();
								?>
							</div>

							<div class="tutor-quiz-feedback-actions-main tutor-flex tutor-items-center tutor-gap-3">
								<?php
								Button::make()
									->label( __( 'Cancel', 'tutor' ) )
									->variant( Variant::GHOST )
									->size( Size::SM )
									->attr( 'type', 'button' )
									->attr( '@click.prevent', 'cancel()' )
									->render();

								Button::make()
									->label( __( 'Save', 'tutor' ) )
									->variant( Variant::PRIMARY )
									->size( Size::SM )
									->attr( 'type', 'button' )
									->attr( '@click.prevent', 'save()' )
									->render();
								?>
							</div>
						</div>
					</div>
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
