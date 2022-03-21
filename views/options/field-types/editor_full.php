<?php
/**
 * Radio horizontal full for settings.
 *
 * @package Tutor LMS
 * @since 2.0
 */

$field_key  = isset( $field['key'] ) ? esc_attr( $field['key'] ) : null;
$field_id   = esc_attr( 'field_' . $field_key );
$saved_data = $this->get( $field_key, array() );
?>
<div class="tutor-option-field-row tutor-d-block" id="<?php echo esc_attr( $field_id ); ?>">
	<?php require tutor()->path . 'views/options/template/common/field_heading.php'; ?>

	<div class="tutor-option-field-input">
		<div class="tutor-wp-editor" style="position: relative;">
			<div class="loading-spinner"></div>
			<?php
			$editor_id    = 'editor_' . $field_id;
			// $content = empty( $saved_data ) ? $content_demo : wp_unslash( $saved_data );
			$content = empty( $saved_data ) ? $field['default'] : wp_unslash( $saved_data );
			$pattern = array( '/\<[\/]{0,1}div[^\>]*\>/i', '/<p>(?:\s|&nbsp;)*?<\/p>/i' );
			$content = preg_replace( $pattern, '', $content );
			$content = html_entity_decode($content);
			$args    = array(
				'textarea_name' => "tutor_option[{$field_key}]",
				'tinymce'       => array(
					'toolbar1' => 'bold,italic,underline,forecolor,fontselect,fontsizeselect,formatselect,alignleft,aligncenter,alignright,bullist,numlist,link,unlink,removeformat',
					'toolbar2' => '',
					'toolbar3' => '',
				),
				'media_buttons' => false,
				'quicktags'     => false,
				'elementpath'   => false,
				'wpautop'       => false,
				'statusbar'     => false,
				'editor_height' => 240,
			);
			wp_editor( $content, $editor_id, $args );
			?>
		</div>
	</div>
</div>
