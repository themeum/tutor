<?php
/**
 * Base Template for Account
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use TUTOR\Icon;

defined( 'ABSPATH' ) || exit;

?>
<div class="tutor-account-header">
	<div class="tutor-account-container">
		<div class="tutor-flex tutor-items-center tutor-justify-between">
			<a href="<?php echo esc_url( $back_url ); ?>" class="tutor-btn tutor-btn-ghost tutor-btn-x-small tutor-btn-icon">
				<?php tutor_utils()->render_svg_icon( Icon::LEFT ); ?>
			</a>
			<h4 class="tutor-account-header-title">
				<?php echo esc_html( $page_data['title'] ?? '' ); ?>
			</h4>
			<a href="<?php echo esc_url( $back_url ); ?>" class="tutor-btn tutor-btn-ghost tutor-btn-x-small tutor-btn-icon">
				<?php tutor_utils()->render_svg_icon( Icon::CROSS ); ?>
			</a>
		</div>
	</div>
</div>
