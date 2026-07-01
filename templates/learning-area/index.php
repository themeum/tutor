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

use Tutor\Components\ConfirmationModal;
use TUTOR\Course_List;
use TUTOR\Icon;
use Tutor\Components\SvgIcon;
use TUTOR\Course;
use TUTOR\Dashboard;
use TUTOR\Input;
use Tutor\Models\CourseModel;
use Tutor\Models\EnrollmentModel;
use TUTOR\Quiz;
use TUTOR\Template;
use TUTOR\User;

$current_user_id          = get_current_user_id();
$tutor_current_post       = get_post();
$tutor_current_post_type  = get_post_type();
$tutor_current_content_id = get_the_ID();
$tutor_course_id          = tutor()->course_post_type === $tutor_current_post_type ? $tutor_current_content_id : tutor_utils()->get_course_id_by_subcontent( $tutor_current_content_id );

do_action( 'tutor/course/single/content/before/all', $tutor_course_id, $tutor_current_content_id );

$tutor_course_list_url      = tutor_utils()->course_archive_page_url();
$tutor_is_enrolled          = EnrollmentModel::is_enrolled( $tutor_course_id );
$tutor_is_public_course     = Course_List::is_public( $tutor_course_id );
$tutor_is_course_instructor = tutor_utils()->is_instructor_of_this_course( $current_user_id, $tutor_course_id, true );
$tutor_is_course_completed  = tutor_utils()->is_completed_course( $tutor_course_id, $current_user_id );
$tutor_can_complete_course  = CourseModel::can_complete_course( $tutor_course_id, $current_user_id ) && ! $tutor_is_course_completed;
$is_tour_completed          = get_user_meta( $current_user_id, User::TOUR_COMPLETED_META, true );

$tutor_course_progress   = tutor_utils()->get_course_completed_percent( $tutor_course_id, $current_user_id );
$tutor_completion_mode   = tutor_utils()->get_option( 'course_completion_process' );
$tutor_retake_course     = tutor_utils()->get_option( 'course_retake_feature', false ) && ( $tutor_is_course_completed || $tutor_course_progress >= 100 );
$tutor_can_retake_course = $tutor_retake_course && ( CourseModel::MODE_FLEXIBLE === $tutor_completion_mode || $tutor_is_course_completed );

// Global variables defined above are used by the 'make_learning_area_sub_page_nav_items' function.
$subpages            = Template::make_learning_area_sub_page_nav_items();
$subpage             = Input::get( 'subpage' );
$attempt_id          = Input::get( 'attempt_id', 0, Input::TYPE_INT );
$user_action         = Input::get( 'action' );
$tutor_course        = get_post( $tutor_course_id );
$course_title        = $tutor_course ? get_the_title( $tutor_course ) : '';
$content_title       = $tutor_current_post ? get_the_title( $tutor_current_post ) : $course_title;
$learning_meta_title = $content_title ? $content_title : __( 'Learning area', 'tutor' );
$site_name           = get_bloginfo( 'name' );

if ( $subpage && ! empty( $subpages[ $subpage ]['title'] ) ) {
	$learning_meta_title = $subpages[ $subpage ]['title'];
} elseif ( Quiz::ACTION_VIEW_DETAILS === $user_action ) {
	if ( $content_title ) {
		/* translators: %s: quiz attempt details. */
		$learning_meta_title = sprintf( __( 'Quiz Attempt Details: %s', 'tutor' ), $content_title );
	}
} elseif ( tutor()->quiz_post_type === $tutor_current_post_type && $content_title ) {
	/* translators: %s: quiz attempt title. */
	$learning_meta_title = sprintf( __( 'Quiz: %s', 'tutor' ), $content_title );
}

/* translators: %s: learning area meta title. */
$page_meta_title = sprintf( __( '%1$s - %2$s', 'tutor' ), $learning_meta_title, $site_name );

Dashboard::set_document_title( $page_meta_title );
?>
<!DOCTYPE html>
	<html <?php language_attributes(); ?>>
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<?php wp_head(); ?>
	</head>
	<body <?php body_class( '' ); ?> x-data="tutorCourseCompleteHandler()">
	<?php wp_body_open(); ?>
<?php

// Auto complete course.
if ( CourseModel::can_autocomplete_course( $tutor_course_id, $current_user_id ) ) {
	$mark_completed = CourseModel::mark_course_as_completed( $tutor_course_id, $current_user_id );
	if ( $mark_completed ) {
		Course::set_review_popup_data( $current_user_id, $tutor_course_id );
	}
}

$course_complete_modal_id = 'tutor-course-complete-modal';
$course_retake_modal_id   = 'tutor-course-retake-modal';

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

?>
	<div
		class="tutor-learning-area<?php echo esc_attr( is_admin_bar_showing() ? ' tutor-has-admin-bar' : '' ); ?>"
		x-data="{ sidebarOpen: false, isFullScreen: false }"
		:class="{ 'is-fullscreen': isFullScreen }"
	>
		<?php tutor_load_template( 'learning-area.components.header' ); ?>
		<div class="tutor-learning-area-body">
			<?php tutor_load_template( 'learning-area.components.sidebar' ); ?>
			<div class="tutor-learning-area-content" role="main">
				<div class="tutor-learning-area-container">
					<?php
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
				:aria-label="isFullScreen ? '<?php echo esc_attr__( 'Exit fullscreen', 'tutor' ); ?>' : '<?php echo esc_attr__( 'Enter fullscreen', 'tutor' ); ?>'"
			>
				<template x-if="!isFullScreen">
					<?php SvgIcon::make()->name( Icon::EXPAND )->flip_rtl()->render(); ?>
				</template>

				<template x-if="isFullScreen">
					<?php SvgIcon::make()->name( Icon::COLLAPSED )->flip_rtl()->render(); ?>
				</template>
			</button>
		</div>
	</div>
	<?php
	if ( is_user_logged_in() && ! $is_tour_completed ) {
		tutor_load_template( 'shared.tour' );
	}
	?>
	<?php wp_footer(); ?>

	<?php
	if ( $tutor_can_complete_course ) {
		$progress = $tutor_course_progress['completed_percent'] ?? 0;
		if ( $progress < 100 ) {
			ConfirmationModal::make()
			->id( $course_complete_modal_id )
			->title( __( 'Finish Course Early?', 'tutor' ) )
			->message( Course::get_complete_modal_content( $tutor_course_progress ), wp_kses_allowed_html( 'post' ) )
			->cancel_text( __( 'Go Back to Course', 'tutor' ) )
			->confirm_text( __( 'Complete Anyway', 'tutor' ) )
			->icon( tutor_utils()->get_themed_svg( 'images/illustrations/warning.svg' ), 80, 80, ConfirmationModal::ICON_TYPE_HTML )
			->confirm_handler( "handleCourseComplete($tutor_course_id)" )
			->mutation_state( 'courseCompleteMutation' )
			->render();
		}
	}
	if ( $tutor_can_retake_course ) {
		ConfirmationModal::make()
		->id( $course_retake_modal_id )
		->title( __( 'Start the Course Again?', 'tutor' ) )
		->message( __( 'Retaking the course will reset your progress and start everything from the beginning.', 'tutor' ) )
		->icon( tutor_utils()->get_themed_svg( 'images/illustrations/retake-course.svg' ), 80, 80, ConfirmationModal::ICON_TYPE_HTML )
		->cancel_text( __( 'Cancel', 'tutor' ) )
		->confirm_text( __( 'Start Retake', 'tutor' ) )
		->confirm_handler( "handleCourseRetake($tutor_course_id)" )
		->mutation_state( 'courseRetakeMutation' )
		->render();
	}
	?>
</body>
</html>