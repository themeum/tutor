<?php
/**
 * Tutor text editor template
 *
 * @package Tutor\Templates
 * @subpackage Global
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.0
 */

 /**
  * While loading template two args can be passed, $content is the default content to inside the editor
  * and $args is for editor config. If args not passed then default args will be used from tutor_utils()->text_editor_config() here.
  */
 $content = isset( $data['content'] ) ? $data['content'] : '';
 $args    = isset( $data['args'] ) ? $data['args'] : array();
?>
<div class="tutor-text-editor-wrapper">
	<?php
		$pattern = array( '/\<[\/]{0,1}div[^\>]*\>/i', '/<p>(?:\s|&nbsp;)*?<\/p>/i' );
		$content = preg_replace( $pattern, '', $content );
		wp_editor( $content, 'tutor-global-text-editor', is_array( $args ) && count( $args ) ? $args : tutor_utils()->text_editor_config() );
	?>
</div>
