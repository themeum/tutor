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

use Tutor\Components\Button;
use Tutor\Components\Constants\Size;
use TUTOR\Icon;
use TUTOR\User;

$attempts_count = tutor_utils()->count( $attempts );
$is_student     = User::is_student( get_current_user_id() ) && tutor_utils()->is_enrolled( $course_id, get_current_user_id(), false );

if ( empty( $attempts ) ) {
	return;
}

$first_attempt      = $attempts[0];
$remaining_attempts = array_slice( $attempts, 1 );

$retry_button = Button::make()->label( __( 'Retry', 'tutor' ) )
					->icon( Icon::RELOAD )
					->size( Size::MEDIUM )
					->tag( 'a' )
					->variant( 'primary' )
					->attr( 'href', get_post_permalink( $quiz_id ) );

$attempt_info = $first_attempt['attempt_info'] ?? array();

$should_retry = false;

if ( tutor_utils()->count( $attempt_info ) ) {
	$allowed_attempts = (int) $attempt_info['attempts_allowed'] ?? 0;
	$feedback_mode    = $attempt_info['feedback_mode'] ?? '';
	$should_retry     = 'retry' === $feedback_mode && $attempts_count < $allowed_attempts;
}
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
			'course_id'       => $course_id,
			'show_quiz_title' => true,
			'show_course'     => true,
			'quiz_id'         => $quiz_id,
			'attempts_count'  => $attempts_count,
			'attempt_id'      => $first_attempt['attempt_id'] ?? 0,
			'is_student'      => $is_student,
			'should_retry'    => $should_retry,
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
						'course_id'      => $course_id,
						'is_student'     => $is_student,
						'should_retry'   => $should_retry,
					)
				);
				?>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

	<?php if ( $is_student && $should_retry ) : ?>
	<div class="tutor-quiz-item-actions tutor-flex">
		<?php $retry_button->render(); ?>
	</div>
	<?php endif; ?>
</div>
