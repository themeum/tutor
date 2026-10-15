<?php
/**
 * Tutor dashboard quiz attempt row.
 * Reusable component for displaying a single quiz attempt row.
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use TUTOR\Icon;
use TUTOR\Quiz_Attempts_List;
use Tutor\Components\SvgIcon;
use Tutor\Components\PreviewTrigger;
use Tutor\Components\Constants\Color;

if ( empty( $attempt ) ) {
	return;
}

$show_quiz_title  = $show_quiz_title ?? false;
$show_course      = $show_course ?? false;
$attempt_number   = $attempt_number ?? null;
$attempts_count   = $attempts_count ?? 0;
$is_previous      = $is_previous ?? false;
$is_learning_area = $is_learning_area ?? false;
$details_url      = $quiz_attempt_obj->get_review_url(
	$attempt,
	array( 'action' => 'view_details' )
) ?? '#';

$hide_quiz_details = Quiz_Attempts_List::is_attempt_details_hidden();

?>
<div class="tutor-quiz-attempts-item" data-learning-area="<?php echo esc_attr( $is_learning_area ? 'true' : 'false' ); ?>">
	<div class="tutor-quiz-item-info">
		<?php
		if ( $show_quiz_title && ! empty( $quiz_title ) ) :
			?>
			<div class="tutor-quiz-item-info-expanded">
				<?php if ( $hide_quiz_details ) : ?>
					<div class="tutor-medium tutor-font-semibold tutor-text-start">
						<?php echo esc_html( $quiz_title ); ?>
					</div>
				<?php else : ?>
					<a href="<?php echo esc_url( $details_url ); ?>" class="tutor-quiz-item-info-title">
						<?php echo esc_html( $quiz_title ); ?>
					</a>
				<?php endif; ?>
				<?php if ( $attempts_count > 1 ) : ?>
					<button @click="expanded = !expanded" class="tutor-quiz-attempts-expand-btn">
						<?php
						printf(
							/* translators: %d: number of attempts */
							esc_html__( '%d Attempts', 'tutor' ),
							esc_attr( $attempts_count )
						);
						?>
						<span class="tutor-quiz-attempts-expand-icon">
							<?php SvgIcon::make()->name( Icon::CHEVRON_DOWN )->size( 18 )->render(); ?>
						</span>
					</button>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<?php if ( $attempt_number ) : ?>
			<div class="tutor-flex tutor-items-start tutor-justify-start tutor-gap-4">
				<?php if ( $hide_quiz_details ) : ?>
					<div class="tutor-medium tutor-font-semibold">
					<?php
					/* translators: %d: attempt number */
					echo esc_html( sprintf( __( 'Attempt %d', 'tutor' ), $attempt_number ) );
					?>
					</div>
				<?php else : ?>
					<a href="<?php echo esc_url( $details_url ); ?>" class="tutor-quiz-item-info-title">
						<?php
						/* translators: %d: attempt number */
						echo esc_html( sprintf( __( 'Attempt %d', 'tutor' ), $attempt_number ) );
						?>
					</a>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<?php if ( ! $is_previous ) : ?>
			<div class="tutor-quiz-item-info-course">
				<?php esc_html_e( 'Course:', 'tutor' ); ?> 
				<?php
					PreviewTrigger::make()
						->id( $course_id ?? 0 )
						->render()
				?>
			</div>
		<?php endif; ?>

		<div class="tutor-quiz-item-info-date">
			<?php
			echo $is_learning_area && ! $hide_quiz_details
				? '<a href="' . esc_url( $details_url ) . '">' . esc_html( $attempt['date'] ?? '' ) . '</a>'
				: esc_html( $attempt['date'] ?? '' );
			?>
		</div>
	</div>

	<div class="tutor-quiz-item-marks">
		<?php Quiz_Attempts_List::render_quiz_attempt_marks_percentage( $attempt['result'], $attempt['marks_percent'] ); ?>
		<div class="tutor-quiz-marks-breakdown">
			<div class="tutor-quiz-marks-correct">
				<?php
				/* translators: %d: number of correct answers */
				echo esc_html( sprintf( __( '%d correct', 'tutor' ), $attempt['correct_answers'] ?? 0 ) );
				?>
			</div>
			<div class="tutor-quiz-marks-incorrect">
				<?php
				/* translators: %d: number of incorrect answers */
				echo esc_html( sprintf( __( '%d incorrect', 'tutor' ), $attempt['incorrect_answers'] ?? 0 ) );
				?>
			</div>
		</div>
	</div>

	<div class="tutor-quiz-item-time">
		<?php SvgIcon::make()->name( Icon::STOPWATCH )->size( 20 )->color( Color::SECONDARY )->render(); ?>
		<?php echo esc_html( $attempt['time_taken'] ?? '' ); ?>
	</div>

	<div class="tutor-quiz-item-result">
		<?php
		$quiz_attempt_obj->render_quiz_attempt_list_badge( $attempt );
		$quiz_attempt_obj->render_student_attempt_popover( $attempt, $attempts_count, $quiz_id, $is_learning_area );
		?>
	</div>
	<?php if ( $attempts_count > 1 ) : ?>
	<div class="tutor-quiz-item-actions" x-show="expanded" x-cloak>
		<?php
			$quiz_attempt_obj->render_details_button( $attempt );
			$quiz_attempt_obj->render_student_attempt_popover( $attempt, $attempts_count, $quiz_id, false, false );
		?>
	</div>
	<?php endif; ?>

	<?php if ( $attempt_number && $is_previous ) : ?>
	<div class="tutor-quiz-item-actions" x-show="expanded" x-cloak>
		<?php
		$quiz_attempt_obj->render_details_button( $attempt );
		?>
	</div>
	<?php endif; ?>
</div>
