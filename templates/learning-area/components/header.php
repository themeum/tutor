<?php
/**
 * Tutor learning area header.
 *
 * @package Tutor\Templates
 * @subpackage LearningArea
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use TUTOR\Icon;

global $tutor_course;
?>
<div class="tutor-learning-header">
	<div class="tutor-learning-header-inner">
		<a class="tutor-learning-header-back" href="<?php echo esc_url( tutor_utils()->tutor_dashboard_url() ); ?>">
			<?php tutor_utils()->render_svg_icon( Icon::LEFT, 20, 20 ); ?>
		</a>
		<h5 class="tutor-learning-header-title">
			<?php echo esc_html( $tutor_course->post_title ); ?>
		</h5>
		<button class="tutor-learning-header-toggle-mobile" @click.stop="sidebarOpen = !sidebarOpen">
			<?php tutor_utils()->render_svg_icon( Icon::MENU, 20, 20 ); ?>
		</button>
	</div>
</div>
