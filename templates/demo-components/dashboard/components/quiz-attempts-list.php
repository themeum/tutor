<?php
/**
 * Tutor dashboard quiz attempts list.
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use TUTOR\Icon;

$quiz_attempts = array(
	array(
		'quiz_title'   => 'Interactive Design Workshop',
		'course_title' => 'Camera Skills & Photo Theory',
		'attempts'     => array(
			array(
				'date'              => 'Fri 8 Oct 2025, 2:30 PM',
				'marks_percent'     => 75,
				'correct_answers'   => 9,
				'incorrect_answers' => 1,
				'time_taken'        => '15 mins',
				'result'            => 'Passed',
			),
		),
	),
	array(
		'quiz_title'   => 'Advanced Photography Techniques',
		'course_title' => 'Mastering the Art of Photography',
		'attempts'     => array(
			array(
				'date'              => 'Mon 12 Oct 2025, 10:00 AM',
				'marks_percent'     => 85,
				'correct_answers'   => 17,
				'incorrect_answers' => 3,
				'time_taken'        => '20 mins',
				'result'            => 'Passed',
			),
			array(
				'date'              => 'Wed 14 Oct 2025, 11:15 AM',
				'marks_percent'     => 65,
				'correct_answers'   => 13,
				'incorrect_answers' => 7,
				'time_taken'        => '25 mins',
				'result'            => 'Failed',
			),
			array(
				'date'              => 'Fri 16 Oct 2025, 9:45 AM',
				'marks_percent'     => 90,
				'correct_answers'   => 18,
				'incorrect_answers' => 2,
				'time_taken'        => '18 mins',
				'result'            => 'Passed',
			),
		),
	),
	array(
		'quiz_title'   => 'Basics of Lighting',
		'course_title' => 'Photography Lighting Essentials',
		'attempts'     => array(
			array(
				'date'              => 'Tue 20 Oct 2025, 3:00 PM',
				'marks_percent'     => 70,
				'correct_answers'   => 14,
				'incorrect_answers' => 6,
				'time_taken'        => '22 mins',
				'result'            => 'Passed',
			),
		),
	),
);

?>
<div class="tutor-quiz-attempts" x-data="tutorPreviewTrigger()">
	<div class="tutor-quiz-attempts-filter">
		<div class="tutor-quiz-attempts-filter-item">All Attempts (37) <?php tutor_utils()->render_svg_icon( Icon::CHEVRON_DOWN ); ?></div>
		<div class="tutor-quiz-attempts-filter-item">Newest First <?php tutor_utils()->render_svg_icon( Icon::CHEVRON_DOWN ); ?></div>
	</div>
	<div class="tutor-quiz-attempts-header">
		<div class="tutor-quiz-attempts-header-item"><?php esc_html_e( 'Quiz info', 'tutor' ); ?></div>
		<div class="tutor-quiz-attempts-header-item"><?php esc_html_e( 'Marks', 'tutor' ); ?></div>
		<div class="tutor-quiz-attempts-header-item"><?php esc_html_e( 'Time', 'tutor' ); ?></div>
		<div class="tutor-quiz-attempts-header-item"><?php esc_html_e( 'Result', 'tutor' ); ?></div>
	</div>
	<div class="tutor-quiz-attempts-list">
		<?php
		foreach ( $quiz_attempts as $quiz_index => $quiz_attempt ) {
			$attempts       = $quiz_attempt['attempts'];
			$attempts_count = count( $attempts );

			tutor_load_template(
				'demo-components.dashboard.components.quiz-attempts-list-item',
				array(
					'quiz_id'      => 'quiz-' . $quiz_index,
					'quiz_title'   => $quiz_attempt['quiz_title'],
					'course_title' => $quiz_attempt['course_title'],
					'attempts'     => $attempts,
				)
			);
		}
		?>
	</div>
</div>
