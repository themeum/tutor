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

use TUTOR\Course_List;
use TUTOR\Icon;
use TUTOR\Input;

wp_head();

$current_user_id = get_current_user_id();

// Tutor global variable for using inside learning area.
$tutor_current_post_type    = get_post_type();
$tutor_current_post         = get_post();
$tutor_current_content_id   = get_the_ID();
$tutor_course_id            = tutor()->course_post_type === $tutor_current_post_type ? $tutor_current_content_id : tutor_utils()->get_course_id_by_subcontent( $tutor_current_content_id );
$tutor_course               = get_post( $tutor_course_id );
$tutor_course_list_url      = tutor_utils()->course_archive_page_url();
$tutor_is_enrolled          = tutor_utils()->is_enrolled( $tutor_course_id );
$tutor_is_public_course     = Course_List::is_public( $tutor_course_id );
$tutor_is_course_instructor = tutor_utils()->has_user_course_content_access( $current_user_id, $tutor_course_id );

?>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<div class="tutor-learning-area<?php echo esc_attr( is_admin_bar_showing() ? ' tutor-has-admin-bar' : '' ); ?>" x-data="{ sidebarOpen: false, isFullScreen: false }" :class="{ 'is-fullscreen': isFullScreen }">
	<?php tutor_load_template( 'learning-area.components.header' ); ?>
	<div class="tutor-learning-area-body">
		<?php tutor_load_template( 'learning-area.components.sidebar' ); ?>
		<div class="tutor-learning-area-content">
			<div class="tutor-learning-area-container">
				<?php
				// Get requested page from query string and sanitize.
				$subpage = Input::get( 'subpage' );
				$is_pro  = false;

				// Pro subpages.
				$pro_subpages = array( 'webinar', 'certificate' );
				$is_pro       = in_array( $subpage, $pro_subpages, true );

				if ( $subpage ) {
					$subpage_template = tutor_get_template( 'learning-area.subpages.' . $subpage, $is_pro );
					if ( file_exists( $subpage_template ) ) {
						tutor_load_template(
							'learning-area.subpages.' . $subpage,
							array(
								'base_url'                 => esc_url( add_query_arg( array( 'subpage' => $subpage ), get_permalink( $tutor_course_id ) ) ),
								'tutor_current_post_type'  => $tutor_current_post_type,
								'tutor_current_post'       => $tutor_current_post,
								'tutor_current_content_id' => $tutor_current_content_id,
								'tutor_course_id'          => $tutor_course_id,
								'tutor_course'             => $tutor_course,
								'tutor_course_list_url'    => $tutor_course_list_url,
								'tutor_is_enrolled'        => $tutor_is_enrolled,
								'tutor_is_public_course'   => $tutor_is_public_course,
								'tutor_is_course_instructor' => $tutor_is_course_instructor,
							),
							$is_pro
						);
					} else {
						do_action( 'tutor_single_content_' . $tutor_post_type );
					}
				} else {
					do_action( 'tutor_single_content_' . $tutor_current_post_type, $tutor_current_post );
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