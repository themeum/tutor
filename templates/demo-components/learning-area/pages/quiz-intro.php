<?php
/**
 * Quiz Intro Page
 *
 * @package Tutor\Templates
 * @subpackage DemoComponents\LearningArea\Pages
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.0.0
 */

use TUTOR\Icon;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Sample quiz data - in real implementation, these would come from quiz settings.
$quiz_title       = __( 'Quick Quiz', 'tutor' );
$quiz_description = __( 'Welcome to the Ultimate General Knowledge Quiz! Whether you\'re a trivia pro or just looking to test your brainpower, this quiz has something for everyone. Let\'s dive in and have some fun!', 'tutor' );
$quiz_questions   = 20;
$quiz_time        = __( '20 Minutes', 'tutor' );
$passing_grade    = 80;

// Sample attempts data - in real implementation, these would come from database.
$attempts = array(
	array(
		'attempt_number'    => 2,
		'date'              => __( 'Fri 8 Oct 2025, 2:30 PM', 'tutor' ),
		'marks_percent'     => 75,
		'correct_answers'   => 9,
		'incorrect_answers' => 1,
		'time_taken'        => '1:15',
		'result'            => __( 'Passed', 'tutor' ),
	),
	array(
		'attempt_number'    => 1,
		'date'              => __( 'Fri 8 Oct 2025, 2:30 PM', 'tutor' ),
		'marks_percent'     => 75,
		'correct_answers'   => 9,
		'incorrect_answers' => 1,
		'time_taken'        => '1:15',
		'result'            => __( 'Passed', 'tutor' ),
	),
);
?>

<div class="tutor-quiz-intro">
	<div class="tutor-card tutor-quiz-intro-card">
		<!-- Quiz Icon -->
		<div class="tutor-quiz-intro-icon tutor-mb-8">
			<img src="<?php echo esc_url( tutor()->url . 'assets/images/quiz-intro.svg' ); ?>" alt="<?php esc_attr_e( 'Quiz', 'tutor' ); ?>" class="tutor-quiz-intro-icon-image">
		</div>

		<!-- Quiz Title -->
		<h1 class="tutor-quiz-intro-title tutor-mb-5">
			<?php echo esc_html( $quiz_title ); ?>
		</h1>

		<!-- Quiz Description -->
		<p class="tutor-quiz-intro-description tutor-mb-8">
			<?php echo esc_html( $quiz_description ); ?>
		</p>

		<!-- Quiz Parameters Table -->
		<div class="tutor-quiz-intro-params tutor-mb-8">
			<div class="tutor-table-wrapper tutor-table-column-borders">
				<table class="tutor-table">
					<tbody>
						<tr>
							<td class="tutor-quiz-intro-param-label">
								<?php tutor_utils()->render_svg_icon( Icon::QUESTION_CIRCLE, 20, 20 ); ?>
								<span><?php esc_html_e( 'Questions', 'tutor' ); ?></span>
							</td>
							<td>
								<span><?php echo esc_html( $quiz_questions ); ?></span>
							</td>
						</tr>
						<tr>
							<td class="tutor-quiz-intro-param-label">
								<?php tutor_utils()->render_svg_icon( Icon::CLOCK, 20, 20 ); ?>
								<span><?php esc_html_e( 'Quiz time', 'tutor' ); ?></span>
							</td>
							<td>
								<span><?php echo esc_html( $quiz_time ); ?></span>
							</td>
						</tr>
						<tr>
							<td class="tutor-quiz-intro-param-label">
								<?php tutor_utils()->render_svg_icon( Icon::CERTIFICATE, 20, 20 ); ?>
								<span><?php esc_html_e( 'Passing Grade', 'tutor' ); ?></span>
							</td>
							<td>
								<span><?php echo esc_html( $passing_grade . '%' ); ?></span>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>

		<!-- Past Attempts Section -->
		<?php if ( ! empty( $attempts ) ) : ?>
			<div class="tutor-quiz-intro-attempts tutor-mb-8">
				<div class="tutor-table-wrapper">
					<table class="tutor-table">
						<thead class="tutor-quiz-intro-attempts-head">
							<tr>
								<th class="tutor-quiz-intro-attempts-header"><?php esc_html_e( 'Attempts Date', 'tutor' ); ?></th>
								<th class="tutor-quiz-intro-attempts-header"><?php esc_html_e( 'Marks', 'tutor' ); ?></th>
								<th class="tutor-quiz-intro-attempts-header"><?php esc_html_e( 'Time', 'tutor' ); ?></th>
								<th class="tutor-quiz-intro-attempts-header"><?php esc_html_e( 'Result', 'tutor' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $attempts as $index => $attempt ) : ?>
								<tr class="tutor-quiz-intro-attempts-row">
									<td class="tutor-quiz-intro-attempts-cell tutor-quiz-intro-attempts-date-col">
										<div class="tutor-quiz-intro-attempt-number">
											<?php
											$attempt_number = isset( $attempt['attempt_number'] ) ? $attempt['attempt_number'] : '';
											/* translators: %d: attempt number */
											echo esc_html( sprintf( __( 'Attempts %d', 'tutor' ), $attempt_number ) );
											?>
										</div>
										<span class="tutor-quiz-intro-attempt-date">
											<?php echo esc_html( $attempt['date'] ); ?>
										</span>
									</td>
									<td class="tutor-quiz-intro-attempts-cell tutor-quiz-intro-attempts-marks-col">
										<div class="tutor-flex tutor-gap-4 tutor-items-center tutor-quiz-intro-stats-row">
											<div x-data="tutorStatics({ value: <?php echo esc_attr( $attempt['marks_percent'] ); ?>, type: 'progress' })">
												<div x-html="render()"></div>
											</div>
											<div class="tutor-quiz-intro-marks-breakdown">
												<div class="tutor-quiz-intro-marks-correct">
													<?php
													/* translators: %d: number of correct answers */
													echo esc_html( sprintf( __( '%d correct', 'tutor' ), $attempt['correct_answers'] ) );
													?>
												</div>
												<div class="tutor-quiz-intro-marks-incorrect">
													<?php
													/* translators: %d: number of incorrect answers */
													echo esc_html( sprintf( __( '%d incorrect', 'tutor' ), $attempt['incorrect_answers'] ) );
													?>
												</div>
											</div>
											<div class="tutor-quiz-intro-mobile-time">
												<div class="tutor-quiz-intro-time-info">
													<?php tutor_utils()->render_svg_icon( Icon::STOPWATCH, 20, 20 ); ?>
													<span class="tutor-quiz-intro-time"><?php echo esc_html( $attempt['time_taken'] ); ?></span>
												</div>
											</div>
											<div class="tutor-quiz-intro-mobile-result">
												<span class="tutor-badge tutor-badge-completed tutor-badge-circle">
													<?php echo esc_html( $attempt['result'] ); ?>
												</span>
											</div>
										</div>
									</td>
									<td class="tutor-quiz-intro-attempts-cell tutor-quiz-intro-attempts-time-col">
										<div class="tutor-quiz-intro-time-info">
											<?php tutor_utils()->render_svg_icon( Icon::STOPWATCH, 20, 20 ); ?>
											<span class="tutor-quiz-intro-time"><?php echo esc_html( $attempt['time_taken'] ); ?></span>
										</div>
									</td>
									<td class="tutor-quiz-intro-attempts-cell tutor-quiz-intro-attempts-result-col">
										<span class="tutor-badge tutor-badge-completed tutor-badge-circle">
											<?php echo esc_html( $attempt['result'] ); ?>
										</span>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			</div>
		<?php endif; ?>

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
</div>

