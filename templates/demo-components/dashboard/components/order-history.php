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

$orders = array(
	array(
		'id'             => '43514',
		'items'          => array(
			array(
				'id'    => '43514',
				'title' => 'Basic',
			),
			array(
				'id'    => '43515',
				'title' => 'Intermediate',
			),
			array(
				'id'    => '43516',
				'title' => 'Advanced',
			),
		),
		'created_at_gmt' => '2025-11-13 13:31:42',
		'total_price'    => '79.00',
		'order_status'   => 'incomplete',
		'payment_method' => 'paypal',
	),
	array(
		'id'             => '43514',
		'items'          => array(
			array(
				'id'    => '43514',
				'title' => 'Basic',
			),
		),
		'created_at_gmt' => '2025-11-13 13:31:42',
		'total_price'    => '79.00',
		'order_status'   => 'incomplete',
		'payment_method' => 'paypal',
	),
)

?>


<div class="tutor-order-history">
	<?php foreach ( $orders as $order_item ) : ?>
		<?php tutor_load_template( 'demo-components.dashboard.components.billing.order-history-card', $order_item ); ?>	
	<?php endforeach; ?>
</div>
