<?php
/**
 * Attachment card component.
 *
 * @package TutorLMS\Templates
 * @since 4.0.0
 */

use TUTOR\Icon;

defined( 'ABSPATH' ) || exit;

$file_name       = isset( $file_name ) ? $file_name : '';
$file_size       = isset( $file_size ) ? $file_size : '';
$is_downloadable = ! empty( $is_downloadable );
$title_attr      = isset( $title_attr ) ? $title_attr : '';
$meta_attr       = isset( $meta_attr ) ? $meta_attr : '';
$action_attr     = isset( $action_attr ) ? $action_attr : '';

if ( '' === $file_name && '' === $title_attr ) {
	return;
}

$icon_classes = array( 'tutor-attachment-card-icon' );

$icon_class_attr = implode( ' ', $icon_classes );

$action_icon  = $is_downloadable ? Icon::DOWNLOAD_2 : Icon::CROSS;
$action_label = $is_downloadable ? __( 'Download', 'tutor' ) : __( 'Remove file', 'tutor' );

?>
<div class="tutor-card tutor-attachment-card">
	<div class="<?php echo esc_attr( $icon_class_attr ); ?>" aria-hidden="true">
		<?php tutor_utils()->render_svg_icon( Icon::RESOURCES, 24, 24 ); ?>
	</div>

	<div class="tutor-attachment-card-body">
		<div class="tutor-attachment-card-title" <?php echo $title_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
			<?php echo esc_html( $file_name ); ?>
		</div>

		<?php if ( $file_size || $meta_attr ) : ?>
			<span class="tutor-attachment-card-meta" <?php echo $meta_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
				<?php echo esc_html( $file_size ); ?>
			</span>
		<?php endif; ?>
	</div>

	<button type="button" class="tutor-btn tutor-btn-ghost tutor-btn-x-small tutor-btn-icon" <?php echo $action_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
		<?php tutor_utils()->render_svg_icon( $action_icon, 16, 16 ); ?>
	</button>
</div>
