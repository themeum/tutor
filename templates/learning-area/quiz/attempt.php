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
use Tutor\Components\Button;
use Tutor\Components\Constants\Size;

global $tutor_is_started_quiz;

$quiz_attempt_info = tutor_utils()->quiz_attempt_info( $tutor_is_started_quiz->attempt_info );

$quiz_attempt_info['date_time_now'] = date( 'Y-m-d H:i:s', tutor_time() );//phpcs:ignore
$time_limit_seconds                 = tutor_utils()->avalue_dot( 'time_limit.time_limit_seconds', $quiz_attempt_info );
$remaining_time_secs                = ( strtotime( $tutor_is_started_quiz->attempt_started_at ) + $time_limit_seconds ) - strtotime( $quiz_attempt_info['date_time_now'] );
$remaining_time_context             = tutor_utils()->seconds_to_time_context( $remaining_time_secs );
$quiz_when_time_expires             = tutor_utils()->get_option( 'quiz_when_time_expires', 'auto_abandon' );
$questions                          = tutor_utils()->get_random_questions_by_quiz();

$form_id        = 'quiz-attempt-form-' . $tutor_is_started_quiz->attempt_id;
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
	x-data='(() => {
		const form = tutorForm({
			id: "<?php echo esc_attr( $form_id ); ?>",
			mode: "onSubmit",
			defaultValues: <?php echo wp_json_encode( $default_values ); ?>,
		});
		const submission = tutorQuizSubmission({
			formId: "<?php echo esc_attr( $form_id ); ?>",
			attemptId: "<?php echo esc_attr( $tutor_is_started_quiz->attempt_id ); ?>",
		});

		return {
			...form,
			...submission,
			init() {
				form.init?.call(this);
				submission.init?.call(this);
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
			'form_id'                => $form_id,
		)
	);
	?>
	<div class="tutor-quiz-questions">
		<?php
		foreach ( $questions as $index => $question ) {
			Quiz::render_question( $question, $index );
		}
		?>
	</div>

	<div class="tutor-quiz-footer">
		<?php
			Button::make()
				->label( __( 'Submit Quiz', 'tutor' ) )
				->size( Size::LARGE )
				->attr( 'form', $form_id )
				->attr( 'type', 'submit' )
				->attr( ':class', '{ \'tutor-btn-loading\': submitQuizMutation?.isPending }' )
				->attr( 'style', 'display: block; margin: 0 auto; min-width: 290px;' )
				->render();
		?>
	</div>
</form>
