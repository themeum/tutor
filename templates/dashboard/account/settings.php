<?php
/**
 * Settings Template for Account
 *
 * @package Tutor\Templates
 * @subpackage Dashboard
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use TUTOR\Icon;

$profile_tab_data = array(
	array(
		'id'    => 'account',
		'label' => 'Account',
		'icon'  => Icon::USER_CIRCLE,
		'text'  => __( 'Name, email, phone number, profiles', 'tutor' ),
	),
	array(
		'id'    => 'social-accounts',
		'label' => 'Social Accounts',
		'icon'  => Icon::GLOBE,
		'text'  => __( 'Linked social media profiles', 'tutor' ),
	),
	array(
		'id'    => 'billing-address',
		'label' => 'Billing Address',
		'icon'  => Icon::BILLING,
		'text'  => __( 'Your payment address', 'tutor' ),
	),
	array(
		'id'    => 'notifications',
		'label' => 'Notifications',
		'icon'  => Icon::NOTIFICATION,
		'text'  => __( 'Message, group, order', 'tutor' ),
	),
	array(
		'id'    => 'preferences',
		'label' => 'Preferences',
		'icon'  => Icon::PREFERENCE,
		'text'  => __( 'Sound effects, animations, theme', 'tutor' ),
	),
);
?>

<section 
	x-data='tutorTabs({
				tabs: <?php echo wp_json_encode( $profile_tab_data ); ?>,
				orientation: "vertical",
				defaultTab: window.innerWidth >= 576 ? "account" : "none",
				urlParams: {
					enabled: true,
					paramName: "tab",
				}
			})'
	class="tutor-profile-settings-section"
>
	<?php tutor_load_template( 'dashboard.account.settings.header' ); ?>
	
	<div class="tutor-dashboard-container">
		<div 
			x-init="$watch('$store.windowWidth', () => {
				if (window.innerWidth >= 576 && activeTab === 'none') {
					selectTab('account');
				} else if (window.innerWidth < 576 && activeTab !== 'none') {
					selectTab('none');
				}
			})"
			@resize.window="
				if (window.innerWidth >= 576 && activeTab === 'none') {
					selectTab('account');
				} else if (window.innerWidth < 576 && activeTab !== 'none') {
					selectTab('none');
				}
			"
			x-cloak
			class="tutor-gap-8"
		>
			<div class="tutor-flex tutor-gap-8">
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

				<div 
					:class="activeTab !== null && activeTab !== 'none' ? 'tutor-profile-tab-activated' : ''" 
					class="tutor-profile-settings-tab-content tutor-w-full"
				>
					<div x-show="activeTab === 'account'" x-cloak class="tutor-tab-panel" role="tabpanel">
						<?php tutor_load_template( 'dashboard.account.settings.account' ); ?>
					</div>
					<div x-show="activeTab === 'social-accounts'" x-cloak class="tutor-tab-panel" role="tabpanel">
						<?php tutor_load_template( 'dashboard.account.settings.social-accounts' ); ?>
					</div>
					<div x-show="activeTab === 'notifications'" x-cloak class="tutor-tab-panel" role="tabpanel">
						<?php tutor_load_template( 'dashboard.account.settings.notifications' ); ?>
					</div>
					<div x-show="activeTab === 'preferences'" x-cloak class="tutor-tab-panel" role="tabpanel">
						<?php tutor_load_template( 'dashboard.account.settings.preferences' ); ?>
					</div>
					<div x-show="activeTab === 'billing-address'" x-cloak class="tutor-tab-panel" role="tabpanel">
						<?php tutor_load_template( 'dashboard.account.settings.billing-address' ); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>
