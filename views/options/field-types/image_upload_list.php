<?php
/**
 * Image Upload List field type.
 * A generic list of themed image uploaders defined by $field["items"],
 * where each item has a "label", "key", and "icon" key.
 *
 * @package Tutor\Views
 * @subpackage Tutor\Settings
 *
 * @since 4.0.4
 */

defined( 'ABSPATH' ) || exit;

use TUTOR\Icon;
use Tutor\Components\SvgIcon;

/**
 * Field settings.
 *
 * @var array $field
 */
$items    = $field['items'] ?? array();
$field_id = 'field_' . $field['key'];
?>
<div class="tutor-option-field-row tutor-d-block tutor-image-upload-list-field" id="<?php echo esc_attr( $field_id ); ?>">
	<div class="tutor-option-field-label">
		<div class="tutor-fs-6 tutor-fw-medium" tutor-option-name><?php echo esc_html( $field['label'] ); ?></div>
	</div>
	<div class="tutor-image-upload-list">
		<?php
		$total_items = count( $items );
		$index       = 0;
		foreach ( $items as $theme => $item ) :
			++$index;
			$attachment_id = absint( get_tutor_option( $item['key'], 0 ) );
			$image_url     = $attachment_id && wp_attachment_is_image( $attachment_id ) ? wp_get_attachment_image_url( $attachment_id, 'medium' ) : '';
			?>
			<div class="tutor-image-upload-item<?php echo esc_attr( $image_url ? ' has-image' : '' ); ?>" data-image-upload-theme="<?php echo esc_attr( $theme ); ?>">
				<div class="tutor-fs-6 tutor-d-flex tutor-align-center tutor-gap-1">
					<?php SvgIcon::make()->name( $item['icon'] )->size( 20 )->render(); ?>
					<?php echo esc_html( $item['label'] ); ?>
				</div>
				<input type="hidden" class="tutor-image-upload-input" name="tutor_option[<?php echo esc_attr( $item['key'] ); ?>]" value="<?php echo esc_attr( $attachment_id ); ?>">
				<div class="tutor-image-upload-control">
				<div class="tutor-image-upload-preview<?php echo esc_attr( $image_url ? ' has-image' : '' ); ?>">
					<img src="<?php echo esc_url( $image_url ); ?>" alt=""<?php echo $image_url ? '' : ' hidden'; ?> />
					<div class="tutor-image-upload-actions">
						<button type="button" class="tutor-btn tutor-image-upload-select" aria-label="<?php esc_attr_e( 'Replace logo', 'tutor' ); ?>">
							<?php SvgIcon::make()->name( Icon::RELOAD_2 )->size( 12 )->render(); ?>
						</button>
						<button type="button" class="tutor-btn tutor-image-upload-remove" aria-label="<?php esc_attr_e( 'Remove logo', 'tutor' ); ?>">
							<?php SvgIcon::make()->name( Icon::DELETE_2 )->size( 12 )->render(); ?>
						</button>
					</div>
				</div>
					<button type="button" class="tutor-btn tutor-image-upload-select tutor-image-upload-empty">
						<?php
							SvgIcon::make()
								->name( Icon::IMAGE_PLUS )
								->size( 20 )
								->render();
						?>
						<?php esc_html_e( 'Upload logo', 'tutor' ); ?>
					</button>
				</div>
			</div>
			<?php if ( $index < $total_items ) : ?>
				<div class="tutor-hr tutor-my-8" aria-hidden="true"></div>
			<?php endif; ?>
		<?php endforeach; ?>
	</div>
	<p class="tutor-fs-7 tutor-color-muted tutor-mt-8 tutor-d-flex tutor-align-center tutor-gap-1">
		<?php SvgIcon::make()->name( Icon::INFO_OCTAGON )->render(); ?>
		<?php esc_html_e( 'Recommended size: 700 x 430 px - Supported formats: .jpg, .jpeg, .png', 'tutor' ); ?>

	</p>
</div>
