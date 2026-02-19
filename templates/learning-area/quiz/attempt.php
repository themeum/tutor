<?php
/**
 * Tutor learning area quiz active template
 *
 * This template get loaded once user submit the quiz or
 * if there any active quiz
 *
 * @package Tutor\Templates
 * @subpackage LearningArea
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use Tutor\Quiz;
use Tutor\Models\QuizModel;
use TUTOR\Icon;
use Tutor\Components\Button;
use Tutor\Components\ConfirmationModal;
use Tutor\Components\Constants\Size;
use Tutor\Components\Constants\Variant;

global $tutor_is_started_quiz;

$quiz_attempt_info = tutor_utils()->quiz_attempt_info( $tutor_is_started_quiz->attempt_info );

$quiz_attempt_info['date_time_now'] = date( 'Y-m-d H:i:s', tutor_time() );//phpcs:ignore
$time_limit_seconds                 = tutor_utils()->avalue_dot( 'time_limit.time_limit_seconds', $quiz_attempt_info );
$remaining_time_secs                = ( strtotime( $tutor_is_started_quiz->attempt_started_at ) + $time_limit_seconds ) - strtotime( $quiz_attempt_info['date_time_now'] );
$remaining_time_context             = tutor_utils()->seconds_to_time_context( $remaining_time_secs );
$quiz_when_time_expires             = tutor_utils()->get_option( 'quiz_when_time_expires', 'auto_abandon' );
$has_time_limit                     = $time_limit_seconds > 0;
$questions                          = tutor_utils()->get_random_questions_by_quiz();
$question_layout_view               = tutor_utils()->get_quiz_option( $tutor_is_started_quiz->quiz_id, 'question_layout_view' );
$question_layout_view               = $question_layout_view ? $question_layout_view : 'single_question';
$feedback_mode                      = tutor_utils()->get_quiz_option( $tutor_is_started_quiz->quiz_id, 'feedback_mode', '' );
$reveal_wait_ms                     = 1000 * (int) tutor_utils()->get_option( 'quiz_answer_display_time' );
$is_linear_layout                   = in_array( $question_layout_view, array( 'single_question', 'question_pagination' ), true );
$show_previous_button               = (bool) tutor_utils()->get_option( 'quiz_previous_button_enabled', true );
$attempts_allowed                   = (int) tutor_utils()->get_quiz_option( $tutor_is_started_quiz->quiz_id, 'attempts_allowed', 0 );
$previous_attempts                  = tutor_utils()->quiz_attempts();
$current_attempt_number             = ( is_array( $previous_attempts ) ? count( $previous_attempts ) : 0 ) + 1;

$reveal_question_types = array( 'true_false', 'single_choice', 'multiple_choice' );
$quiz_answers          = array();
foreach ( $questions as $question ) {
	if ( ! in_array( $question->question_type, $reveal_question_types, true ) ) {
		continue;
	}

	$answers = QuizModel::get_answers_by_quiz_question( $question->question_id );
	foreach ( $answers as $answer ) {
		if ( ! empty( $answer->is_correct ) ) {
			$quiz_answers[] = $answer->answer_id;
		}
	}
}

$form_id             = 'quiz-attempt-form-' . $tutor_is_started_quiz->attempt_id;
$modal_id            = 'tutor-quiz-abandon-modal';
$modal_cancel_button = Button::make()
	->label( __( 'Stay Here', 'tutor' ) )
	->variant( Variant::SECONDARY )
	->size( Size::SMALL )
	->attr( '@click', "handleAbandonCancel(); TutorCore.modal.closeModal('$modal_id')" )
	->get();

$default_values = array(
	'attempt[' . $tutor_is_started_quiz->attempt_id . '][quiz_question_ids][]' => array_map(
		function ( $question ) {
			return $question->question_id;
		},
		$questions
	),
);

?>
<form 
	id="<?php echo esc_attr( $form_id ); ?>"
	class="tutor-quiz tutor-quiz-submission"
	data-question-layout-view="<?php echo esc_attr( $question_layout_view ); ?>"
	x-data='(() => {
		const form = tutorForm({
			id: "<?php echo esc_attr( $form_id ); ?>",
			mode: "onSubmit",
			defaultValues: <?php echo wp_json_encode( $default_values ); ?>,
		});
		const submission = tutorQuizSubmission({
			formId: "<?php echo esc_attr( $form_id ); ?>",
			attemptId: "<?php echo esc_attr( $tutor_is_started_quiz->attempt_id ); ?>",
			quizId: <?php echo esc_attr( $tutor_is_started_quiz->quiz_id ); ?>,
			abandonModalId: "<?php echo esc_attr( $modal_id ); ?>",
			feedbackMode: "<?php echo esc_attr( $feedback_mode ); ?>",
			revealWaitMs: <?php echo esc_attr( (int) $reveal_wait_ms ); ?>,
		});

		const layout = tutorQuizLayout({
			layout: "<?php echo esc_attr( $question_layout_view ); ?>",
			formId: "<?php echo esc_attr( $form_id ); ?>",
			totalQuestions: <?php echo esc_attr( count( $questions ) ); ?>,
			feedbackMode: "<?php echo esc_attr( $feedback_mode ); ?>",
			revealWaitMs: <?php echo esc_attr( (int) $reveal_wait_ms ); ?>,
		});

		return {
			...form,
			...submission,
			...layout,
			init() {
				form.init?.call(this);
				submission.init?.call(this);
				layout.init?.call(this);
			},
		};
	})()'
	x-bind="getFormBindings()"
	@submit.prevent="handleSubmit(
		(data) => handleQuizSubmit(data),
		(errors) => handleQuizError(errors)
	)($event)"
>
	<?php
	tutor_load_template(
		'learning-area.quiz.progress-bar',
		array(
			'remaining_time_secs'    => max( 0, (int) $remaining_time_secs ),
			'quiz_when_time_expires' => $quiz_when_time_expires,
			'has_time_limit'         => $has_time_limit,
			'form_id'                => $form_id,
			'modal_id'               => $modal_id,
			'total_questions'        => count( $questions ),
		)
	);
	?>
  
	<div
		class="tutor-quiz-questions"
		data-question-layout-view="<?php echo esc_attr( $question_layout_view ); ?>"
		x-cloak
	>
		<?php if ( 'question_pagination' === $question_layout_view ) : ?>
			<div class="tutor-quiz-questions-pagination">
				<ul>
					<?php foreach ( $questions as $index => $question ) : ?>
						<li>
							<button
								type="button"
								class="tutor-quiz-question-paginate-item"
								:class="{ 'active': currentIndex === <?php echo esc_attr( $index + 1 ); ?> }"
								@click="goTo(<?php echo esc_attr( $index + 1 ); ?>)"
							>
								<?php echo esc_html( $index + 1 ); ?>
							</button>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endif; ?>

		<?php if ( $is_linear_layout ) : ?>
			<div class="tutor-quiz-question-meta">
				<div class="tutor-quiz-question-indicator">
					<?php
					echo wp_kses(
						sprintf(
							/* translators: %s: question number indicator (e.g. 01/15) */
							__( 'Question No: %s', 'tutor' ),
							'<strong x-text="String(currentIndex).padStart(2, \'0\') + \'/\' + String(totalQuestions).padStart(2, \'0\')"></strong>'
						),
						array(
							'strong' => array(
								'x-text' => true,
							),
						)
					);
					?>
				</div>
				<div class="tutor-quiz-attempt-progress">
					<?php
					echo wp_kses(
						sprintf(
							/* translators: %s: allowed attempts (number or ∞) */
							__( 'Total Attempt: %s', 'tutor' ),
							'<strong>' . esc_html( $current_attempt_number ) . '/' . ( 0 === $attempts_allowed ? '&infin;' : esc_html( $attempts_allowed ) ) . '</strong>'
						),
						array( 'strong' => array() )
					);
					?>
				</div>
			</div>
		<?php endif; ?>

		<?php
		foreach ( $questions as $index => $question ) {
			$question_settings = maybe_unserialize( $question->question_settings );
			$answer_required   = isset( $question_settings['answer_required'] ) && '1' === $question_settings['answer_required'];
			$question_index    = $index + 1;
			?>
			<div
				class="tutor-quiz-question-wrapper"
				data-quiz-question-index="<?php echo esc_attr( $question_index ); ?>"
				data-answer-required="<?php echo esc_attr( $answer_required ? '1' : '0' ); ?>"
				x-show="isQuestionActive(<?php echo esc_attr( $question_index ); ?>)"
				x-cloak
			>
				<?php Quiz::render_question( $question, $question_index ); ?>
			</div>
			<?php
		}
		?>
	</div>

	<?php if ( $is_linear_layout ) : ?>
		<div
			class="tutor-quiz-footer"
			:data-reveal-state="revealFooterState"
			x-cloak
		>
			<div class="tutor-quiz-footer-inner">
				<div
					class="tutor-quiz-footer-feedback"
					x-show="revealFooterState !== ''"
				>
					<span
						class="tutor-quiz-footer-feedback-icon"
						x-show="revealFooterState === 'correct'"
					>
						<?php tutor_utils()->render_svg_icon( Icon::CHECK_2, 26, 26 ); ?>
					</span>
					<span
						class="tutor-quiz-footer-feedback-icon"
						x-show="revealFooterState === 'incorrect'"
					>
						<?php tutor_utils()->render_svg_icon( Icon::CROSS, 26, 26 ); ?>
					</span>
					<span
						class="tutor-quiz-footer-feedback-text"
						x-show="revealFooterState === 'correct'"
					>
						<?php esc_html_e( 'Nicely Done!', 'tutor' ); ?>
					</span>
					<span
						class="tutor-quiz-footer-feedback-text"
						x-show="revealFooterState === 'incorrect'"
					>
						<?php esc_html_e( 'Wrong Answer', 'tutor' ); ?>
					</span>
				</div>
				
				<?php
					Button::make()
						->label( __( 'Skip Question', 'tutor' ) )
						->size( Size::LARGE )
						->variant( Variant::LINK_GRAY )
						->attr( 'type', 'button' )
						->attr( ':disabled', 'isRevealSubmitting || isRevealing' )
						->attr( 'x-show', 'canSkip(currentIndex) && revealFooterState === ""' )
						->attr( '@click', 'goNext({ skipValidation: true })' )
						->attr( 'class', 'tutor-quiz-skip-btn' )
						->render();
				?>

				<div class="tutor-quiz-footer-actions">
				<?php
					Button::make()
						->label( __( 'Back', 'tutor' ) )
						->size( Size::LARGE )
						->variant( Variant::OUTLINE )
						->icon( Icon::ARROW_LEFT_2, 'left', 20, 20 )
						->attr( 'type', 'button' )
						->attr( ':disabled', 'isRevealSubmitting || isRevealing' )
						->attr( '@click', 'goPrev()' )
						->attr( 'x-show', $show_previous_button ? 'currentIndex > 1' : 'false' )
						->attr( 'class', 'tutor-quiz-answer-previous-btn' )
						->render();

					Button::make()
						->label( __( 'Next', 'tutor' ) )
						->size( Size::LARGE )
						->attr( 'type', 'button' )
						->attr( ':disabled', 'isRevealSubmitting || isRevealing || shouldDisableNextButton()' )
						->attr( '@click', 'goNext()' )
						->attr( 'x-show', 'currentIndex < totalQuestions' )
						->attr( 'class', 'tutor-quiz-answer-next-btn' )
						->render();

					Button::make()
						->label( __( 'Submit Quiz', 'tutor' ) )
						->size( Size::LARGE )
						->attr( 'type', 'submit' )
						->attr( 'x-show', 'currentIndex === totalQuestions' )
						->attr( ':disabled', 'isRevealSubmitting || isRevealing' )
						->attr( ':class', '{ \'tutor-btn-loading\': submitQuizMutation?.isPending }' )
						->attr( 'class', 'tutor-quiz-submit-btn' )
						->render();
				?>
				</div>
			</div>
		</div>
	<?php else : ?>
		<div class="tutor-quiz-footer">
			<?php
				Button::make()
					->label( __( 'Submit Quiz', 'tutor' ) )
					->size( Size::LARGE )
					->attr( 'form', $form_id )
					->attr( 'type', 'submit' )
					->attr( ':disabled', 'isRevealSubmitting || isRevealing' )
					->attr( ':class', '{ \'tutor-btn-loading\': submitQuizMutation?.isPending }' )
					->attr( 'style', 'display: block; margin: 0 auto; min-width: 290px;' )
					->render();
			?>
		</div>
	<?php endif; ?>
	<?php
		ConfirmationModal::make()
			->id( $modal_id )
			->title( __( 'Abandon Quiz?', 'tutor' ) )
			->message( __( 'Do you want to abandon this quiz? The quiz will be submitted partially up to this question if you leave this page.', 'tutor' ) )
			->confirm_text( __( 'Yes, Leave Quiz', 'tutor' ) )
			->confirm_handler( "handleAbandonConfirm(); TutorCore.modal.closeModal('$modal_id')" )
			->mutation_state( 'abandonQuizMutation' )
			->cancel_button( $modal_cancel_button )
			->render();
	?>
</form>

<script type="application/octet-stream" id="tutor-quiz-context">
	<?php echo esc_html( bin2hex( wp_json_encode( $quiz_answers ) ) ); ?>
</script>
