<?php
/**
 * Tutor learning area quiz attempt details.
 *
 * @package Tutor\Templates
 * @subpackage LearningArea
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use TUTOR\Icon;
use TUTOR\Quiz_Attempts_List;
use TUTOR\Input;
use Tutor\Models\QuizModel;
use Tutor\Components\Button;
use Tutor\Components\Constants\Size;
use Tutor\Components\Constants\Variant;

defined( 'ABSPATH' ) || exit;

global $tutor_current_post, $tutor_course_id;

$back_url = get_permalink( $tutor_current_post->ID );

$is_quiz_details_hidden = Quiz_Attempts_List::is_attempt_details_hidden();

if ( $is_quiz_details_hidden ) {
	return;
}

$quiz_id = $tutor_current_post->ID;
$user_id = get_current_user_id();

$attempt_id   = Input::get( 'attempt_id', 0, Input::TYPE_INT );
$attempt_data = null;

if ( $attempt_id > 0 ) {
	$attempt_data = tutor_utils()->get_attempt( $attempt_id );

	// Enforce ownership and quiz-match in learning area context.
	if (
		! $attempt_data ||
		(int) ( $attempt_data->quiz_id ?? 0 ) !== (int) $quiz_id ||
		(int) ( $attempt_data->user_id ?? 0 ) !== (int) $user_id
	) {
		$attempt_data = null;
	}
}

if ( ! $attempt_data ) {
	$attempt_data = ( new QuizModel() )->get_quiz_attempt( $quiz_id, $user_id );
}

if ( ! $attempt_data || empty( $attempt_data->attempt_id ) ) {
	tutor_utils()->tutor_empty_state( __( 'Attempt not found', 'tutor' ) );
	return;
}

$questions = tutor_utils()->get_questions_by_quiz( $quiz_id );

?>
<div class="tutor-quiz-summary-page">
	<div class="tutor-quiz-summary-header">
		<div class="tutor-quiz-summary-header-inner">
			<div class="tutor-flex tutor-items-center tutor-gap-4">
				<?php
					Button::make()
						->icon( Icon::ARROW_LEFT_2 )
						->tag( 'a' )
						->attr( 'href', $back_url )
						->variant( Variant::GHOST )
						->size( Size::X_SMALL )
						->icon_only()
						->render();
				?>
				<h5 class="tutor-h5 tutor-font-semibold"><?php esc_html_e( 'Quiz Summary', 'tutor' ); ?></h5>
			</div>

			<?php
				Button::make()
					->icon( Icon::CROSS )
					->tag( 'a' )
					->attr( 'href', $back_url )
					->attr( 'type', 'button' )
					->variant( Variant::GHOST )
					->size( Size::X_SMALL )
					->icon_only()
					->render();
			?>
		</div>
	</div>

	<div class="tutor-surface-l1">
		<?php tutor_load_template( 'shared.components.quiz.attempt-details.summary', array( 'attempt_data' => $attempt_data ) ); ?>
	</div>

	<div class="tutor-quiz-summary-body">
		<?php
		tutor_load_template(
			'shared.components.quiz.attempt-details.questions-sidebar',
			array(
				'quiz_id'      => $quiz_id,
				'attempt_data' => $attempt_data,
			)
		);
		?>
		<div class="tutor-quiz-summary-content">
			<h3 class="tutor-h3 tutor-sm-text-h5 tutor-text-subdued tutor-mb-10 tutor-sm-mb-5">
				<?php esc_html_e( 'Review your answers', 'tutor' ); ?>
			</h3>
			<?php
			tutor_load_template(
				'shared.components.quiz.attempt-details.review-answers',
				array(
					'questions'    => is_array( $questions ) ? $questions : array(),
					'attempt_data' => $attempt_data,
				)
			);
			?>
		</div>
	</div>
</div>
