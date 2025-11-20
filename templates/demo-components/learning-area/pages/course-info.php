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
<div class="tutor-course-info tutor-pt-7">
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
	<div class="tutor-course-info-table"></div>
</div>
