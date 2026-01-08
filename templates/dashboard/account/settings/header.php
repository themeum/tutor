<?php
/**
 * Tutor dashboard profile header
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use TUTOR\Icon;

?>
<div class="tutor-profile-header">
	<div 
		x-data="{ 
			windowWidth: window.innerWidth,
			isDirty: {}
		}"
		class="tutor-dashboard-container tutor-flex tutor-items-center tutor-justify-between">
		<div class="tutor-profile-header-left tutor-flex tutor-items-center"
			@resize.window="windowWidth = window.innerWidth"
			@tutor-form-state-change.document="if ($event.detail.id === `tutor-${activeTab}-form`) isDirty[$event.detail.id] = $event.detail.isDirty"
		>
			<button @click="window.history.back()" class="tutor-btn tutor-btn-ghost tutor-btn-x-small tutor-btn-icon">
				<?php tutor_utils()->render_svg_icon( Icon::LEFT ); ?>
			</button>
			<h4 
				class="tutor-profile-header-title tutor-text-h4 tutor-font-semibold tutor-ml-4"
				x-text="windowWidth <= 576 ? (activeTab === 'none' ? '<?php esc_html_e( 'Settings', 'tutor' ); ?>' : tabs.find(tab =>tab.id == activeTab).label) : '<?php esc_html_e( 'Settings', 'tutor' ); ?>'"
			></h4>
			<span 
				class="tutor-badge tutor-badge-secondary tutor-badge-circle tutor-ml-5 tutor-sm-hidden"
				x-show="activeTab !== 'none' && isDirty[`tutor-${activeTab}-form`]"
				x-cloak
			>
				<?php esc_html_e( 'Unsaved changes', 'tutor' ); ?>
			</span>
		</div>
		<div class="tutor-profile-header-right tutor-flex tutor-gap-4">
			<div x-show="activeTab !== 'none' && isDirty[`tutor-${activeTab}-form`]" x-cloak>
				<button 
					type="button" 
					class="tutor-btn tutor-btn-ghost tutor-btn-x-small"
					@click="TutorCore.form.reset(`tutor-${activeTab}-form`)"
				>
					<?php esc_html_e( 'Discard', 'tutor' ); ?>
				</button>
				<button 
					type="submit"
					class="tutor-btn tutor-btn-primary tutor-btn-x-small"
					x-bind:form="activeTab === 'none' ? '' : `tutor-${activeTab}-form`"
				>
					<?php esc_html_e( 'Save', 'tutor' ); ?>
				</button>
			</div>
			<div class="tutor-profile-header-close"
				@click="activeTab = 'none'"
				x-show="activeTab === 'none' || !isDirty[`tutor-${activeTab}-form`]"
			>
				<button class="tutor-btn tutor-btn-ghost tutor-btn-x-small tutor-btn-icon">
					<?php tutor_utils()->render_svg_icon( Icon::CROSS ); ?>
				</button>
			</div>
		</div>
	</div>
</div>