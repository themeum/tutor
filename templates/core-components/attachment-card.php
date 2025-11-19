<?php
/**
 * Attachment card component.
 *
 * @package TutorLMS\Templates
 * @since 4.0.0
 */

use TUTOR\Icon;

defined( 'ABSPATH' ) || exit;

$file_name      = isset( $file_name ) ? $file_name : '';
$file_size      = isset( $file_size ) ? $file_size : '';
$is_downloading = ! empty( $is_downloading );

if ( '' === $file_name ) {
	return;
}

$icon_classes = array( 'tutor-attachment-card-icon' );

if ( $is_downloading ) {
	$icon_classes[] = 'tutor-attachment-card-icon-loading';
}

$icon_class_attr = implode( ' ', $icon_classes );

$icon_name    = $is_downloading ? Icon::LOADING : Icon::RESOURCES;
$action_icon  = $is_downloading ? Icon::CROSS : Icon::DOWNLOAD_2;
$action_label = $is_downloading ? __( 'Cancel download', 'tutor' ) : __( 'Download file', 'tutor' );
?>
<div class="tutor-attachment-card">
	<div class="<?php echo esc_attr( $icon_class_attr ); ?>" aria-hidden="true">
		<?php tutor_utils()->render_svg_icon( $icon_name, 24, 24 ); ?>
	</div>

	<div class="tutor-attachment-card-body">
		<div class="tutor-attachment-card-title">
			<?php echo esc_html( $file_name ); ?>
		</div>

		<?php if ( $file_size ) : ?>
			<span class="tutor-attachment-card-meta">
				<?php echo esc_html( $file_size ); ?>
			</span>
		<?php endif; ?>
	</div>

	<div class="tutor-attachment-card-actions">
		<?php tutor_utils()->render_svg_icon( $action_icon, 16, 16 ); ?>
	</div>
</div>

