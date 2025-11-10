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

?>
<div class="tutor-quiz-attempts-list-item-wrapper">
	<div class="tutor-quiz-attempts-list-item">
		<div class="tutor-quiz-item-info">
			<div class="tutor-flex tutor-items-center tutor-gap-4">
				<div class="tutor-text-medium tutor-text-semibold">Interactive Design Workshop</div>
				<button class="tutor-text-small tutor-font-medium tutor-text-brand">3 Attempts</button>
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
					Camera Skills & Photo Theory
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
			<div class="tutor-text-tiny tutor-text-secondary">Fri 8 Oct 2025, 2:30 PM</div>
		</div>
		<div class="tutor-quiz-item-marks">
			<div x-data="tutorStatics({ value: 75, type: 'progress' })">
				<div x-html="render()"></div>
			</div>
			<div class="tutor-quiz-marks-breakdown">
				<div class="tutor-quiz-marks-correct">
					<?php
					/* translators: %d: number of correct answers */
					echo sprintf( esc_html__( '%d correct', 'tutor' ), 9 );
					?>
				</div>
				<div class="tutor-quiz-marks-incorrect">
					<?php
					/* translators: %d: number of incorrect answers */
					echo sprintf( esc_html__( '%d incorrect', 'tutor' ), 1 );
					?>
				</div>
			</div>
		</div>
		<div class="tutor-quiz-item-time">
			<?php tutor_utils()->render_svg_icon( Icon::STOPWATCH, 20, 20, array( 'class' => 'tutor-icon-secondary' ) ); ?>
			1:15 min
		</div>
		<div class="tutor-quiz-item-result">
			<div class="tutor-badge tutor-badge-completed tutor-badge-circle"><?php esc_html_e( 'Passed', 'tutor' ); ?></div>
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
							<?php tutor_utils()->render_svg_icon( Icon::RELOAD_3 ); ?> Retry
						</button>
						<button class="tutor-popover-menu-item">
							<?php tutor_utils()->render_svg_icon( Icon::DELETE_2 ); ?> Details
						</button>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
