<?php
/**
 * AttachmentCard Component Class.
 *
 * @package Tutor\Components
 * @author Themeum
 * @link https://themeum.com
 * @since 4.0.0
 */

namespace Tutor\Components;

use TUTOR\Icon;

defined( 'ABSPATH' ) || exit;

/**
 * Class AttachmentCard
 *
 * Example Usage:
 * AttachmentCard::make()
 *     ->file_name( 'lesson-plan.pdf' )
 *     ->file_size( '1.2 MB' )
 *     ->is_downloadable( true )
 *     ->action_attr( '@click', 'downloadFile()' )
 *     ->render();
 *
 * @since 4.0.0
 */
class AttachmentCard extends BaseComponent {

	/**
	 * File name
	 *
	 * @var string
	 */
	protected $file_name = '';

	/**
	 * File size
	 *
	 * @var string
	 */
	protected $file_size = '';

	/**
	 * Is downloadable
	 *
	 * @var bool
	 */
	protected $is_downloadable = false;

	/**
	 * Title attribute
	 *
	 * @var array
	 */
	protected $title_attr = array();

	/**
	 * Meta attribute
	 *
	 * @var array
	 */
	protected $meta_attr = array();

	/**
	 * Action attribute
	 *
	 * @var array
	 */
	protected $action_attr = array();

	/**
	 * Set file name
	 *
	 * @param string $file_name file name.
	 *
	 * @return self
	 */
	public function file_name( string $file_name ): self {
		$this->file_name = $file_name;
		return $this;
	}

	/**
	 * Set file size
	 *
	 * @param string $file_size file size.
	 *
	 * @return self
	 */
	public function file_size( string $file_size ): self {
		$this->file_size = $file_size;
		return $this;
	}

	/**
	 * Set is downloadable
	 *
	 * @param bool $is_downloadable is downloadable.
	 *
	 * @return self
	 */
	public function is_downloadable( bool $is_downloadable ): self {
		$this->is_downloadable = $is_downloadable;
		return $this;
	}

	/**
	 * Set title attribute
	 *
	 * @param string $key   Attribute name.
	 * @param string $value Attribute value.
	 *
	 * @return self
	 */
	public function title_attr( string $key, string $value ): self {
		$this->title_attr[ $key ] = $value;
		return $this;
	}

	/**
	 * Set meta attribute
	 *
	 * @param string $key   Attribute name.
	 * @param string $value Attribute value.
	 *
	 * @return self
	 */
	public function meta_attr( string $key, string $value ): self {
		$this->meta_attr[ $key ] = $value;
		return $this;
	}

	/**
	 * Set action attribute
	 *
	 * @param string $key   Attribute name.
	 * @param string $value Attribute value.
	 *
	 * @return self
	 */
	public function action_attr( string $key, string $value ): self {
		$this->action_attr[ $key ] = $value;
		return $this;
	}

	/**
	 * Get custom attributes string
	 *
	 * @param array $attributes attributes.
	 *
	 * @return string
	 */
	private function get_custom_attributes_string( array $attributes ): string {
		$compiled = array();

		foreach ( $attributes as $key => $value ) {
			$compiled[] = sprintf( '%s="%s"', esc_attr( $key ), esc_attr( $value ) );
		}

		return implode( ' ', $compiled );
	}

	/**
	 * Get component content
	 *
	 * @return string
	 */
	public function get(): string {
		$file_name       = $this->file_name;
		$file_size       = $this->file_size;
		$is_downloadable = $this->is_downloadable;
		$title_attr      = $this->get_custom_attributes_string( $this->title_attr );
		$meta_attr       = $this->get_custom_attributes_string( $this->meta_attr );
		$action_attr     = $this->get_custom_attributes_string( $this->action_attr );

		if ( '' === $file_name && '' === $title_attr ) {
			return '';
		}

		$icon_classes    = array( 'tutor-attachment-card-icon' );
		$icon_class_attr = implode( ' ', $icon_classes );

		$action_icon = $is_downloadable ? Icon::DOWNLOAD_2 : Icon::CROSS;

		ob_start();
		?>
		<div class="tutor-card tutor-attachment-card">
			<div class="<?php echo esc_attr( $icon_class_attr ); ?>" aria-hidden="true">
				<?php tutor_utils()->render_svg_icon( Icon::RESOURCES, 24, 24 ); ?>
			</div>

			<div class="tutor-attachment-card-body">
				<div class="tutor-attachment-card-title" <?php echo $title_attr; // phpcs:ignore --already-escaped WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
					<?php echo esc_html( $file_name ); ?>
				</div>

				<?php if ( $file_size || $meta_attr ) : ?>
					<span class="tutor-attachment-card-meta" <?php echo $meta_attr; // phpcs:ignore --already-escaped WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
						<?php echo esc_html( $file_size ); ?>
					</span>
				<?php endif; ?>
			</div>

			<button type="button" class="tutor-btn tutor-btn-ghost tutor-btn-x-small tutor-btn-icon" <?php echo $action_attr; // phpcs:ignore --already-escaped WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
				<?php tutor_utils()->render_svg_icon( $action_icon, 16, 16 ); ?>
			</button>
		</div>
		<?php

		$this->component_string = ob_get_clean();
		return $this->component_string;
	}
}
