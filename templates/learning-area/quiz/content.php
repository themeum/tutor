<?php
/**
 * Tutor learning area quiz.
 *
 * @package Tutor\Templates
 * @subpackage LearningArea
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use Tutor\Components\Button;
use Tutor\Models\QuizModel;
use TUTOR\Quiz;

$quiz = $quiz ?? null;
if ( ! $quiz || ! is_a( $quiz, 'WP_Post' ) ) {
	return;
}

$quiz_id         = $quiz->ID;
$total_questions = (int) tutor_utils()->total_questions_for_student_by_quiz( $quiz_id );
$quiz_options    = get_post_meta( $quiz_id, 'tutor_quiz_option', true );

$passing_grade      = (int) $quiz_options['passing_grade'] ?? 0;
$quiz_time          = $quiz_options['time_limit'] ?? null;
$quiz_item_readable = ! empty( $quiz_time ) ? $quiz_time['time_value'] . ' ' . $quiz_time['time_type'] : '';

$quiz_model = new QuizModel();
$attempts   = $quiz_model->quiz_attempts( $quiz_id, $user_id );

?>
<div class="tutor-quiz-intro">
	<div class="tutor-card">
		<!-- Quiz Icon -->
		<div class="tutor-quiz-intro-icon tutor-mb-8">
			<img src="<?php echo esc_url( tutor()->url . 'assets/images/quiz-intro.svg' ); ?>" alt="<?php esc_attr_e( 'Quiz', 'tutor' ); ?>">
		</div>

		<!-- Quiz Title -->
		<h1 class="tutor-quiz-intro-title tutor-mb-5">
			<?php echo esc_html( $quiz->post_title ); ?>		
		</h1>

		<!-- Quiz Description -->
		<p class="tutor-quiz-intro-description tutor-mb-8">
			<?php echo wp_kses_post( $quiz->post_content ); ?>	
		</p>

		<!-- Quiz Parameters Table -->
		<div class="tutor-table-wrapper tutor-table-bordered tutor-table-column-borders tutor-quiz-intro-params tutor-mb-8">
			<?php
				Quiz::render_quiz_summary( $total_questions, $quiz_item_readable, $passing_grade );
			?>
		</div>

		<!-- Past Attempts Section -->
		<?php Quiz::render_quiz_attempts( $quiz_id ); ?>
		
		<!-- Action Buttons -->
		<div class="tutor-quiz-intro-actions tutor-flex tutor-justify-end tutor-gap-3 tutor-mt-8">
			<?php
				Button::make()->label( __( 'Skip Quiz', 'tutor' ) )->attr( 'class', 'tutor-btn-ghost' )->render();
			?>

			<form
				x-data="tutorQuizAutoStart({
					quizID: <?php echo esc_attr( $quiz_id ); ?>,
					autoStart: <?php echo $quiz_auto_start ? 'true' : 'false'; ?>,
				})"
				x-init="init()"
				@submit.prevent="handleStartQuiz()"
			>
				<?php wp_nonce_field( tutor()->nonce_action, tutor()->nonce ); ?>
				<input type="hidden" value="<?php echo esc_attr( $quiz_id ); ?>" name="quiz_id"/>
				<input type="hidden" value="tutor_start_quiz" name="tutor_action"/>

				<?php
				Button::make()
					->label( __( 'Start Quiz', 'tutor' ) )
					->attr( 'x-bind:disabled', 'startQuizMutation?.isPending' )
					->attr( ':class', "{ 'tutor-btn-loading': startQuizMutation?.isPending }" )
					->render();
				?>
			</form>
		</div>
	</div>
</div>
