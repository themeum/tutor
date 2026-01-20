<?php
/**
 * Order History Template for Billing
 *
 * @package Tutor\Templates
 * @subpackage Dashboard
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use Tutor\Components\EmptyState;
use Tutor\Components\Pagination;
use TUTOR\Input;
use Tutor\Models\OrderModel;

$time_period     = null;
$start_date      = Input::get( 'start_date' );
$end_date        = Input::get( 'end_date' );
$selected_filter = Input::get( 'data', 'all' );

if ( tutor_utils()->is_monetize_by_tutor() ) {
	$response    = ( new OrderModel() )->get_user_orders( $time_period, $start_date, $end_date, $selected_filter, $user_id, $item_per_page, $offset, $order_filter );
	$orders      = $response['results'];
	$total_items = $response['total_count'];
} else {
	$orders      = tutor_utils()->get_orders_by_user_id( $user_id, $time_period, $start_date, $end_date, $offset, $item_per_page, $order_filter );
	$total_items = tutor_utils()->get_total_orders_by_user_id( $user_id, $time_period, $start_date, $end_date );
	$total_items = ! empty( $total_items ) ? count( $total_items ) : 0;
}
?>

<div class="tutor-flex tutor-justify-between tutor-px-6 tutor-py-5 tutor-border-b">
	<?php require_once tutor_get_template( 'dashboard.account.billing.order-history-filters' ); ?>
</div>

<?php
if ( empty( $orders ) ) :
	EmptyState::make()->title( 'No Orders Found!' )->render();
else :
	?>
<div class="tutor-flex tutor-flex-column tutor-gap-4 tutor-order-history">
	<?php foreach ( $orders as $order ) : //phpcs:ignore ?>
		<?php include tutor_get_template( 'dashboard.account.billing.order-history-card' ); ?>	
	<?php endforeach; ?>
</div>

	<?php
	Pagination::make()
	->attr( 'class', 'tutor-px-6 tutor-py-6 tutor-border-t' )
	->current( $current_page )
	->total( $total_items )
	->limit( $item_per_page )
	->render();
endif;
