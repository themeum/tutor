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
		'text'  => 'Name, email, phone number, profiles',
	),
	array(
		'id'    => 'social-accounts',
		'label' => 'Social Accounts',
		'icon'  => Icon::GLOBE,
		'text'  => 'Name, email, phone number, profiles',
	),
	array(
		'id'    => 'billing-address',
		'label' => 'Billing Address',
		'icon'  => Icon::BILLING,
		'text'  => 'Name, email, phone number, profiles',
	),
	array(
		'id'    => 'notifications',
		'label' => 'Notifications',
		'icon'  => Icon::NOTIFICATION,
		'text'  => 'Name, email, phone number, profiles',
	),
	array(
		'id'    => 'preferences',
		'label' => 'Preferences',
		'icon'  => Icon::PREFERENCE,
		'text'  => 'Name, email, phone number, profiles',
	),
);
?>

<section class="tutor-profile-settings-section">

	<?php tutor_load_template( 'demo-components.dashboard.components.profile-header' ); ?>

	<div class="tutor-dashboard-container">
		<div 
			x-data='tutorTabs({
				tabs: <?php echo wp_json_encode( $profile_tab_data ); ?>,
				orientation: "vertical",
				defaultTab: "account",
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
						<div class="tutor-flex tutor-flex-column tutor-items-start">
							<span x-text="tab.label" class="tutor-text-small"></span>
							<span x-text="tab.text" class="tutor-text-tiny tutor-hidden tutor-sm-block tutor-text-subdued"></span>
						</div>
					</button>
				</template>
			</div>

			<div class="tutor-profile-settings-tab-content tutor-w-full tutor-mt-7 tutor-md-mt-1">
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
	</div>
</section>
