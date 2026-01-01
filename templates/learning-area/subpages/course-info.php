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
	<div class="tutor-course-info-certificate">
		<div class="tutor-course-info-certificate-thumb">
			<svg width="224" height="224" viewBox="0 0 224 224" fill="none" xmlns="http://www.w3.org/2000/svg">
				<rect x="12" y="24.7114" width="200" height="150" rx="6" fill="#FFE138"/>
				<path d="M12.0322 30.6544C12.0322 28.8909 12.9729 27.3756 13.4433 26.8384L29.851 43.2461V156.806L13.8143 172.98C12.3847 171.917 12.0306 169.483 12.0322 168.4V30.6544Z" fill="#FFD300"/>
				<path d="M212 30.4566C212 28.6932 211.059 27.1779 210.589 26.6406L194.181 43.0484V156.608C194.181 156.608 204.954 167.478 210.3 172.869C211.73 171.806 212.002 169.286 212 168.202V30.4566Z" fill="#FFD300"/>
				<path d="M205.145 174.711C207.516 174.711 209.009 174.219 210.291 172.894L195.356 156.86L30.5028 156.034C30.5028 156.034 21.0985 165.782 13.7782 172.929C15.2075 174.359 16.5259 174.675 17.9833 174.673C78.7314 174.673 202.774 174.711 205.145 174.711Z" fill="#FFB125"/>
				<rect x="27" y="39.7114" width="170" height="120" rx="4" fill="#FFFFF5"/>
				<mask id="mask0_5427_57302" style="mask-type:alpha" maskUnits="userSpaceOnUse" x="27" y="39" width="170" height="121">
				<rect x="27" y="39.7114" width="170" height="120" rx="4" fill="#F8F4E3"/>
				</mask>
				<g mask="url(#mask0_5427_57302)">
				<circle cx="159.048" cy="167.579" r="132.048" fill="#F8F4E3"/>
				</g>
				<path d="M191.886 190.032L179.057 186.734L175.563 199.253L149.216 153.619L165.451 144.246L191.886 190.032Z" fill="#FF6168"/>
				<path d="M114.93 189.955L127.661 186.683L131.182 199.288L157.547 153.622L141.317 144.251L114.93 189.955Z" fill="#FF6168"/>
				<path d="M153.236 107.814C158.239 102.801 167.093 105.679 168.188 112.673C175.174 111.567 180.651 119.089 177.433 125.388C183.744 128.598 183.744 137.909 177.432 141.118C180.649 147.425 175.178 154.957 168.185 153.834C167.077 160.821 158.229 163.71 153.234 158.693C148.231 163.704 139.379 160.826 138.285 153.832C131.299 154.938 125.821 147.416 129.039 141.117C122.729 137.906 122.729 128.596 129.041 125.387C125.825 119.081 131.295 111.55 138.286 112.67C139.383 105.679 148.234 102.802 153.236 107.814Z" fill="#FF8F81" stroke="#FF8F81" stroke-width="2.71365" stroke-linejoin="round"/>
				<path d="M153.24 144.939C146.767 144.939 141.52 139.691 141.52 133.218C141.52 126.745 146.767 121.498 153.24 121.498C159.713 121.498 164.961 126.745 164.961 133.218C164.961 139.691 159.713 144.939 153.24 144.939Z" fill="#FF002C"/>
				<rect x="40.8906" y="139.75" width="52.1152" height="6.2583" rx="3.12915" fill="#D3D5CC"/>
				<rect x="42.4873" y="54" width="36.7646" height="6.2583" rx="3.12915" fill="#D3D5CC"/>
				<rect x="42.4873" y="68.2583" width="69.2012" height="6.2583" rx="3.12915" fill="#D3D5CC"/>
				<path d="M42.4873 130.467C53.2068 130.772 75.1639 127.359 77.2363 111.264C79.8268 91.1465 26.0275 115.684 91.4097 130.467" stroke="#D3D5CC" stroke-width="5" stroke-linecap="round"/>
			</svg>
		</div>
		<div class="tutor-course-info-certificate-content tutor-pr-13 tutor-sm-pr-none">
			<h4 class="tutor-h4 tutor-sm-text-medium"><?php esc_html_e( 'Congratulations on getting you certificate!', 'tutor' ); ?></h4>
			<div class="tutor-medium tutor-sm-text-small tutor-mt-4 tutor-sm-mt-3">
				<span class="tutor-text-subdued tutor-sm-block"><?php esc_html_e( 'You completed this course on ', 'tutor' ); ?></span>
				September 3, 2025
			</div>
			<div class="tutor-medium tutor-sm-text-small tutor-mt-4 tutor-sm-mt-1 tutor-text-secondary">
				<?php esc_html_e( 'Grade received: ', 'tutor' ); ?>
				<span class="tutor-font-semibold tutor-text-primary">85.35%</span>
			</div>
		</div>
		<div class="tutor-course-info-certificate-buttons">
			<button type="button" class="tutor-btn tutor-btn-secondary tutor-btn-x-small">
				<?php esc_html_e( 'View Certificate', 'tutor' ); ?>
			</button>
			<button type="button" class="tutor-btn tutor-btn-primary tutor-btn-x-small tutor-gap-2">
				<?php tutor_utils()->render_svg_icon( Icon::DOWNLOAD_2 ); ?>
				<?php esc_html_e( 'Download Certificate', 'tutor' ); ?>
			</button>
		</div>
	</div>

	<div class="tutor-course-thumb">
		<img src="http://localhost:10058/wp-content/uploads/2025/07/Course-Thumb-02.webp" alt="course thumb" />
	</div>

	<div class="tutor-course-intro">
		<div class="tutor-flex tutor-items-center tutor-justify-center tutor-gap-3 tutor-tiny tutor-text-secondary">
			<?php tutor_utils()->render_svg_icon( Icon::RELOAD_2 ); ?>
			August 28, 2025 Last Updated
		</div>
		<h3 class="tutor-h3 tutor-sm-text-h5 tutor-mt-3">Intimate Photography Portraits</h3>
		<div class="tutor-medium tutor-sm-text-small tutor-text-secondary tutor-mt-4">by School of Rock</div>
	</div>

	<div class="tutor-course-sticky-card tutor-mt-9">
		<div class="tutor-course-thumb">
			<img src="http://localhost:10058/wp-content/uploads/2025/07/Course-Thumb-02.webp" alt="course thumb" />
		</div>
		<div class="tutor-flex tutor-flex-column tutor-gap-2">
			<h5 class="tutor-h5 tutor-sm-text-medium">Intimate Photography Portraits</h5>
			<div class="tutor-medium tutor-sm-text-small tutor-text-secondary">by School of Rock</div>
		</div>
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
