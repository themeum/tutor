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

global $tutor_course, $tutor_course_list_url;

$current_url = trailingslashit( $tutor_course_list_url ) . $tutor_course->post_name;

$menu_items = array(
	'resources'   => array(
		'title' => esc_html__( 'Resources', 'tutor' ),
		'icon'  => Icon::RESOURCES,
		'url'   => esc_url( add_query_arg( 'subpage', 'resources', $current_url ) ),
	),
	'qna'         => array(
		'title' => esc_html__( 'Q&A', 'tutor' ),
		'icon'  => Icon::QA,
		'url'   => esc_url( add_query_arg( 'subpage', 'qna', $current_url ) ),
	),
	'course-info' => array(
		'title' => esc_html__( 'Course Info', 'tutor' ),
		'icon'  => Icon::INFO_OCTAGON,
		'url'   => esc_url( add_query_arg( 'subpage', 'course-info', $current_url ) ),
	),
	'webinar'     => array(
		'title' => esc_html__( 'Webinar', 'tutor' ),
		'icon'  => Icon::VIDEO_CAMERA_2,
		'url'   => esc_url( add_query_arg( 'subpage', 'webinar', $current_url ) ),
	),
	'certificate' => array(
		'title' => esc_html__( 'Certificate', 'tutor' ),
		'icon'  => Icon::CERTIFICATE_2,
		'url'   => esc_url( add_query_arg( 'subpage', 'certificate', $current_url ) ),
	),
);

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
