<?php
/**
 * Event Badge Component
 *
 * @package TutorLMS\Templates
 */

use TUTOR\Icon;

defined( 'ABSPATH' ) || exit;

// Default values.
$text    = isset( $text ) ? $text : __( 'Live Class', 'tutor' );
$icon    = isset( $icon ) ? $icon : Icon::ZOOM_COLORIZE;
$variant = isset( $variant ) ? sanitize_key( $variant ) : '';

$badge_classes = array( 'tutor-event-badge' );

if ( ! empty( $variant ) ) {
	$badge_classes[] = 'tutor-event-badge-' . $variant;
}

$badge_classes = array_filter( array_map( 'sanitize_html_class', $badge_classes ) );

?>
<div class="<?php echo esc_attr( implode( ' ', $badge_classes ) ); ?>">
	<?php if ( ! empty( $icon ) ) : ?>
		<span class="tutor-event-badge-icon">
			<?php tutor_utils()->render_svg_icon( $icon, 16, 16 ); ?>
		</span>
	<?php endif; ?>
	<span class="tutor-event-badge-text"><?php echo esc_html( $text ); ?></span>
</div>

