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

$field_key = isset( $field['key'] ) ? esc_attr( $field['key'] ) : null;
$field_id  = esc_attr( 'field_' . $field_key );
$value     = $this->get( $field['key'], $field['default'] );
if ( isset( $field['default'] ) && empty( $value ) ) {
	$value = $field['default'];
}
$if_other_color = isset( $field['preset_name'] ) && 'other' == $field['preset_name'] ? ' other_color' : '';
?>
<div class="tutor-option-field-row">
	<div class="tutor-option-field-label">
		<div class="tutor-fs-6 tutor-fw-medium tutor-mb-8" tutor-option-name><?php echo esc_attr( $field['label'] ); ?></div>
		<div class="tutor-fs-7 tutor-color-muted"><?php echo esc_attr( $field['desc'] ); ?></div>
	</div>
	<div class="tutor-option-field-input">
		<label for="id_<?php echo esc_attr( $field_id ); ?>" class="color-picker-input" data-key="<?php echo esc_attr( $field['preset_name'] ); ?>" style="border-color: rgb(205, 207, 213); box-shadow: none;">
			<input type="color" data-picker="<?php echo esc_attr( $field['preset_name'] ); ?>" name="tutor_option[<?php echo esc_attr( $field['key'] ); ?>]" id="id_<?php echo esc_attr( $field_id ); ?>" value="<?php echo esc_attr( $value ); ?>">
			<input type="text" value="<?php echo esc_attr( $field['value'] ?? $field['default'] ); ?>" />
		</label>
	</div>
</div>
