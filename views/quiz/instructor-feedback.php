<?php
/**
 * Instructor Feedback Template
 *
 * @package InstructorFeedback
 *
 * @since v2.0.0
 */

$attempt_data = $data['attempt_data'];
$attempt_info = isset( $attempt_data->attempt_info ) ? unserialize( $attempt_data->attempt_info ) : false;
$content      = '';
if ( $attempt_info ) {
	$content = isset( $attempt_info->instructor_feedback ) ? $attempt_info->instructor_feedback : '';
}
?>
<div class="wrap">
	<div class="quiz-attempt-answers-wrap">
		<div class="attempt-answers-header tutor-mb-10">
			<div class="attempt-header-quiz">
				<h3><?php esc_html_e( 'Instructor Feedback', 'tutor' ); ?></h3>
			</div>
		</div>
		<div class="tutor-instructor-feedback-wrap tutor-mb-15">
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
		<button class="tutor-btn <?php echo is_admin() ? 'tutor-btn-wordpress' : ''; ?> tutor-instructor-feedback tutor-mt-5" data-attempt-id="<?php echo esc_attr( $attempt_data->attempt_id ); ?>" data-toast_success_message="<?php esc_html_e( 'Updated', 'tutor' ); ?>">
				<?php esc_html_e( 'Update', 'tutor' ); ?>
		</button>
	</div>
</div>
