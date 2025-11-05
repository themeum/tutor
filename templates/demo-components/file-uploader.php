<?php
/**
 * File uploader component documentation
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use TUTOR\Icon;

?>
<section class="tutor-bg-white tutor-py-6 tutor-px-8 tutor-rounded-lg tutor-shadow-sm">
	<h1 class="tutor-text-2xl tutor-font-bold tutor-mb-6">File Uploader</h1>

	<!-- Basic File Uploader -->
	<div class="tutor-mb-8">
		<h2 class="tutor-text-xl tutor-font-semibold tutor-mb-3">Basic File Uploader</h2>
		<p class="tutor-text-gray-600 tutor-mb-4">
			File uploader component with drag & drop functionality
		</p>
		<div x-data="tutorFileUploader({
			multiple: true,
			accept: '.pdf,.doc,.docx,.jpg,.jpeg,.png',
			maxSize: 52428800,
			onFileSelect: (files) => console.log('Files selected:', files),
			onError: (error) => alert(error),
		})">
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
				>
				<div class="tutor-file-uploader-icon">
					<?php tutor_utils()->render_svg_icon( Icon::UPLOAD_FILE, 24, 24 ); ?>
				</div>
				<div class="tutor-file-uploader-content">
					<p class="tutor-file-uploader-title">Drop files here or click to upload</p>
					<p class="tutor-file-uploader-subtitle">PDF, DOC, DOCX, JPG, PNG Formats (Max 50MB)</p>
				</div>
				<button type="button" class="tutor-btn tutor-btn-primary-soft" :disabled="isDisabled">
					Select Files
				</button>
			</div>
		</div>
	</div>

	<!-- Usage Example -->
	<div class="tutor-mb-8">
		<h2 class="tutor-text-xl tutor-font-semibold tutor-mb-3">Usage</h2>
		<div class="tutor-bg-gray-50 tutor-p-4 tutor-rounded-lg">
			<pre class="tutor-text-sm tutor-text-gray-700"><code>&lt;div x-data="tutorFileUploader({
	multiple: true,
	accept: '.pdf,.doc,.docx,.jpg,.jpeg,.png',
	maxSize: 52428800, // 50MB in bytes
	onFileSelect: (files) => console.log('Files selected:', files),
	onError: (error) => alert(error)
})"&gt;
	&lt;div class="tutor-file-uploader"
		@click="openFileDialog()"
		@dragover.prevent="handleDragOver($event)"
		@drop.prevent="handleDrop($event)"&gt;
		&lt;input type="file" x-ref="fileInput" class="tutor-file-uploader-input"&gt;
		&lt;!-- Upload UI --&gt;
	&lt;/div&gt;
&lt;/div&gt;</code></pre>
		</div>
	</div>
</section>
