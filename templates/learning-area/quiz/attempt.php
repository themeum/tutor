<?php
/**
 * Tutor learning area quiz active template
 *
 * This template get loaded once user submit the quiz or
 * if there is any active quiz
 *
 * @package Tutor\Templates
 * @subpackage LearningArea
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use Tutor\Quiz;
use Tutor\Models\QuizModel;
use TUTOR\Icon;
use TUTOR\Quiz_Attempts_List;
use Tutor\Components\SvgIcon;
use Tutor\Components\Button;
use Tutor\Components\ConfirmationModal;
use Tutor\Components\Modal;
use Tutor\Components\Constants\Size;
use Tutor\Components\Constants\Variant;
use Tutor\Helpers\UrlHelper;

global $tutor_is_started_quiz;

// Quiz attempt data.
$quiz_attempt_info                  = tutor_utils()->quiz_attempt_info( $tutor_is_started_quiz->attempt_info );
$quiz_attempt_info['date_time_now'] = date( 'Y-m-d H:i:s', tutor_time() ); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date

// Time limit calculations.
$time_limit_seconds     = (int) tutor_utils()->avalue_dot( 'time_limit.time_limit_seconds', $quiz_attempt_info );
$has_time_limit         = $time_limit_seconds > 0;
$remaining_time_secs    = $has_time_limit
	? ( strtotime( $tutor_is_started_quiz->attempt_started_at ) + $time_limit_seconds ) - strtotime( $quiz_attempt_info['date_time_now'] )
	: 0;
$remaining_time_context = tutor_utils()->seconds_to_time_context( $remaining_time_secs );

// Quiz settings.
$quiz_when_time_expires = tutor_utils()->get_option( 'quiz_when_time_expires', 'auto_abandon' );
$quiz_settings          = tutor_utils()->get_quiz_option( (int) $tutor_is_started_quiz->quiz_id );
$show_timeout_attempts  = 'auto_abandon' !== $quiz_when_time_expires;
$timeout_modal_message  = 'auto_abandon' === $quiz_when_time_expires
	? __( 'Your quiz was abandoned automatically because time expired before you submitted it.', 'tutor' )
	: __( 'Your quiz has been submitted automatically.', 'tutor' );
$reveal_wait_ms         = 1000 * $quiz_settings['answers_reveal_duration'];
$show_previous_button   = (bool) tutor_utils()->get_option( 'quiz_previous_button_enabled', true );
$hide_previous_button   = '1' === (string) ( $quiz_settings['hide_previous_button'] ?? '0' );
$hide_quiz_time_display = '1' === (string) ( $quiz_settings['hide_quiz_time_display'] ?? '0' );
$show_previous_button   = $show_previous_button && ! $hide_previous_button;

// Quiz layout.
$question_layout_view = $quiz_settings['question_layout_view'] ?? 'single_question';
$enable_pagination    = '1' === (string) ( $quiz_settings['enable_pagination'] ?? '0' );
$enable_answer_reveal = '1' === (string) ( $quiz_settings['enable_answer_reveal'] ?? '0' );
$is_linear_layout     = 'single_question' === $question_layout_view;
$is_pagination_layout = 'single_question' === $question_layout_view && $enable_pagination;

// Pagination style — only applies to single question layout with pagination enabled.
$supported_pagination_styles = array( 'shape', 'radio', 'number' );
$pagination_style            = $quiz_settings['pagination_type'] ?? 'shape';
$pagination_style            = in_array( $pagination_style, $supported_pagination_styles, true ) ? $pagination_style : 'shape';

// Questions and attempts.
$questions                     = tutor_utils()->get_random_questions_by_quiz();
$hide_question_number_overview = (bool) ( $quiz_settings['hide_question_number_overview'] ?? false );

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

$form_id             = 'quiz-attempt-form-' . $tutor_is_started_quiz->attempt_id . '-' . $tutor_is_started_quiz->quiz_id;
$modal_id            = 'tutor-quiz-abandon-modal';
$submitted_modal_id  = 'tutor-quiz-submitted-modal';
$timeout_modal_id    = 'tutor-quiz-timeout-modal';
$attempt_details_url = Quiz_Attempts_List::is_attempt_details_hidden()
	? ''
	: UrlHelper::add_query_params(
		get_pagenum_link(),
		array(
			'action'     => Quiz::ACTION_VIEW_DETAILS,
			'attempt_id' => (int) $tutor_is_started_quiz->attempt_id,
		)
	);
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
			submittedModalId: "<?php echo esc_attr( $submitted_modal_id ); ?>",
			timeoutModalId: "<?php echo esc_attr( $timeout_modal_id ); ?>",
			totalQuestions: <?php echo esc_attr( count( $questions ) ); ?>,
			enableAnswerReveal: <?php echo $enable_answer_reveal ? 'true' : 'false'; ?>,
			revealWaitMs: <?php echo esc_attr( (int) $reveal_wait_ms ); ?>,
		});

		const layout = tutorQuizLayout({
			layout: "<?php echo esc_attr( $question_layout_view ); ?>",
			formId: "<?php echo esc_attr( $form_id ); ?>",
			totalQuestions: <?php echo esc_attr( count( $questions ) ); ?>,
			enableAnswerReveal: <?php echo $enable_answer_reveal ? 'true' : 'false'; ?>,
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
			'hide_quiz_time_display' => $hide_quiz_time_display,
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
		<?php if ( $is_linear_layout && ! $hide_question_number_overview ) : ?>
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
			</div>
		<?php endif; ?>

		<?php
		do_action( 'tutor_quiz/body/before', $tutor_is_started_quiz->quiz_id, $quiz_attempt_info );
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
				:class="{ 'tutor-quiz-question-wrapper-active': isQuestionActive(<?php echo esc_attr( $question_index ); ?>) }"
				x-cloak
			>
				<?php Quiz::render_question( $question, $question_index ); ?>
			</div>
			<?php
		}
		?>
	</div>

	<?php if ( $is_pagination_layout && count( $questions ) > 1 ) : ?>
		<div
			class="tutor-quiz-questions-pagination"
			data-pagination-style="<?php echo esc_attr( $pagination_style ); ?>"
		>
			<ul>
				<?php foreach ( $questions as $index => $question ) : ?>
					<li>
						<button
							type="button"
							class="tutor-quiz-question-paginate-item"
							:class="getPaginationItemClass(<?php echo esc_attr( $index + 1 ); ?>)"
							:data-state="getPaginationState(<?php echo esc_attr( $index + 1 ); ?>)"
							@click="goTo(<?php echo esc_attr( $index + 1 ); ?>)"
						>
							<span class="tutor-quiz-question-paginate-label">
								<?php echo esc_html( $index + 1 ); ?>
							</span>
							<span class="tutor-quiz-question-paginate-icon tutor-quiz-question-paginate-icon-correct">
								<?php SvgIcon::make()->name( Icon::CHECK_2 )->size( 12 )->render(); ?>
							</span>
							<span class="tutor-quiz-question-paginate-icon tutor-quiz-question-paginate-icon-incorrect">
								<?php SvgIcon::make()->name( Icon::CROSS )->size( 12 )->render(); ?>
							</span>
						</button>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	<?php endif; ?>

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
						<?php SvgIcon::make()->name( Icon::CHECK_2 )->size( 26 )->render(); ?>
					</span>
					<span
						class="tutor-quiz-footer-feedback-icon"
						x-show="revealFooterState === 'incorrect'"
					>
						<?php SvgIcon::make()->name( Icon::CROSS )->size( 26 )->render(); ?>
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
						->icon( Icon::ARROW_LEFT_2, 'left', 20 )
						->flip_rtl()
						->attr( 'type', 'button' )
						->attr( ':disabled', 'isRevealSubmitting' )
						->attr( '@click', 'goPrev()' )
						->attr( 'x-show', $show_previous_button ? 'currentIndex > 1' : 'false' )
						->attr( 'class', 'tutor-quiz-answer-previous-btn' )
						->render();

					Button::make()
						->label( __( 'Next', 'tutor' ) )
						->size( Size::LARGE )
						->attr( 'type', 'button' )
						->attr( ':disabled', 'isRevealSubmitting || shouldDisableNextButton()' )
						->attr( '@click', 'goNext()' )
						->attr( 'x-show', 'currentIndex < totalQuestions' )
						->attr( 'class', 'tutor-quiz-answer-next-btn' )
						->render();

					Button::make()
						->label( __( 'Submit Quiz', 'tutor' ) )
						->size( Size::LARGE )
						->attr( 'type', 'submit' )
						->attr( 'x-show', 'currentIndex === totalQuestions' )
						->attr( ':disabled', 'isRevealSubmitting' )
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
					->attr( ':disabled', 'isRevealSubmitting' )
					->attr( ':class', '{ \'tutor-btn-loading\': submitQuizMutation?.isPending }' )
					->attr( 'style', 'display: block; margin: 0 auto; min-width: 290px;' )
					->render();
			?>
		</div>
	<?php endif; ?>
	<?php
		ConfirmationModal::make()
			->id( $modal_id )
			->icon( tutor_utils()->get_themed_svg( 'images/illustrations/warning.svg' ), 80, 80, ConfirmationModal::ICON_TYPE_HTML )
			->title( __( 'Leave this Quiz?', 'tutor' ) )
			->message( __( 'If you leave now, your quiz will be submitted with the answers completed so far.', 'tutor' ) )
			->confirm_text( __( 'Yes, Leave Quiz', 'tutor' ) )
			->confirm_handler( "handleAbandonConfirm(); TutorCore.modal.closeModal('$modal_id')" )
			->mutation_state( 'abandonQuizMutation' )
			->cancel_button( $modal_cancel_button )
			->render();
	?>

	<?php
		Modal::make()
			->id( $submitted_modal_id )
			->width( '426px' )
			->template(
				tutor()->path . 'templates/learning-area/quiz/modals/result.php',
				array(
					'modal_id'      => $submitted_modal_id,
					'title'         => __( 'Quiz Submitted', 'tutor' ),
					'message'       => __( 'Your answers are locked in. Ready to check your score?', 'tutor' ),
					'icon_html'     => tutor_utils()->get_themed_svg( 'images/illustrations/quiz-submitted.svg' ),
					'show_attempts' => false,
					'action_url'    => $attempt_details_url,
					'action_label'  => __( 'View Results', 'tutor' ),
				)
			)
			->render();

		Modal::make()
			->id( $timeout_modal_id )
			->width( '426px' )
			->template(
				tutor()->path . 'templates/learning-area/quiz/modals/result.php',
				array(
					'modal_id'      => $timeout_modal_id,
					'title'         => __( 'Times up!', 'tutor' ),
					'message'       => $timeout_modal_message,
					'icon_html'     => tutor_utils()->get_themed_svg( 'images/illustrations/quiz-timeout.svg' ),
					'show_attempts' => $show_timeout_attempts,
					'action_url'    => $attempt_details_url,
					'action_label'  => __( 'View Results', 'tutor' ),
				)
			)
			->render();
		?>
</form>

<script type="application/octet-stream" id="tutor-quiz-context">
	<?php echo esc_html( bin2hex( wp_json_encode( $quiz_answers ) ) ); ?>
</script>
