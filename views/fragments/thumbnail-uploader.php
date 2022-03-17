<?php
	extract( $data ); // $media_heading, $media_id, $input_name, $media_url

	if ( empty( $media_url ) ) {
		$media_url = wp_get_attachment_url( $media_id );
	}

	$is_borderless = isset( $data['borderless'] ) && $data['borderless'] == true;
	$placeholder   = ! empty( $data['placeholder'] ) ? $data['placeholder'] : '';
	$background    = ! empty( $data['background']) ? $data['background'] : '#eff1f7';
	$border_color  = ! empty( $data['border']) ? $data['border'] : '#eff1f7';
?>
<div class="tutor-thumbnail-uploader tutor-mt-12" data-media-heading="<?php echo ! empty( $media_heading ) ? $media_heading : __( 'Select or Upload Media Of Your Chosen Persuasion', 'tutor' ); ?>" data-button-text="<?php echo ! empty( $button_text ) ? $button_text : __( 'Use this media', 'tutor' ); ?>">
	<div class="thumbnail-wrapper tutor-d-flex tutor-align-items-center <?php echo $is_borderless ? 'tutor-is-borderless' : 'tutor-p-16'; ?>">
		<div class="thumbnail-preview image-previewer" style="background:<?php echo esc_attr( $background ); ?>; border: 2px solid <?php echo esc_attr( $border_color ); ?>;">
			<span class="preview-loading"></span>
			<input type="hidden" class="tutor-tumbnail-id-input" name="<?php echo $input_name; ?>" value="<?php echo ! empty( $media_id ) ? $media_id : ''; ?>">
			<img src="<?php echo $media_url ? $media_url : $placeholder; ?>" data-placeholder="<?php echo $placeholder; ?>"/>
			<span class="delete-btn" style="<?php echo ! $media_url ? 'display:none' : ''; ?>"></span>
		</div>
		<div class="thumbnail-input">
			<p class="text-regular-body tutor-color-black-60">
				<?php _e( 'Size', 'tutor' ); ?>: <span class="tutor-fs-7 tutor-fw-medium"><?php _e( '700x430 pixels', 'tutor' ); ?>;</span>
				<br />
				<?php _e( 'File Support', 'tutor' ); ?>: <span class="tutor-fs-7 tutor-fw-medium"><?php _e( 'jpg, .jpeg,. gif, or .png.', 'tutor' ); ?></span>
			</p>

			<button type="button" class="tutor-btn tutor-btn-primary tutor-is-sm tutor-mt-16 tutor-thumbnail-upload-button">
				<span class="tutor-btn-icon tutor-icon-image-filled"></span>
				<span><?php _e( 'Upload Image', 'tutor' ); ?></span>
			</button>
		</div>
	</div>
</div>
