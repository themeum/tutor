<?php
/**
 * Brand logo settings field.
 * This is custom field type for Brand logo settings.
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
$logos = array(
	'light' => array(
		'label' => __( 'Light Mode', 'tutor' ),
		'key'   => 'brand_logo_light',
		'icon'  => Icon::LIGHT,
	),
	'dark'  => array(
		'label' => __( 'Dark Mode', 'tutor' ),
		'key'   => 'brand_logo_dark',
		'icon'  => Icon::DARK,
	),
);
?>
<div class="tutor-option-field-row tutor-d-block tutor-brand-logo-field" id="field_brand_logo">
	<div class="tutor-option-field-label">
		<div class="tutor-fs-6 tutor-fw-medium" tutor-option-name><?php echo esc_html( $field['label'] ); ?></div>
	</div>
	<div class="tutor-brand-logo-list">
		<?php
		$total_logos = count( $logos );
		$index       = 0;
		foreach ( $logos as $theme => $logo ) :
			++$index;
			$attachment_id = absint( get_tutor_option( $logo['key'], 0 ) );
			$image_url     = $attachment_id && wp_attachment_is_image( $attachment_id ) ? wp_get_attachment_image_url( $attachment_id, 'medium' ) : '';
			?>
			<div class="tutor-brand-logo-upload<?php echo esc_attr( $image_url ? ' has-image' : '' ); ?>" data-brand-logo-theme="<?php echo esc_attr( $theme ); ?>">
				<div class="tutor-fs-6 tutor-d-flex tutor-align-center tutor-gap-1">
					<?php SvgIcon::make()->name( $logo['icon'] )->size( 20 )->render(); ?>
					<?php echo esc_html( $logo['label'] ); ?>
				</div>
				<input type="hidden" class="tutor-brand-logo-upload-input" name="tutor_option[<?php echo esc_attr( $logo['key'] ); ?>]" value="<?php echo esc_attr( $attachment_id ); ?>">
				<div class="tutor-brand-logo-upload-control">
				<div class="tutor-brand-logo-upload-preview<?php echo esc_attr( $image_url ? ' has-image' : '' ); ?>">
					<img src="<?php echo esc_url( $image_url ); ?>" alt=""<?php echo $image_url ? '' : ' hidden'; ?> />
					<div class="tutor-brand-logo-upload-actions">
						<button type="button" class="tutor-btn tutor-brand-logo-upload-select" aria-label="<?php esc_attr_e( 'Replace logo', 'tutor' ); ?>">
							<?php SvgIcon::make()->name( Icon::RELOAD_2 )->size( 12 )->render(); ?>
						</button>
						<button type="button" class="tutor-btn tutor-brand-logo-upload-remove" aria-label="<?php esc_attr_e( 'Remove logo', 'tutor' ); ?>">
							<?php SvgIcon::make()->name( Icon::DELETE_2 )->size( 12 )->render(); ?>
						</button>
					</div>
				</div>
					<button type="button" class="tutor-btn tutor-brand-logo-upload-select tutor-brand-logo-upload-empty">
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
			<?php if ( $index < $total_logos ) : ?>
				<div class="tutor-hr tutor-my-8" aria-hidden="true"></div>
			<?php endif; ?>
		<?php endforeach; ?>
	</div>
	<p class="tutor-fs-7 tutor-color-muted tutor-mt-8 tutor-d-flex tutor-align-center tutor-gap-1">
		<?php SvgIcon::make()->name( Icon::INFO_OCTAGON )->render(); ?>
		<?php esc_html_e( 'Recommended size: 700 × 430 px • Supported formats: .jpg, .jpeg, .png', 'tutor' ); ?>

	</p>
</div>
