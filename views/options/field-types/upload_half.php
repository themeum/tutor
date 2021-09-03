<?php
$default      = isset( $field['default'] ) ? $field['default'] : '';
$option_value = $this->get( $field['key'], $default );
$field_id     = 'field_' . $field['key'];
?>
<div class="tutor-option-field-row col-1x145"id="<?php echo $field_id; ?>"
>
	<div class="tutor-option-field-label">
		<h5 class="label"><?php echo __( $field['label'], 'tutor' ); ?></h5>
		<p class="desc"><?php echo __( $field['desc'], 'tutor' ); ?></p>
	</div>
	<div class="tutor-option-field-input image-previewer is-selected">
		<div class="signature-upload-wrap">
			<div class="signature-upload">
				<div class="signature-preview">
					<span class="preview-loading"></span>
					<img class="upload_preview" src="<?php echo $option_value; ?>" alt="signature preview">
					<span class="delete-btn"></span>
				</div>
				<div class="signature-info">
					<p style="font-size: 15px">
						File Support:
						<span style="color: #222427; font-weight: 500;">jpg, .jpeg, .png</span>
					</p>
					<p style="font-size: 13px; margin-top:7px;">Image size ratio: 4:1</p>
				</div>
			</div>
			<label for="signature-uploader" class="tutor-btn tutor-is-sm image_upload_button">
				<input type="hidden" class="input_file" name="tutor_option[<?php echo $field['key']; ?>]" value="<?php echo $option_value; ?>">
				<input type="file" class="image_uploader" id="<?php echo $field_id; ?>" accept=".jpg, .jpeg, .png, .svg">
				<!-- <span class="tutor-btn-icon las la-image"></span> -->
				<span class="tutor-btn-icon tutor-v2-icon-test icon-image-filled"></span>
				<span>Upload Image</span>
			</label>
		</div>
	</div>
</div>
