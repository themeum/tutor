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

wp_head();

// Tutor global variable for using inside learning area.

$tutor_course_content_id = get_the_ID();
$tutor_course_id         = tutor_utils()->get_course_id_by_subcontent( $tutor_course_content_id );
$tutor_course            = get_post( $tutor_course_id );
$tutor_course_list_url   = tutor_utils()->course_archive_page_url();

?>
<div class="tutor-learning-area" x-data="{ sidebarOpen: false, isFullScreen: false }" :class="{ 'is-fullscreen': isFullScreen }">
	<?php tutor_load_template( 'learning-area.components.header' ); ?>
	<div class="tutor-learning-area-body">
		<?php tutor_load_template( 'learning-area.components.sidebar' ); ?>
		<div class="tutor-learning-area-content">
			<div class="tutor-learning-area-container">
				<?php
				// Get requested page from query string and sanitize.
				$subpage = Input::get( 'subpage' );

				// Whitelist allowed items to avoid arbitrary file inclusion.
				$nav_items = array(
					'resources',
					'qna',
					'course-info',
					'webinar',
					'certificate',
				);

				$nav_items = apply_filters( 'tutor_learning_area_nav_items', $nav_items );

				if ( $subpage && in_array( $subpage, $nav_items, true ) ) {
					tutor_load_template( 'learning-area.subpages.' . $subpage );
				} else {
					tutor_load_template( 'learning-area.lesson.index' );
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
<?php wp_footer(); ?>
