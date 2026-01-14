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

use TUTOR\Icon;

global $tutor_course;
?>
<div class="tutor-learning-header">
	<div class="tutor-learning-header-inner">
		<button class="tutor-learning-header-back">
			<?php tutor_utils()->render_svg_icon( Icon::LEFT, 20, 20 ); ?>
		</button>
		<h5 class="tutor-learning-header-title">
			<?php echo esc_html( $tutor_course->post_title ); ?>
		</h5>
		<div class="tutor-learning-header-mobile">
			<button class="tutor-learning-header-toggle-mobile" @click.stop="sidebarOpen = !sidebarOpen">
				<?php tutor_utils()->render_svg_icon( Icon::MENU, 20, 20 ); ?>
			</button>
		</div>
	</div>
</div>
