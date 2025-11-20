<?php
/**
 * Tutor profile pages header.
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use TUTOR\Icon;

$page_title = $page_title ?? __( 'Profile', 'tutor' );

?>
<div class="tutor-profile-header">
	<div class="tutor-profile-container">
		<div class="tutor-flex tutor-items-center tutor-justify-between">
			<button class="tutor-btn tutor-btn-ghost tutor-btn-x-small tutor-btn-icon">
				<?php tutor_utils()->render_svg_icon( Icon::LEFT ); ?>
			</button>
			<h4 class="tutor-profile-header-title">
				<?php echo esc_html( $page_title ); ?>
			</h4>
			<button class="tutor-btn tutor-btn-ghost tutor-btn-x-small tutor-btn-icon">
				<?php tutor_utils()->render_svg_icon( Icon::CROSS ); ?>
			</button>
		</div>
	</div>
</div>
