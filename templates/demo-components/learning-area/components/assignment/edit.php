<?php
/**
 * Assignment Edit
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use TUTOR\Icon;

$back_url = add_query_arg(
	array(
		'subpage' => 'assignment',
	),
	remove_query_arg( 'edit' )
);

$assignment_title = 'React Fundamentals: Building Your First Component';

$file_uploader_config = array(
	'multiple'       => true,
	'accept'         => '.pdf,.doc,.docx,.jpg,.jpeg,.png',
	'max_size'       => 52428800,
	'icon'           => Icon::FILE_ATTACHEMENT,
	'title'          => __( 'Drop files here or click to upload', 'tutor' ),
	'subtitle'       => __( 'PDF, DOC, DOCX, JPG, PNG Formats (Max 50MB)', 'tutor' ),
	'button_text'    => __( 'Select Files', 'tutor' ),
	'on_file_select' => 'null',
	'on_error'       => 'null',
);

// @TODO: Will be removed later
$attemps_url = add_query_arg(
	array(
		'subpage'  => 'assignment',
		'attempts' => 'true',
	),
	remove_query_arg( 'edit' )
);

?>

<div class="tutor-assignment-edit">
	<div>
		<a href="<?php echo esc_url( $back_url ); ?>" class="tutor-btn tutor-btn-secondary tutor-gap-2">
			<?php tutor_utils()->render_svg_icon( Icon::ARROW_LEFT ); ?>
			<?php esc_html_e( 'Back', 'tutor' ); ?>
		</a>
	</div>

	<div class="tutor-assignment-form">
		<div class="tutor-small tutor-text-brand">
			<?php echo esc_html( $assignment_title ); ?>
		</div>

		<h4 class="tutor-h4">
			<?php esc_html_e( 'Submit Assignment', 'tutor' ); ?>
		</h4>

		<div class="tutor-input-field">
			<!-- @TODO: render tinyMCE editor -->
			<?php wp_editor( '', 'assignment_title', array() ); ?>
		</div>

		<div class="tutor-assignment-file-uploader">
			<div class="tutor-medium">
				<?php esc_html_e( 'Assignments', 'tutor' ); ?>
			</div>
			<?php tutor_load_template( 'core-components.file-uploader', $file_uploader_config ); ?>
		</div>
	</div>

	<div class="tutor-assignment-actions">
		<!-- @TODO: need to add functionality -->
		<button class="tutor-btn tutor-btn-ghost tutor-btn-medium">
			<?php esc_html_e( 'Save Draft', 'tutor' ); ?>
		</button>
		<!-- @TODO: need to add functionality -->
		<button onclick="TutorCore.modal.showModal('assignment-confirm-submission-modal')" class="tutor-btn tutor-btn-primary tutor-btn-medium">
			<?php esc_html_e( 'Submit Assignment', 'tutor' ); ?>
		</button>
	</div>
</div>

<?php tutor_load_template( 'demo-components.learning-area.components.assignment.modal' ); ?>