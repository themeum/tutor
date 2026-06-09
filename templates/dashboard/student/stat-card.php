<?php
/**
 * Single Stat Card Reusable Component
 *
 * @package Tutor\Templates
 * @subpackage Dashboard
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use Tutor\Components\SvgIcon;

$tag             = isset( $url ) && ! empty( $url ) ? 'a' : 'div';
$class_name      = $class ?? '';
$icon_size       = $icon_size ?? 20;
$modal_id        = isset( $modal_id ) ? sanitize_key( $modal_id ) : '';
$modal_action    = $modal_id ? sprintf( "TutorCore.modal.showModal('%s')", $modal_id ) : '';
$card_attributes = '';

if ( 'a' === $tag ) {
	$card_attributes = sprintf( ' href="%s"', esc_url( $url ) );
} elseif ( $modal_action ) {
	$card_attributes = sprintf(
		' role="button" tabindex="0" @click="%1$s" @keydown.enter.prevent="%1$s" @keydown.space.prevent="%1$s"',
		esc_attr( $modal_action )
	);
}
?>

<<?php echo tag_escape( $tag ); ?><?php echo $card_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Attribute values are escaped above. ?> class="tutor-stat-card <?php echo esc_attr( $class_name ); ?>">
	<div class="tutor-stat-card-header">
		<h3 class="tutor-stat-card-title">
			<?php echo esc_html( $title ); ?>
		</h3>
		<div class="tutor-stat-card-icon tutor-flex">
			<?php SvgIcon::make()->name( $icon )->size( $icon_size )->render(); ?>
		</div>
	</div>
	<div class="tutor-stat-card-content">
		<div class="tutor-stat-card-value">
			<?php echo esc_html( $value ); ?>
		</div>
	</div>
</<?php echo tag_escape( $tag ); ?>>
