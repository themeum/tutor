<?php
/**
 * Tutor text editor template
 *
 * @package TutorTextEditor
 *
 * @since v2.0.0
 */

 $div_id  = isset( $data['div_id'] ) ? $data['div_id'] : '';
 $content = isset( $data['content'] ) ? $data['content'] : '';
 $args    = isset( $data['args'] ) ? $data['args'] : array();
?>
<div id="<?php echo esc_attr( isset( $data['div_id'] ) ? $data['div_id'] : '' ); ?>"></div>
<?php
// var_dump( get_post_meta( $attempt_id, 'instructor_feedback', true ) );
	// // esc_textarea( get_post_meta( $attempt_id, 'instructor_feedback', true ) );.
	// $editor_config = array(
	// 'wpautop'          => true,
	// 'media_buttons'    => false,
	// 'default_editor'   => 'TinyMCE',
	// 'drag_drop_upload' => false,
	// 'editor_css'       => '<style>#wp-tutor-instructor-quiz-feedback-editor-container { margin-right: 1px;}</style>',
	// 'textarea_name'    => 'tutor-instructor-feedback-content',
	// 'tinymce'          => true,
	// 'quicktags'        => false,
	// 'textarea_rows'    => 3,
	// );
	// wp_editor( get_post_meta( $attempt_id, 'instructor_feedback', true ), 'tutor-instructor-quiz-feedback', $editor_config );
	tutor_utils()->render_text_editor( $content, $div_id, $args );
?>
