<?php
/**
 * Tutor dashboard quiz attempts list item.
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use TUTOR\Icon;

$attempts_count = count( $attempts );

if ( empty( $attempts ) ) {
	return;
}

$first_attempt      = $attempts[0];
$remaining_attempts = array_slice( $attempts, 1 );

?>
<div x-data="{ expanded: false }" class="tutor-quiz-attempts-item-wrapper" :class="{ 'tutor-quiz-previous-attempts': expanded }">
	<div class="tutor-quiz-attempts-item">
		<div class="tutor-quiz-item-info">
			<div class="tutor-flex tutor-items-start tutor-gap-4">
				<div class="tutor-text-medium tutor-text-semibold"><?php echo esc_html( $quiz_title ); ?></div>
				<?php if ( $attempts_count > 1 ) : ?>
					<button @click="expanded = !expanded" class="tutor-quiz-attempts-expand-btn">
						<?php
						echo sprintf(
							/* translators: %d: number of attempts */
							esc_html__( '%d Attempts', 'tutor' ),
							esc_attr( $attempts_count )
						);
						?>
					</button>
				<?php endif; ?>
			</div>
			<div class="tutor-text-small tutor-text-secondary">
				<?php esc_html_e( 'Course:', 'tutor' ); ?> 
				<span 
					x-data="tutorPreviewTrigger()"
					x-ref="trigger"
					class="tutor-preview-trigger"
					data-tutor-preview="course"
					data-tutor-preview-id="123"
					>
					<?php echo esc_html( $course_title ); ?>
					<div 
						x-ref="content"
						x-show="open"
						x-cloak
						@click.outside="handleClickOutside()"
						class="tutor-popover tutor-preview-card"
					>
						<div class="tutor-preview-card-loading" x-show="isLoading">Loading...</div>
					</div>
				</span>
			</div>
			<div class="tutor-text-tiny tutor-text-secondary"><?php echo esc_html( $first_attempt['date'] ); ?></div>
		</div>
		<div class="tutor-quiz-item-marks">
			<div x-data="tutorStatics({ value: <?php echo esc_attr( $first_attempt['marks_percent'] ); ?>, type: 'progress' })">
				<div x-html="render()"></div>
			</div>
			<div class="tutor-quiz-marks-breakdown">
				<div class="tutor-quiz-marks-correct">
					<?php
					/* translators: %d: number of correct answers */
					echo esc_html( sprintf( __( '%d correct', 'tutor' ), $first_attempt['correct_answers'] ) );
					?>
				</div>
				<div class="tutor-quiz-marks-incorrect">
					<?php
					/* translators: %d: number of incorrect answers */
					echo esc_html( sprintf( __( '%d incorrect', 'tutor' ), $first_attempt['incorrect_answers'] ) );
					?>
				</div>
			</div>
		</div>
		<div class="tutor-quiz-item-time">
			<?php tutor_utils()->render_svg_icon( Icon::STOPWATCH, 20, 20, array( 'class' => 'tutor-icon-secondary' ) ); ?>
			<?php echo esc_html( $first_attempt['time_taken'] ); ?>
		</div>
		<div class="tutor-quiz-item-result">
			<div class="tutor-badge <?php echo 'Passed' === $first_attempt['result'] ? 'tutor-badge-completed' : 'tutor-badge-failed'; ?> tutor-badge-circle">
				<?php echo esc_html( $first_attempt['result'] ); ?>
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
							<?php tutor_utils()->render_svg_icon( Icon::EYE ); ?> <?php esc_html_e( 'Details', 'tutor' ); ?>
						</button>
					</div>
				</div>
			</div>
		</div>
	</div>

	<?php if ( ! empty( $remaining_attempts ) ) : ?>
		<div x-show="expanded" x-collapse x-cloak class="tutor-quiz-previous-attempts">
			<div class="tutor-text-tiny tutor-text-subdued tutor-py-4 tutor-px-6">
				<?php esc_html_e( 'Previous Attempts', 'tutor' ); ?>
			</div>
			<?php foreach ( $remaining_attempts as $key => $attempt ) : ?>
				<div class="tutor-quiz-attempts-item">
					<div class="tutor-quiz-item-info">
						<div class="tutor-text-medium tutor-text-semibold">
							<?php
							/* translators: %d: attempt number */
							echo esc_html( sprintf( __( 'Attempt %d', 'tutor' ), count( $remaining_attempts ) - $key ) );
							?>
						</div>
						<div class="tutor-text-tiny tutor-text-secondary"><?php echo esc_html( $attempt['date'] ); ?></div>
					</div>
					<div class="tutor-quiz-item-marks">
						<div x-data="tutorStatics({ value: <?php echo esc_attr( $attempt['marks_percent'] ); ?>, type: 'progress' })">
							<div x-html="render()"></div>
						</div>
						<div class="tutor-quiz-marks-breakdown">
							<div class="tutor-quiz-marks-correct">
								<?php
								/* translators: %d: number of correct answers */
								echo esc_html( sprintf( __( '%d correct', 'tutor' ), $attempt['correct_answers'] ) );
								?>
							</div>
							<div class="tutor-quiz-marks-incorrect">
								<?php
								/* translators: %d: number of incorrect answers */
								echo esc_html( sprintf( __( '%d incorrect', 'tutor' ), $attempt['incorrect_answers'] ) );
								?>
							</div>
						</div>
					</div>
					<div class="tutor-quiz-item-time">
						<?php tutor_utils()->render_svg_icon( Icon::STOPWATCH, 20, 20, array( 'class' => 'tutor-icon-secondary' ) ); ?>
						<?php echo esc_html( $attempt['time_taken'] ); ?>
					</div>
					<div class="tutor-quiz-item-result">
						<div class="tutor-badge <?php echo 'Passed' === $attempt['result'] ? 'tutor-badge-completed' : 'tutor-badge-cancelled'; ?> tutor-badge-circle">
							<?php echo esc_html( $attempt['result'] ); ?>
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
										<?php tutor_utils()->render_svg_icon( Icon::EYE ); ?> <?php esc_html_e( 'Details', 'tutor' ); ?>
									</button>
								</div>
							</div>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</div>
