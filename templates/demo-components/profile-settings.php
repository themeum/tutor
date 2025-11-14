<?php
/**
 * Profile settings page
 *
 * @package Tutor
 * @since 1.0.0
 */

use TUTOR\Icon;

$profile_tab_data = array(
	array(
		'id'    => 'account',
		'label' => 'Account',
		'icon'  => Icon::USER_CIRCLE,
	),
	array(
		'id'    => 'security',
		'label' => 'Security',
		'icon'  => Icon::SECURITY,
	),
	array(
		'id'    => 'social-accounts',
		'label' => 'Social Accounts',
		'icon'  => Icon::GLOBE,
	),
	array(
		'id'    => 'billing-address',
		'label' => 'Billing Address',
		'icon'  => Icon::BILLING,
	),
	array(
		'id'    => 'notifications',
		'label' => 'Notifications',
		'icon'  => Icon::NOTIFICATION,
	),
	array(
		'id'    => 'preferences',
		'label' => 'Preferences',
		'icon'  => Icon::PREFERENCE,
	),
);
?>

<section class="tutor-profile-settings-section">
	<div class="tutor-profile-settings-header">
		<div class="tutor-profile-settings-container tutor-flex tutor-items-center tutor-justify-between">
			<div class="tutor-flex tutor-items-center">
				<button type="button" class="tutor-profile-settings-back-btn tutor-btn tutor-btn-ghost tutor-btn-x-small">
					<span x-data="tutorIcon({ name: '<?php echo esc_html( Icon::ARROW_LEFT ); ?>', width: 20, height: 20})"></span>
				</button>
				<h4 class="tutor-text-h4 tutor-font-semibold tutor-ml-4">Settings</h4>
				<span class="tutor-badge tutor-badge-secondary tutor-badge-circle tutor-ml-5">Unsaved changes</span>
			</div>
			<div class="tutor-flex tutor-gap-4">
				<button type="button" class="tutor-btn tutor-btn-ghost tutor-btn-x-small">Discard</button>
				<button type="button" class="tutor-btn tutor-btn-primary tutor-btn-x-small">Save</button>
			</div>
		</div>
	</div>

	<div class="tutor-profile-settings-container">
		<div 
			x-data='tutorTabs({
				tabs: <?php echo wp_json_encode( $profile_tab_data ); ?>,
				orientation: "vertical",
				defaultTab: "notifications",
				urlParams: {
					enabled: false,
					paramName: "tab",
				}
			})'
			x-cloak
			class="tutor-gap-8">
			<div x-ref="tablist" role="tablist" aria-orientation="vertical" class="tutor-tabs-nav tutor-profile-settings-tab">
				<template x-for="tab in tabs" :key="tab.id">
					<button
						type="button"
						role="tab"
						:class='getTabClass(tab)'
						x-bind:aria-selected="isActive(tab.id)"
						:disabled="tab.disabled ? true : false"
						@click="selectTab(tab.id)"
					>
						<span x-data="tutorIcon({ name: tab.icon, width: 20, height: 20})"></span>
						<span x-text="tab.label" class="tutor-text-small"></span>
					</button>
				</template>
			</div>

			<div x-show="activeTab === 'account'" x-cloak class="tutor-tab-panel" role="tabpanel">
				<?php tutor_load_template( 'demo-components.dashboard.components.settings.account' ); ?>
			</div>
			<div x-show="activeTab === 'security'" x-cloak class="tutor-tab-panel" role="tabpanel">
				<?php tutor_load_template( 'demo-components.dashboard.components.settings.security' ); ?>
			</div>
			<div x-show="activeTab === 'social-accounts'" x-cloak class="tutor-tab-panel" role="tabpanel">
				<?php tutor_load_template( 'demo-components.dashboard.components.settings.social-accounts' ); ?>
			</div>
			<div x-show="activeTab === 'notifications'" x-cloak class="tutor-tab-panel" role="tabpanel">
				<?php tutor_load_template( 'demo-components.dashboard.components.settings.notifications' ); ?>
			</div>
			<div x-show="activeTab === 'preferences'" x-cloak class="tutor-tab-panel" role="tabpanel">
				<?php tutor_load_template( 'demo-components.dashboard.components.settings.preferences' ); ?>
			</div>
			<div x-show="activeTab === 'billing-address'" x-cloak class="tutor-tab-panel" role="tabpanel">
				<?php tutor_load_template( 'demo-components.dashboard.components.settings.billing-address' ); ?>
			</div>
		</div>
	</div>
</section>
