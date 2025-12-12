<?php
/**
 * Course Card Template
 *
 * @package Tutor\Templates
 * @subpackage Dashboard\Enrolled_Courses
 * @author Themeum
 */

use TUTOR\Icon;

$course_permalink = get_the_permalink();
$course_title     = get_the_title();

$tutor_course_img = get_tutor_course_thumbnail_src();

$course_id       = get_the_ID();
$course_progress = tutor_utils()->get_course_completed_percent( $course_id, 0, true );

$course_categories = get_the_terms( $course_id, \Tutor\Models\CourseModel::COURSE_CATEGORY );
$category_names    = is_array( $course_categories ) && ! is_wp_error( $course_categories ) ? wp_list_pluck( $course_categories, 'name' ) : array();
$category          = implode( ', ', $category_names );

?>

<a href="<?php echo esc_html( $course_permalink ); ?>">
	<div class="tutor-card tutor-progress-card">
		<?php if ( ! empty( $tutor_course_img ) ) : ?>
			<div class="tutor-progress-card-thumbnail">
				<img src="<?php echo esc_url( $tutor_course_img ); ?>" alt="<?php echo esc_attr( $course_title ); ?>" />
				<div class="tutor-progress-card-kebab-overlay">
					<div x-data="tutorPopover({ placement: 'bottom-end' })" class="tutor-progress-card-menu">
						<button x-ref="trigger" @click="toggle()"
							class="tutor-btn tutor-btn-icon tutor-btn-ghost tutor-btn-small tutor-progress-card-menu-btn"
							aria-label="<?php echo esc_attr__( 'More options', 'tutor' ); ?>">
							<?php tutor_utils()->render_svg_icon( Icon::THREE_DOTS_VERTICAL, 20, 20 ); ?>
						</button>
						<div x-ref="content" x-show="open" x-cloak @click.outside="handleClickOutside()"
							class="tutor-popover tutor-popover-bottom">
							<div class="tutor-popover-menu">
								<button class="tutor-popover-menu-item">
									<?php tutor_utils()->render_svg_icon( Icon::EDIT_2 ); ?>
									<?php echo esc_html__( 'Edit', 'tutor' ); ?>
								</button>
								<button class="tutor-popover-menu-item">
									<?php tutor_utils()->render_svg_icon( Icon::DELETE_2 ); ?>
									<?php echo esc_html__( 'Delete', 'tutor' ); ?>
								</button>
							</div>
						</div>
					</div>
				</div>
			</div>
		<?php endif; ?>
		<div class="tutor-progress-card-content">
			<?php if ( ! empty( $course_title ) ) : ?>
				<div class="tutor-progress-card-header">
					<?php if ( ! empty( $category ) ) : ?>
						<div class="tutor-progress-card-category">
							<?php echo esc_html( $category ); ?>
						</div>
					<?php endif; ?>
					<?php if ( ! empty( $course_title ) ) : ?>
						<h3 class="tutor-progress-card-title">
							<?php echo esc_html( $course_title ); ?>
						</h3>
					<?php endif; ?>
				</div>
			<?php endif; ?>
			<?php if ( $course_progress['completed_count'] > 0 || $course_progress['total_count'] > 0 ) : ?>
				<div class="tutor-progress-card-progress">
					<?php if ( $course_progress['total_count'] > 0 ) : ?>
						<div class="tutor-progress-card-details">
							<?php
							echo esc_html( $course_progress['completed_percent'] ) . ' ' . esc_html__( 'of', 'tutor' ) . ' ' . esc_html( $course_progress['total_count'] ) . ' ' . esc_html__( 'lessons', 'tutor' );
							?>
							<span class="tutor-progress-card-separator">•</span>
							<?php echo esc_html( $course_progress['completed_percent'] ); ?>%
							<?php echo esc_html__( 'Complete', 'tutor' ); ?>
						</div>
					<?php endif; ?>
					<?php if ( $course_progress['completed_percent'] >= 0 ) : ?>
						<div class="tutor-progress-card-bar">
							<div class="tutor-progress-bar" data-tutor-animated>
								<div class="tutor-progress-bar-fill"
									style="--tutor-progress-width: <?php echo esc_attr( $course_progress['completed_percent'] ); ?>%;">
								</div>
							</div>
						</div>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</a>