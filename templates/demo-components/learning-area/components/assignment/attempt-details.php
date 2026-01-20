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
use Tutor\Components\AttachmentCard;

$assignment_title = 'React Fundamentals: Building Your First Component';

$attempts = array(
	array(
		'attempt_id'          => '3',
		'attempt_date'        => '2023-01-03 12:00:00',
		'total_marks'         => '100',
		'pass_marks'          => '50',
		'earned_marks'        => '0',
		'status'              => 'pending',
		'instructor_feedback' => "In this assignment, you'll demonstrate your understanding of React fundamentals by building a reusable component from scratch.",
		'content'             => "In this assignment, you'll demonstrate your understanding of React fundamentals by building a reusable component from scratch.
		
		Create a reusable React component that demonstrates your understanding of:
		- Functional components
		- Props and prop types
		- State management with hooks
		- Event handling
		- Conditional rendering",
		'attachments'         => array(
			array(
				'file_name' => 'assignment-1.pdf',
				'file_size' => '50MB',
			),
			array(
				'file_name' => 'assignment-2.pdf',
				'file_size' => '50MB',
			),
		),
	),
);

$attempt = $attempts[0];

// @TODO: Will be removed later
$attempts_url = add_query_arg(
	array(
		'subpage'  => 'assignment',
		'attempts' => 'true',
	),
	remove_query_arg( 'attempt_id' )
);

?>

<div class="tutor-assignment-attempts tutor-assignment-attempts-details">
	<div class="tutor-assignment-attempts-header">
		<div>
			<a href="<?php echo esc_url( $attempts_url ); ?>" class="tutor-btn tutor-btn-secondary tutor-gap-2">
				<?php tutor_utils()->render_svg_icon( Icon::ARROW_LEFT ); ?>
				<?php esc_html_e( 'Back', 'tutor' ); ?>
			</a>
		</div>
		
		<div class="tutor-assignment-attempts-table">
			<h4 class="tutor-h4 tutor-sm-text-medium">
				<?php echo esc_html( $assignment_title ); ?>
			</h4>

			<?php tutor_load_template( 'demo-components.learning-area.components.assignment.attempts-table', array( 'attempts' => $attempts ) ); ?>
		</div>
	</div>

	<div class="tutor-assignment-submission">
		<div class="tutor-small tutor-text-subdued tutor-sm-text-tiny">
			<?php esc_html_e( 'Your Submission', 'tutor' ); ?>
		</div>

		<div class="tutor-p1 tutor-sm-text-small">
			<?php echo wp_kses_post( $attempt['content'] ); ?>
		</div>

		<?php if ( ! empty( $attempt['attachments'] ) ) : ?>
			<div class="tutor-assignment-attachments-cards tutor-mt-6">
				<?php foreach ( $attempt['attachments'] as $attachment ) : ?>
					<?php
					AttachmentCard::make()
						->file_name( $attachment['file_name'] )
						->file_size( $attachment['file_size'] )
						->render();
					?>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>

	<?php if ( ! empty( $attempt['instructor_feedback'] ) ) : ?>
		<div class="tutor-assignment-feedback">
			<div class="tutor-small tutor-text-subdued tutor-sm-text-tiny">
				<?php esc_html_e( 'Instructor Feedback', 'tutor' ); ?>
			</div>

			<div class="tutor-p1 tutor-sm-text-small">
				<?php echo wp_kses_post( $attempt['instructor_feedback'] ); ?>
			</div>
		</div>
	<?php endif; ?>

	<div class="tutor-assignment-actions">
		<a href="#" class="tutor-btn tutor-btn-primary">
			<?php esc_html_e( 'Continue Lesson', 'tutor' ); ?>
		</a>
	</div>

</div>
