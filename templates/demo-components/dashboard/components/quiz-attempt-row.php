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
		<?php if ( $show_quiz_title && ! empty( $quiz_title ) ) : ?>
			<div class="tutor-flex tutor-items-start tutor-justify-start tutor-gap-4">
				<div class="tutor-quiz-item-info-title"><?php echo esc_html( $quiz_title ); ?></div>
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
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<?php if ( $attempt_number ) : ?>
			<div class="tutor-quiz-item-info-title">
				<?php
				/* translators: %d: attempt number */
				echo esc_html( sprintf( __( 'Attempt %d', 'tutor' ), $attempt_number ) );
				?>
			</div>
		<?php endif; ?>

		<?php if ( $show_course && ! empty( $course_title ) && ! empty( $course_data ) ) : ?>
			<div class="tutor-quiz-item-info-course">
				<?php esc_html_e( 'Course:', 'tutor' ); ?> 
				<div 
					x-data="tutorPreviewTrigger({ data: <?php echo esc_attr( wp_json_encode( $course_data ) ); ?> })"
					x-ref="trigger"
					class="tutor-preview-trigger"
				>
					<span class="tutor-preview-trigger-text"><?php echo esc_html( $course_title ); ?></span>
					<div 
						x-ref="content"
						x-show="open"
						x-cloak
						@click.outside="handleClickOutside()"
						class="tutor-popover tutor-preview-card"
					>
					</div>
				</div>
			</div>
		<?php endif; ?>

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
		<?php tutor_utils()->render_svg_icon( Icon::STOPWATCH, 20, 20, array( 'class' => 'tutor-icon-secondary' ) ); ?>
		<?php echo esc_html( $attempt['time_taken'] ?? '' ); ?>
	</div>

	<div class="tutor-quiz-item-result">
		<div class="tutor-badge <?php echo 'Passed' === ( $attempt['result'] ?? '' ) ? 'tutor-badge-success' : 'tutor-badge-error'; ?> tutor-badge-rounded">
			<?php echo esc_html( $attempt['result'] ?? '' ); ?>
		</div>
		<div x-data="tutorPopover({ placement: 'bottom', offset: 4 })" class="tutor-quiz-item-result-more">
			<button class="tutor-btn tutor-btn-secondary tutor-btn-icon tutor-btn-x-small" x-ref="trigger" @click="toggle()">
				<?php tutor_utils()->render_svg_icon( Icon::THREE_DOTS_VERTICAL ); ?>
			</button>

			<div 
				x-ref="content"
				x-show="open"
				x-cloak
				@click.outside="handleClickOutside()"
				class="tutor-popover"
			>
				<div class="tutor-popover-menu" style="min-width: 120px;">
					<button class="tutor-popover-menu-item">
						<?php tutor_utils()->render_svg_icon( Icon::RELOAD_3 ); ?> <?php esc_html_e( 'Retry', 'tutor' ); ?>
					</button>
					<button class="tutor-popover-menu-item">
						<?php tutor_utils()->render_svg_icon( Icon::RESOURCES ); ?> <?php esc_html_e( 'Details', 'tutor' ); ?>
					</button>
				</div>
			</div>
		</div>
	</div>

	<div class="tutor-quiz-item-buttons">
		<button class="tutor-btn tutor-btn-primary">
			<?php tutor_utils()->render_svg_icon( Icon::RELOAD_3 ); ?> <?php esc_html_e( 'Retry', 'tutor' ); ?>
		</button>
		<button class="tutor-btn tutor-btn-secondary">
			<?php tutor_utils()->render_svg_icon( Icon::RESOURCES ); ?> <?php esc_html_e( 'Details', 'tutor' ); ?>
		</button>
	</div>
</div>
