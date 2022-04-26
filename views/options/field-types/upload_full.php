<?php
$default      = isset( $field['default'] ) ? $field['default'] : '';
$option_value = $this->get( $field['key'], $default );
$field_id     = 'field_' . $field['key'];
?>
<div class="tutor-option-field-row tutor-d-block" id="<?php echo esc_attr( $field_id ); ?>">
	<div class="tutor-option-field-label">
		<div class="tutor-fs-6 tutor-fw-medium tutor-mb-8" tutor-option-name><?php echo esc_attr( $field['label'] ); ?></div>
	</div>
	<?php
		tutor_load_template_from_custom_path(
			tutor()->path . '/views/fragments/thumbnail-uploader.php',
			array(
				'media_id'   => $option_value,
				'input_name' => 'tutor_option[' . $field['key'] . ']',
				'desc' => $field['desc'],
			),
			false
		);
		?>
</div>
