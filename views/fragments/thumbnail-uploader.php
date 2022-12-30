<?php
/**
 * Quiz list single view
 *
 * @package Tutor\Views
 * @subpackage Tutor\Fragments
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.0
 */

// Extract vars media_heading,media_id,input_name,media_url.
extract( $data );

if ( empty( $media_url ) ) {
	$media_url = wp_get_attachment_url( $media_id );
}

$is_borderless = isset( $data['borderless'] ) && true == $data['borderless'];
$placeholder   = ! empty( $data['placeholder'] ) ? $data['placeholder'] : '';
$background    = ! empty( $data['background'] ) ? $data['background'] : '#eff1f7';
$border_color  = ! empty( $data['border'] ) ? $data['border'] : '#eff1f7';
?>
<div class="tutor-thumbnail-uploader tutor-mt-12" data-media-heading="<?php echo ! empty( $media_heading ) ? esc_attr( $media_heading ) : esc_attr__( 'Select or Upload Media Of Your Chosen Persuasion', 'tutor' ); ?>" data-button-text="<?php echo esc_html( ! empty( $button_text ) ? $button_text : __( 'Use this media', 'tutor' ) ); ?>">
	<div class="thumbnail-wrapper tutor-d-flex tutor-align-center <?php echo $is_borderless ? 'tutor-is-borderless' : 'tutor-p-16'; ?>">
		<div class="thumbnail-preview image-previewer tutor-mr-28" style="background:<?php echo esc_attr( $background ); ?>; border: 2px solid <?php echo esc_attr( $border_color ); ?>;">
			<span class="preview-loading"></span>
			<input type="hidden" class="tutor-tumbnail-id-input" name="<?php echo esc_attr( $input_name ); ?>" value="<?php echo esc_attr( ! empty( $media_id ) ? $media_id : '' ); ?>">
			<img src="<?php echo esc_url( $media_url ? $media_url : $placeholder ); ?>" data-placeholder="<?php echo esc_attr( $placeholder ); ?>"/>
			<span class="delete-btn" style="<?php echo ! $media_url ? 'display:none' : ''; ?>"></span>
		</div>
		<div class="thumbnail-input">
			<div class="tutor-fs-6 tutor-color-secondary">
				<?php if ( isset( $data['desc']['file_size'] ) ) : ?>
					<?php esc_html_e( 'Size: ', 'tutor' ); ?>
					<span class="tutor-fs-7 tutor-fw-medium">
						<?php echo esc_html( $data['desc']['file_size'] ); ?>
					</span>
				<?php else : ?>
					<?php esc_html_e( 'Size: ', 'tutor' ); ?>
					<span class="tutor-fs-7 tutor-fw-medium">
						<?php esc_html_e( '700x430 pixels', 'tutor' ); ?>
					</span>
				<?php endif; ?>
				<br />
				<?php if ( isset( $data['desc']['file_support'] ) ) : ?>
					<?php esc_html_e( 'File Support: ', 'tutor' ); ?>
					<span class="tutor-fs-7 tutor-fw-medium">
						<?php echo esc_html( $data['desc']['file_support'] ); ?>
					</span>
				<?php else : ?>
					<?php esc_html_e( 'File Support: ', 'tutor' ); ?>
					<span class="tutor-fs-7 tutor-fw-medium">
						<?php esc_html_x( 'jpg, .jpeg,. gif, or .png', 'tutor-supported-image-type', 'tutor' ); ?>
					</span>
				<?php endif; ?>
			</div>

			<button type="button" class="tutor-btn tutor-btn-primary tutor-btn-sm tutor-mt-16 tutor-thumbnail-upload-button">
				<span class="tutor-icon-image-landscape tutor-mr-8" area-hidden="true"></span>
				<span><?php esc_html_e( 'Upload Image', 'tutor' ); ?></span>
			</button>
		</div>
	</div>
</div>
