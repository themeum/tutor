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
use TUTOR\User;

$settings_tab_data = array(
	array(
		'id'              => 'account',
		'label'           => 'Account',
		'icon'            => Icon::USER_CIRCLE,
		'text'            => __( 'Name, email, phone number, profiles', 'tutor' ),
		'template'        => 'dashboard.account.settings.account',
		'custom_template' => null,
		'role'            => false,
	),
	array(
		'id'              => 'security',
		'label'           => 'Security',
		'icon'            => Icon::SECURITY,
		'text'            => __( 'Password, 2FA', 'tutor' ),
		'template'        => 'dashboard.account.settings.security',
		'custom_template' => null,
		'role'            => false,
	),
	array(
		'id'              => 'social-accounts',
		'label'           => 'Social Accounts',
		'icon'            => Icon::GLOBE,
		'text'            => __( 'Linked social media profiles', 'tutor' ),
		'template'        => 'dashboard.account.settings.social-accounts',
		'custom_template' => null,
		'role'            => false,
	),
	array(
		'id'              => 'withdraw',
		'label'           => 'Withdraw',
		'icon'            => Icon::WITHDRAW,
		'text'            => __( 'Withdrawal and refund', 'tutor' ),
		'template'        => 'dashboard.account.settings.withdraw',
		'custom_template' => null,
		'role'            => User::INSTRUCTOR,
	),
	array(
		'id'              => 'notifications',
		'label'           => 'Notifications',
		'icon'            => Icon::NOTIFICATION,
		'text'            => __( 'Message, group, order', 'tutor' ),
		'template'        => 'dashboard.account.settings.notifications',
		'custom_template' => null,
		'role'            => false,
	),
	array(
		'id'              => 'preferences',
		'label'           => 'Preferences',
		'icon'            => Icon::PREFERENCE,
		'text'            => __( 'Sound effects, animations, theme', 'tutor' ),
		'template'        => 'dashboard.account.settings.preferences',
		'custom_template' => null,
		'role'            => false,
	),
);

// @TODO: previously it was 'tutor_dashboard/nav_items/settings/nav_items' which gives 'phpcs' error
$settings_tab_data = apply_filters( 'tutor_dashboard_settings_tabs', $settings_tab_data );

$settings_tab_data = array_values(
	array_filter(
		$settings_tab_data,
		function ( $tab ) {
			return 'account' === $tab['id'] || ! $tab['role'] || ( User::INSTRUCTOR === $tab['role'] && current_user_can( tutor()->instructor_role ) );
		}
	)
);
?>

<section x-data="tutorSettings()">
	<div 
		x-data='tutorTabs({
			tabs: <?php echo wp_json_encode( $settings_tab_data ); ?>,
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
				<div class="tutor-flex tutor-gap-8 tutor-mb-9">
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
						<?php foreach ( $settings_tab_data as $settings_tab ) : ?>
							<div x-show="activeTab === '<?php echo esc_attr( $settings_tab['id'] ); ?>'" x-cloak class="tutor-tab-panel" role="tabpanel">
								<?php
								$form_id = "tutor-{$settings_tab['id']}-form";
								if ( $settings_tab['custom_template'] ) {
									tutor_load_template_from_custom_path(
										$settings_tab['custom_template'],
										array( 'form_id' => $form_id )
									);
								} else {
									tutor_load_template(
										$settings_tab['template'],
										array( 'form_id' => $form_id )
									);
								}
								?>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>