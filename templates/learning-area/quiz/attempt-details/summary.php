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

$preview_data = array(
	'title'          => 'Course Title',
	'url'            => 'https://example.com/course/123',
	'thumbnail'      => 'https://workademy.tutorlms.io/wp-content/uploads/2025/09/Cloud-It-Ops_-Cloud-Fundamentals-for-Enterprise-Teams.webp',
	'instructor'     => 'Instructor Name',
	'instructor_url' => '#',
)

?>
<div class="tutor-quiz-summary">
	<h2 class="tutor-h2 tutor-sm-text-h3 tutor-mb-3 tutor-sm-mb-2 tutor-text-center">Introduction to A-Frame</h2>
	<div class="tutor-medium tutor-sm-text-tiny tutor-text-subdued tutor-text-center tutor-mb-6">
		<?php esc_html_e( 'Topic', 'tutor' ); ?>
		<div 
			x-data="tutorPreviewTrigger({ data: <?php echo esc_attr( wp_json_encode( $preview_data ) ); ?> })"
			x-ref="trigger"
			class="tutor-preview-trigger"
		>
			<span class="tutor-preview-trigger-text tutor-text-secondary" >Basic of UX Design</span>
			<div 
				x-ref="content"
				x-show="open"
				x-cloak
				@click.outside="handleClickOutside()"
				class="tutor-popover tutor-preview-card"
			></div>
		</div>
		<?php esc_html_e( 'in', 'tutor' ); ?>
		<div 
			x-data="tutorPreviewTrigger({ data: <?php echo esc_attr( wp_json_encode( $preview_data ) ); ?> })"
			x-ref="trigger"
			class="tutor-preview-trigger"
		>
			<span class="tutor-preview-trigger-text tutor-text-secondary" >UI/UX Principles</span>
			<div 
				x-ref="content"
				x-show="open"
				x-cloak
				@click.outside="handleClickOutside()"
				class="tutor-popover tutor-preview-card"
			></div>
		</div>
	</div>
	<div class="tutor-quiz-result">
		<div class="tutor-quiz-result-progress" x-data="tutorStatics({ value: 75, size: 'large', type: 'progress' })">
			<div x-html="render()" ></div>
		</div>
		<div class="tutor-quiz-result-marks">
			<div class="tutor-result-badge passed">
				<?php tutor_utils()->render_svg_icon( Icon::BADGE_CHECK, 32, 32 ); ?>
				<?php esc_html_e( 'Passed', 'tutor' ); ?>
			</div>
			<!-- <div class="tutor-result-badge pending">
				<?php tutor_utils()->render_svg_icon( Icon::BADGE_INFO, 32, 32 ); ?>
				<?php esc_html_e( 'Pending', 'tutor' ); ?>
			</div>
			<div class="tutor-result-badge failed">
				<?php tutor_utils()->render_svg_icon( Icon::BADGE_INFO, 32, 32 ); ?>
				<?php esc_html_e( 'Failed', 'tutor' ); ?>
			</div> -->
			<div class="tutor-flex tutor-flex-column tutor-gap-2 tutor-sm-gap-1">
				<div class="tutor-flex tutor-items-center tutor-gap-3">
					<?php esc_html_e( 'Earned Marks', 'tutor' ); ?>
					<span class="tutor-font-semibold tutor-text-primary">7.00</span>
					<span>(70%)</span>
				</div>
				<div class="tutor-flex tutor-items-center tutor-gap-3">
					<?php esc_html_e( 'Pass Marks', 'tutor' ); ?>
					<span class="tutor-font-semibold tutor-text-primary">4.00</span>
					<span>(40%)</span>
				</div>
			</div>
			<div class="tutor-flex tutor-items-center tutor-gap-3">
				<?php tutor_utils()->render_svg_icon( Icon::CLOCK_2, 24, 24 ); ?>
				<span class="tutor-font-semibold tutor-text-primary">1:15sec</span>
				<span>of 3 min</span>
			</div>
		</div>
		<div class="tutor-quiz-result-statics">
			<div class="tutor-quiz-result-static-item correct">
				<span class="tutor-font-semibold tutor-text-primary">7</span> <?php esc_html_e( 'correct', 'tutor' ); ?>
			</div>
			<div class="tutor-quiz-result-static-item incorrect">
				<span class="tutor-font-semibold tutor-text-primary">3</span> <?php esc_html_e( 'incorrect', 'tutor' ); ?>
			</div>
			<div class="tutor-quiz-result-static-item total">
				<span class="tutor-font-semibold tutor-text-primary">10</span> <?php esc_html_e( 'total', 'tutor' ); ?>
			</div>
		</div>
		<div class="tutor-quiz-result-retake">
			<button type="button" class="tutor-btn tutor-btn-primary-soft tutor-gap-2 tutor-btn-block">
				<?php tutor_utils()->render_svg_icon( Icon::RELOAD_3, 20, 20 ); ?>
				<?php esc_html_e( 'Retake Quiz', 'tutor' ); ?>
			</button>
		</div>
	</div>
	<div class="tutor-tiny tutor-sm-text-tiny tutor-text-subdued tutor-text-center">
		<?php esc_html_e( 'Attempted on-', 'tutor' ); ?> Fri 8 Oct 2025, 2:30 PM
	</div>
</div>
