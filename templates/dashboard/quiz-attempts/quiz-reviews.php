<?php
/**
 * Frontend Student's Quiz Review
 *
 * @package Tutor\Templates
 * @subpackage Dashboard\Quiz_Attempts
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.4.0
 */

defined( 'ABSPATH' ) || exit;

use TUTOR\Input;
use Tutor\Components\EmptyState;
use Tutor\Models\QuizModel;

$attempt_id   = Input::get( 'attempt_id', 0, Input::TYPE_INT );
$attempt_data = tutor_utils()->get_attempt( $attempt_id );
$user_id      = tutor_utils()->avalue_dot( 'user_id', $attempt_data );
$quiz_id      = (int) tutor_utils()->avalue_dot( 'quiz_id', $attempt_data );
$back_url     = remove_query_arg( 'attempt_id' );
$attempt_info = isset( $attempt_data->attempt_info ) ? maybe_unserialize( $attempt_data->attempt_info ) : array();
$can_review   = $attempt_id > 0 && tutor_utils()->can_user_manage( 'attempt', $attempt_id );

if ( ! $attempt_data || ! $can_review ) {
	EmptyState::make()
		->title( __( 'Attempt not found or access permission denied', 'tutor' ) )
		->icon( tutor_utils()->get_themed_svg( 'images/illustrations/quiz-empty.svg' ) )
		->render();
	return;
}

$form_id             = 'quiz-attempt-review-form';
$form_default_values = array(
	'feedback' => tutor_utils()->count( $attempt_info ) && isset( $attempt_info['instructor_feedback'] ) ? $attempt_info['instructor_feedback'] : '',
);

$attempt_answers_map = array();
$questions           = QuizModel::get_quiz_answers_by_attempt_id( $attempt_id );

if ( is_array( $questions ) ) {
	foreach ( $questions as $question ) {
		$question_id = (int) ( $question->question_id ?? 0 );

		if ( $question_id > 0 ) {
			$attempt_answers_map[ $question_id ] = $question;
			$answer_status                       = QuizModel::get_attempt_answer_status( $question );
			$form_default_values[ "review_statuses[{$question_id}]" ] = $answer_status;
		}
	}
}
?>

<div class="wrap">

	<?php if ( ! is_admin() ) : ?>
	<form
		id="<?php echo esc_attr( $form_id ); ?>"
		x-data='(() => {
			const form = tutorForm({
				id: "<?php echo esc_attr( $form_id ); ?>",
				mode: "onSubmit",
				defaultValues: <?php echo wp_json_encode( $form_default_values, JSON_HEX_APOS ); ?>
			});
			const feedback =  tutorQuizAttemptFeedback({
				attemptId: <?php echo esc_attr( $attempt_id ); ?>,
				formId: "<?php echo esc_attr( $form_id ); ?>"
			});

			return {
				...form,
				...feedback,
				init() {
					form.init?.call(this);
					feedback.init?.call(this);
				},
			};
		})()'
		x-bind="getFormBindings()"
		@submit.prevent="handleSubmit((data) => handleSaveFeedback(data))($event)"
	>
	<?php endif; ?>

	<div class="tutor-quiz-attempt-details-wrapper ">
		<?php
		if ( is_admin() ) {
			tutor_load_template_from_custom_path(
				tutor()->path . '/views/quiz/attempt-details.php',
				array(
					'attempt_id'   => $attempt_id,
					'attempt_data' => $attempt_data,
					'user_id'      => (int) $user_id,
					'context'      => 'frontend-dashboard-students-attempts',
				)
			);
		} else {
			tutor_load_template(
				'shared.components.quiz.attempt-details',
				array(
					'attempt_id'           => $attempt_id,
					'attempt_data'         => $attempt_data,
					'quiz_id'              => $quiz_id,
					'user_id'              => (int) $user_id,
					'back_url'             => $back_url,
					'is_instructor_review' => true,
					'context'              => 'frontend-dashboard-students-attempts',
				)
			);
		}
		?>
	</div>

	<?php
	if ( is_admin() ) {
		tutor_load_template_from_custom_path(
			tutor()->path . 'views/quiz/instructor-feedback.php',
			array( 'attempt_data' => $attempt_data )
		);
	} else {
		tutor_load_template(
			'dashboard.quiz-attempts.quiz-attempts-feedback',
			array( 'attempt_data' => $attempt_data )
		);
	}
	?>

	<?php if ( ! is_admin() ) : ?>
	</form>
	<?php endif; ?>
</div>
