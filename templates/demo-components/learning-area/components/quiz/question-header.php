<?php
/**
 * True False
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

?>

<div class="tutor-quiz-question-header">
	<div class="tutor-quiz-question-number">
		<?php echo esc_html( $index ); ?>
	</div>

	<div class="tutor-quiz-question-title">
		<?php echo esc_html( $question_title ); ?>
	</div>

	<span class="tutor-badge tutor-badge-secondary tutor-badge-circle tutor-text-secondary">
		<span class="tutor-text-subdued">
			<?php esc_html_e( 'Points: ', 'tutor' ); ?>
		</span>
		<?php echo esc_html( $points ); ?>
	</span>
</div>
