<?php
/**
 * Color field for settings.
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

$field_key = $field['key'] ?? '';
$field_id  = 'field_' . $field_key;
$value     = $this->get( $field_key, $field['default'] ?? '' );
if ( isset( $field['default'] ) && empty( $value ) ) {
	$value = $field['default'];
}
$wrapper_class = isset( $field['class'] ) ? $field['class'] : '';
?>
<div class="tutor-option-field-row <?php echo esc_attr( $wrapper_class ); ?>">
	<div class="tutor-option-field-label">
		<div class="tutor-fs-6 tutor-fw-medium tutor-mb-8" tutor-option-name><?php echo esc_html( $field['label'] ?? '' ); ?></div>
		<div class="tutor-fs-7 tutor-color-muted"><?php echo esc_html( $field['desc'] ?? '' ); ?></div>
	</div>
	<div class="tutor-option-field-input">
		<label for="id_<?php echo esc_attr( $field_id ); ?>" class="color-picker-input" data-key="<?php echo esc_attr( $field['preset_name'] ?? '' ); ?>" style="border-color: rgb(205, 207, 213); box-shadow: none;">
			<input type="color" data-picker="<?php echo esc_attr( $field['preset_name'] ?? '' ); ?>" name="tutor_option[<?php echo esc_attr( $field_key ); ?>]" id="id_<?php echo esc_attr( $field_id ); ?>" value="<?php echo esc_attr( $value ); ?>">
			<input type="text" value="<?php echo esc_attr( $value ); ?>" />
		</label>
	</div>
</div>
