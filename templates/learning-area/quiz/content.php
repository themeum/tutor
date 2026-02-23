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
use Tutor\Models\QuizModel;
use TUTOR\Quiz;

defined( 'ABSPATH' ) || exit;


$quiz = $quiz ?? null;
if ( ! $quiz || ! is_a( $quiz, 'WP_Post' ) ) {
	return;
}

$user_id         = get_current_user_id();
$quiz_id         = $quiz->ID;
$total_questions = (int) tutor_utils()->total_questions_for_student_by_quiz( $quiz_id );
$quiz_options    = get_post_meta( $quiz_id, 'tutor_quiz_option', true );

$passing_grade      = (int) $quiz_options['passing_grade'] ?? 0;
$quiz_time          = $quiz_options['time_limit'] ?? null;
$quiz_item_readable = ! empty( $quiz_time ) ? $quiz_time['time_value'] . ' ' . $quiz_time['time_type'] : '';

$quiz_model = new QuizModel();
$attempts   = $quiz_model->quiz_attempts( $quiz_id, $user_id );

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
				<div class="tutor-quiz-attempts-header-item"><?php esc_html_e( 'Quiz info', 'tutor' ); ?></div>
				<div class="tutor-quiz-attempts-header-item"><?php esc_html_e( 'Marks', 'tutor' ); ?></div>
				<div class="tutor-quiz-attempts-header-item"><?php esc_html_e( 'Time', 'tutor' ); ?></div>
				<div class="tutor-quiz-attempts-header-item"><?php esc_html_e( 'Result', 'tutor' ); ?></div>
			</div>

			<?php if ( ! empty( $attempts ) ) : ?>
				<div class="tutor-quiz-attempts-list">
					<?php
					foreach ( $attempts as $index => $attempt ) :
						$attempt_number = $index + 1;
						$attempt_info   = maybe_unserialize( $attempt->attempt_info );
						$total_marks    = floatval( $attempt->total_marks );
						$earned_marks   = floatval( $attempt->earned_marks );
						$percentage     = $total_marks > 0 ? round( ( $earned_marks / $total_marks ) * 100 ) : 0;

						// Calculate correct and incorrect answers.
						$answers           = QuizModel::get_quiz_answers_by_attempt_id( $attempt->attempt_id );
						$correct_answers   = 0;
						$incorrect_answers = 0;
						if ( is_array( $answers ) && count( $answers ) > 0 ) {
							foreach ( $answers as $answer ) {
								if ( (bool) isset( $answer->is_correct ) ? $answer->is_correct : '' ) {
									$correct_answers++;
								} elseif ( 'open_ended' !== $answer->question_type || 'short_answer' !== $answer->question_type ) {
									$incorrect_answers++;
								}
							}
						}

						// Determine pass/fail status.
						$passing_grade = isset( $attempt_info['passing_grade'] ) ? intval( $attempt_info['passing_grade'] ) : 80;

						$pass_badge_class = 'tutor-badge-warning';
						if ( QuizModel::RESULT_PASS === $attempt->result ) {
							$pass_badge_class = 'tutor-badge-success';
						} elseif ( QuizModel::RESULT_FAIL === $attempt->result ) {
							$pass_badge_class = 'tutor-badge-danger';
						}

						$time_display = $attempt->attempt_ended_at && $attempt->attempt_ended_at ? human_time_diff( strtotime( $attempt->attempt_started_at ), strtotime( $attempt->attempt_ended_at ) ) : 'N/A';
						$attempt_date = tutor_i18n_get_formated_date( $attempt->attempt_started_at );

						// Calculate stroke offset for progress circle.
						$circumference = 127.86282100110459;
						$stroke_offset = $circumference - ( $percentage / 100 ) * $circumference;
						?>
					<div class="tutor-quiz-attempts-item-wrapper">
						<div class="tutor-quiz-attempts-item">
							<div class="tutor-quiz-item-info">
								<div class="tutor-flex tutor-items-start tutor-justify-start tutor-gap-4">
									<div class="tutor-quiz-item-info-title">
										<?php
										// translators: %d is the number of attempt.
										printf( esc_html__( 'Attempt %d', 'tutor' ), (int) $attempt_number );
										?>
									</div>
								</div>
								<div class="tutor-quiz-item-info-date"><?php echo esc_html( $attempt_date ); ?></div>
							</div>
							<div class="tutor-quiz-item-marks">
								<div x-data="tutorStatics({ value: <?php echo esc_attr( $percentage ); ?>, type: 'progress' })">
									<div x-html="render()" class="tutor-statics">
										<svg class="tutor-statics-progress" viewBox="0 0 44 44" width="44" height="44">
											<circle cx="22" cy="22" r="20.35" fill="none" stroke="var(--tutor-actions-brand-secondary)" stroke-width="3.3"></circle>
											<circle cx="22" cy="22" r="20.35" fill="none" stroke="var(--tutor-actions-brand-primary)" stroke-width="3.3" stroke-linecap="round" stroke-dasharray="<?php echo esc_attr( $circumference ); ?>" stroke-dashoffset="<?php echo esc_attr( $stroke_offset ); ?>" style="transition: stroke-dashoffset 0.6s ease;"></circle>
										</svg>
										<div class="tutor-statics-progress-label"><?php echo esc_html( $percentage ); ?>%</div>
									</div>
								</div>
								<div class="tutor-quiz-marks-breakdown">
									<div class="tutor-quiz-marks-correct">
										<?php
										// translators: %d number of correct ans.
										printf( esc_html__( '%d correct', 'tutor' ), (int) $correct_answers );
										?>
									</div>
									<div class="tutor-quiz-marks-incorrect">
										<?php
										// translators: %d number of incorrect ans.
										printf( esc_html__( '%d incorrect', 'tutor' ), (int) $incorrect_answers );
										?>
									</div>
								</div>
							</div>

							<div class="tutor-quiz-item-time">
								<svg width="20" height="20" viewBox="0 0 20 20" fill="none" role="presentation" aria-hidden="true" class="tutor-icon-secondary">
									<path d="M10 7.5V10.8333L11.6667 12.5M4.16699 2.5L1.66699 5M18.333 5L15.833 2.5M5.31634 15.583L3.33301 17.4997M14.7002 15.5586L16.6669 17.5003M16.6663 10.8337C16.6663 14.5156 13.6816 17.5003 9.99967 17.5003C6.31778 17.5003 3.33301 14.5156 3.33301 10.8337C3.33301 7.15176 6.31778 4.16699 9.99967 4.16699C13.6816 4.16699 16.6663 7.15176 16.6663 10.8337Z" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"></path>
								</svg>
								<?php echo esc_html( $time_display ); ?>
							</div>

							<div class="tutor-quiz-item-result">
								<div class="tutor-badge <?php esc_attr( $pass_badge_class ); ?>  tutor-badge-rounded">
								<?php echo esc_html( $attempt->result ); ?>
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
												<?php esc_html_e( 'Retry', 'tutor' ); ?>
											</button>
											<button class="tutor-popover-menu-item">
												<svg width="16" height="16" viewBox="0 0 24 24" fill="none" role="presentation" aria-hidden="true">
													<path d="M14.185 2.753v3.596a1.84 1.84 0 0 0 1.847 1.839h4.125" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"></path>
													<path d="M7.647 7.647h3.265M7.647 12h8.706m-8.706 4.353h8.706m3.897-7.785v8.568a4.25 4.25 0 0 1-1.362 2.97 4.282 4.282 0 0 1-3.072 1.14h-7.59a4.298 4.298 0 0 1-3.1-1.124 4.26 4.26 0 0 1-1.376-2.986V6.862a4.25 4.25 0 0 1 1.362-2.97 4.28 4.28 0 0 1 3.072-1.14h5.714a3.5 3.5 0 0 1 2.361.905l2.96 2.722a2.969 2.969 0 0 1 1.031 2.189Z" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"></path>
												</svg>
												<?php esc_html_e( 'Details', 'tutor' ); ?>
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
									<?php esc_html_e( 'Retry', 'tutor' ); ?>
								</button>
								<button class="tutor-btn tutor-btn-secondary">
									<svg width="16" height="16" viewBox="0 0 24 24" fill="none" role="presentation" aria-hidden="true">
										<path d="M14.185 2.753v3.596a1.84 1.84 0 0 0 1.847 1.839h4.125" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"></path>
										<path d="M7.647 7.647h3.265M7.647 12h8.706m-8.706 4.353h8.706m3.897-7.785v8.568a4.25 4.25 0 0 1-1.362 2.97 4.282 4.282 0 0 1-3.072 1.14h-7.59a4.298 4.298 0 0 1-3.1-1.124 4.26 4.26 0 0 1-1.376-2.986V6.862a4.25 4.25 0 0 1 1.362-2.97 4.28 4.28 0 0 1 3.072-1.14h5.714a3.5 3.5 0 0 1 2.361.905l2.96 2.722a2.969 2.969 0 0 1 1.031 2.189Z" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"></path>
									</svg>
									<?php esc_html_e( 'Details', 'tutor' ); ?>
								</button>
							</div>
						</div>
					</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
	<!-- Action Buttons -->
	<div class="tutor-quiz-intro-actions tutor-flex tutor-justify-end tutor-gap-3 tutor-mt-8">
		<?php
			Button::make()->label( __( 'Skip Quiz', 'tutor' ) )->attr( 'class', 'tutor-btn-ghost' )->render();
		?>

		<form id="tutor-start-quiz" method="post">
			<?php wp_nonce_field( tutor()->nonce_action, tutor()->nonce ); ?>

			<input type="hidden" value="<?php echo esc_attr( $quiz_id ); ?>" name="quiz_id"/>
			<input type="hidden" value="tutor_start_quiz" name="tutor_action"/>

			<?php Button::make()->label( __( 'Start Quiz', 'tutor' ) )->render(); ?>
		</form>
	</div>
	</div>
</div>
