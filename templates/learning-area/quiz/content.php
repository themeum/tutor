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
use Tutor\Components\ConfirmationModal;
use Tutor\Components\Constants\Size;
use Tutor\Components\Constants\Variant;
use Tutor\Models\QuizModel;
use TUTOR\Quiz;

global $tutor_current_post, $tutor_course_id;

$quiz = $quiz ?? null;
if ( ! $quiz || ! is_a( $quiz, 'WP_Post' ) ) {
	return;
}

$quiz_id            = $quiz->ID;
$total_questions    = (int) tutor_utils()->total_questions_for_student_by_quiz( $quiz_id );
$quiz_options       = get_post_meta( $quiz_id, 'tutor_quiz_option', true );
$total_marks        = Quiz::get_quiz_total_marks( $quiz_id );
$passing_grade      = (int) $quiz_options['passing_grade'] ?? 0;
$quiz_time          = $quiz_options['time_limit'] ?? null;
$has_time_limit     = is_array( $quiz_time ) && ! empty( $quiz_time['time_value'] ) && (int) $quiz_time['time_value'] > 0;
$quiz_item_readable = $has_time_limit ? $quiz_time['time_value'] . ' ' . $quiz_time['time_type'] : null;

$quiz_model        = new QuizModel();
$attempts          = $quiz_model->quiz_attempts( $quiz_id, get_current_user_id() );
$attempted_count   = is_array( $attempts ) ? count( $attempts ) : 0;
$feedback_mode     = tutor_utils()->get_quiz_option( $quiz_id, 'feedback_mode', 0 );
$attempts_allowed  = 'retry' !== $feedback_mode ? 1 : (int) tutor_utils()->get_quiz_option( $quiz_id, 'attempts_allowed', 0 );
$attempt_remaining = (int) $attempts_allowed - (int) $attempted_count;
$can_start_quiz    = $attempt_remaining > 0 || 0 === $attempts_allowed;

$current_content_id = $tutor_current_post ? $tutor_current_post->ID : $quiz_id;
$course_id          = $tutor_course_id ? $tutor_course_id : tutor_utils()->get_course_id_by_subcontent( $current_content_id );
$contents           = tutor_utils()->get_course_prev_next_contents_by_id( $current_content_id );
$next_id            = $contents ? $contents->next_id : 0;
$skip_url           = get_the_permalink( $next_id ? $next_id : $course_id );
$skip_modal_id      = 'tutor-quiz-skip-to-next';

$skip_modal_cancel_button = Button::make()
	->label( __( 'Cancel', 'tutor' ) )
	->variant( Variant::SECONDARY )
	->size( Size::SMALL )
	->attr( '@click', "TutorCore.modal.closeModal('$skip_modal_id')" )
	->get();

$skip_modal_confirm_button = Button::make()
	->tag( 'a' )
	->label( __( 'Yes, Skip This', 'tutor' ) )
	->variant( Variant::DESTRUCTIVE )
	->size( Size::SMALL )
	->attr( 'href', esc_url( $skip_url ) )
	->get();

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
				Quiz::render_quiz_summary( $total_questions, $quiz_item_readable, $total_marks, $passing_grade );
			?>
		</div>

		<!-- Past Attempts Section -->
		<?php Quiz::render_quiz_attempts( $quiz_id ); ?>
		
		<!-- Action Buttons -->
		<div class="tutor-quiz-intro-actions tutor-flex tutor-justify-end tutor-gap-3 tutor-mt-8">
			<?php
			if ( $can_start_quiz && 0 === $attempted_count ) {
				Button::make()
					->label( __( 'Skip Quiz', 'tutor' ) )
					->variant( Variant::GHOST )
					->attr( '@click', "TutorCore.modal.showModal('$skip_modal_id')" )
					->render();
			}
			?>

			<?php if ( $can_start_quiz ) : ?>
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
			<?php endif; ?>
		</div>
		<?php if ( $can_start_quiz && 0 === $attempted_count ) : ?>
			<?php
			ConfirmationModal::make()
				->id( $skip_modal_id )
				->title( __( 'Do You Want to Skip This Quiz?', 'tutor' ) )
				->message( __( 'Are you sure you want to skip this quiz? Please confirm your choice.', 'tutor' ) )
				->confirm_button( $skip_modal_confirm_button )
				->cancel_button( $skip_modal_cancel_button )
				->render();
			?>
		<?php endif; ?>
	</div>
</div>
