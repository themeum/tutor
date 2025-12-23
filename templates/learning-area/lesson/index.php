<?php
/**
 * Tutor learning area lesson.
 *
 * @package Tutor\Templates
 * @subpackage LearningArea
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use TUTOR\Icon;


$tabs_data = array(
	array(
		'id'    => 'overview',
		'label' => 'Overview',
		'icon'  => Icon::COURSES,
	),
	array(
		'id'    => 'notes',
		'label' => 'Notes',
		'icon'  => Icon::NOTES,
	),
	array(
		'id'    => 'comments',
		'label' => 'Comments',
		'icon'  => Icon::COMMENTS,
	),
);

?>
<div class="tutor-pt-9">
	<div 
		x-data='tutorTabs({
			tabs: <?php echo wp_json_encode( $tabs_data ); ?>,
			defaultTab: "overview",
			urlParams: {
				paramName: "tab",
			}
		})'
		class="tutor-surface-l1 tutor-border tutor-rounded-lg"
	>
		<div x-ref="tablist" class="tutor-tabs-nav tutor-p-6 tutor-border-b" role="tablist" aria-orientation="horizontal">
			<template x-for="tab in tabs" :key="tab.id">
				<button
					type="button" 
					role="tab" 
					:class='getTabClass(tab)' 
					x-bind:aria-selected="isActive(tab.id)" 
					:disabled="tab.disabled ? true : false" 
					@click="selectTab(tab.id)"
					>
					<span x-data="TutorCore.icon({ name: tab.icon, width: 24, height: 24})"></span>
					<span x-text="tab.label"></span>
				</button>
			</template>
		</div>

		<div class="tutor-tabs-content tutor-p-6">
			<div x-show="activeTab === 'overview'" x-cloak class="tutor-tab-panel" role="tabpanel">
				<h4 class="tutor-heading-4 tutor-mb-4">Learning the way of water</h4>
				<p class="tutor-mb-6">I do a something I call a “twenty application test” every quarter
					where I will send out my resume to 20 companies who are hiring designers to see if I can
					make the cut. </p>
				<p class="tutor-mb-6">Learning the way of water is about embracing flow, adaptability, and
					resilience in both thought and action. Just as water carves through stone over time,
					those who follow its path learn to navigate obstacles with patience and quiet strength.
					It’s not about overpowering challenges, but moving with them—finding paths of least
					resistance, bending when necessary, and returning to form when pressure subsides. Like a
					stream that adapts to its landscape, learning the way of water teaches us to remain calm
					under pressure and persistent through change. In this way, water becomes not just a
					force of nature, but a quiet mentor in the art of living.</p>
				<p class="tutor-mb-6">Learning the way of water is about embracing flow, adaptability, and
					resilience in both thought and action. Just as water carves through stone over time,
					those who follow its path learn to navigate obstacles with patience and quiet strength.
					It’s not about overpowering challenges, but moving with them—finding paths of least
					resistance, bending when necessary, and returning to form when pressure subsides. Like a
					stream that adapts to its landscape, learning the way of water teaches us to remain calm
					under pressure and persistent through change. In this way, water becomes not just a
					force of nature, but a quiet mentor in the art of living.</p>
				<p class="tutor-mb-6">Learning the way of water is about embracing flow, adaptability, and
					resilience in both thought and action. Just as water carves through stone over time,
					those who follow its path learn to navigate obstacles with patience and quiet strength.
					It’s not about overpowering challenges, but moving with them—finding paths of least
					resistance, bending when necessary, and returning to form when pressure subsides. Like a
					stream that adapts to its landscape, learning the way of water teaches us to remain calm
					under pressure and persistent through change. In this way, water becomes not just a
					force of nature, but a quiet mentor in the art of living.</p>
				<p class="tutor-mb-6">Learning the way of water is about embracing flow, adaptability, and
					resilience in both thought and action. Just as water carves through stone over time,
					those who follow its path learn to navigate obstacles with patience and quiet strength.
					It’s not about overpowering challenges, but moving with them—finding paths of least
					resistance, bending when necessary, and returning to form when pressure subsides. Like a
					stream that adapts to its landscape, learning the way of water teaches us to remain calm
					under pressure and persistent through change. In this way, water becomes not just a
					force of nature, but a quiet mentor in the art of living.</p>
			</div>
			<div x-show="activeTab === 'notes'" x-cloak class="tutor-tab-panel" role="tabpanel">
				Notes
			</div>
			<div x-show="activeTab === 'comments'" x-cloak class="tutor-tab-panel" role="tabpanel">
				Comments
			</div>
		</div>
	</div>

	<div class="tutor-flex tutor-items-center tutor-justify-between tutor-mt-11">
		<button type="button" class="tutor-btn tutor-btn-ghost tutor-btn-small">
			<?php tutor_utils()->render_svg_icon( Icon::CHEVRON_LEFT_2 ); ?>
			<?php esc_html_e( 'Previous', 'tutor' ); ?>
		</button>
		<button type="button" class="tutor-btn tutor-btn-secondary tutor-btn-large tutor-rounded-full tutor-gap-5">
			<?php esc_html_e( 'Mark as complete', 'tutor' ); ?>
			<?php
			tutor_utils()->render_svg_icon(
				Icon::CHECK_2,
				20,
				20,
				array(
					'class' => 'tutor-icon-secondary',
				)
			);
			?>
		</button>
		<button type="button" class="tutor-btn tutor-btn-ghost tutor-btn-small">
			<?php esc_html_e( 'Next', 'tutor' ); ?>
			<?php tutor_utils()->render_svg_icon( Icon::CHEVRON_RIGHT_2 ); ?>
		</button>
	</div>
</div>
