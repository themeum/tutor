<?php
/**
 * Dashboard Order History
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use TUTOR\Icon;

$subscriptions = array(
	array(
		'id'                    => '43516',
		'user_id'               => '43516',
		'plan_id'               => '43516',
		'order_id'              => '43516',
		'status'                => 'completed',
		'auto_renew'            => '1',
		'is_trial_enabled'      => '1',
		'is_trial_used'         => '1',
		'regular_price'         => '79.00',
		'sale_price'            => '79.00',
		'enrollment_fee'        => '0.00',
		'coupon_code'           => '',
		'order_price'           => '79.00',
		'payment_method'        => 'paypal',
		'trx_id'                => '43516',
		'payment_payload'       => null,
		'created_at_gmt'        => '2025-11-17 09:34:14',
		'updated_at_gmt'        => '2025-11-17 09:34:14',
		'first_order_id'        => '43516',
		'active_order_id'       => '43516',
		'trial_end_date_gmt'    => '2025-11-17 09:34:14',
		'start_date_gmt'        => '2025-11-17 09:34:14',
		'end_date_gmt'          => '2025-11-17 09:34:14',
		'next_payment_date_gmt' => '2025-11-17 09:34:14',
		'note'                  => 'Auto renew failed',
		'plan_name'             => 'Auto Renew',
		'plan_type'             => 'category',
		'user_login'            => 'blind',
	),
	array(
		'id'                    => '8',
		'user_id'               => '1',
		'plan_id'               => '110',
		'order_id'              => '0',
		'trx_id'                => '',
		'status'                => 'expired',
		'auto_renew'            => '1',
		'is_trial_enabled'      => '0',
		'is_trial_used'         => '0',
		'regular_price'         => '0.00',
		'sale_price'            => '',
		'enrollment_fee'        => '0.00',
		'coupon_code'           => '',
		'order_price'           => '',
		'payment_method'        => 'stripe',
		'payment_payload'       => '',
		'created_at_gmt'        => '2025-09-30 07:41:04',
		'updated_at_gmt'        => '2025-09-30 07:41:04',
		'first_order_id'        => '3144',
		'active_order_id'       => '43514',
		'trial_end_date_gmt'    => '',
		'start_date_gmt'        => '2025-09-30 07:41:47',
		'end_date_gmt'          => '2025-10-30 07:41:47',
		'next_payment_date_gmt' => '',
		'note'                  => 'Subscription expired',
		'plan_name'             => 'Neuro Explorer',
		'plan_type'             => 'course',
		'user_login'            => 'blind',
	),
	array(
		'id'                    => '9',
		'user_id'               => '1',
		'plan_id'               => '110',
		'order_id'              => '0',
		'trx_id'                => '',
		'status'                => 'incomplete',
		'auto_renew'            => '1',
		'is_trial_enabled'      => '0',
		'is_trial_used'         => '0',
		'regular_price'         => '0.00',
		'sale_price'            => '',
		'enrollment_fee'        => '0.00',
		'coupon_code'           => '',
		'order_price'           => '',
		'payment_method'        => 'paypal',
		'payment_payload'       => '',
		'created_at_gmt'        => '2025-09-30 07:41:04',
		'updated_at_gmt'        => '2025-09-30 07:41:04',
		'first_order_id'        => '3144',
		'active_order_id'       => '43514',
		'trial_end_date_gmt'    => '',
		'start_date_gmt'        => '2025-09-30 07:41:47',
		'end_date_gmt'          => '2025-10-30 07:41:47',
		'next_payment_date_gmt' => '',
		'note'                  => 'Subscription expired',
		'plan_name'             => 'Neuro Explorer',
		'plan_type'             => 'course',
		'user_login'            => 'blind',
	),
)

?>

<div class="tutor-subscription-history">
	<?php foreach ( $subscriptions as $subscription ) : ?>
		<?php tutor_load_template( 'demo-components.dashboard.components.billing.subscription-history-card', $subscription ); ?>	
	<?php endforeach; ?>
</div>
