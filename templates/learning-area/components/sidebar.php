<?php
/**
 * Tutor learning area sidebar.
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use Tutor\Components\Button;
use Tutor\Components\Constants\Size;
use Tutor\Components\Constants\Variant;
use Tutor\Components\ConfirmationModal;
use Tutor\Components\Popover;
use Tutor\Components\Progress;
use Tutor\Components\Tooltip;
use TUTOR\Icon;
use TUTOR\Input;
use TUTOR\Template;

global $tutor_course,
$current_user_id,
$tutor_course_id,
$tutor_course_list_url,
$tutor_current_content_id,
$tutor_is_enrolled,
$tutor_is_public_course,
$tutor_is_course_instructor,
$tutor_current_post;

$is_preview       = get_post_meta( $tutor_current_post->ID, '_is_preview', true );
$current_url      = trailingslashit( $tutor_course_list_url ) . $tutor_course->post_name;
$course_completed = tutor_utils()->get_course_completed_percent( $tutor_course_id, $current_user_id );

$menu_items     = Template::make_learning_area_sub_page_nav_items( $current_url );
$active_menu    = Input::get( 'subpage', '' );
$reset_modal_id = 'tutor-course-reset-progress-modal';

?>
<div 
	class="tutor-learning-sidebar" 
	x-data="tutorLearningSidebar({ isCollapsed: <?php echo empty( $active_menu ) ? 'true' : 'false'; ?>, courseId: <?php echo (int) $tutor_course->ID; ?>, resetModalId: '<?php echo esc_attr( $reset_modal_id ); ?>' })"
	:class="{ 'is-open': sidebarOpen }" 
	@click.outside="sidebarOpen = false"
>
	<div class="tutor-hidden tutor-lg-flex tutor-items-center tutor-px-4">
		<h5 class="tutor-learning-header-title">
			<?php echo esc_html( $tutor_course->post_title ); ?>
		</h5>
		<button class="tutor-learning-header-toggle-mobile" @click.stop="sidebarOpen = !sidebarOpen">
			<?php tutor_utils()->render_svg_icon( Icon::CROSS_2, 20, 20 ); ?>
		</button>
	</div>
	<div class="tutor-learning-sidebar-curriculum">
		<div class="tutor-learning-progress">
			<div class="tutor-learning-progress-content">
				<div class="tutor-learning-progress-text">
					<?php
					// translators: %s: course completed percentage.
					echo sprintf( esc_html__( '%s Completed', 'tutor' ), '<span>' . esc_html( $course_completed ) . '%</span>' );
					?>
				</div>
				<div>
					<?php
					Button::make()
						->variant( Variant::GHOST )
						->size( Size::X_SMALL )
						->icon( Icon::RELOAD_2, 'left', 16, 16, array( 'class' => 'tutor-icon-secondary' ) )
						->icon_only()
						->attr( '@click', 'confirmReset()' )
						->render();

					ConfirmationModal::make()
						->id( $reset_modal_id )
						->title( __( 'Are Your Sure?', 'tutor' ) )
						->message( __( 'This will permanently erase your quiz scores, completed lessons, and certificates for this course.', 'tutor' ) )
						->cancel_text( __( 'No, Keep My Progress', 'tutor' ) )
						->confirm_text( __( 'Yes, Reset Everything', 'tutor' ) )
						->icon( Icon::WARNING_COLORIZED )
						->confirm_handler( 'resetProgress()' )
						->mutation_state( 'resetProgressMutation' )
						->render();
					?>
				</div>
			</div>
			<div class="tutor-progress-bar" data-tutor-animated="">
				<div class="tutor-progress-bar-fill" style="--tutor-progress-width: <?php echo esc_attr( $course_completed ); ?>%;"></div>
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

					<div x-data="{ expanded: <?php echo esc_attr( $is_topic_active ? 'true' : 'false' ); ?> }" class="tutor-learning-nav-topic <?php echo esc_attr( $is_topic_active ? 'active' : '' ); ?>">
						<div role="button" @click="expanded = !expanded" class="tutor-learning-nav-header">
							<div class="tutor-learning-nav-header-progress">
								<?php
								Progress::make()
									->value( $total_contents['percentage'] ?? 0 )
									->type( 'circle' )
									->size( Size::X_SMALL )
									->show_label( false )
									->background( 'var(--tutor-actions-gray-empty)' )
									->stroke_color( 'var(--tutor-border-hover)' )
									->render();
								?>
							</div>
							<div class="tutor-learning-nav-header-title" title="<?php the_title(); ?>">
								<?php the_title(); ?>
							</div>
							<div class="tutor-learning-nav-header-actions">
								<?php if ( ! empty( $topic_summery ) ) : ?>
								<div class="tutor-learning-nav-header-summary">
									<?php
									Tooltip::make()
										->content( $topic_summery )
										->trigger_element( tutor_utils()->get_svg_icon( Icon::INFO_OCTAGON, 16, 16, array( 'class' => 'tutor-icon-secondary' ) ) )
										->size( Size::LARGE )
										->arrow( Tooltip::ARROW_CENTER )
										->render();
									?>
								</div>
								<?php endif; ?>
								<div class="tutor-learning-nav-header-arrow" :class="{ 'is-expanded': expanded }">
									<?php tutor_utils()->render_svg_icon( Icon::CHEVRON_UP_2, 20, 20 ); ?>
								</div>
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

								$can_access = ! $is_preview || $tutor_is_enrolled || get_post_meta( $topic_item->ID, '_is_preview', true ) || $tutor_is_public_course || $tutor_is_course_instructor;
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
	<div class="tutor-learning-sidebar-pages" :class="{ 'expanded': !collapsed }">
		<div class="tutor-sidebar-resizer" x-show="!collapsed" @mousedown="startResizing($event)" x-cloak></div>
		<div class="tutor-sidebar-restore-dropdown" x-show="!collapsed" x-cloak>
			<button :class="{ 'is-minimized': pagesHeight <= 40 }" @click="togglePagesHeight()">
				<?php tutor_utils()->render_svg_icon( Icon::CHEVRON_DOWN_2 ); ?>
			</button>
		</div>
		<div class="tutor-learning-pages" x-ref="pagesList" :class="{ 'is-resizing': resizing }" :style="!collapsed && { height: pagesHeight + 'px' }">
			<?php
			ob_start();
			foreach ( $menu_items as $key => $item ) {
				$active_class = ( $key === $active_menu ) ? 'active' : '';
				?>
				<a href="<?php echo esc_url( $item['url'] ); ?>" 
					class="tutor-learning-pages-item <?php echo esc_attr( $active_class ); ?>"
					<?php if ( isset( $item['onclick'] ) ) : ?>
						onclick="<?php echo esc_attr( $item['onclick'] ); ?>"
					<?php endif; ?>
				>
					<?php tutor_utils()->render_svg_icon( $item['icon'], 20, 20 ); ?>
					<?php echo esc_html( $item['title'] ); ?>
				</a>
				<?php
			}
			$menu_html = ob_get_clean();
			?>

			<div x-show="collapsed" x-cloak>
				<?php
				$allowed_html = wp_kses_allowed_html( 'post' );
				if ( isset( $allowed_html['a'] ) ) {
					$allowed_html['a']['onclick'] = true;
				}

				Popover::make()
					->body( $menu_html, $allowed_html )
					->trigger(
						Button::make()
							->variant( Variant::GHOST )
							->label( __( 'More', 'tutor' ) )
							->icon( Icon::THREE_DOTS, 'left', 20, 20 )
							->attr( 'class', 'tutor-learning-pages-item' )
							->attr( 'x-ref', 'trigger' )
							->attr( '@click', 'toggle()' )
							->get()
					)
					->render();
				?>
			</div>

			<div x-show="!collapsed" x-cloak>
				<?php echo $menu_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>
		</div>
	</div>
</div>
