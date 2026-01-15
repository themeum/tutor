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
			Quiz::render_quiz_summary_table( $total_questions, $quiz_item_readable, $passing_grade );
		?>
	</div>
	<!-- Past Attempts Section -->
	<div class="tutor-quiz-intro-attempts tutor-mb-8">
		<div class="tutor-table-wrapper">
			<table class="tutor-table">
				<thead class="tutor-quiz-intro-attempts-head">
				<tr>
					<th class="tutor-quiz-intro-attempts-header">Attempts Date</th>
					<th class="tutor-quiz-intro-attempts-header">Marks</th>
					<th class="tutor-quiz-intro-attempts-header">Time</th>
					<th class="tutor-quiz-intro-attempts-header">Result</th>
				</tr>
				</thead>
				<tbody>
				<tr class="tutor-quiz-intro-attempts-row">
					<td class="tutor-quiz-intro-attempts-cell tutor-quiz-intro-attempts-date-col">
						<div class="tutor-quiz-intro-attempt-number">
							Attempts 2										
						</div>
						<span class="tutor-quiz-intro-attempt-date">
						Fri 8 Oct 2025, 2:30 PM										</span>
					</td>
					<td class="tutor-quiz-intro-attempts-cell tutor-quiz-intro-attempts-marks-col">
						<div class="tutor-flex tutor-gap-4 tutor-items-center tutor-quiz-intro-stats-row">
							<div x-data="tutorStatics({ value: 75, type: 'progress' })">
							<div x-html="render()" class="tutor-statics">
								<svg class="tutor-statics-progress" viewBox="0 0 44 44" width="44" height="44">
									<circle cx="22" cy="22" r="20.35" fill="none" stroke="var(--tutor-actions-brand-secondary)" stroke-width="3.3"></circle>
									<circle cx="22" cy="22" r="20.35" fill="none" stroke="var(--tutor-actions-brand-primary)" stroke-width="3.3" stroke-linecap="round" stroke-dasharray="127.86282100110459" stroke-dashoffset="31.965705250276145" style="transition: stroke-dashoffset 0.6s ease;"></circle>
								</svg>
								<div class="tutor-statics-progress-label">75%</div>
							</div>
							</div>
							<div class="tutor-quiz-intro-marks-breakdown">
							<div class="tutor-quiz-intro-marks-correct">
								9 correct												
							</div>
							<div class="tutor-quiz-intro-marks-incorrect">
								1 incorrect												
							</div>
							</div>
							<div class="tutor-quiz-intro-mobile-time">
							<div class="tutor-quiz-intro-time-info">
								<svg width="20" height="20" viewBox="0 0 20 20" fill="none" role="presentation" aria-hidden="true">
									<path d="M10 7.5V10.8333L11.6667 12.5M4.16699 2.5L1.66699 5M18.333 5L15.833 2.5M5.31634 15.583L3.33301 17.4997M14.7002 15.5586L16.6669 17.5003M16.6663 10.8337C16.6663 14.5156 13.6816 17.5003 9.99967 17.5003C6.31778 17.5003 3.33301 14.5156 3.33301 10.8337C3.33301 7.15176 6.31778 4.16699 9.99967 4.16699C13.6816 4.16699 16.6663 7.15176 16.6663 10.8337Z" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"></path>
								</svg>
								<span class="tutor-quiz-intro-time">1:15</span>
							</div>
							</div>
							<div class="tutor-quiz-intro-mobile-result">
							<span class="tutor-badge tutor-badge-completed tutor-badge-circle">
							Passed												</span>
							</div>
						</div>
					</td>
					<td class="tutor-quiz-intro-attempts-cell tutor-quiz-intro-attempts-time-col">
						<div class="tutor-quiz-intro-time-info">
							<svg width="20" height="20" viewBox="0 0 20 20" fill="none" role="presentation" aria-hidden="true">
							<path d="M10 7.5V10.8333L11.6667 12.5M4.16699 2.5L1.66699 5M18.333 5L15.833 2.5M5.31634 15.583L3.33301 17.4997M14.7002 15.5586L16.6669 17.5003M16.6663 10.8337C16.6663 14.5156 13.6816 17.5003 9.99967 17.5003C6.31778 17.5003 3.33301 14.5156 3.33301 10.8337C3.33301 7.15176 6.31778 4.16699 9.99967 4.16699C13.6816 4.16699 16.6663 7.15176 16.6663 10.8337Z" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"></path>
							</svg>
							<span class="tutor-quiz-intro-time">1:15</span>
						</div>
					</td>
					<td class="tutor-quiz-intro-attempts-cell tutor-quiz-intro-attempts-result-col">
						<span class="tutor-badge tutor-badge-completed tutor-badge-circle">
						Passed										</span>
					</td>
				</tr>
				</tbody>
			</table>
		</div>
	</div>
	<!-- Action Buttons -->
	<div class="tutor-quiz-intro-actions tutor-flex tutor-justify-end tutor-gap-3 tutor-mt-8">
		<button class="tutor-btn tutor-btn-ghost tutor-btn-md">
			<?php esc_html_e( 'Skip Quiz', 'tutor' ); ?>
		</button>
		<button class="tutor-btn tutor-btn-primary tutor-btn-md">
			<?php esc_html_e( 'Start Quiz', 'tutor' ); ?>
		</button>
	</div>
	</div>
	<?php tutor_load_template( 'learning-area.components.footer' ); ?>
</div>
