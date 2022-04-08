<?php
$default      = isset( $field['default'] ) ? $field['default'] : '';
$option_value = $this->get( $field['key'], $default );
$field_id     = 'field_' . $field['key'];
?>
<div class="tutor-option-field-row col-1x145"id="<?php echo esc_attr( $field_id ); ?>">
	<div class="tutor-option-field-label">
		<div class="tutor-fs-6 tutor-fw-medium tutor-mb-8" tutor-option-name><?php echo __( $field['label'], 'tutor' ); ?></div>
		<div class="tutor-fs-7 tutor-color-muted"><?php echo __( $field['desc'], 'tutor' ); ?></div>
	</div>
	<div class="tutor-option-field-input image-previewer is-selected">
		<div class="signature-upload-wrap">
			<div class="signature-upload">
				<div class="signature-preview">
					<span class="preview-loading"></span>
					<img class="upload_preview" src="<?php echo esc_attr( $option_value ); ?>" alt="signature preview">
					<span class="delete-btn"></span>
				</div>
				<!-- @todo: hard coded string -->
				<div class="signature-info">
					<div style="font-size: 15px">
						<?php _e("File Support", "tutor"); ?>:
						<span style="color: #222427; font-weight: 500;"><?php _e("jpg, .jpeg, .png", "tutor"); ?></span>
					</div>
					<div style="font-size: 13px; margin-top:7px;"><?php _e("Image size ratio: 4:1", "tutor"); ?></div>
				</div>
			</div>
			<label for="signature-uploader" class="tutor-btn tutor-btn-primary tutor-btn-sm image_upload_button">
				<input type="hidden" class="input_file" name="tutor_option[<?php echo esc_attr( $field_key ); ?>]" value="<?php echo esc_attr( $option_value ); ?>">
				<input type="file" class="image_uploader" id="<?php echo esc_attr( $field_id ); ?>" accept=".jpg, .jpeg, .png, .svg">
				<span class="tutor-icon-image-landscape"></span>
				<span><?php _e("Upload Image", "tutor"); ?></span>
			</label>
		</div>
	</div>
</div>
