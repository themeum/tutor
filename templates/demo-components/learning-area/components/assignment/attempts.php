<?php
/**
 * Assignment Attempts
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use TUTOR\Icon;

$assignment_title = 'React Fundamentals: Building Your First Component';

$attempts = array(
	array(
		'attempt_id'   => '3',
		'attempt_date' => '2023-01-03 12:00:00',
		'total_marks'  => '100',
		'pass_marks'   => '50',
		'earned_marks' => '0',
		'status'       => 'pending',
	),
	array(
		'attempt_id'   => '1',
		'attempt_date' => '2023-01-01 12:00:00',
		'total_marks'  => '100',
		'pass_marks'   => '50',
		'earned_marks' => '80',
		'status'       => 'passed',
	),
	array(
		'attempt_id'   => '2',
		'attempt_date' => '2023-01-02 12:00:00',
		'total_marks'  => '100',
		'pass_marks'   => '50',
		'earned_marks' => '40',
		'status'       => 'failed',
	),
);

?>

<div class="tutor-assignment-attempts">
	<div class="tutor-assignment-attempts-table">
		<h4 class="tutor-h4 tutor-sm-text-medium">
			<?php echo esc_html( $assignment_title ); ?>
		</h4>

		<?php tutor_load_template( 'demo-components.learning-area.components.assignment.attempts-table', array( 'attempts' => $attempts ) ); ?>
	</div>

	<div class="tutor-assignment-actions">
		<a href="#" class="tutor-btn tutor-btn-primary tutor-gap-2">
			<?php tutor_utils()->render_svg_icon( Icon::RELOAD_2, 20, 20 ); ?>
			<?php esc_html_e( 'Resubmission', 'tutor' ); ?>
		</a>
	</div>

	<div class="tutor-section-separator tutor-my-4"></div>

	<?php tutor_load_template( 'demo-components.learning-area.components.assignment.details' ); ?>

	<div class="tutor-assignment-actions">
		<a href="#" class="tutor-btn tutor-btn-primary">
			<?php esc_html_e( 'Continue Lesson', 'tutor' ); ?>
		</a>
	</div>

</div>
