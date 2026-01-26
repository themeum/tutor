<?php
/**
 * Assignment Details
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use Tutor\Components\AttachmentCard;

$assignment_description = "In this assignment, you'll demonstrate your understanding of React fundamentals by building a reusable component from scratch.
<ul>
 	<li>Create a reusable React component that demonstrates your understanding of:</li>
 	<li>Functional components</li>
 	<li>Props and prop types</li>
 	<li>State management with hooks</li>
 	<li>Event handling</li>
 	<li>Conditional rendering</li>
</ul>";

$assignment_files = array(
	array(
		'file_name' => 'starter-template.zip',
		'file_size' => '2.4 MB',
	),
	array(
		'file_name' => 'rubric-details.pdf',
		'file_size' => '124 KB',
	),
);

?>

<div class="tutor-assignment-description">
	<div class="tutor-small tutor-text-subdued tutor-sm-text-tiny">
		<?php esc_html_e( 'Assignment Description', 'tutor' ); ?>
	</div>
	<div class="tutor-medium tutor-text-secondary tutor-sm-text-small">
		<?php echo wp_kses_post( $assignment_description ); ?>
	</div>
</div>

<div class="tutor-assignment-attachments">
	<div class="tutor-small tutor-text-subdued tutor-sm-text-tiny">
		<?php esc_html_e( 'Assignments', 'tutor' ); ?>
	</div>
	<div class="tutor-medium tutor-sm-text-small">
		<?php esc_html_e( 'Download resources and materials for this assignment', 'tutor' ); ?>
	</div>
	<div class="tutor-assignment-attachments-cards">
		<?php foreach ( $assignment_files as $file ) : ?>
			<?php
			AttachmentCard::make()
				->file_name( $file['file_name'] )
				->file_size( $file['file_size'] )
				->render();
			?>
		<?php endforeach; ?>
	</div>
</div>