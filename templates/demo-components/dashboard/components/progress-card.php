<?php
/**
 * Progress Card Component
 * Reusable progress card component for dashboard
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use TUTOR\Icon;

// Default values.
$image_url         = isset( $image_url ) ? $image_url : '';
$category          = isset( $category ) ? $category : '';
$course_title      = isset( $course_title ) ? $course_title : '';
$lessons_completed = isset( $lessons_completed ) ? $lessons_completed : 0;
$lessons_total     = isset( $lessons_total ) ? $lessons_total : 0;
$progress_percent  = isset( $progress_percent ) ? $progress_percent : 0;
$resume_url        = isset( $resume_url ) ? $resume_url : '#';
$resume_icon       = isset( $resume_icon ) ? $resume_icon : Icon::PLAY;
$resume_text       = isset( $resume_text ) ? $resume_text : esc_html__( 'Resume', 'tutor' );

?>
<div class="tutor-card tutor-progress-card">
	<?php if ( ! empty( $image_url ) ) : ?>
		<div class="tutor-progress-card-thumbnail">
			<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $course_title ); ?>" />
			<div class="tutor-progress-card-kebab-overlay">
				<div x-data="tutorPopover({ placement: 'bottom-end' })" class="tutor-progress-card-menu">
					<button 
						x-ref="trigger"
						@click="toggle()"
						class="tutor-btn tutor-btn-icon tutor-btn-ghost tutor-btn-small tutor-progress-card-menu-btn"
						aria-label="<?php echo esc_attr__( 'More options', 'tutor' ); ?>"
					>
						<?php tutor_utils()->render_svg_icon( Icon::THREE_DOTS_VERTICAL, 20, 20 ); ?>
					</button>
					<div 
						x-ref="content"
						x-show="open"
						x-cloak
						@click.outside="handleClickOutside()"
						class="tutor-popover tutor-popover-bottom"
					>
						<div class="tutor-popover-menu">
							<button class="tutor-popover-menu-item">
								<?php tutor_utils()->render_svg_icon( Icon::EDIT_2 ); ?> <?php echo esc_html__( 'Edit', 'tutor' ); ?>
							</button>
							<button class="tutor-popover-menu-item">
								<?php tutor_utils()->render_svg_icon( Icon::DELETE_2 ); ?> <?php echo esc_html__( 'Delete', 'tutor' ); ?>
							</button>
						</div>
					</div>
				</div>
			</div>
		</div>
	<?php endif; ?>
	<div class="tutor-progress-card-content">
		<?php if ( ! empty( $category ) || ! empty( $course_title ) ) : ?>
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
		<?php if ( $lessons_total > 0 || $progress_percent > 0 ) : ?>
			<div class="tutor-progress-card-progress">
				<?php if ( $lessons_total > 0 ) : ?>
					<div class="tutor-progress-card-details">
						<?php
						echo esc_html( $lessons_completed ) . ' ' . esc_html__( 'of', 'tutor' ) . ' ' . esc_html( $lessons_total ) . ' ' . esc_html__( 'lessons', 'tutor' );
						?>
						<span class="tutor-progress-card-separator">•</span>
						<?php echo esc_html( $progress_percent ); ?>% <?php echo esc_html__( 'Complete', 'tutor' ); ?>
					</div>
				<?php endif; ?>
				<?php if ( $progress_percent > 0 ) : ?>
					<div class="tutor-progress-card-bar">
						<div class="tutor-progress-bar" data-tutor-animated>
							<div class="tutor-progress-bar-fill" style="--tutor-progress-width: <?php echo esc_attr( $progress_percent ); ?>%;"></div>
						</div>
					</div>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
	<div class="tutor-progress-card-actions">
		<a href="<?php echo esc_url( $resume_url ); ?>" class="tutor-btn tutor-btn-primary tutor-btn-small">
			<?php tutor_utils()->render_svg_icon( $resume_icon, 16, 16 ); ?>
			<?php echo esc_html( $resume_text ); ?>
		</a>
		<div x-data="tutorPopover({ placement: 'bottom-end' })" class="tutor-progress-card-menu">
			<button 
				x-ref="trigger"
				@click="toggle()"
				class="tutor-btn tutor-btn-icon tutor-btn-ghost tutor-btn-small tutor-progress-card-menu-btn"
				aria-label="<?php echo esc_attr__( 'More options', 'tutor' ); ?>"
			>
				<?php tutor_utils()->render_svg_icon( Icon::THREE_DOTS_VERTICAL, 20, 20 ); ?>
			</button>
			<div 
				x-ref="content"
				x-show="open"
				x-cloak
				@click.outside="handleClickOutside()"
				class="tutor-popover tutor-popover-bottom"
			>
				<div class="tutor-popover-menu">
					<button class="tutor-popover-menu-item">
						<?php tutor_utils()->render_svg_icon( Icon::EDIT_2 ); ?> <?php echo esc_html__( 'Edit', 'tutor' ); ?>
					</button>
					<button class="tutor-popover-menu-item">
						<?php tutor_utils()->render_svg_icon( Icon::DELETE_2 ); ?> <?php echo esc_html__( 'Delete', 'tutor' ); ?>
					</button>
				</div>
			</div>
		</div>
	</div>
	<div class="tutor-progress-card-footer">
		<a href="<?php echo esc_url( $resume_url ); ?>" class="tutor-btn tutor-btn-primary tutor-btn-block">
			<?php tutor_utils()->render_svg_icon( $resume_icon, 16, 16 ); ?>
			<?php echo esc_html( $resume_text ); ?>
		</a>
	</div>
</div>

