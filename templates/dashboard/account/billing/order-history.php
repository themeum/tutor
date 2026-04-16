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

use Tutor\Components\Button;
use Tutor\Components\Constants\Variant;
use Tutor\Components\DateFilter;
use Tutor\Components\DropdownFilter;
use Tutor\Components\EmptyState;
use Tutor\Components\Pagination;
use Tutor\Components\Sorting;
use TUTOR\Dashboard;
use Tutor\Ecommerce\OrderController;
use TUTOR\Input;
use Tutor\Models\OrderModel;

$time_period     = null;
$start_date      = Input::get( 'start_date' );
$end_date        = Input::get( 'end_date' );
$selected_filter = Input::get( 'data', 'all' );

$filter_options = array();

if ( tutor_utils()->is_monetize_by_tutor() ) {

	$args = array();
	if ( ! tutor_utils()->is_addon_enabled( 'subscription' ) ) {
		$args['order_type'] = OrderModel::TYPE_SINGLE_ORDER;
	}
	$response    = ( new OrderModel() )->get_user_orders( $time_period, $start_date, $end_date, $selected_filter, $user_id, $item_per_page, $offset, $order_filter, $args );
	$orders      = $response['results'];
	$total_items = $response['total_count'];

	$filter_options = ( new OrderController( false ) )->tabs_key_value( 'dashboard' );
} else {
	$orders      = tutor_utils()->get_orders_by_user_id( $user_id, $time_period, $start_date, $end_date, $offset, $item_per_page, $order_filter );
	$total_items = tutor_utils()->get_total_orders_by_user_id( $user_id, $time_period, $start_date, $end_date );
	$total_items = ! empty( $total_items ) ? count( $total_items ) : 0;
}

$dropdown_options = array_map(
	function( $filter ) use ( $selected_filter ) {
		$key = $filter['key'] ?? '';
		return array(
			'label'  => $filter['title'],
			'value'  => $key,
			'count'  => (int) $filter['value'],
			'url'    => $filter['url'],
			'active' => $key === $selected_filter || ( empty( $key ) && 'all' === $selected_filter ),
		);
	},
	$filter_options
);
?>

<div class="tutor-flex tutor-items-center tutor-justify-between tutor-px-6 tutor-py-5 tutor-border-b">
	<?php DropdownFilter::make()->options( $dropdown_options )->render(); ?>
	<div class="tutor-flex tutor-items-center tutor-gap-3">
		<?php
		$query_params = array( 'data', 'order', 'start_date', 'end_date' );
		if ( Input::has_any( $query_params, Input::GET_REQUEST ) ) {
			Button::make()
				->tag( 'a' )
				->attr( 'href', Dashboard::get_account_page_url( 'billing' ) )
				->attr( 'class', 'tutor-text-brand' )
				->label( __( 'Clear all', 'tutor' ) )
				->variant( Variant::LINK )
				->render();
		}

		DateFilter::make()->type( DateFilter::TYPE_RANGE )->placement( 'bottom-end' )->render();
		Sorting::make()->order( $order_filter )->render();
		?>
	</div>
</div>

<?php
if ( empty( $orders ) ) :
	EmptyState::make()->title( 'No Orders Found!' )->render();
else :
	?>
<div class="tutor-flex tutor-flex-column tutor-gap-4 tutor-order-history">
	<?php foreach ( $orders as $order_data ) : ?>
		<?php
		$order = OrderModel::normalize_order_for_history( $order_data );
		include tutor_get_template( 'dashboard.account.billing.order-history-card' );
		?>
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
