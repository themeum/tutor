<?php
/**
 * Live Session Card Component
 *
 * @package TutorLMS\Templates
 */

use TUTOR\Icon;

defined( 'ABSPATH' ) || exit;

// Default values.
$text = isset( $text ) ? $text : __( 'Live Session', 'tutor' );
$icon = isset( $icon ) ? $icon : Icon::ZOOM_COLORIZE;

?>
<div class="tutor-live-session-card">
	<span class="tutor-live-session-card-icon">
		<?php tutor_utils()->render_svg_icon( $icon, 16, 16 ); ?>
	</span>
	<span class="tutor-live-session-card-text"><?php echo esc_html( $text ); ?></span>
</div>

