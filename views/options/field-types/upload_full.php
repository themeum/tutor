<?php
$default      = isset( $field['default'] ) ? $field['default'] : '';
$option_value = $this->get( $field['key'], $default );
$field_id     = 'field_' . $field['key'];
?>
<div class="tutor-option-field-row d-block" id="<?php echo $field_id; ?>">
	<div class="tutor-option-field-label">
		<label><?php echo $field['label']; ?></label>
	</div>
	<div class="tutor-option-field-input image-previewer is-selected">
		<div class="d-flex logo-upload">
			<div class="logo-preview">
				<span class="preview-loading"></span>
				<img class="upload_previewer" src="<?php echo $option_value; ?>" alt="course builder logo">
				<span class="delete-btn"></span>
			</div>
			<div class="logo-upload-wrap">
				<p>
					Size: <strong>200x40 pixels;</strong> File Support:
					<strong>jpg, .jpeg or .png.</strong>
				</p>
				<label for="builder-logo-upload" class="tutor-btn tutor-is-sm image_upload_button">
					<input type="hidden" class="image_url_field" name="tutor_option[<?php echo $field['key']; ?>]" value="<?php echo $option_value; ?>">
					<input type="file" class="image_uploader" id="<?php echo $field_id; ?>" accept=".jpg, .jpeg, .png, .svg">
					<span class="tutor-btn-icon tutor-v2-icon-test icon-image-filled"></span>
					<span>Upload Image</span>
				</label>
			</div>
		</div>
	</div>
</div>
