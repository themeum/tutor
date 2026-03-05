<?php
/**
 * Tutor quiz summary.
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use TUTOR\Icon;
use Tutor\Components\Button;
use Tutor\Components\Constants\Variant;
use Tutor\Components\PreviewTrigger;
use Tutor\Models\QuizModel;

if ( ! isset( $attempt_data ) || ! is_object( $attempt_data ) ) {
	return;
}

$attempt_id = (int) $attempt_data->attempt_id;
$quiz_id    = (int) $attempt_data->quiz_id;
$course_id  = (int) $attempt_data->course_id;
$topic_id   = (int) wp_get_post_parent_id( $quiz_id );

$attempt_info       = maybe_unserialize( $attempt_data->attempt_info );
$passing_grade      = is_array( $attempt_info ) ? (int) ( $attempt_info['passing_grade'] ?? 0 ) : 0;
$allowed_attempts   = is_array( $attempt_info ) ? (int) ( $attempt_info['attempts_allowed'] ?? 0 ) : 0;
$feedback_mode      = is_array( $attempt_info ) ? (string) ( $attempt_info['feedback_mode'] ?? '' ) : '';
$total_marks        = (float) $attempt_data->total_marks;
$earned_marks       = (float) $attempt_data->earned_marks;
$pass_marks         = ( $total_marks * $passing_grade ) / 100;
$earned_percentage  = (float) QuizModel::calculate_attempt_earned_percentage( $attempt_data );
$attempt_result     = QuizModel::get_attempt_result( $attempt_id );
$attempted_at_label = date_i18n( get_option( 'date_format' ) . ', ' . get_option( 'time_format' ), strtotime( $attempt_data->attempt_started_at ) );

$timing                 = QuizModel::get_quiz_attempt_timing( $attempt_data );
$attempt_duration       = $timing['attempt_duration'] ?? '';
$attempt_duration_taken = $timing['attempt_duration_taken'] ?? '';

$answers   = QuizModel::get_quiz_answers_by_attempt_id( $attempt_id );
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

$can_retry = 'retry' === $feedback_mode && $attempts_count < $allowed_attempts;

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
<div class="tutor-quiz-summary">
	<h2 class="tutor-h2 tutor-sm-text-h3 tutor-mb-3 tutor-sm-mb-2 tutor-text-center">
		<?php echo esc_html( get_the_title( $quiz_id ) ); ?>
	</h2>

	<div class="tutor-medium tutor-sm-text-tiny tutor-text-subdued tutor-text-center tutor-mb-6">
		<?php esc_html_e( 'Topic', 'tutor' ); ?>
		<?php
		if ( $topic_id ) {
			PreviewTrigger::make()->id( $topic_id )->render();
		} else {
			echo esc_html( get_the_title( $quiz_id ) );
		}
		?>
		<?php esc_html_e( 'in', 'tutor' ); ?>
		<?php
		if ( $course_id ) {
			PreviewTrigger::make()->id( $course_id )->render();
		} else {
			echo esc_html__( 'Course', 'tutor' );
		}
		?>
	</div>

	<div class="tutor-quiz-result">
		<div 
			class="tutor-quiz-result-progress"
			x-data="tutorStatics({ 
				value: <?php echo esc_attr( $earned_percentage ); ?>, 
				size: 'large', 
				type: 'progress' 
			})"
		>
			<div x-html="render()"></div>
		</div>

		<div class="tutor-quiz-result-marks">
			<div class="tutor-result-badge <?php echo esc_attr( $result_badge_class ); ?>">
				<?php tutor_utils()->render_svg_icon( $result_icon, 32, 32 ); ?>
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
				<?php tutor_utils()->render_svg_icon( Icon::CLOCK_2, 24, 24 ); ?>
				<span class="tutor-font-semibold tutor-text-primary">
					<?php echo esc_html( $attempt_duration_taken ); ?>
				</span>
				<?php if ( $attempt_duration ) : ?>
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

		<?php if ( $can_retry ) : ?>
			<div class="tutor-quiz-result-retake">
				<?php
				Button::make()
					->label( __( 'Retake Quiz', 'tutor' ) )
					->variant( Variant::PRIMARY_SOFT )
					->icon( Icon::RELOAD_3, 'left', 20, 20 )
					->attr( 'type', 'button' )
					->attr( 'class', 'tutor-gap-2 tutor-btn-block' )
					->attr(
						'@click',
						sprintf(
							'TutorCore.modal.showModal("tutor-retry-modal", { data: %s });',
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

	<div class="tutor-tiny tutor-sm-text-tiny tutor-text-subdued tutor-text-center">
		<?php
		echo esc_html(
			sprintf(
				/* translators: %s: localized attempt date time. */
				__( 'Attempted on- %s', 'tutor' ),
				$attempted_at_label
			)
		);
		?>
	</div>
</div>
