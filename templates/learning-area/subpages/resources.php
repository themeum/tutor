<?php
/**
 * Tutor learning area resources.
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use TUTOR\Icon;

$tabs_data = array(
	array(
		'id'    => 'downloads',
		'label' => 'Downloads',
		'icon'  => Icon::DOWNLOAD_2,
	),
	array(
		'id'    => 'external_links',
		'label' => 'External Links',
		'icon'  => Icon::LINK,
	),
);

?>

<div class="tutor-learning-area-resources">
	<h4 class="tutor-h4 tutor-mb-4 tutor-sm-hidden"><?php esc_html_e( 'Course Resources', 'tutor' ); ?></h4>

	<div class="tutor-resources-wrapper">
		<div x-data='tutorTabs({
				tabs: <?php echo wp_json_encode( $tabs_data ); ?>,
				defaultTab: "downloads",
				urlParams: {
					paramName: "tab",
				}
			})'
		>
			<div x-ref="tablist" class="tutor-tabs-nav" role="tablist" aria-orientation="horizontal">
				<template x-for="tab in tabs" :key="tab.id">
					<button
					type="button"
					role="tab"
					:class='getTabClass(tab)'
					x-bind:aria-selected="isActive(tab.id)"
					:disabled="tab.disabled ? true : false"
					@click="selectTab(tab.id)"
					>
						<span x-data="TutorCore.icon({ name: tab.icon, width: 20, height: 20})"></span>
						<span x-text="tab.label"></span>
					</button>
				</template>
			</div>

			<div class="tutor-tabs-content">
				<div x-show="activeTab === 'downloads'" x-cloak class="tutor-tab-panel" role="tabpanel">
					<div class="tutor-resources-list">
						<div class="tutor-card tutor-attachment-card">
							<div class="tutor-attachment-card-icon" aria-hidden="true">
								<?php tutor_utils()->render_svg_icon( Icon::RESOURCES, 24, 24 ); ?>
							</div>

							<div class="tutor-attachment-card-body">
								<div class="tutor-attachment-card-title">
									Course Slides (PDF)
								</div>
								<span class="tutor-attachment-card-meta">
									Week 1–3 lecture slides
								</span>
							</div>

							<div class="tutor-attachment-card-actions">
								<button type="button" class="tutor-btn tutor-btn-ghost tutor-btn-x-small tutor-btn-icon">
									<?php tutor_utils()->render_svg_icon( Icon::DOWNLOAD_2, 16, 16 ); ?>
								</button>
							</div>
						</div>
						<div class="tutor-card tutor-attachment-card">
							<div class="tutor-attachment-card-icon" aria-hidden="true">
								<?php tutor_utils()->render_svg_icon( Icon::RESOURCES, 24, 24 ); ?>
							</div>

							<div class="tutor-attachment-card-body">
								<div class="tutor-attachment-card-title">
									Project Starter Code (ZIP)
								</div>
								<span class="tutor-attachment-card-meta">
									Includes setup files & instructions
								</span>
							</div>

							<div class="tutor-attachment-card-actions">
								<button type="button" class="tutor-btn tutor-btn-ghost tutor-btn-x-small tutor-btn-icon">
									<?php tutor_utils()->render_svg_icon( Icon::DOWNLOAD_2, 16, 16 ); ?>
								</button>
							</div>
						</div>
						<div class="tutor-card tutor-attachment-card">
							<div class="tutor-attachment-card-icon" aria-hidden="true">
								<?php tutor_utils()->render_svg_icon( Icon::RESOURCES, 24, 24 ); ?>
							</div>

							<div class="tutor-attachment-card-body">
								<div class="tutor-attachment-card-title">
									Course Slides (PDF)
								</div>
								<span class="tutor-attachment-card-meta">
									Week 1–3 lecture slides
								</span>
							</div>

							<div class="tutor-attachment-card-actions">
								<button type="button" class="tutor-btn tutor-btn-ghost tutor-btn-x-small tutor-btn-icon">
									<?php tutor_utils()->render_svg_icon( Icon::DOWNLOAD_2, 16, 16 ); ?>
								</button>
							</div>
						</div>
					</div>
				</div>
				<div x-show="activeTab === 'external_links'" x-cloak class="tutor-tab-panel" role="tabpanel">
					<div class="tutor-resources-list">
						<div class="tutor-card tutor-attachment-card">
							<div class="tutor-attachment-card-icon" aria-hidden="true">
								<?php tutor_utils()->render_svg_icon( Icon::LINK, 24, 24 ); ?>
							</div>

							<div class="tutor-attachment-card-body">
								<div class="tutor-attachment-card-title">
									Official Docs
								</div>
								<span class="tutor-attachment-card-meta">
									React documentation
								</span>
							</div>

							<div class="tutor-attachment-card-actions">
								<a href="#" class="tutor-btn tutor-btn-ghost tutor-btn-x-small tutor-btn-icon">
									<?php tutor_utils()->render_svg_icon( Icon::ARROW_RIGHT_2, 16, 16 ); ?>
								</a>
							</div>
						</div>
						<div class="tutor-card tutor-attachment-card">
							<div class="tutor-attachment-card-icon" aria-hidden="true">
								<?php tutor_utils()->render_svg_icon( Icon::LINK, 24, 24 ); ?>
							</div>

							<div class="tutor-attachment-card-body">
								<div class="tutor-attachment-card-title">
									Community Forum
								</div>
								<span class="tutor-attachment-card-meta">
									Join discussion with peers
								</span>
							</div>

							<div class="tutor-attachment-card-actions">
								<a href="#" class="tutor-btn tutor-btn-ghost tutor-btn-x-small tutor-btn-icon">
									<?php tutor_utils()->render_svg_icon( Icon::ARROW_RIGHT_2, 16, 16 ); ?>
								</a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
