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

use Tutor\Components\Button;

global $tutor_is_started_quiz;

$quiz_attempt_info = tutor_utils()->quiz_attempt_info( $tutor_is_started_quiz->attempt_info );

$quiz_attempt_info['date_time_now'] = date( 'Y-m-d H:i:s', tutor_time() );//phpcs:ignore
$time_limit_seconds                 = tutor_utils()->avalue_dot( 'time_limit.time_limit_seconds', $quiz_attempt_info );
$remaining_time_secs                = ( strtotime( $tutor_is_started_quiz->attempt_started_at ) + $time_limit_seconds ) - strtotime( $quiz_attempt_info['date_time_now'] );
$remaining_time_context             = tutor_utils()->seconds_to_time_context( $remaining_time_secs );

$questions = tutor_utils()->get_random_questions_by_quiz();

?>

<div class="tutor-quiz tutor-quiz-submission">
	<?php tutor_load_template( 'learning-area.quiz.progress-bar' ); ?>
	<div class="tutor-quiz-questions">
		<?php foreach ( $questions as $question ) : ?>
			<?php
			$template      = str_replace( '_', '-', $question->question_type ) . '.php';
			$template_path = __DIR__ . '/questions/' . $template;

			if ( file_exists( $template_path ) ) {
				require $template_path;
			}
			?>
		<?php endforeach; ?>
	</div>

	<?php Button::make()->label( __( 'Submit Quiz', 'tutor' ) )->attr( 'style', 'display: block; margin: 0 auto;' )->render(); ?>
</div>
