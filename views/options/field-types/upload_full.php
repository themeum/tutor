<?php
$default      = isset( $field['default'] ) ? $field['default'] : '';
$option_value = $this->get( $field['key'], $default );
$field_id     = 'field_' . $field['key'];
?>
<div class="tutor-option-field-row tutor-d-block" id="<?php echo esc_attr( $field_id ); ?>">
	<div class="tutor-option-field-label">
		<h5 class="label"><?php echo esc_attr( $field['label'] ); ?></h5>
	</div>
	<?php
		tutor_load_template_from_custom_path(
			tutor()->path . '/views/fragments/thumbnail-uploader.php',
			array(
				'media_id'   => $option_value,
				'input_name' => 'tutor_option[' . $field['key'] . ']',
			),
			false
		);
		?>
</div>
