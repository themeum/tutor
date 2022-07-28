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
	<div class="thumbnail-wrapper tutor-d-flex tutor-align-center <?php echo $is_borderless ? 'tutor-is-borderless' : 'tutor-p-16'; ?>">
		<div class="thumbnail-preview image-previewer tutor-mr-28" style="background:<?php echo esc_attr( $background ); ?>; border: 2px solid <?php echo esc_attr( $border_color ); ?>;">
			<span class="preview-loading"></span>
			<input type="hidden" class="tutor-tumbnail-id-input" name="<?php echo $input_name; ?>" value="<?php echo ! empty( $media_id ) ? $media_id : ''; ?>">
			<img src="<?php echo $media_url ? $media_url : $placeholder; ?>" data-placeholder="<?php echo $placeholder; ?>"/>
			<span class="delete-btn" style="<?php echo ! $media_url ? 'display:none' : ''; ?>"></span>
		</div>
		<div class="thumbnail-input">
			<div class="tutor-fs-6 tutor-color-secondary">
			<?php
				if ( isset($data['desc']['file_size']) ) {
					printf( __( 'Size: <span class="tutor-fs-7 tutor-fw-medium">%s</span>', 'tutor' ), esc_attr( $data['desc']['file_size'] ) );
				} else {
					printf( __( 'Size: <span class="tutor-fs-7 tutor-fw-medium">%s</span>', 'tutor' ), '700x430 pixels' );
				}
				?>
				<br />
				<?php
				if ( isset($data['desc']['file_support']) ) {
					printf( __( 'File Support: <span class="tutor-fs-7 tutor-fw-medium">%s</span>', 'tutor' ), esc_attr( $data['desc']['file_support'] ) );
				} else {
					printf( __( 'File Support: <span class="tutor-fs-7 tutor-fw-medium">%s</span>', 'tutor' ), 'jpg, .jpeg,. gif, or .png.' );
				}
				?>
			</div>

			<button type="button" class="tutor-btn tutor-btn-primary tutor-btn-sm tutor-mt-16 tutor-thumbnail-upload-button">
				<span class="tutor-icon-image-landscape tutor-mr-8" area-hidden="true"></span>
				<span><?php _e( 'Upload Image', 'tutor' ); ?></span>
			</button>
		</div>
	</div>
</div>
