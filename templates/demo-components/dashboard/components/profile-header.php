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
	<div class="tutor-dashboard-container tutor-flex tutor-items-center tutor-justify-between">
		<div class="tutor-profile-header-left tutor-flex tutor-items-center"
			x-data="{ windowWidth: window.innerWidth }"
			@resize.window="windowWidth = window.innerWidth"
		>
			<button @click="window.history.back()" class="tutor-btn tutor-btn-ghost tutor-btn-x-small tutor-btn-icon">
				<?php tutor_utils()->render_svg_icon( Icon::LEFT ); ?>
			</button>
			<h4 
				class="tutor-profile-header-title tutor-text-h4 tutor-font-semibold tutor-ml-4"
				x-text="windowWidth <= 576 ? (activeTab === 'none' ? '<?php esc_html_e( 'Settings', 'tutor' ); ?>' : tabs.find(tab =>tab.id == activeTab).label) : '<?php esc_html_e( 'Settings', 'tutor' ); ?>'"
			></h4>
			<span class="tutor-badge tutor-badge-rounded tutor-ml-5 tutor-sm-hidden">Unsaved changes</span>
		</div>
		<div class="tutor-profile-header-right tutor-flex tutor-gap-4">
			<div class="tutor-sm-hidden">
				<button type="button" class="tutor-btn tutor-btn-ghost tutor-btn-x-small">Discard</button>
				<button type="button" class="tutor-btn tutor-btn-primary tutor-btn-x-small">Save</button>
			</div>
			<div class="tutor-profile-header-close"
				@click="activeTab = 'none'"
			>
				<button class="tutor-btn tutor-btn-ghost tutor-btn-x-small tutor-btn-icon tutor-hidden tutor-sm-flex">
					<?php tutor_utils()->render_svg_icon( Icon::CROSS ); ?>
				</button>
			</div>
		</div>
	</div>
</div>