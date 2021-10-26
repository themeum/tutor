<?php
$value = $this->get( $field['key'] );
if ( isset( $field['default'] ) && empty( $value ) ) {
	$value = $field['default'];
}
$field_id = 'field_' . $field['key'];
?>

<div class="tutor-option-field-row">
	<div class="tutor-option-field-label">
		<h5 class="label"><?php echo esc_attr( $field['label'] ); ?></h5>
		<p class="desc"><?php echo esc_attr( $field['desc'] ); ?></p>
	</div>
	<div class="tutor-option-field-input">
		<label for="id_<?php echo esc_attr( $field_id ); ?>" class="color-picker-input" data-key="<?php echo esc_attr( $field['preset_name'] ); ?>" style="border-color: rgb(205, 207, 213); box-shadow: none;">
			<input type="color" data-picker="<?php echo esc_attr( $field['preset_name'] ); ?>" name="tutor_option[<?php echo esc_attr( $field['key'] ); ?>]" id="id_<?php echo esc_attr( $field_id ); ?>" value="<?php echo esc_attr( $value ); ?>">
			<div class="picker-value text-regular-small"><?php echo esc_attr( $value ); ?></div>
		</label>
	</div>
</div>
