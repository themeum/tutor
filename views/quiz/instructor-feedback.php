<?php
/**
 * Instructor Feedback Template
 *
 * @package Tutor\Views
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.0
 */

$attempt_data = $data['attempt_data'];
$attempt_info = isset( $attempt_data->attempt_info ) ? unserialize( $attempt_data->attempt_info ) : false;
$content      = '';
if ( $attempt_info ) {
	$content = isset( $attempt_info['instructor_feedback'] ) ? $attempt_info['instructor_feedback'] : '';
}

?>
<div>
	<div class="quiz-attempt-answers-wrap">
		<div class="attempt-answers-header tutor-mb-12">
			<div class="attempt-header-quiz tutor-mt-24">
				<span class="tutor-color-black tutor-fs-6 tutor-fw-medium"><?php esc_html_e( 'Instructor Feedback', 'tutor' ); ?></span>
			</div>
		</div>
		<div class="tutor-instructor-feedback-wrap tutor-mb-16">
			<div id="tutor-instructor-feedback-editor"></div>
			<?php
				$editor_args          = array(
					'content' => $content,
					'args'    => array(),
				);
				$text_editor_template = tutor()->path . 'templates/global/tutor-text-editor.php';
				tutor_load_template_from_custom_path( $text_editor_template, $editor_args );
				?>
		</div>
		<button class="tutor-btn tutor-btn-primary tutor-instructor-feedback tutor-mt-4" data-attempt-id="<?php echo esc_attr( $attempt_data->attempt_id ); ?>" data-toast_success_message="<?php esc_html_e( 'Updated', 'tutor' ); ?>">
				<?php esc_html_e( 'Update', 'tutor' ); ?>
		</button>
	</div>
</div>
