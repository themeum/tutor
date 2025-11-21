<?php
/**
 * Tutor Learning Area.
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use TUTOR\Icon;

?>
<div class="tutor-learning-area" x-data="{ sidebarOpen: false, isFullScreen: false }" :class="{ 'is-fullscreen': isFullScreen }">
	<?php tutor_load_template( 'demo-components.learning-area.components.header' ); ?>
	<div class="tutor-learning-area-body">
		<?php tutor_load_template( 'demo-components.learning-area.components.sidebar' ); ?>
		<div class="tutor-learning-area-content">
			<div class="tutor-learning-area-container">
				<!-- <?php tutor_load_template( 'demo-components.learning-area.components.lesson' ); ?> -->
				<?php tutor_load_template( 'demo-components.learning-area.pages.course-info' ); ?>
			</div>
		</div>
		<button 
			class="tutor-btn tutor-btn-outline tutor-btn-small tutor-btn-icon tutor-expand-btn"
			@click="isFullScreen = !isFullScreen"
		>
			<template x-if="!isFullScreen">
				<?php tutor_utils()->render_svg_icon( Icon::EXPAND ); ?>
			</template>

			<template x-if="isFullScreen">
				<?php tutor_utils()->render_svg_icon( Icon::COLLAPSED ); ?>
			</template>
		</button>
	</div>
</div>
