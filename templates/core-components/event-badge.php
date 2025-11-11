<?php
/**
 * Event Badge Component
 *
 * @package TutorLMS\Templates
 */

use TUTOR\Icon;

defined( 'ABSPATH' ) || exit;

// Default values.
$text = isset( $text ) ? $text : __( 'Live Class', 'tutor' );
$icon = isset( $icon ) ? $icon : Icon::ZOOM_COLORIZE;

?>
<div class="tutor-event-badge">
	<span class="tutor-event-badge-icon">
		<?php tutor_utils()->render_svg_icon( $icon, 16, 16 ); ?>
	</span>
	<span class="tutor-event-badge-text"><?php echo esc_html( $text ); ?></span>
</div>

