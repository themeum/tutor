<?php
/**
 * Tutor learning area lesson.
 *
 * @package Tutor\Templates
 * @subpackage LearningArea
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use TUTOR\Input;
use TUTOR\Lesson;

$lesson = $lesson ?? null;
if ( ! $lesson || ! is_a( $lesson, 'WP_Post' ) ) {
	return;
}

global $tutor_course_id;
global $post;

$tabs_data = Lesson::get_nav_items( $lesson->ID );

$active_tab = Input::get( 'tab', 'overview' );
$valid_tabs = wp_list_pluck( $tabs_data, 'id' );
if ( ! in_array( $active_tab, $valid_tabs, true ) && ! empty( $tabs_data ) ) {
	$active_tab = $tabs_data[0]['id'];
}

$json_data                                 = array();
$json_data['post_id']                      = get_the_ID();
$json_data['best_watch_time']              = 0;
$json_data['autoload_next_course_content'] = (bool) get_tutor_option( 'autoload_next_course_content' );

$best_watch_time = tutor_utils()->get_lesson_reading_info( get_the_ID(), 0, 'video_best_watched_time' );
if ( $best_watch_time > 0 ) {
	$json_data['best_watch_time'] = $best_watch_time;
}

$video_info = tutor_utils()->get_video_info();
$source_key = is_object( $video_info ) && 'html5' !== $video_info->source ? 'source_' . $video_info->source : null;
$has_source = ( is_object( $video_info ) && $video_info->source_video_id ) || ( isset( $source_key ) ? $video_info->$source_key : null );

?>
<div class="tutor-lesson-content tutor-pt-6">
	<div 
		x-data='tutorTabs({
			tabs: <?php echo wp_json_encode( $tabs_data ); ?>,
			defaultTab: "overview",
			urlParams: {
				paramName: "tab",
			}
		})'
		class="tutor-surface-l1 tutor-border tutor-rounded-lg tutor-overflow-hidden"
	>
		<!-- Load Lesson Video -->
		<?php
		if ( $has_source ) :
			$completion_mode                              = tutor_utils()->get_option( 'course_completion_process' );
			$json_data['strict_mode']                     = ( 'strict' === $completion_mode );
			$json_data['control_video_lesson_completion'] = (bool) tutor_utils()->get_option( 'control_video_lesson_completion', false );
			$json_data['required_percentage']             = (int) tutor_utils()->get_option( 'required_percentage_to_complete_video_lesson', 80 );
			$json_data['video_duration']                  = $video_info->duration_sec ?? 0;
			$json_data['lesson_completed']                = tutor_utils()->is_completed_lesson( $lesson->ID, get_current_user_id() ) !== false;
			$json_data['is_enrolled']                     = tutor_utils()->is_enrolled( $tutor_course_id, get_current_user_id() ) !== false;
			?>
		<input type="hidden" id="tutor_video_tracking_information" value="<?php echo esc_attr( json_encode( $json_data ) ); ?>">
		<div class="tutor-lesson-video-wrapper">
			<?php echo apply_filters( 'tutor_single_lesson_video', tutor_lesson_video( false ), $video_info, $source_key ); //phpcs:ignore ?>
		</div>
		<?php endif; ?>

		<?php if ( count( $tabs_data ) > 1 ) : ?>
		<div x-ref="tablist" class="tutor-tabs-nav tutor-p-6 tutor-border-b" role="tablist" aria-orientation="horizontal">
			<template x-for="tab in tabs" :key="tab.id">
				<button
					type="button" 
					role="tab" 
					:class='getTabClass(tab)' 
					x-bind:aria-selected="isActive(tab.id)" 
					:disabled="tab.disabled ? true : false" 
					@click="selectTab(tab.id)"
					>
					<span x-data="TutorCore.icon({ name: tab.icon, width: 20, height: 20})"></span>
					<span x-text="tab.label"></span>
				</button>
			</template>

			<?php do_action( 'tutor_lesson_single_after_nav_items', $lesson->ID, $active_tab ); ?>
		</div>
		<?php endif; ?>

		<?php if ( ! empty( $tabs_data ) ) : ?>
		<div class="tutor-tabs-content tutor-p-6">
			<?php
			foreach ( $tabs_data as $key => $content ) {
				$is_pro = isset( $content['is_pro'] ) && true === $content['is_pro'];
				tutor_load_template(
					$content['template_path'],
					array(
						'is_active' => $content['id'] === $active_tab,
						'post'      => $post,
						'course_id' => $tutor_course_id,
						'lesson_id' => $lesson->ID,
					),
					$is_pro
				);
			}
			?>
		</div>
		<?php endif; ?>
	</div>

	<?php tutor_load_template( 'learning-area.components.footer' ); ?>
</div>
