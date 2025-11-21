<?php
/**
 * Tutor learning area course info.
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use TUTOR\Icon;

?>
<div class="tutor-course-info tutor-pt-7 tutor-pb-12">
	<div class="tutor-course-info-certificate"></div>
	<div class="tutor-course-thumb">
		<img src="http://localhost:10058/wp-content/uploads/2025/07/Course-Thumb-02.webp" alt="course thumb" />
	</div>
	<div class="tutor-course-intro">
		<div class="tutor-course-intro-date">
			<?php tutor_utils()->render_svg_icon( Icon::RELOAD_2 ); ?>
			August 28, 2025 Last Updated
		</div>
		<h3 class="tutor-course-intro-title">Intimate Photography Portraits</h3>
		<div class="tutor-course-intro-author">by School of Rock</div>
	</div>
	<div class="tutor-course-description">
		<div x-data="{ expanded: true }" class="tutor-course-description-item">
			<div role="button" @click="expanded = !expanded" class="tutor-course-description-header">
				<div class="tutor-course-description-header-title">
					About this Course
				</div>
				<div class="tutor-course-description-header-icon" :class="{ 'is-expanded': expanded }">
					<?php tutor_utils()->render_svg_icon( Icon::CHEVRON_DOWN_2, 24, 24 ); ?>
				</div>
			</div>
			<div x-show="expanded" x-collapse x-cloak class="tutor-course-description-body">
				Lorem ipsum dolor sit amet consectetur adipisicing elit. Quisquam nobis asperiores, dicta delectus rem omnis voluptas eaque magni. Totam sed delectus corporis praesentium modi voluptatem error saepe quaerat illum deleniti?
			</div>
		</div>
		<div x-data="{ expanded: false }" class="tutor-course-description-item">
			<div role="button" @click="expanded = !expanded" class="tutor-course-description-header">
				<div class="tutor-course-description-header-title">
					What you'll learn
				</div>
				<div class="tutor-course-description-header-icon" :class="{ 'is-expanded': expanded }">
					<?php tutor_utils()->render_svg_icon( Icon::CHEVRON_DOWN_2, 24, 24 ); ?>
				</div>
			</div>
			<div x-show="expanded" x-collapse x-cloak class="tutor-course-description-body">
				<div class="tutor-course-description-list">
					<div class="tutor-course-description-list-item">
						<?php tutor_utils()->render_svg_icon( Icon::CHECK_2 ); ?>
						<div class="tutor-course-description-list-content">
							Master the basics of Figma, from interface navigation to essential tools.
						</div>
					</div>
					<div class="tutor-course-description-list-item">
						<?php tutor_utils()->render_svg_icon( Icon::CHECK_2 ); ?>
						<div class="tutor-course-description-list-content">
							Create stunning, functional designs with advanced Figma features.
						</div>
					</div>
					<div class="tutor-course-description-list-item">
						<?php tutor_utils()->render_svg_icon( Icon::CHECK_2 ); ?>
						<div class="tutor-course-description-list-content">
							Develop practical design projects that enhance your portfolio.
						</div>
					</div>
					<div class="tutor-course-description-list-item">
						<?php tutor_utils()->render_svg_icon( Icon::CHECK_2 ); ?>
						<div class="tutor-course-description-list-content">
							Continuously update skills with weekly new projects, staying ahead in design trends.
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="tutor-course-info-table tutor-table-wrapper tutor-table-column-borders tutor-mt-6">
		<table class="tutor-table tutor-surface-l1">
			<tr>
				<td>
					<div class="tutor-flex tutor-items-center tutor-gap-4 tutor-sm-gap-3">
						<?php tutor_utils()->render_svg_icon( Icon::INSTRUCTOR, 20, 20 ); ?>
						<?php esc_html_e( 'Instructors', 'tutor' ); ?>
					</div>
				</td>
				<td class="tutor-flex tutor-items-center tutor-sm-items-start tutor-gap-5 tutor-sm-gap-4">
					<div class="tutor-avatar tutor-avatar-56 tutor-avatar-sm-40">
						<img src="https://i.pravatar.cc/150?u=a042581f4e29026704d" alt="User Avatar" class="tutor-avatar-image">
					</div>
					<div class="tutor-flex tutor-flex-column tutor-gap-2 tutor-sm-gap-1">
						<div class="tutor-medium tutor-font-medium tutor-sm-text-small">Bilbo Baggins</div>
						<div class="tutor-tiny tutor-text-secondary">
							Dr. Rosa I. Arriaga, Senior Research Scientist <br />School of Interactive Computing
						</div>
					</div>
				</td>
			</tr>
			<tr>
				<td>
					<div class="tutor-flex tutor-items-center tutor-gap-4 tutor-sm-gap-3">
						<?php tutor_utils()->render_svg_icon( Icon::PASSED, 20, 20 ); ?>
						<?php esc_html_e( 'Total Enrolled', 'tutor' ); ?>
					</div>
				</td>
				<td>1200 Students</td>
			</tr>
			<tr>
				<td>
					<div class="tutor-flex tutor-items-center tutor-gap-4 tutor-sm-gap-3">
						<?php tutor_utils()->render_svg_icon( Icon::LEVEL, 20, 20 ); ?>
						<?php esc_html_e( 'Level', 'tutor' ); ?>
					</div>
				</td>
				<td>Beginner</td>
			</tr>
			<tr>
				<td>
					<div class="tutor-flex tutor-items-center tutor-gap-4 tutor-sm-gap-3">
						<?php tutor_utils()->render_svg_icon( Icon::TIME, 20, 20 ); ?>
						<?php esc_html_e( 'Duration', 'tutor' ); ?>
					</div>
				</td>
				<td>8+ Hours</td>
			</tr>
			<tr>
				<td>
					<div class="tutor-flex tutor-items-center tutor-gap-4 tutor-sm-gap-3">
						<?php tutor_utils()->render_svg_icon( Icon::RATINGS, 20, 20 ); ?>
						<?php esc_html_e( 'Student Ratings', 'tutor' ); ?>
					</div>
				</td>
				<td class="tutor-flex tutor-items-center tutor-sm-items-start tutor-gap-5 tutor-sm-gap-4">
					<div class="tutor-flex tutor-items-center tutor-gap-2" data-rating-value="4.5">
						<i class="tutor-icon-star-bold tutor-icon-exception4 tutor-icon-exception4" data-rating-value="1"></i>
						<i class="tutor-icon-star-bold tutor-icon-exception4 tutor-icon-exception4" data-rating-value="2"></i>
						<i class="tutor-icon-star-bold tutor-icon-exception4 tutor-icon-exception4" data-rating-value="3"></i>
						<i class="tutor-icon-star-bold tutor-icon-exception4 tutor-icon-exception4" data-rating-value="4"></i>
						<i class="tutor-icon-star-half-bold tutor-icon-exception4 tutor-icon-exception4" data-rating-value="5"></i>
					</div>
					Average Rating 4.6
				</td>
			</tr>
			<tr>
				<td>
					<div class="tutor-flex tutor-items-center tutor-gap-4 tutor-sm-gap-3">
						<?php tutor_utils()->render_svg_icon( Icon::RESOURCES, 20, 20 ); ?>
						<?php esc_html_e( 'Resources', 'tutor' ); ?>
					</div>
				</td>
				<td>3 Files</td>
			</tr>
			<tr>
				<td>
					<div class="tutor-flex tutor-items-center tutor-gap-4 tutor-sm-gap-3">
						<?php tutor_utils()->render_svg_icon( Icon::MATERIAL, 20, 20 ); ?>
						<?php esc_html_e( 'Materials', 'tutor' ); ?>
					</div>
				</td>
				<td>
					<ul>
						<li>Introduction to Programming</li>
						<li>Advanced Data Structures</li>
						<li>Web Development Basics</li>
						<li>Machine Learning Fundamentals</li>
					</ul>
				</td>
			</tr>
			<tr>
				<td>
					<div class="tutor-flex tutor-items-center tutor-gap-4 tutor-sm-gap-3">
						<?php tutor_utils()->render_svg_icon( Icon::CERTIFICATE_2, 20, 20 ); ?>
						<?php esc_html_e( 'Certificate', 'tutor' ); ?>
					</div>
				</td>
				<td>You will receive certificate on completion</td>
			</tr>
		</table>
	</div>
</div>
