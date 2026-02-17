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

use Tutor\Components\PreviewTrigger;
use TUTOR\Icon;

if ( empty( $attempt ) ) {
	return;
}

$show_quiz_title = $show_quiz_title ?? false;
$show_course     = $show_course ?? false;
$attempt_number  = $attempt_number ?? null;
$attempts_count  = $attempts_count ?? 0;

?>
<div class="tutor-quiz-attempts-item">
	<div class="tutor-quiz-item-info">
		<?php
		if ( $show_quiz_title && ! empty( $quiz_title ) ) :
			?>
			<div class="tutor-flex tutor-items-start tutor-justify-start tutor-gap-4">
				<div class="tutor-quiz-item-info-title">
					<?php echo esc_html( $quiz_title ); ?>
				</div>
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
							<?php tutor_utils()->render_svg_icon( Icon::CHEVRON_DOWN, 18, 18 ); ?>
						</span>
					</button>
				<?php else : ?>
				<a href="<?php echo esc_url( $quiz_attempt_obj->get_review_url( $attempt ) ); ?>" class="tutor-text-medium-tiny tutor-student-attempt-detail tutor-text-brand"><?php echo esc_html__( 'See Details', 'tutor' ); ?></a>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<?php if ( $attempt_number ) : ?>
			<div class="tutor-flex tutor-items-start tutor-justify-start tutor-gap-4">
				<div class="tutor-quiz-item-info-title">
					<?php
					/* translators: %d: attempt number */
					echo esc_html( sprintf( __( 'Attempt %d', 'tutor' ), $attempt_number ) );
					?>
				</div>
				<a href="<?php echo esc_url( $quiz_attempt_obj->get_review_url( $attempt ) ); ?>" class="tutor-text-medium-tiny tutor-student-attempt-detail tutor-text-brand"><?php echo esc_html__( 'See Details', 'tutor' ); ?></a>
			</div>
		<?php endif; ?>

		<div class="tutor-quiz-item-info-course">
			<?php esc_html_e( 'Course:', 'tutor' ); ?> 
			<?php
			PreviewTrigger::make()
				->id( $course_id ?? 0 )
				->render()
			?>
		</div>

		<div class="tutor-quiz-item-info-date tutor-text-subdued"><?php echo esc_html( $attempt['date'] ?? '' ); ?></div>
		<?php if ( ! empty( $attempt['student'] ) ) : ?>
		<div class="tutor-quiz-item-info-date tutor-text-subdued"><?php echo esc_html__( 'Student Name: ', 'tutor' ) . esc_html( $attempt['student'] ); ?></div>
		<?php endif; ?>
	</div>

	<div class="tutor-quiz-item-marks">
		<div x-data="tutorStatics({ value: <?php echo esc_attr( $attempt['marks_percent'] ?? 0 ); ?>, type: 'progress' })">
			<div x-html="render()"></div>
		</div>
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
		<?php tutor_utils()->render_svg_icon( Icon::STOPWATCH, 20, 20, array( 'class' => 'tutor-icon-secondary' ) ); ?>
		<?php echo esc_html( $attempt['time_taken'] ?? '' ); ?>
	</div>

	<div class="tutor-quiz-item-result">
		<?php
		$quiz_attempt_obj->render_quiz_attempt_list_badge( $attempt );
		$quiz_attempt_obj->render_student_attempt_popover( $attempt, $attempts_count, $quiz_id );
		?>
		
	</div>
</div>
