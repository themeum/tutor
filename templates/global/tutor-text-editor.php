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
<div class="tutor-text-editor-wrapper">
	<div id="<?php echo esc_attr( isset( $data['div_id'] ) ? $data['div_id'] : '' ); ?>"></div>
	<?php
		tutor_utils()->render_text_editor( $content, $div_id, $args );
	?>

</div>
