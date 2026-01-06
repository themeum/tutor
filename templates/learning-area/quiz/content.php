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

$quiz = $quiz ?? null;
if ( ! $quiz || ! is_a( $quiz, 'WP_Post' ) ) {
	return;
}

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
				<h4 class="tutor-heading-4 tutor-mb-4">
					<?php echo esc_html( $quiz->post_title ); ?>
				</h4>
				<?php echo wp_kses_post( $quiz->post_content ); ?>
			</div>
			<div x-show="activeTab === 'notes'" x-cloak class="tutor-tab-panel" role="tabpanel">
				<?php esc_html_e( 'Notes', 'tutor' ); ?>
			</div>
			<div x-show="activeTab === 'comments'" x-cloak class="tutor-tab-panel" role="tabpanel">
				<?php esc_html_e( 'Comments', 'tutor' ); ?>
			</div>
		</div>
	</div>

	<?php tutor_load_template( 'learning-area.components.footer' ); ?>
</div>
