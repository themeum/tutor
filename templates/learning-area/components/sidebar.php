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
use Tutor\Components\SvgIcon;
use Tutor\Components\Constants\Color;
use TUTOR\Course;
use TUTOR\Template;

global $tutor_course,
$current_user_id,
$tutor_course_id,
$tutor_course_list_url,
$tutor_current_content_id,
$tutor_is_enrolled,
$tutor_is_public_course,
$tutor_is_course_instructor,
$tutor_current_post,
$tutor_course_progress,
$tutor_is_course_completed,
$tutor_can_complete_course,
$tutor_can_retake_course,
$course_complete_modal_id,
$course_retake_modal_id;

$is_preview  = get_post_meta( $tutor_current_post->ID, '_is_preview', true );
$current_url = trailingslashit( $tutor_course_list_url ) . $tutor_course->post_name;

$menu_items  = Template::make_learning_area_sub_page_nav_items( $current_url );
$active_menu = Template::learning_area_active_subpage();

$course_reset_progress = tutor_utils()->get_option( 'course_reset_progress', false );
$reset_modal_id        = 'tutor-course-reset-progress-modal';

?>
<div 
	class="tutor-learning-sidebar" 
	x-data="tutorLearningSidebar({ courseId: <?php echo (int) $tutor_course->ID; ?>, resetModalId: '<?php echo esc_attr( $reset_modal_id ); ?>' })"
	x-trap.noscroll="sidebarOpen"
	:class="{ 'is-open': sidebarOpen }" 
	@click.outside="closeSidebar()"
	@toggle-sidebar.window="toggleSidebar()"
>
	<div class="tutor-hidden tutor-lg-flex tutor-items-center tutor-px-4">
		<h5 class="tutor-learning-header-title tutor-my-none">
			<?php echo esc_html( $tutor_course->post_title ); ?>
		</h5>
		<div class="tutor-learning-header-toggle-mobile">
			<?php
			Button::make()
				->label( __( 'Close course sidebar', 'tutor' ) )
				->variant( Variant::GHOST )
				->size( Size::SMALL )
				->icon( Icon::CROSS_2, 'left', 20 )
				->icon_only()
				->attr( '@click.stop', '$dispatch(\'toggle-sidebar\')' )
				->render();
			?>
		</div>
	</div>
	<div class="tutor-learning-sidebar-curriculum">
		<div class="tutor-learning-progress">
			<div class="tutor-learning-progress-content">
				<div class="tutor-learning-progress-text tutor-py-2">
					<?php
					// translators: %s: course completed percentage.
					printf( esc_html__( '%s Completed', 'tutor' ), '<span>' . esc_html( $tutor_course_progress ) . '%</span>' );
					?>
				</div>
				<div class="tutor-flex">
					<?php
					if ( $course_reset_progress && ! $tutor_is_course_completed ) {
						Button::make()
							->label( __( 'Reset Progress', 'tutor' ) )
							->variant( Variant::GHOST )
							->size( Size::X_SMALL )
							->icon( Icon::RELOAD_2, 'left', 16, Color::SECONDARY )
							->icon_only()
							->attr( '@click', 'confirmReset()' )
							->render();

						ConfirmationModal::make()
							->id( $reset_modal_id )
							->title( __( 'Reset Course Progress?', 'tutor' ) )
							->message( __( 'This will remove your completed lessons, quizzes, and assignments. You will start the course from the beginning.', 'tutor' ) )
							->cancel_text( __( 'No, Keep My Progress', 'tutor' ) )
							->confirm_text( __( 'Yes, Reset Everything', 'tutor' ) )
							->icon( tutor_utils()->get_themed_svg( 'images/illustrations/reset-course.svg' ), 80, 80, ConfirmationModal::ICON_TYPE_HTML )
							->confirm_handler( 'resetProgress()' )
							->mutation_state( 'resetProgressMutation' )
							->render();
					}
					?>
				</div>
			</div>
			<?php
				Progress::make()
					->value( $tutor_course_progress )
					->type( 'bar' )
					->render();
			?>
		</div>
		<div class="tutor-learning-nav" role="navigation">
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

					<div 
						x-data="{ expanded: <?php echo esc_attr( ( $is_topic_active || ( 0 === $topics->current_post && ! empty( $active_menu ) ) ) ? 'true' : 'false' ); ?> }" 
						class="tutor-learning-nav-topic <?php echo esc_attr( $is_topic_active ? 'active' : '' ); ?>"
						:class="{ 'expanded': expanded }"
					>
						<div 
							role="button" 
							tabindex="0" 
							@click="expanded = !expanded" 
							@keydown.enter.prevent="expanded = !expanded" 
							@keydown.space.prevent="expanded = !expanded" 
							:aria-expanded="expanded ? 'true' : 'false'"
							class="tutor-learning-nav-header"
						>
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
										->trigger_element( SvgIcon::make()->name( Icon::INFO_OCTAGON )->size( 16 )->color( Color::SECONDARY )->get() )
										->size( Size::LARGE )
										->arrow( Tooltip::ARROW_CENTER )
										->render();
									?>
								</div>
								<?php endif; ?>
								<div class="tutor-learning-nav-header-arrow" :class="{ 'is-expanded': expanded }">
									<?php SvgIcon::make()->name( Icon::CHEVRON_DOWN_2 )->size( 20 )->render(); ?>
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
	<div class="tutor-learning-sidebar-pages <?php echo ! empty( $active_menu ) ? 'expanded' : ''; ?>">
		<?php if ( ! empty( $active_menu ) ) : ?>
			<div class="tutor-sidebar-resizer" @mousedown="startResizing($event)"></div>
			<div class="tutor-sidebar-restore-dropdown">
				<button 
					:class="{ 'is-minimized': pagesHeight && pagesHeight <= 40 }" 
					@click="togglePagesHeight()"
					:aria-expanded="pagesHeight > 40 ? 'true' : 'false'"
					:aria-label="pagesHeight <= 40 ? '<?php echo esc_js( __( 'Expand panel', 'tutor' ) ); ?>' : '<?php echo esc_js( __( 'Collapse panel', 'tutor' ) ); ?>'"
				>
					<?php SvgIcon::make()->name( Icon::CHEVRON_DOWN_2 )->render(); ?>
				</button>
			</div>
		<?php endif; ?>
		<div class="tutor-learning-pages" x-ref="pagesList" :class="{ 'is-resizing': resizing }" <?php echo ! empty( $active_menu ) ? ':style="pagesHeight !== null ? { height: pagesHeight + \'px\' } : {}"' : ''; ?>>
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
					<?php if ( isset( $item['locked'] ) ) : ?>
						<?php
							SvgIcon::make()
							->name( Icon::LOCK_CIRCLE )
							->size( 12 )
							->attr( 'class', 'tutor-lock-icon' )
							->render();
						?>
					<?php endif; ?>
					<?php SvgIcon::make()->name( $item['icon'] )->size( 20 )->render(); ?>
					<?php echo esc_html( $item['title'] ); ?>
				</a>
				<?php
			}
			$menu_html = ob_get_clean();
			?>

			<?php if ( empty( $active_menu ) ) : ?>
			<div>
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
							->icon( Icon::MORE, 'left', 20 )
							->attr( 'class', 'tutor-learning-pages-item' )
							->attr( 'x-ref', 'trigger' )
							->attr( '@click', 'toggle()' )
							->get()
					)
					->render();
				?>
			</div>
			<?php else : ?>
			<div class="tutor-learning-pages-nav">
				<?php echo $menu_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>
			<?php endif; ?>
		</div>
	</div>
	<?php if ( $tutor_is_enrolled ) : ?>
	<div class="tutor-hidden tutor-md-flex tutor-flex-column tutor-gap-2">
		<?php
		$incomplete_msg = Course::get_course_completion_restrict_msg( $tutor_course_id, $current_user_id );
		if ( $tutor_can_complete_course || $incomplete_msg ) {
			Course::render_course_complete_btn( $course_complete_modal_id, $tutor_course_id, $tutor_course_progress, Size::MEDIUM, $incomplete_msg ?? '', true );
		}
		if ( $tutor_can_retake_course ) {
			Course::render_course_retake_btn( $course_retake_modal_id, Size::MEDIUM, true );
		}
		?>
	</div>
	<?php endif; ?>
</div>
