<?php
/**
 * Thumbnail upload view
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

$field_key    = $field['key'] ?? '';
$default      = $field['default'] ?? '';
$option_value = $this->get( $field_key, $default );
$field_id     = 'field_' . $field_key;
?>
<div class="tutor-option-field-row tutor-d-block" id="<?php echo esc_attr( $field_id ); ?>">
	<div class="tutor-option-field-label">
		<div class="tutor-fs-6 tutor-fw-medium tutor-mb-8" tutor-option-name><?php echo esc_html( $field['label'] ?? '' ); ?></div>
	</div>
	<?php
		tutor_load_template_from_custom_path(
			tutor()->path . '/views/fragments/thumbnail-uploader.php',
			array(
				'media_id'   => $option_value,
				'input_name' => 'tutor_option[' . $field_key . ']',
				'desc'       => $field['desc'] ?? '',
			),
			false
		);
		?>
</div>
