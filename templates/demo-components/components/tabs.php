<?php
/**
 * Tabs
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.conm>
 * @link https://themeum.com
 * @since 4.0.0
 */

use TUTOR\Icon;

$tabs_data = array(
	array(
		'id'    => 'lesson',
		'label' => 'Lesson',
		'icon'  => Icon::LESSON,
	),
	array(
		'id'    => 'assignments',
		'label' => 'Assignments',
		'icon'  => Icon::ASSIGNMENT,
	),
	array(
		'id'    => 'quizzes',
		'label' => 'Quizzes',
		'icon'  => Icon::QUIZ,
	),
);

?>

<section class="tutor-bg-white tutor-py-6 tutor-px-8 tutor-rounded-lg tutor-shadow-sm">
	<h1 class="tutor-text-2xl tutor-font-bold tutor-mb-6">Tabs</h1>
	<p class="tutor-text-gray-600 tutor-mb-4">
		Tabs with two types of orientations: horizontal and vertical. Use <span class="tutor-text-black">Icon::--name--<span> to get the name of the icon.
	</p>

	<div class="tutor-mb-8">
		<h2 class="tutor-text-xl tutor-font-semibold tutor-mb-3">Horizontal Tabs</h2>

		<div x-data='tutorTabs({
				tabs: <?php echo wp_json_encode( $tabs_data ); ?>,
				defaultTab: "lesson",
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
						<span x-data="TutorCore.icon({ name: tab.icon, width: 24, height: 24})"></span>
						<span x-text="tab.label"></span>
					</button>
				</template>
			</div>
	
			<div class="tutor-tabs-content">
				<div x-show="activeTab === 'lesson'" x-cloak class="tutor-tab-panel" role="tabpanel">
					Lesson Content
				</div>
				<div x-show="activeTab === 'assignments'" x-cloak class="tutor-tab-panel" role="tabpanel">
					Assignments Content
				</div>
				<div x-show="activeTab === 'quizzes'" x-cloak class="tutor-tab-panel" role="tabpanel">
					Quizzes Content
				</div>
			</div>
		</div>
	</div>

	<div class="tutor-mb-8">
		<h2 class="tutor-text-xl tutor-font-semibold tutor-mb-3">Vertical Tabs</h2>

		<div x-data='tutorTabs({
				tabs: <?php echo wp_json_encode( $tabs_data ); ?>,
				orientation: "vertical",
				defaultTab: "quizzes",
				urlParams: {
					enabled: false,
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
						<span x-data="tutorIcon({ name: tab.icon, width: 24, height: 24})"></span>
						<span x-text="tab.label"></span>
					</button>
				</template>
			</div>
	
			<div class="tutor-tabs-content">
				<div x-show="activeTab === 'lesson'" x-cloak class="tutor-tab-panel" role="tabpanel">
					Lesson Content
				</div>
				<div x-show="activeTab === 'assignments'" x-cloak class="tutor-tab-panel" role="tabpanel">
					Assignments Content
				</div>
				<div x-show="activeTab === 'quizzes'" x-cloak class="tutor-tab-panel" role="tabpanel">
					Quizzes Content
				</div>
			</div>
		</div>
	</div>
</section>