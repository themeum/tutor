<?php
/**
 * Tutor learning area sidebar.
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use TUTOR\Icon;
use TUTOR\Input;
use TUTOR\Template;

global $tutor_course,
$tutor_course_list_url,
$tutor_current_content_id,
$tutor_is_enrolled,
$tutor_current_post;

$is_preview  = get_post_meta( $tutor_current_post->ID, '_is_preview', true );
$current_url = trailingslashit( $tutor_course_list_url ) . $tutor_course->post_name;

$menu_items  = Template::make_learning_area_sub_page_nav_items( $current_url );
$active_menu = Input::get( 'subpage', '' );

?>
<div class="tutor-learning-sidebar" :class="{ 'is-open': sidebarOpen }" @click.outside="sidebarOpen = false">
	<div class="tutor-learning-sidebar-curriculum">
		<div class="tutor-learning-progress">
			<div class="tutor-learning-progress-content">
				<div class="tutor-learning-progress-text"><span>7%</span> Completed</div>
				<button class="tutor-learning-progress-reset">
					<?php tutor_utils()->render_svg_icon( Icon::RELOAD_2, 20, 20 ); ?>
				</button>
			</div>
			<div class="tutor-progress-bar" data-tutor-animated="">
				<div class="tutor-progress-bar-fill" style="--tutor-progress-width: 75%;"></div>
			</div>
		</div>
		<div class="tutor-learning-nav">
			<?php
			$topics = tutor_utils()->get_topics( $tutor_course->ID );
			if ( $topics->have_posts() ) {
				while ( $topics->have_posts() ) {
					$topics->the_post();
					$topic_id        = get_the_ID();
					$topic_summery   = get_the_content();
					$total_contents  = tutor_utils()->count_completed_contents_by_topic( $topic_id );
					$course_contents = tutor_utils()->get_course_contents_by_topic( get_the_ID(), -1 );
					$is_topic_active = ! empty(
						array_filter(
							$course_contents->posts,
							function ( $content ) use ( $tutor_current_post ) {
								return $content->ID === $tutor_current_post->ID;
							}
						)
					);
					?>

					<div x-data="{ expanded: true }" class="tutor-learning-nav-topic active">
						<div role="button" @click="expanded = !expanded" class="tutor-learning-nav-header">
							<div class="tutor-learning-nav-header-progress">
								<div x-data="tutorStatics({ 
									value: 65,
									size: 'tiny',
									type: 'progress',
									showLabel: false,
									background: 'var(--tutor-actions-gray-empty)',
									strokeColor: 'var(--tutor-border-hover)' })">
									<div x-html="render()"></div>
								</div>
								<!-- <div class="tutor-learning-nav-header-progress-inner"></div> -->
							</div>
							<div class="tutor-learning-nav-header-title">
								<?php the_title(); ?>
							</div>
							<div class="tutor-learning-nav-header-arrow" :class="{ 'is-expanded': expanded }">
								<?php tutor_utils()->render_svg_icon( Icon::CHEVRON_UP_2, 20, 20 ); ?>
							</div>
						</div>
						<div x-show="expanded" x-collapse x-cloak class="tutor-learning-nav-body">
							<?php
							// Backward compatible hook.
							do_action( 'tutor/lesson_list/before/topic', $topic_id );
							// Loop through lesson, quiz, assignment, zoom lesson.
							while ( $course_contents->have_posts() ) {
								$course_contents->the_post();

								$topic_item = get_post();

								$can_access = ! $is_preview || $tutor_is_enrolled || get_post_meta( $post->ID, '_is_preview', true ) || $is_public_course || $is_instructor_of_this_course;
								$can_access = apply_filters( 'tutor_course/single/content/show_permalink', $can_access, get_the_ID() );
								$can_access = null === $can_access ? true : $can_access;

								// Rendered the nav item using hook from their respective files.
								do_action( 'tutor_learning_area_nav_item_' . $topic_item->post_type, $topic_item, $can_access );
							}
							$course_contents->reset_postdata();
							do_action( 'tutor/lesson_list/after/topic', $topic_id ); // Backward compatible hook.
							?>
						</div>
					</div>
					<?php
				}
				wp_reset_postdata();
			}
			?>
		</div>
	</div>
	<div class="tutor-learning-sidebar-pages">
		<div class="tutor-learning-pages">
			<?php
			foreach ( $menu_items as $key => $item ) {
				$active_class = ( $key === $active_menu ) ? 'active' : '';
				?>
				<a href="<?php echo esc_url( $item['url'] ); ?>" class="tutor-learning-pages-item <?php echo esc_attr( $active_class ); ?>">
					<?php tutor_utils()->render_svg_icon( $item['icon'], 20, 20 ); ?>
					<?php echo esc_html( $item['title'] ); ?>
				</a>
				<?php
			}
			?>
		</div>
	</div>
</div>
