<?php
/**
 * Radio horizontal full for settings.
 *
 * @package Tutor\Views
 * @subpackage Tutor\Settings
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Field settings.
 *
 * @var mixed $this  Options_V2 or Tools_V2 instance.
 * @var array $field
 */

$field_key  = $field['key'] ?? '';
$field_id   = 'field_' . $field_key;
$saved_data = $this->get( $field_key, array() );
?>
<div class="tutor-option-field-row tutor-d-block" id="<?php echo esc_attr( $field_id ); ?>">
	<?php require tutor()->path . 'views/options/template/common/field_heading.php'; ?>

	<div class="tutor-option-field-input">
		<div class="tutor-wp-editor" style="position: relative;">
			<div class="loading-spinner"></div>
			<?php
			$editor_id = 'editor_' . $field_id;
			$content   = empty( $saved_data ) ? $field['default'] : wp_unslash( json_decode( $saved_data ) );
			$content   = html_entity_decode( tutor_utils()->clean_html_content( $content ) );

			$args = array(
				'textarea_name' => 'tutor_option[' . $field_key . ']',
				'tinymce'       => array(
					'toolbar1' => 'bold, italic, underline, forecolor, fontselect, fontsizeselect, formatselect, alignleft, aligncenter, alignright, bullist, numlist, link, unlink, removeformat',
				),
				'media_buttons' => false,
				'quicktags'     => true,
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
