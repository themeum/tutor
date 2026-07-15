<?php
/**
 * Tutor quiz summary.
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use TUTOR\Icon;
use Tutor\Components\SvgIcon;
use Tutor\Components\Button;
use Tutor\Components\ConfirmationModal;
use Tutor\Components\Constants\Color;
use Tutor\Components\Progress;
use Tutor\Components\Constants\Variant;
use Tutor\Components\PreviewTrigger;
use Tutor\Models\QuizModel;
use TUTOR\Quiz;
use Tutor\Helpers\UrlHelper;
use TUTOR\Quiz_Attempts_List;

if ( ! isset( $attempt_data ) || ! is_object( $attempt_data ) ) {
	return;
}

$attempt_id           = (int) $attempt_data->attempt_id;
$quiz_id              = (int) $attempt_data->quiz_id;
$course_id            = (int) $attempt_data->course_id;
$topic_id             = (int) wp_get_post_parent_id( $quiz_id );
$is_instructor_review = ! empty( $is_instructor_review );
$student_id           = (int) ( $attempt_data->user_id ?? 0 );
$student              = $student_id > 0 ? get_userdata( $student_id ) : null;
$student_name         = $student ? $student->display_name : '';
$student_profile_url  = $student_id > 0 ? tutor_utils()->profile_url( $student_id, false ) : '';

$attempt_info         = maybe_unserialize( $attempt_data->attempt_info );
$passing_grade        = is_array( $attempt_info ) ? (int) ( $attempt_info['passing_grade'] ?? 0 ) : 0;
$instructor_feedback  = is_array( $attempt_info ) ? (string) ( $attempt_info['instructor_feedback'] ?? '' ) : '';
$total_marks          = (float) $attempt_data->total_marks;
$earned_marks         = (float) $attempt_data->earned_marks;
$pass_marks           = ( $total_marks * $passing_grade ) / 100;
$earned_percentage    = (float) QuizModel::calculate_attempt_earned_percentage( $attempt_data );
$attempt_result       = QuizModel::get_attempt_result( $attempt_id );
$attempted_at_label   = date_i18n( get_option( 'date_format' ) . ', ' . get_option( 'time_format' ), strtotime( $attempt_data->attempt_started_at ) );
$is_manually_reviewed = ! empty( $attempt_data->is_manually_reviewed );

$timing                 = QuizModel::get_quiz_attempt_timing( $attempt_data );
$attempt_duration       = $timing['attempt_duration'] ?? '';
$attempt_duration_taken = $timing['attempt_duration_taken'] ?? '';

$answers   = isset( $answers ) ? $answers : QuizModel::get_quiz_answers_by_attempt_id( $attempt_id );
$correct   = 0;
$incorrect = 0;

if ( is_array( $answers ) ) {
	foreach ( $answers as $answer ) {
		if ( ! empty( $answer->is_correct ) ) {
			++$correct;
		} elseif ( ! in_array( $answer->question_type, array( 'open_ended', 'short_answer' ), true ) ) {
			++$incorrect;
		}
	}
}

$total_questions = (int) $attempt_data->total_questions;
$attempts_count  = 0;

$attempts = ( new QuizModel() )->quiz_attempts( $quiz_id, get_current_user_id() );
if ( is_array( $attempts ) ) {
	$attempts_count = count( $attempts );
}

$quiz_settings           = tutor_utils()->get_quiz_option( $quiz_id, '', array() );
$limit_attempts_allowed  = '1' === (string) ( $quiz_settings['limit_attempts_allowed'] ?? '0' );
$attempts_allowed        = (int) ( $quiz_settings['attempts_allowed'] ?? 0 );
$can_retry               = Quiz::can_retry_quiz( $limit_attempts_allowed, $attempts_allowed, $attempts_count );
$has_instructor_feedback = '' !== trim( wp_strip_all_tags( $instructor_feedback ) );
$retry_modal_id          = 'tutor-retry-modal-' . $attempt_id;

$result_badge_class = 'failed';
$result_label       = __( 'Failed', 'tutor' );
$result_icon        = Icon::BADGE_INFO;

if ( QuizModel::RESULT_PASS === $attempt_result ) {
	$result_badge_class = 'passed';
	$result_label       = __( 'Passed', 'tutor' );
	$result_icon        = Icon::BADGE_CHECK;
} elseif ( QuizModel::RESULT_PENDING === $attempt_result ) {
	$result_badge_class = 'pending';
	$result_label       = __( 'Pending', 'tutor' );
}
?>
<div class="tutor-quiz-summary" x-data="tutorQuizRetryAttempt()" x-init="init()">
	<div class="tutor-quiz-summary-overview">
		<h2 class="tutor-h2 tutor-sm-text-h3 tutor-mb-3 tutor-sm-mb-2 tutor-text-center">
			<?php echo esc_html( get_the_title( $quiz_id ) ); ?>
		</h2>

		<div class="tutor-flex tutor-gap-2 tutor-items-center tutor-justify-center tutor-medium tutor-sm-text-tiny tutor-text-subdued tutor-mb-6">
			<div class="tutor-flex tutor-gap-2 tutor-items-center tutor-overflow-hidden">
				<?php esc_html_e( 'Topic', 'tutor' ); ?>
				<i class="tutor-text-secondary tutor-truncate">
					<?php echo esc_html( get_the_title( $topic_id ? $topic_id : $quiz_id ) ); ?>
				</i>
			</div>
			<div class="tutor-flex tutor-gap-2 tutor-items-center tutor-overflow-hidden">
				<?php esc_html_e( 'in', 'tutor' ); ?>
				<?php
				if ( $course_id ) {
					PreviewTrigger::make()->id( $course_id )->render();
				} else {
					echo esc_html__( 'Course', 'tutor' );
				}
				?>
			</div>
		</div>

		<div class="tutor-quiz-result">
			<?php Quiz_Attempts_List::render_quiz_attempt_marks_percentage( $attempt_result, $earned_percentage, 'large', 'tutor-quiz-result-progress' ); ?>

			<div class="tutor-quiz-result-marks">
				<div class="tutor-result-badge <?php echo esc_attr( $result_badge_class ); ?>">
					<?php SvgIcon::make()->name( $result_icon )->size( 32 )->render(); ?>
					<?php echo esc_html( $result_label ); ?>
				</div>

				<div class="tutor-flex tutor-flex-column tutor-gap-2 tutor-sm-gap-1">
					<div class="tutor-flex tutor-items-center tutor-gap-3">
						<?php esc_html_e( 'Earned Marks', 'tutor' ); ?>
						<span class="tutor-font-semibold tutor-text-primary">
							<?php echo esc_html( number_format_i18n( $earned_marks, 2 ) ); ?>
						</span>
						<span>
							<?php echo esc_html( '(' . number_format_i18n( $earned_percentage, 0 ) . '%)' ); ?>
						</span>
					</div>

					<div class="tutor-flex tutor-items-center tutor-gap-3">
						<?php esc_html_e( 'Pass Marks', 'tutor' ); ?>
						<span class="tutor-font-semibold tutor-text-primary">
							<?php echo esc_html( number_format_i18n( $pass_marks, 2 ) ); ?>
						</span>
						<span>
							<?php echo esc_html( '(' . number_format_i18n( $passing_grade, 0 ) . '%)' ); ?>
						</span>
					</div>
				</div>

				<div class="tutor-flex tutor-items-center tutor-gap-3">
					<?php SvgIcon::make()->name( Icon::CLOCK_2 )->size( 24 )->render(); ?>
					<span class="tutor-font-semibold tutor-text-primary">
						<?php echo esc_html( $attempt_duration_taken ); ?>
					</span>
					<?php if ( (int) $attempt_duration ) : ?>
						<span>
							<?php
								echo esc_html(
									sprintf(
										// translators: %s: localized attempt duration.
										__( 'of %s', 'tutor' ),
										$attempt_duration
									)
								);
							?>
						</span>
					<?php endif; ?>
				</div>
			</div>

			<div class="tutor-quiz-result-statics">
				<div class="tutor-quiz-result-static-item correct">
					<?php
					printf(
						wp_kses(
							/* translators: %d: number of correct answers. */
							__( '<span class="tutor-font-semibold tutor-text-primary">%d</span> correct', 'tutor' ),
							array(
								'span' => array(
									'class' => true,
								),
							)
						),
						esc_html( $correct )
					);
					?>
				</div>

				<div class="tutor-quiz-result-static-item incorrect">
					<?php
					printf(
						wp_kses(
							/* translators: %d: number of incorrect answers. */
							__( '<span class="tutor-font-semibold tutor-text-primary">%d</span> incorrect', 'tutor' ),
							array(
								'span' => array(
									'class' => true,
								),
							)
						),
						esc_html( $incorrect )
					);
					?>
				</div>

				<div class="tutor-quiz-result-static-item total">
					<?php
					printf(
						wp_kses(
							/* translators: %d: number of total questions. */
							__( '<span class="tutor-font-semibold tutor-text-primary">%d</span> total', 'tutor' ),
							array(
								'span' => array(
									'class' => true,
								),
							)
						),
						esc_html( $total_questions )
					);
					?>
				</div>
			</div>

			<?php if ( ! $is_instructor_review && $can_retry ) : ?>
				<div class="tutor-quiz-result-retake">
					<?php
					Button::make()
						->label( __( 'Retry Quiz', 'tutor' ) )
						->variant( Variant::PRIMARY_SOFT )
						->icon( Icon::RELOAD_3, 'left', 20 )
						->attr( 'type', 'button' )
						->attr( 'class', 'tutor-gap-2 tutor-btn-block' )
						->attr(
							'@click',
							sprintf(
								'TutorCore.modal.showModal("%s", { data: %s });',
								$retry_modal_id,
								wp_json_encode(
									array(
										'quizID'      => $quiz_id,
										'redirectURL' => get_post_permalink( $quiz_id ),
									)
								)
							)
						)
						->render();
					?>
				</div>
			<?php endif; ?>
		</div>
	</div>

	<div class="tutor-quiz-result-footer">
		<div>
			<?php
			echo esc_html(
				sprintf(
					/* translators: %s: localized attempt date time. */
					__( 'Attempted on: %s', 'tutor' ),
					$attempted_at_label
				)
			);
			?>
		</div>

		<?php if ( $is_instructor_review && $student_name && $student_profile_url ) : ?>
			<div>
				<?php esc_html_e( 'Attempted by:', 'tutor' ); ?>
				<a
					href="<?php echo esc_url( $student_profile_url ); ?>"
					class="tutor-font-semibold tutor-text-brand"
				>
					<?php echo esc_html( $student_name ); ?>
				</a>
			</div>
		<?php elseif ( ! $is_instructor_review && $is_manually_reviewed ) : ?>
			<div class="tutor-quiz-result-footer-note">
				<?php esc_html_e( 'Edited by Instructor', 'tutor' ); ?>
			</div>
		<?php endif; ?>
	</div>

	<?php if ( ! $is_instructor_review && $has_instructor_feedback ) : ?>
		<div class="tutor-quiz-summary-feedback">
			<div data-title>
				<?php esc_html_e( 'Instructor Feedback', 'tutor' ); ?>
			</div>
			<div data-body>
				<?php echo wp_kses_post( $instructor_feedback ); ?>
			</div>
		</div>
	<?php endif; ?>

	<?php if ( ! $is_instructor_review && $can_retry ) : ?>
		<?php
		ConfirmationModal::make()
			->id( $retry_modal_id )
			->title( __( 'Retake Quiz?', 'tutor' ) )
			->icon( tutor_utils()->get_themed_svg( 'images/illustrations/quiz-retry.svg' ), 80, 80, ConfirmationModal::ICON_TYPE_HTML )
			->message( __( 'Retrying this quiz will reset your current attempt. Your answers and score from this attempt will be lost.', 'tutor' ) )
			->confirm_handler( 'retryMutation?.mutate({...payload?.data})' )
			->confirm_text( __( 'Retry Quiz', 'tutor' ) )
			->mutation_state( 'retryMutation' )
			->render();
		?>
	<?php endif; ?>

	<?php if ( ! $answers ) : ?>
	<div class="tutor-empty-quiz-details">
		<?php SvgIcon::make()->name( Icon::WARNING )->color( Color::CRITICAL )->size( 20 )->render(); ?>
		<p class="error-message"><?php echo esc_html__( 'No Questions Answered.', 'tutor' ); ?></p>
	</div>
	<?php endif; ?>
</div>
