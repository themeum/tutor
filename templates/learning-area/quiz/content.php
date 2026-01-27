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

use Tutor\Components\Button;
use Tutor\Components\Table;
use TUTOR\Icon;
use TUTOR\Quiz;

defined( 'ABSPATH' ) || exit;


$quiz = $quiz ?? null;
if ( ! $quiz || ! is_a( $quiz, 'WP_Post' ) ) {
	return;
}

$quiz_id         = $quiz->ID;
$total_questions = (int) tutor_utils()->total_questions_for_student_by_quiz( $quiz_id );
$quiz_options    = get_post_meta( $quiz_id, 'tutor_quiz_option', true );

$passing_grade      = (int) $quiz_options['passing_grade'] ?? 0;
$quiz_time          = $quiz_options['time_limit'] ?? null;
$quiz_item_readable = ! empty( $quiz_time ) ? $quiz_time['time_value'] . ' ' . $quiz_time['time_type'] : '';

$attempts;

?>
<div class="tutor-quiz-intro">
	<div class="tutor-card tutor-quiz-intro-card">
	<!-- Quiz Icon -->
	<div class="tutor-quiz-intro-icon tutor-mb-8">
		<img src="http://localhost:10048/wp-content/plugins/tutor/assets/images/quiz-intro.svg" alt="Quiz" class="tutor-quiz-intro-icon-image">
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
			Quiz::render_quiz_summary( $total_questions, $quiz_item_readable, $passing_grade );
		?>
	</div>
	<!-- Past Attempts Section -->
	<div class="tutor-dashboard-page-card">
		<div class="tutor-quiz-attempts">
			<div class="tutor-quiz-attempts-header">
				<div class="tutor-quiz-attempts-header-item">Quiz info</div>
				<div class="tutor-quiz-attempts-header-item">Marks</div>
				<div class="tutor-quiz-attempts-header-item">Time</div>
				<div class="tutor-quiz-attempts-header-item">Result</div>
			</div>
			<div class="tutor-quiz-attempts-list">
				<div x-data="{ expanded: false }" class="tutor-quiz-attempts-item-wrapper" :class="{ 'tutor-quiz-previous-attempts': expanded }">
					<!-- First Attempt (Always Visible with Quiz Title & Expand Button) -->
					<div class="tutor-quiz-attempts-item">
					<div class="tutor-quiz-item-info">
						<div class="tutor-flex tutor-items-start tutor-justify-start tutor-gap-4">
							<div class="tutor-quiz-item-info-title">Attempt 2</div>
						</div>
						<div class="tutor-quiz-item-info-date">Fri 8 Oct 2025, 2:30 PM</div>
					</div>
					<div class="tutor-quiz-item-marks">
						<div x-data="tutorStatics({ value: 75, type: 'progress' })">
							<div x-html="render()" class="tutor-statics">
								<svg class="tutor-statics-progress" viewBox="0 0 44 44" width="44" height="44">
								<circle cx="22" cy="22" r="20.35" fill="none" stroke="var(--tutor-actions-brand-secondary)" stroke-width="3.3"></circle>
								<circle cx="22" cy="22" r="20.35" fill="none" stroke="var(--tutor-actions-brand-primary)" stroke-width="3.3" stroke-linecap="round" stroke-dasharray="127.86282100110459" stroke-dashoffset="31.965705250276145" style="transition: stroke-dashoffset 0.6s ease;"></circle>
								</svg>
								<div class="tutor-statics-progress-label">75%</div>
							</div>
						</div>
						<div class="tutor-quiz-marks-breakdown">
							<div class="tutor-quiz-marks-correct">
								9 correct			
							</div>
							<div class="tutor-quiz-marks-incorrect">
								1 incorrect			
							</div>
						</div>
					</div>
					<div class="tutor-quiz-item-time">
						<svg width="20" height="20" viewBox="0 0 20 20" fill="none" role="presentation" aria-hidden="true" class="tutor-icon-secondary">
							<path d="M10 7.5V10.8333L11.6667 12.5M4.16699 2.5L1.66699 5M18.333 5L15.833 2.5M5.31634 15.583L3.33301 17.4997M14.7002 15.5586L16.6669 17.5003M16.6663 10.8337C16.6663 14.5156 13.6816 17.5003 9.99967 17.5003C6.31778 17.5003 3.33301 14.5156 3.33301 10.8337C3.33301 7.15176 6.31778 4.16699 9.99967 4.16699C13.6816 4.16699 16.6663 7.15176 16.6663 10.8337Z" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"></path>
						</svg>
						15 mins	
					</div>
					<div class="tutor-quiz-item-result">
						<div class="tutor-badge tutor-badge-success tutor-badge-rounded">
							Passed		
						</div>
						<div x-data="tutorPopover({ placement: 'bottom', offset: 4 })" class="tutor-quiz-item-result-more">
							<button class="tutor-btn tutor-btn-secondary tutor-btn-icon tutor-btn-x-small" x-ref="trigger" @click="toggle()">
								<svg width="16" height="16" viewBox="0 0 32 32" fill="none" role="presentation" aria-hidden="true">
								<circle cx="16" cy="9.5" r="1.75" fill="currentColor"></circle>
								<circle cx="16" cy="16" r="1.75" fill="currentColor"></circle>
								<circle cx="16" cy="22.5" r="1.75" fill="currentColor"></circle>
								</svg>
							</button>
							<div x-ref="content" x-show="open" @click.outside="handleClickOutside()" class="tutor-popover" style="display: none;">
								<div class="tutor-popover-menu" style="min-width: 120px;">
								<button class="tutor-popover-menu-item">
									<svg width="16" height="16" viewBox="0 0 20 20" fill="none" role="presentation" aria-hidden="true">
										<path d="M17 10C17 11.3845 16.5895 12.7378 15.8203 13.889C15.0511 15.0401 13.9579 15.9373 12.6788 16.4672C11.3997 16.997 9.99224 17.1356 8.63437 16.8655C7.2765 16.5954 6.02922 15.9287 5.05026 14.9497C4.07129 13.9708 3.4046 12.7235 3.13451 11.3656C2.86441 10.0078 3.00303 8.6003 3.53285 7.32122C4.06266 6.04213 4.95987 4.94888 6.11101 4.17971C7.26215 3.41054 8.61553 3 10 3C11.96 3 13.8344 3.77778 15.2422 5.13111L17 6.88889M17 6.88889V3M17 6.88889H13.1111" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"></path>
									</svg>
									Retry					
								</button>
								<button class="tutor-popover-menu-item">
									<svg width="16" height="16" viewBox="0 0 24 24" fill="none" role="presentation" aria-hidden="true">
										<path d="M14.185 2.753v3.596a1.84 1.84 0 0 0 1.847 1.839h4.125" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"></path>
										<path d="M7.647 7.647h3.265M7.647 12h8.706m-8.706 4.353h8.706m3.897-7.785v8.568a4.25 4.25 0 0 1-1.362 2.97 4.282 4.282 0 0 1-3.072 1.14h-7.59a4.298 4.298 0 0 1-3.1-1.124 4.26 4.26 0 0 1-1.376-2.986V6.862a4.25 4.25 0 0 1 1.362-2.97 4.28 4.28 0 0 1 3.072-1.14h5.714a3.5 3.5 0 0 1 2.361.905l2.96 2.722a2.969 2.969 0 0 1 1.031 2.189Z" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"></path>
									</svg>
									Details					
								</button>
								</div>
							</div>
						</div>
					</div>
					<div class="tutor-quiz-item-buttons">
						<button class="tutor-btn tutor-btn-primary">
							<svg width="16" height="16" viewBox="0 0 20 20" fill="none" role="presentation" aria-hidden="true">
								<path d="M17 10C17 11.3845 16.5895 12.7378 15.8203 13.889C15.0511 15.0401 13.9579 15.9373 12.6788 16.4672C11.3997 16.997 9.99224 17.1356 8.63437 16.8655C7.2765 16.5954 6.02922 15.9287 5.05026 14.9497C4.07129 13.9708 3.4046 12.7235 3.13451 11.3656C2.86441 10.0078 3.00303 8.6003 3.53285 7.32122C4.06266 6.04213 4.95987 4.94888 6.11101 4.17971C7.26215 3.41054 8.61553 3 10 3C11.96 3 13.8344 3.77778 15.2422 5.13111L17 6.88889M17 6.88889V3M17 6.88889H13.1111" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"></path>
							</svg>
							Retry		
						</button>
						<button class="tutor-btn tutor-btn-secondary">
							<svg width="16" height="16" viewBox="0 0 24 24" fill="none" role="presentation" aria-hidden="true">
								<path d="M14.185 2.753v3.596a1.84 1.84 0 0 0 1.847 1.839h4.125" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"></path>
								<path d="M7.647 7.647h3.265M7.647 12h8.706m-8.706 4.353h8.706m3.897-7.785v8.568a4.25 4.25 0 0 1-1.362 2.97 4.282 4.282 0 0 1-3.072 1.14h-7.59a4.298 4.298 0 0 1-3.1-1.124 4.26 4.26 0 0 1-1.376-2.986V6.862a4.25 4.25 0 0 1 1.362-2.97 4.28 4.28 0 0 1 3.072-1.14h5.714a3.5 3.5 0 0 1 2.361.905l2.96 2.722a2.969 2.969 0 0 1 1.031 2.189Z" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"></path>
							</svg>
							Details		
						</button>
					</div>
					</div>
					<!-- Additional Attempts (Collapsible) -->
				</div>
			</div>
		</div>
	</div>
	<!-- Action Buttons -->
	<div class="tutor-quiz-intro-actions tutor-flex tutor-justify-end tutor-gap-3 tutor-mt-8">
		<?php
			Button::make()->label( __( 'Skip Quiz', 'tutor' ) )->attr( 'class', 'tutor-btn-ghost' )->render();
			Button::make()->label( __( 'Start Quiz', 'tutor' ) )->render();
		?>
	</div>
	</div>
</div>
