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
						Before the picture: the idea
					</div>
					<div class="tutor-learning-nav-header-arrow" :class="{ 'is-expanded': expanded }">
						<?php tutor_utils()->render_svg_icon( Icon::CHEVRON_UP_2, 20, 20 ); ?>
					</div>
				</div>
				<div x-show="expanded" x-collapse x-cloak class="tutor-learning-nav-body">
					<div class="tutor-learning-nav-item">
						<a href="#">
							<?php tutor_utils()->render_svg_icon( Icon::COMPLETED_COLORIZE, 20, 20 ); ?>
							<div>Introduction</div>
						</a>
					</div>
					<div class="tutor-learning-nav-item active">
						<a href="#">
							<div class="tutor-learning-nav-progress">
								<div x-data="tutorStatics({ 
									value: 65,
									size: 'tiny',
									type: 'progress',
									showLabel: false,
									background: 'var(--tutor-actions-gray-empty)',
									strokeColor: 'var(--tutor-border-hover)' })">
									<div x-html="render()"></div>
								</div>
							</div>
							<div>Introduction</div>
						</a>
					</div>
					<div class="tutor-learning-nav-item">
						<a href="#">
							<?php tutor_utils()->render_svg_icon( Icon::COURSES, 20, 20 ); ?>
							<div>Journey to transparent wash </div>
						</a>
					</div>
					<div class="tutor-learning-nav-item">
						<a href="#">
							<?php tutor_utils()->render_svg_icon( Icon::QUIZ_2, 20, 20 ); ?>
							<div>Quick Quiz</div>
						</a>
					</div>
					<div class="tutor-learning-nav-item">
						<a href="#">
							<?php tutor_utils()->render_svg_icon( Icon::BOOK_2, 20, 20 ); ?>
							<div>Assignment</div>
						</a>
					</div>
				</div>
			</div>
			<div x-data="{ expanded: false }" class="tutor-learning-nav-topic">
				<div role="button" @click="expanded = !expanded" class="tutor-learning-nav-header">
					<div class="tutor-learning-nav-header-progress">
						<div class="tutor-learning-nav-header-progress-inner"></div>
					</div>
					<div class="tutor-learning-nav-header-title">
						Before the picture: the idea
					</div>
					<div class="tutor-learning-nav-header-arrow" :class="{ 'is-expanded': expanded }">
						<?php tutor_utils()->render_svg_icon( Icon::CHEVRON_UP_2, 20, 20 ); ?>
					</div>
				</div>
				<div x-show="expanded" x-collapse x-cloak class="tutor-learning-nav-body">
					<div class="tutor-learning-nav-item">
						<a href="#">
							<?php tutor_utils()->render_svg_icon( Icon::COURSES, 20, 20 ); ?>
							<div>Journey to transparent wash </div>
						</a>
					</div>
					<div class="tutor-learning-nav-item">
						<a href="#">
							<?php tutor_utils()->render_svg_icon( Icon::QUIZ_2, 20, 20 ); ?>
							<div>Quick Quiz</div>
						</a>
					</div>
					<div class="tutor-learning-nav-item">
						<a href="#">
							<?php tutor_utils()->render_svg_icon( Icon::BOOK_2, 20, 20 ); ?>
							<div>Assignment</div>
						</a>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="tutor-learning-sidebar-pages">
		<div class="tutor-learning-pages">
			<a href="#" class="tutor-learning-pages-item">
				<?php tutor_utils()->render_svg_icon( Icon::RESOURCES, 20, 20 ); ?>
				<?php esc_html_e( 'Resources', 'tutor' ); ?>
			</a>
			<a href="#" class="tutor-learning-pages-item">
				<?php tutor_utils()->render_svg_icon( Icon::QA, 20, 20 ); ?>
				<?php esc_html_e( 'Q&A', 'tutor' ); ?>
			</a>
			<a href="#" class="tutor-learning-pages-item">
				<?php tutor_utils()->render_svg_icon( Icon::INFO, 20, 20 ); ?>
				<?php esc_html_e( 'Course Info', 'tutor' ); ?>
			</a>
			<a href="#" class="tutor-learning-pages-item active">
				<?php tutor_utils()->render_svg_icon( Icon::VIDEO_CAMERA_2, 20, 20 ); ?>
				<?php esc_html_e( 'Webinar', 'tutor' ); ?>
			</a>
			<a href="#" class="tutor-learning-pages-item">
				<?php tutor_utils()->render_svg_icon( Icon::CERTIFICATE_2, 20, 20 ); ?>
				<?php esc_html_e( 'Certificate', 'tutor' ); ?>
			</a>
		</div>

		<button 
			class="tutor-btn tutor-btn-outline tutor-btn-small tutor-btn-icon tutor-expand-btn"
			@click="isFullScreen = !isFullScreen"
		>
			<?php tutor_utils()->render_svg_icon( Icon::EXPAND ); ?>
		</button>
	</div>
</div>
