<?php
/**
 * Show lesson nav item on the learning area
 *
 * @package Tutor\Templates
 * @subpackage LearningArea
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use TUTOR\Icon;

?>

<div class="tutor-learning-nav-item">
	<a href="#">
		<?php tutor_utils()->render_svg_icon( Icon::COMPLETED_COLORIZE, 20, 20 ); ?>
		<div>Introduction</div>
	</a>
</div>
<div class="tutor-learning-nav-item active">
	<a href="#">
		<div class="tutor-learning-nav-progress">
			<div x-data="tutorStatics({ 
				value: 65,
				size: 'tiny',
				type: 'progress',
				showLabel: false,
				background: 'var(--tutor-actions-gray-empty)',
				strokeColor: 'var(--tutor-border-hover)' })">
				<div x-html="render()"></div>
			</div>
		</div>
		<div>Introduction</div>
	</a>
</div>
