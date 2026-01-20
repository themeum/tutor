<?php
/**
 * FileUploader Component Class.
 *
 * @package Tutor\Components
 * @author Themeum
 * @link https://themeum.com
 * @since 4.0.0
 */

namespace Tutor\Components;

use Tutor\Components\Constants\Size;
use Tutor\Components\Constants\Variant;
use TUTOR\Icon;
use Tutor\Components\Button;
use Tutor\Components\AttachmentCard;

defined( 'ABSPATH' ) || exit;

/**
 * Class FileUploader
 *
 * Example Usage (Native Multiple):
 * FileUploader::make()
 *     ->name( 'course_attachments' )
 *     ->uploader_title( __( 'Upload Assignments', 'tutor' ) )
 *     ->uploader_subtitle( __( 'Support PDF, DOCX (Max 20MB)', 'tutor' ) )
 *     ->accept( '.pdf,.docx' )
 *     ->multiple( true )
 *     ->max_size( 20 * 1024 * 1024 )
 *     ->render();
 *
 * Example Usage (Native Single):
 * FileUploader::make()
 *     ->name( 'resume' )
 *     ->accept( '.pdf,.doc' )
 *     ->render();
 *
 * Example Usage (WP Media Image):
 * FileUploader::make()
 *     ->variant( FileUploader::IMAGE_UPLOADER )
 *     ->use_wp_media( true )
 *     ->wp_media_library_type( 'image' )
 *     ->name( 'profile_photo' )
 *     ->render();
 *
 * Example Usage (WP Media Multiple Documents):
 * FileUploader::make()
 *     ->use_wp_media( true )
 *     ->multiple( true )
 *     ->wp_media_library_type( 'application/pdf,application/msword' )
 *     ->name( 'shared_files' )
 *     ->render();
 *
 * Example Usage (Native Image):
 * FileUploader::make()
 *     ->variant( FileUploader::IMAGE_UPLOADER )
 *     ->name( 'cover_photo' )
 *     ->render();
 *
 * Example Usage (Custom UI):
 * FileUploader::make()
 *     ->name( 'custom_upload' )
 *     ->uploader_icon( Icon::RESOURCES )
 *     ->uploader_title( __( 'Resource Center', 'tutor' ) )
 *     ->uploader_subtitle( __( 'Add any relevant documents here', 'tutor' ) )
 *     ->uploader_button_text( __( 'Choose Resources', 'tutor' ) )
 *     ->render();
 *
 * @since 4.0.0
 */
class FileUploader extends BaseComponent {

	/**
	 * Variant constants.
	 *
	 * @since 4.0.0
	 */
	public const FILE_UPLOADER  = 'file-uploader';
	public const IMAGE_UPLOADER = 'image-uploader';


	/**
	 * File uploader accept attribute.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	protected $accept = '.pdf,.doc,.docx,.jpg,.jpeg,.png';

	/**
	 * File uploader max size.
	 *
	 * @since 4.0.0
	 *
	 * @var int
	 */
	protected $max_size = null;

	/**
	 * File uploader icon.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	protected $uploader_icon = Icon::UPLOAD_FILE;

	/**
	 * File uploader title.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	protected $uploader_title = '';

	/**
	 * File uploader subtitle.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	protected $uploader_subtitle = '';

	/**
	 * File uploader button text.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	protected $uploader_button_text = '';

	/**
	 * Component variant.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	protected $variant = self::FILE_UPLOADER;

	/**
	 * Whether to use WordPress media library instead of native file input.
	 *
	 * @since 4.0.0
	 *
	 * @var bool
	 */
	protected $use_wp_media = false;

	/**
	 * Title for WordPress media modal.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	protected $wp_media_title = '';

	/**
	 * Button text for WordPress media modal.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	protected $wp_media_button_text = '';

	/**
	 * Library type filter for WordPress media (image, video, audio, application).
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	protected $wp_media_library_type = '';

	/**
	 * Whether multiple option can be selected in input.
	 *
	 * @since 4.0.0
	 *
	 * @var boolean
	 */
	protected $multiple = false;

	/**
	 * InputField name attribute.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	protected $name = '';

	/**
	 * InputField ID attribute.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	protected $id = '';

	/**
	 * Whether input is required.
	 *
	 * @since 4.0.0
	 *
	 * @var bool
	 */
	protected $required = false;

	/**
	 * InputField value.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	protected $value = '';

	/**
	 * Set uploader accept attribute.
	 *
	 * Common types: .pdf, .doc, .docx, .jpg, .jpeg, .png
	 * For WP Media: image, video, audio, application
	 *
	 * @since 4.0.0
	 *
	 * @param string $accept Accept attribute.
	 *
	 * @return $this
	 */
	public function accept( $accept ) {
		$this->accept = $accept;

		return $this;
	}

	/**
	 * Set uploader max size.
	 *
	 * @since 4.0.0
	 *
	 * @param int $max_size Max size in bytes.
	 *
	 * @return $this
	 */
	public function max_size( $max_size = null ) {
		if ( null === $max_size ) {
			$max_size = wp_max_upload_size();
		}
		$this->max_size = $max_size;

		return $this;
	}

	/**
	 * Set uploader icon.
	 *
	 * @since 4.0.0
	 *
	 * @param string $icon Icon name.
	 *
	 * @return $this
	 */
	public function uploader_icon( $icon ) {
		$this->uploader_icon = $icon;

		return $this;
	}

	/**
	 * Set uploader title.
	 *
	 * @since 4.0.0
	 *
	 * @param string $title Title text.
	 *
	 * @return $this
	 */
	public function uploader_title( $title ) {
		$this->uploader_title = $title;

		return $this;
	}

	/**
	 * Set uploader subtitle.
	 *
	 * @since 4.0.0
	 *
	 * @param string $subtitle Subtitle text.
	 *
	 * @return $this
	 */
	public function uploader_subtitle( $subtitle ) {
		$this->uploader_subtitle = $subtitle;

		return $this;
	}

	/**
	 * Set uploader button text.
	 *
	 * @since 4.0.0
	 *
	 * @param string $button_text Button text.
	 *
	 * @return $this
	 */
	public function uploader_button_text( $button_text ) {
		$this->uploader_button_text = $button_text;

		return $this;
	}

	/**
	 * Set component variant.
	 *
	 * @since 4.0.0
	 *
	 * @param string $variant Component variant (file-uploader|image-uploader).
	 *
	 * @return $this
	 */
	public function variant( $variant ) {
		$this->variant = $variant;

		return $this;
	}

	/**
	 * Set use WordPress media library.
	 *
	 * @since 4.0.0
	 *
	 * @param bool $use_wp_media Use WP media library.
	 *
	 * @return $this
	 */
	public function use_wp_media( $use_wp_media = true ) {
		$this->use_wp_media = (bool) $use_wp_media;

		return $this;
	}

	/**
	 * Set WP media title.
	 *
	 * @since 4.0.0
	 *
	 * @param string $wp_media_title WP media title.
	 *
	 * @return $this
	 */
	public function wp_media_title( $wp_media_title ) {
		$this->wp_media_title = $wp_media_title;

		return $this;
	}

	/**
	 * Set WP media button text.
	 *
	 * @since 4.0.0
	 *
	 * @param string $wp_media_button_text WP media button text.
	 *
	 * @return $this
	 */
	public function wp_media_button_text( $wp_media_button_text ) {
		$this->wp_media_button_text = $wp_media_button_text;

		return $this;
	}

	/**
	 * Set WP media library type.
	 *
	 * Common types: image, video, audio, application, text
	 *
	 * @since 4.0.0
	 *
	 * @param string $wp_media_library_type WP media library type.
	 *
	 * @return $this
	 */
	public function wp_media_library_type( $wp_media_library_type ) {
		$this->wp_media_library_type = $wp_media_library_type;

		return $this;
	}

	/**
	 * Set multiple.
	 *
	 * @since 4.0.0
	 *
	 * @param bool $multiple Multiple.
	 *
	 * @return $this
	 */
	public function multiple( $multiple = true ) {
		$this->multiple = (bool) $multiple;

		return $this;
	}

	/**
	 * Set input name.
	 *
	 * @since 4.0.0
	 *
	 * @param string $name Name.
	 *
	 * @return $this
	 */
	public function name( $name ) {
		$this->name = sanitize_key( $name );

		return $this;
	}

	/**
	 * Set input ID.
	 *
	 * @since 4.0.0
	 *
	 * @param string $id InputField ID.
	 *
	 * @return $this
	 */
	public function id( $id ) {
		$this->id = sanitize_key( $id );

		return $this;
	}

	/**
	 * Set required.
	 *
	 * @since 4.0.0
	 *
	 * @param bool $required Required.
	 *
	 * @return $this
	 */
	public function required( $required = true ) {
		$this->required = $required;

		return $this;
	}

	/**
	 * Set value.
	 *
	 * @since 4.0.0
	 *
	 * @param string $value Value.
	 *
	 * @return $this
	 */
	public function value( $value ) {
		$this->value = $value;

		return $this;
	}

	/**
	 * Get component content
	 *
	 * @return string
	 */
	public function get(): string {
		$multiple            = $this->multiple;
		$accept              = $this->accept;
		$max_size            = $this->max_size ?? wp_max_upload_size();
		$icon                = $this->uploader_icon;
		$title               = ! empty( $this->uploader_title ) ? $this->uploader_title : __( 'Drop files here or click to upload', 'tutor' );
		$subtitle            = ! empty( $this->uploader_subtitle ) ? $this->uploader_subtitle : __( 'PDF, DOC, DOCX, JPG, PNG Formats (Max 50MB)', 'tutor' );
		$button_text         = ! empty( $this->uploader_button_text ) ? $this->uploader_button_text : __( 'Select Files', 'tutor' );
		$on_file_select      = $this->attributes['onFileSelect'] ?? 'null';
		$on_error            = $this->attributes['onError'] ?? 'null';
		$variant             = $this->variant;
		$uploader_attributes = $this->attributes;

		// Remove Alpine specific attributes from HTML attributes.
		unset( $uploader_attributes['onFileSelect'] );
		unset( $uploader_attributes['onError'] );

		$this->attributes = $uploader_attributes;

		ob_start();
		?>
		<div 
			class="tutor-file-uploader-wrapper"
			x-data="tutorFileUploader({
				multiple: <?php echo $multiple ? 'true' : 'false'; ?>,
				accept: '<?php echo esc_attr( $accept ); ?>',
				maxSize: <?php echo (int) $max_size; ?>,
				onFileSelect: <?php echo esc_js( $on_file_select ); ?>,
				onError: <?php echo esc_js( $on_error ); ?>,
				variant: '<?php echo esc_attr( $variant ); ?>',
				value: values.<?php echo esc_attr( $this->name ); ?>,
				name: '<?php echo esc_attr( $this->name ); ?>',
				required: <?php echo is_bool( $this->required ) ? ( $this->required ? 'true' : 'false' ) : "'" . esc_js( $this->required ) . "'"; ?>,
				useWPMedia: <?php echo $this->use_wp_media ? 'true' : 'false'; ?>,
				wpMediaTitle: '<?php echo esc_js( $this->wp_media_title ); ?>',
				wpMediaButtonText: '<?php echo esc_js( $this->wp_media_button_text ); ?>',
				wpMediaLibraryType: '<?php echo esc_js( $this->wp_media_library_type ); ?>',
			})"
		>
			<template x-if="imagePreview">
				<div class="tutor-file-preview">
					<img :src="imagePreview" alt="Preview">
					<div class="tutor-file-preview-overlay">
						<div class="tutor-file-preview-actions">
							<?php
								Button::make()
									->label( __( 'Delete', 'tutor' ) )
									->variant( Variant::DESTRUCTIVE )
									->size( Size::SMALL )
									->attr( 'type', 'button' )
									->attr( '@click.stop', 'removeFile()' )
									->render();

								Button::make()
									->label( __( 'Upload New', 'tutor' ) )
									->variant( Variant::PRIMARY )
									->size( Size::SMALL )
									->attr( 'type', 'button' )
									->attr( '@click.stop', 'openFileDialog()' )
									->render();
							?>
						</div>
					</div>
				</div>
			</template>

			<template x-if="variant === 'file-uploader' && selectedFiles.length > 0">
				<div class="tutor-flex tutor-flex-column tutor-gap-3">
					<div class="tutor-grid tutor-grid-cols-2 tutor-sm-grid-col-1 tutor-gap-5">
						<template x-for="(file, index) in selectedFiles" :key="index">
							<div class="tutor-attachment-card-wrapper">
								<?php
								AttachmentCard::make()
									->title_attr( 'x-text', 'file.name' )
									->meta_attr( 'x-text', 'file.size' )
									->action_attr( '@click.stop', 'removeFile(index)' )
									->render();
								?>
							</div>
						</template>
					</div>
					<div class="tutor-mt-1">
						<button type="button" class="tutor-btn tutor-btn-primary-soft tutor-btn-sm" @click="openFileDialog()">
							<?php esc_html_e( 'Upload More Files', 'tutor' ); ?>
						</button>
					</div>
				</div>
			</template>

			<!-- Upload Area -->
			<div
				x-show="!imagePreview && (variant !== 'file-uploader' || selectedFiles.length === 0)"
				class="tutor-file-uploader"
				:class="{
					'tutor-file-uploader-drag-over': isDragOver,
					'tutor-file-uploader-disabled': isDisabled
				}"
				@click="openFileDialog()"
				@dragover.prevent="handleDragOver($event)"
				@dragleave.prevent="handleDragLeave($event)"
				@drop.prevent="handleDrop($event)"
				<?php echo self::IMAGE_UPLOADER === $variant ? 'data-image-uploader' : ''; ?>
			>
				<input 
					type="file" 
					class="tutor-file-uploader-input"
					:multiple="multiple"
					:accept="accept"
					:disabled="isDisabled"
					x-ref="fileInput"
					@change="handleFileSelect($event)"
					name="<?php echo esc_attr( $this->name ); ?>"
					id="<?php echo esc_attr( ! empty( $this->id ) ? $this->id : $this->name ); ?>"
					<?php echo $this->get_attributes_string(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
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
		<?php
		$this->component_string = ob_get_clean();
		return $this->component_string;
	}
}
