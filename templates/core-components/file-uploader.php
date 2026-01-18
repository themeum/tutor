<?php
/**
 * File Uploader Component
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use TUTOR\Icon;

$multiple       = $multiple ?? true;
$accept         = $accept ?? '.pdf,.doc,.docx,.jpg,.jpeg,.png';
$max_size       = $max_size ?? 52428800; // 50MB
$icon           = $icon ?? Icon::UPLOAD_FILE;
$title          = $title ?? __( 'Drop files here or click to upload', 'tutor' );
$subtitle       = $subtitle ?? __( 'PDF, DOC, DOCX, JPG, PNG Formats (Max 50MB)', 'tutor' );
$button_text    = $button_text ?? __( 'Select Files', 'tutor' );
$on_file_select = $on_file_select ?? 'null';
$on_error       = $on_error ?? 'null';

?>
<div 
	x-data="tutorFileUploader({
		multiple: <?php echo $multiple ? 'true' : 'false'; ?>,
		accept: '<?php echo esc_attr( $accept ); ?>',
		maxSize: <?php echo esc_attr( $max_size ); ?>,
		onFileSelect: <?php echo esc_js( $on_file_select ); ?>,
		onError: <?php echo esc_js( $on_error ); ?>,
	})"
>
	<!-- Upload Area -->
	<div
		class="tutor-file-uploader"
		:class="{
			'tutor-file-uploader-drag-over': isDragOver,
			'tutor-file-uploader-disabled': isDisabled
		}"
		@click="openFileDialog()"
		@dragover.prevent="handleDragOver($event)"
		@dragleave.prevent="handleDragLeave($event)"
		@drop.prevent="handleDrop($event)"
	>
		<input 
			type="file" 
			class="tutor-file-uploader-input"
			:multiple="multiple"
			:accept="accept"
			:disabled="isDisabled"
			x-ref="fileInput"
			@change="handleFileSelect($event)"
		>
		<div class="tutor-file-uploader-icon">
			<?php tutor_utils()->render_svg_icon( $icon, 24, 24 ); ?>
		</div>
		<div class="tutor-file-uploader-content">
			<p class="tutor-file-uploader-title"><?php echo esc_html( $title ); ?></p>
			<p class="tutor-file-uploader-subtitle"><?php echo esc_html( $subtitle ); ?></p>
		</div>
		<button type="button" class="tutor-btn tutor-btn-primary-soft" :disabled="isDisabled">
			<?php echo esc_html( $button_text ); ?>
		</button>
	</div>
</div>
