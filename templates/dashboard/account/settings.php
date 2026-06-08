<?php
/**
 * Settings Template for Account
 *
 * @package Tutor\Templates
 * @subpackage Dashboard
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 *
 * These variables are inherited from parent templates:
 * template: templates/account.php
 *
 * @var string $back_url
 */

defined( 'ABSPATH' ) || exit;

use TUTOR\Icon;
use TUTOR\User;

$settings_tab_data = array(
	'account'         => array(
		'id'       => 'account',
		'label'    => __( 'Account', 'tutor' ),
		'icon'     => Icon::USER_CIRCLE,
		'text'     => __( 'Name, email, phone number, profiles', 'tutor' ),
		'template' => 'dashboard.account.settings.account',
		'role'     => false,
	),
	'security'        => array(
		'id'       => 'security',
		'label'    => __( 'Security', 'tutor' ),
		'icon'     => Icon::SECURITY,
		'text'     => __( 'Password, 2FA', 'tutor' ),
		'template' => 'dashboard.account.settings.security',
		'role'     => false,
	),
	'social-accounts' => array(
		'id'       => 'social-accounts',
		'label'    => __( 'Social Accounts', 'tutor' ),
		'icon'     => Icon::GLOBE,
		'text'     => __( 'Linked social media profiles', 'tutor' ),
		'template' => 'dashboard.account.settings.social-accounts',
		'role'     => false,
	),
	'withdraw'        => array(
		'id'       => 'withdraw',
		'label'    => __( 'Withdraw', 'tutor' ),
		'icon'     => Icon::WITHDRAW,
		'text'     => __( 'Withdrawal and refund', 'tutor' ),
		'template' => 'dashboard.account.settings.withdraw',
		'role'     => User::INSTRUCTOR,
	),
	'preferences'     => array(
		'id'       => 'preferences',
		'label'    => __( 'Preferences', 'tutor' ),
		'icon'     => Icon::FILTER_2,
		'text'     => __( 'Sound effects, animations, theme', 'tutor' ),
		'template' => 'dashboard.account.settings.preferences',
		'role'     => false,
	),
);

// phpcs:ignore WordPress.NamingConventions.ValidHookName
$settings_tab_data = apply_filters( 'tutor_dashboard/nav_items/settings/nav_items', $settings_tab_data );

$settings_tab_data = array_values(
	array_filter(
		$settings_tab_data,
		function ( $tab ) {
			return 'account' === $tab['id'] || ! $tab['role'] || ( User::INSTRUCTOR === $tab['role'] && current_user_can( tutor()->instructor_role ) && User::is_instructor_view() );
		}
	)
);

?>

<section x-data="tutorSettings()">
	<div 
		x-data='(() => {
			const tabs = tutorTabs({
				tabs: <?php echo wp_json_encode( $settings_tab_data ); ?>,
				orientation: "vertical",
				defaultTab: window.innerWidth >= 768 ? "account" : "none",
				urlParams: {
					enabled: true,
					paramName: "tab",
				}
			});

			return {
				...tabs,
				backUrl: <?php echo wp_json_encode( $back_url ); ?>,
				selectTab(tabId) {
					if (tabId === "none") {
						this.activeTab = "none";

						if (this.urlParamsConfig.enabled) {
							const url = new URL(window.location.href);
							url.searchParams.delete(this.urlParamsConfig.paramName);
							window.history.replaceState({}, "", url.toString());
						}

						return;
					}

					return tabs.selectTab.call(this, tabId);
				},
				handleClose() {
					if (window.innerWidth < 768 && this.activeTab !== "none") {
						this.selectTab("none");
						return;
					}

					window.location.href = this.backUrl;
				},
			};
		})()'
		class="tutor-profile-settings-section"
	>
		<?php tutor_load_template( 'dashboard.account.settings.header', array( 'back_url' => $back_url ) ); ?>

		<div class="tutor-account-container">
			<div 
				x-init="$watch('$store.windowWidth', () => {
					if (window.innerWidth >= 768 && activeTab === 'none') {
						selectTab('account');
					} else if (window.innerWidth < 768 && activeTab !== 'none') {
						selectTab('none');
					}
				})"
				@resize.window="
					if (window.innerWidth >= 768 && activeTab === 'none') {
						selectTab('account');
					} else if (window.innerWidth < 768 && activeTab !== 'none') {
						selectTab('none');
					}
				"
				x-cloak
				class="tutor-gap-8"
			>
				<div class="tutor-flex tutor-gap-8 tutor-my-9 tutor-sm-my-6">
					<div x-ref="tablist" role="tablist" aria-orientation="vertical" class="tutor-tabs-nav tutor-profile-settings-tab tutor-p-5">
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
									<span x-text="tab.text" class="tutor-text-tiny tutor-hidden tutor-md-block tutor-text-subdued"></span>
								</div>
							</button>
						</template>
					</div>

					<div 
						role="main"
						:class="activeTab !== null && activeTab !== 'none' ? 'tutor-profile-tab-activated' : ''" 
						class="tutor-profile-settings-tab-content tutor-w-full"
					>
						<?php foreach ( $settings_tab_data as $settings_tab ) : ?>
							<div x-show="activeTab === '<?php echo esc_attr( $settings_tab['id'] ); ?>'" x-cloak class="tutor-tab-panel" role="tabpanel">
								<?php
								$form_id = "tutor-{$settings_tab['id']}-form";
								tutor_load_template(
									$settings_tab['template'],
									array( 'form_id' => $form_id ),
									isset( $settings_tab['is_pro'] ) && $settings_tab['is_pro'] ? true : false
								);
								?>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>
