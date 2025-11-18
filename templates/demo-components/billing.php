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
)

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
					<div>All</div>
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
			</div>
		</div>

	</div>
</div>