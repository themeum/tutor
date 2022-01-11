<?php
/**
 * Tutor text editor template
 *
 * @package TutorTextEditor
 *
 * @since v2.0.0
 */

 $content = isset( $data['content'] ) ? $data['content'] : '';
 $args    = isset( $data['args'] ) ? $data['args'] : array();
?>
<div class="tutor-text-editor-wrapper">
	<?php
		$pattern = array( '/\<[\/]{0,1}div[^\>]*\>/i', '/<p>(?:\s|&nbsp;)*?<\/p>/i' );
		$content = preg_replace( $pattern, '', $content );
		wp_editor( $content, 'tutor-global-text-editor', tutor_utils()->text_editor_config() );
	?>
</div>
