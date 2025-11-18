<?php
/**
 * User billing details. (Orders and Subscriptions)
 *
 * @package TutorPress\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @version 4.0.0
 */

use TUTOR\Icon;

/**
 * Check if the current page is the subscription details page.
 *
 * @return bool
 * @since 4.0.0
 */
function is_subscription_details_page() {
	// Using filter_input which doesn't add slashes.
	$subpage         = filter_input( INPUT_GET, 'subpage', FILTER_SANITIZE_SPECIAL_CHARS );
	$tab             = filter_input( INPUT_GET, 'tab', FILTER_SANITIZE_SPECIAL_CHARS );
	$subscription_id = filter_input( INPUT_GET, 'subscription_id', FILTER_VALIDATE_INT );

	return 'billing' === $subpage &&
			'subscriptions' === $tab &&
			! empty( $subscription_id );
}

$back_url = add_query_arg(
	array(
		'subpage' => 'billing',
		'tab'     => 'subscriptions',
	),
	remove_query_arg( 'subscription_id' )
);

$billing_tabs = array(
	array(
		'id'    => 'orders',
		'label' => __( 'Order History', 'tutor' ),
		'icon'  => Icon::HISTORY,
	),
	array(
		'id'    => 'subscriptions',
		'label' => __( 'Subscriptions', 'tutor' ),
		'icon'  => Icon::SUBSCRIPTION,
	),
);

?>

<div class="tutor-billing">
	<div class="tutor-billing-header">
		<div class="tutor-billing-container">
			<div class="tutor-flex tutor-justify-between">
				<div class="tutor-flex tutor-items-center tutor-gap-4">
					<button class="tutor-btn tutor-btn-ghost tutor-btn-x-small tutor-btn-icon">
						<?php tutor_utils()->render_svg_icon( Icon::LEFT ); ?>
					</button>
					<span class="tutor-h4">
						<?php esc_html_e( 'Billing', 'tutor' ); ?>
					</span>
				</div>
				<div>
					<button class="tutor-btn tutor-btn-ghost tutor-btn-x-small tutor-btn-icon">
						<?php tutor_utils()->render_svg_icon( Icon::CROSS ); ?>
					</button>
				</div>
			</div>
		</div>
	</div>

	<div class="tutor-billing-container">
		<!-- Billing Card -->
		<div class="tutor-billing-body tutor-mt-9 tutor-flex-tutor-flex-column">
			<!-- Tabs -->
			<div x-data='tutorTabs({
					tabs: <?php echo wp_json_encode( $billing_tabs ); ?>,
					defaultTab: "orders",
					urlParams: {
						paramName: "tab",
					}
				})'
			>
				<?php if ( ! is_subscription_details_page() ) : ?>
					<div x-ref="tablist" class="tutor-billing-tabs tutor-tabs-nav" role="tablist" aria-orientation="horizontal">
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

					<!-- Filters -->
					<div class="tutor-billing-filters">
						<div x-data="tutorPopover({ placement: 'bottom-start', offset: 4 })">
							<button x-ref="trigger" @click="toggle()" class="tutor-btn tutor-btn-link tutor-btn-x-small tutor-p-none tutor-gap-2">
								<?php esc_html_e( 'All', 'tutor' ); ?>
								<?php tutor_utils()->render_svg_icon( Icon::CHEVRON_DOWN ); ?>
							</button>

							<div x-ref="content" x-cloak x-show="open" @click.outside="handleClickOutside()" class="tutor-popover tutor-py-4" style="width: 120px;">
								<ul>
									<li class="tutor-m-none">
										<a href="#" class="tutor-px-5 tutor-py-4 tutor-flex tutor-w-full">
											<?php esc_html_e( 'All', 'tutor' ); ?>
										</a>
									</li>
									<li class="tutor-m-none">
										<a href="#" class="tutor-px-5 tutor-py-4 tutor-flex tutor-w-full">
											<?php esc_html_e( 'Pending', 'tutor' ); ?>
										</a>
									</li>
									<li class="tutor-m-none">
										<a href="#" class="tutor-px-5 tutor-py-4 tutor-flex tutor-w-full">
											<?php esc_html_e( 'Active', 'tutor' ); ?>
										</a>
									</li>
									<li class="tutor-m-none">
										<a href="#" class="tutor-px-5 tutor-py-4 tutor-flex tutor-w-full">
											<?php esc_html_e( 'On Hold', 'tutor' ); ?>
										</a>
									</li>
									<li class="tutor-m-none">
										<a href="#" class="tutor-px-5 tutor-py-4 tutor-flex tutor-w-full">
											<?php esc_html_e( 'Expired', 'tutor' ); ?>
										</a>
									</li>
									<li class="tutor-m-none">
										<a href="#" class="tutor-px-5 tutor-py-4 tutor-flex tutor-w-full">
											<?php esc_html_e( 'Cancelled', 'tutor' ); ?>
										</a>
									</li>
								</ul>
							</div>
						</div>
						<div>Newest first</div>
					</div>

					<!-- Tabs Content -->
					<div class="tutor-billing-tab-content tutor-tabs-content">
						<template x-if="activeTab === 'orders'">
							<div class="tutor-tab-panel" role="tabpanel">
								<?php tutor_load_template( 'demo-components.dashboard.components.order-history' ); ?>
							</div>
						</template>
						<template x-if="activeTab === 'subscriptions'">
							<div class="tutor-tab-panel" role="tabpanel">
								<?php tutor_load_template( 'demo-components.dashboard.components.subscription-history' ); ?>
							</div>
						</template>
					</div>
				<?php else : ?>
					<div class="tutor-billing-tabs">
						<!-- Go to the subscription page -->
						<a href="<?php echo esc_url( $back_url ); ?>" class="tutor-btn tutor-btn-secondary tutor-gap-2">
							<?php tutor_utils()->render_svg_icon( Icon::LEFT ); ?>
							<?php esc_html_e( 'Back', 'tutor' ); ?>
						</a>
					</div>

					<?php tutor_load_template( 'demo-components.dashboard.components.billing.subscription-details' ); ?>
				<?php endif; ?>
			</div>
		</div>

	</div>
</div>