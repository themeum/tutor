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

defined( 'ABSPATH' ) || exit;

use TUTOR\Course_List;
use TUTOR\Icon;
use Tutor\Components\SvgIcon;
use TUTOR\Input;
use Tutor\Models\CourseModel;
use TUTOR\Quiz;
use TUTOR\Template;

// Tutor global variable for using inside learning area.
$current_user_id          = get_current_user_id();
$tutor_current_post_type  = get_post_type();
$tutor_current_post       = get_post();
$tutor_current_content_id = get_the_ID();
$tutor_course_id          = tutor()->course_post_type === $tutor_current_post_type ? $tutor_current_content_id : tutor_utils()->get_course_id_by_subcontent( $tutor_current_content_id );
$tutor_course             = get_post( $tutor_course_id );
$tutor_course_list_url    = tutor_utils()->course_archive_page_url();
$tutor_is_enrolled        = tutor_utils()->is_enrolled( $tutor_course_id );
$tutor_is_public_course   = Course_List::is_public( $tutor_course_id );

$args = array(
	'current_post_type' => $tutor_current_post_type,
	'current_post_id'   => $tutor_current_content_id,
	'course_id'         => $tutor_course_id,
	'is_public'         => $tutor_is_public_course,
	'is_enrolled'       => $tutor_is_enrolled,
);

$tutor_course_content_access = CourseModel::has_course_content_access( $args );
if ( ! $tutor_course_content_access ) {
	if ( $current_user_id ) {
		tutor_load_template( 'single.lesson.required-enroll' );
	} else {
		tutor_load_template( 'login' );
	}
	return;
}

wp_head();

$current_user_id = get_current_user_id();
$subpages        = Template::make_learning_area_sub_page_nav_items();

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

$tutor_is_started_quiz = false;
if ( tutor()->quiz_post_type === $tutor_current_post_type ) {
	$tutor_is_started_quiz = tutor_utils()->is_started_quiz( $tutor_current_content_id );

	if ( $tutor_is_started_quiz ) {
		tutor_load_template( 'learning-area.quiz.attempt' );
		wp_footer();
		exit;
	}
}

$attempt_id  = Input::get( 'attempt_id', 0, Input::TYPE_INT );
$user_action = Input::get( 'action' );

if ( Quiz::ACTION_VIEW_DETAILS === $user_action && $attempt_id ) {
	tutor_load_template( 'learning-area.quiz.attempt-details' );
	wp_footer();
	exit;
}

$subpages = Template::make_learning_area_sub_page_nav_items();
?>
<body <?php body_class(); ?>>
	<div
		class="tutor-learning-area<?php echo esc_attr( is_admin_bar_showing() ? ' tutor-has-admin-bar' : '' ); ?>"
		x-data="{ sidebarOpen: false, isFullScreen: false }"
		:class="{ 'is-fullscreen': isFullScreen }"
	>
		<?php tutor_load_template( 'learning-area.components.header' ); ?>
		<div class="tutor-learning-area-body">
			<?php tutor_load_template( 'learning-area.components.sidebar' ); ?>
			<div class="tutor-learning-area-content">
				<div class="tutor-learning-area-container">
					<?php
					// Get requested page from query string and sanitize.
					$subpage = Input::get( 'subpage' );
					if ( $subpage ) {
						$template = $subpages[ $subpage ]['template'] ?? '';
						if ( file_exists( $template ) ) {
							tutor_load_template_from_custom_path( $template );
						} else {
							do_action( 'tutor_single_content_' . $tutor_current_post_type );
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
					<?php SvgIcon::make()->name( Icon::EXPAND )->render(); ?>
				</template>

				<template x-if="isFullScreen">
					<?php SvgIcon::make()->name( Icon::COLLAPSED )->render(); ?>
				</template>
			</button>
		</div>
	</div>
	<?php wp_footer(); ?>
</body>
