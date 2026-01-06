<?php
/**
 * Tutor dashboard quiz attempts group.
 * Accordion wrapper for one quiz with all its attempts.
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

$attempts_count = count( $attempts );

if ( empty( $attempts ) ) {
	return;
}

$first_attempt      = $attempts[0];
$remaining_attempts = array_slice( $attempts, 1 );


?>
<div x-data="{ expanded: false }" class="tutor-quiz-attempts-item-wrapper" :class="{ 'tutor-quiz-previous-attempts': expanded }">
	<!-- First Attempt (Always Visible with Quiz Title & Expand Button) -->
	<?php
	tutor_load_template(
		'dashboard.components.quiz-attempt-row',
		array(
			'attempt'         => $first_attempt,
			'quiz_title'      => $quiz_title,
			'course_title'    => $course_title,
			'show_quiz_title' => true,
			'show_course'     => true,
			'quiz_id'         => $quiz_id,
			'attempts_count'  => $attempts_count,
			'attempt_id'      => $first_attempt['attempt_id'] ?? 0,
		)
	);
	?>

	<!-- Additional Attempts (Collapsible) -->
	<?php if ( ! empty( $remaining_attempts ) ) : ?>
		<div x-show="expanded" x-collapse x-cloak class="tutor-quiz-previous-attempts">
			<div class="tutor-text-tiny tutor-text-subdued tutor-py-4 tutor-px-6 tutor-quiz-previous-attempts-title">
				<?php esc_html_e( 'Previous Attempts', 'tutor' ); ?>
			</div>
			<?php foreach ( $remaining_attempts as $key => $attempt ) : ?>
				<?php
				tutor_load_template(
					'dashboard.components.quiz-attempt-row',
					array(
						'attempt'        => $attempt,
						'attempt_number' => count( $remaining_attempts ) - $key,
						'quiz_id'        => $quiz_id,
						'attempt_id'     => $attempt['attempt_id'] ?? 0,
					)
				);
				?>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</div>
