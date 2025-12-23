
<?php
/**
 * Learning area main file
 *
 * @package Tutor\Templates
 * @subpackage LearningArea
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use TUTOR\Icon;
use TUTOR\Input;

?>
<div class="tutor-learning-area" x-data="{ sidebarOpen: false, isFullScreen: false }" :class="{ 'is-fullscreen': isFullScreen }">
	<?php tutor_load_template( 'demo-components.learning-area.components.header' ); ?>
	<div class="tutor-learning-area-body">
		<?php tutor_load_template( 'demo-components.learning-area.components.sidebar' ); ?>
		<div class="tutor-learning-area-content">
			<div class="tutor-learning-area-container">
				<?php
				// Get requested page from query string and sanitize.
				$learning_page = Input::get( 'page', 'home' );

				// Whitelist allowed pages to avoid arbitrary file inclusion.
				$allowed_pages = array(
					'resources',
					'qna',
					'course-info',
					'webinar',
					'certificate',
				);

				$allowed_pages = (array) apply_filters( 'tutor_demo_dashboard_allowed_pages', $allowed_pages );

				if ( $learning_page && in_array( $learning_page, $allowed_pages, true ) ) {
					tutor_load_template( 'demo-components.learning-area.pages.' . $learning_page );
				} else {
					tutor_load_template( 'demo-components.learning-area.components.lesson' );
				}
				?>
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
