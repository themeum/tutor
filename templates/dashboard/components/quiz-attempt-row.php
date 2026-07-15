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

use Tutor\Components\PreviewTrigger;
use TUTOR\Icon;
use Tutor\Components\SvgIcon;
use Tutor\Components\Constants\Color;

if ( empty( $attempt ) ) {
	return;
}

$details_url = $quiz_attempt_obj->get_review_url(
	$attempt,
	array( 'action' => 'view_details' )
) ?? '#';

?>
<div class="tutor-quiz-attempts-item">
	<div class="tutor-quiz-item-info">
		<?php if ( ! empty( $quiz_title ) ) : ?>
		<a href="<?php echo esc_url( $details_url ); ?>" class="tutor-quiz-item-info-title">
			<?php echo esc_html( $quiz_title ); ?>
		</a>
		<?php endif; ?>

		<div class="tutor-quiz-item-info-course">
			<?php esc_html_e( 'Course:', 'tutor' ); ?> 
			<?php
			PreviewTrigger::make()
				->id( $course_id ?? 0 )
				->render()
			?>
		</div>

		<div class="tutor-quiz-item-info-student"><?php echo esc_html__( 'Student: ', 'tutor' ) . esc_html( $attempt['student'] ); ?></div>
		<div class="tutor-quiz-item-info-date"><?php echo esc_html( $attempt['date'] ?? '' ); ?></div>
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
		<?php SvgIcon::make()->name( Icon::STOPWATCH )->size( 20 )->color( Color::SECONDARY )->render(); ?>
		<?php echo esc_html( $attempt['time_taken'] ?? '' ); ?>
	</div>

	<div class="tutor-quiz-item-result">
		<?php
		$quiz_attempt_obj->render_quiz_attempt_list_badge( $attempt );
		$quiz_attempt_obj->render_quiz_attempt_popover( $attempt );
		?>

	</div>
</div>

<div class="tutor-quiz-item-actions">
	<?php $quiz_attempt_obj->render_quiz_attempt_buttons( $attempt ); ?>
</div>
