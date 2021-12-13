<?php
	extract( $data ); // $media_heading, $media_id, $input_name, $media_url

if ( empty( $media_url ) ) {
	$media_url = wp_get_attachment_url( $media_id );
}
?>
<div class="tutor-thumbnail-uploader" data-media-heading="<?php echo ! empty( $media_heading ) ? $media_heading : __( 'Select or Upload Media Of Your Chosen Persuasion', 'tutor' ); ?>" data-button-text="<?php echo ! empty( $button_text ) ? $button_text : __( 'Use this media', 'tutor' ); ?>">
	<div class="thumbnail-wrapper tutor-bs-d-flex tutor-bs-align-items-center tutor-mt-10 tutor-p-15">
		<div class="thumbnail-preview image-previewer">
			<span class="preview-loading"></span>
			<input type="hidden" class="tutor-tumbnail-id-input" name="<?php echo $input_name; ?>" value="<?php echo ! empty( $media_id ) ? $media_id : ''; ?>">
			<img src="<?php echo $media_url; ?>"/>
			<span class="delete-btn" style="<?php echo ! $media_url ? 'display:none' : ''; ?>"></span>
		</div>
		<div class="thumbnail-input">
			<p class="text-regular-body color-text-subsued">
				<?php _e('Size', 'ttuor'); ?>: <strong class="text-medium-body"><?php _e( '700x430 pixels', 'tutor' ); ?>;</strong>
				<br />
				<?php _e( 'File Support', 'tutor' ); ?>: <strong class="text-medium-body"><?php _e( 'jpg, .jpeg,. gif, or .png.', 'tutor' ); ?></strong>
			</p>

			<button class="tutor-btn tutor-is-sm tutor-mt-15 tutor-thumbnail-upload-button">
				<span class="tutor-btn-icon ttr-image-filled"></span>
				<span><?php _e( 'Upload Image', 'tutor' ); ?></span>
			</button>
		</div>
	</div>
</div>
