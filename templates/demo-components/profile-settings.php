<?php
/**
 * Account settings component
 *
 * @package Tutor
 * @since 1.0.0
 */

use TUTOR\Icon;

$tabs_data = array(
	array(
		'id'    => 'account',
		'label' => 'Account',
		'icon'  => Icon::USER,
	),
	array(
		'id'    => 'security',
		'label' => 'Security',
		'icon'  => Icon::SECURITY,
	),
	array(
		'id'    => 'social-accounts',
		'label' => 'Social Accounts',
		'icon'  => Icon::SECURITY,
	),
	array(
		'id'    => 'billing-address',
		'label' => 'Billing Address',
		'icon'  => Icon::NOTIFICATION,
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
	)
);

?>

<section class="tutor-account-settings-section">
	<!-- header  -->
	<div class="tutor-account-settings-header">
		<div class="tutor-account-settings-container tutor-flex tutor-items-center tutor-justify-between">
			<div class="tutor-flex tutor-items-center">
				<button type="button" class="tutor-account-settings-back-btn tutor-btn tutor-btn-ghost tutor-btn-x-small">
					<span x-data="tutorIcon({ name: '<?php echo Icon::ARROW_LEFT; ?>', width: 20, height: 20})"></span>
				</button>
				<div class="tutor-text-h4 tutor-font-semibold tutor-ml-4">Settings</div>
				<span class="tutor-badge tutor-badge-secondary tutor-badge-circle tutor-ml-5">Unsaved changes</span>
			</div>
			<div class="tutor-flex tutor-gap-4">
				<button type="button" class="tutor-btn tutor-btn-ghost tutor-btn-x-small">Discard</button>
				<button type="button" class="tutor-btn tutor-btn-primary tutor-btn-x-small">Save</button>
			</div>
		</div>
	</div>
	
	<div 
		x-data='tutorTabs({
			tabs: <?php echo wp_json_encode( $tabs_data ); ?>,
			orientation: "vertical",
			defaultTab: "account",
			urlParams: {
				enabled: false,
				paramName: "tab",
			}
		})'
		x-cloak
		class="tutor-account-settings-container tutor-gap-8"
	>
		<div x-ref="tablist" role="tablist" aria-orientation="vertical"
			class="tutor-tabs-nav tutor-account-settings-tab"
		>
			<template x-for="tab in tabs" :key="tab.id">
				<button
				type="button"
				role="tab"
				:class='getTabClass(tab)'
				x-bind:aria-selected="isActive(tab.id)"
				:disabled="tab.disabled ? true : false"
				@click="selectTab(tab.id)"
				>
					<!-- <span x-data="tutorIcon({ name: tab.icon, width: 24, height: 24})"></span> -->
					<span><?php tutor_utils()->render_svg_icon( Icon::SECURITY, 24, 24 ); ?></span>
					<span x-text="tab.label"></span>
				</button>
			</template>
		</div>

		<div class="tutor-tabs-content">
			<div x-show="activeTab === 'account'" x-cloak class="tutor-tab-panel" role="tabpanel">
				<?php tutor_load_template( 'demo-components.dashboard.components.settings.accounts' ); ?>
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