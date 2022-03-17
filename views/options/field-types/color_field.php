<?php
/**
 * Color field for settings.
 *
 * @package Tutor LMS
 * @since 2.0
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
		<h5 class="label"><?php echo esc_attr( $field['label'] ); ?></h5>
		<p class="desc"><?php echo esc_attr( $field['desc'] ); ?></p>
	</div>
	<div class="tutor-option-field-input">
		<label for="id_<?php echo esc_attr( $field_id ); ?>" class="color-picker-input" data-key="<?php echo esc_attr( $field['preset_name'] ); ?>" style="border-color: rgb(205, 207, 213); box-shadow: none;">
			<input type="color" data-picker="<?php echo esc_attr( $field['preset_name'] ); ?>" name="tutor_option[<?php echo esc_attr( $field['key'] ); ?>]" id="id_<?php echo esc_attr( $field_id ); ?>" value="<?php echo esc_attr( $value ); ?>">
			<div class="picker-value tutor-fs-7 tutor-fw-normal"><?php echo esc_attr( $value ); ?></div>
		</label>
	</div>
</div>
