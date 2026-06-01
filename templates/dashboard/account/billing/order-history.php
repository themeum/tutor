<?php
/**
 * Order History Template for Billing
 *
 * @package Tutor\Templates
 * @subpackage Dashboard
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 *
 * These variables are inherited from parent template.
 * template: tutor/templates/dashboard/account/billing.php
 *
 * @var int    $user_id       The user ID.
 * @var int    $offset        The offset for pagination.
 * @var int    $item_per_page The number of items per page.
 * @var string $order_filter  The order filter.
 * @var int    $current_page  The current page.
 */

defined( 'ABSPATH' ) || exit;

use Tutor\Components\Button;
use Tutor\Components\Constants\Size;
use Tutor\Components\Constants\Variant;
use Tutor\Components\DateFilter;
use Tutor\Components\DropdownFilter;
use Tutor\Components\EmptyState;
use Tutor\Components\Pagination;
use Tutor\Components\Sorting;
use TUTOR\Dashboard;
use Tutor\Ecommerce\Ecommerce;
use TUTOR\Input;

$monetize_by = tutor_utils()->get_option( 'monetize_by' );
if ( 'free' === $monetize_by ) {
	EmptyState::make()->title( __( 'No Orders Found!', 'tutor' ) )->render();
	return;
}

$start_date      = Input::get( 'start_date' );
$end_date        = Input::get( 'end_date' );
$selected_filter = Input::get( 'data', 'all' );

$args = array(
	'status'     => $selected_filter,
	'start_date' => $start_date,
	'end_date'   => $end_date,
	'limit'      => $item_per_page,
	'offset'     => $offset,
	'order'      => $order_filter,
);

$response    = tutor_utils()->get_orders_by_user_id( $user_id, $args );
$orders      = $response->results ?? array();
$total_items = $response->total_count ?? 0;

$status_options = apply_filters( 'tutor_order_history_status_options', array(), $selected_filter );
?>

<div class="tutor-flex tutor-items-center tutor-justify-between tutor-px-6 tutor-py-5 tutor-border-b">
	<?php DropdownFilter::make()->options( $status_options )->render(); ?>
	<div class="tutor-flex tutor-items-center tutor-gap-3">
		<?php
		$query_params = array( 'data', 'order', 'start_date', 'end_date' );
		if ( Input::has_any( $query_params, Input::GET_REQUEST ) ) {
			Button::make()
				->tag( 'a' )
				->size( Size::X_SMALL )
				->attr( 'href', Dashboard::get_account_page_url( 'billing' ) )
				->attr( 'class', 'tutor-text-brand' )
				->label( __( 'Clear all', 'tutor' ) )
				->variant( Variant::LINK )
				->render();
		}

		DateFilter::make()
			->type( DateFilter::TYPE_RANGE )
			->placement( DateFilter::PLACEMENT_BOTTOM_END )
			->trigger_size( Size::X_SMALL )
			->hide_initial_label()
			->render();

		Sorting::make()->size( Size::X_SMALL )->order( $order_filter )->render();
		?>
	</div>
</div>

<?php
if ( empty( $orders ) ) :
	EmptyState::make()
		->title( 'No Orders Found!' )
		->icon( tutor_utils()->get_themed_svg( 'images/illustrations/order-empty.svg' ) )
		->render();
else :
	?>
<div class="tutor-flex tutor-flex-column tutor-order-history">
	<?php
	$default_card_template       = Ecommerce::MONETIZE_BY === $monetize_by ? tutor_get_template( 'dashboard.account.billing.native-order-history-card' ) : '';
	$order_history_card_template = apply_filters( 'tutor_order_history_card_template', $default_card_template );
	foreach ( $orders as $order_data ) :
		if ( file_exists( $order_history_card_template ) ) {
			tutor_load_template_from_custom_path( $order_history_card_template, array( 'order_data' => $order_data ), false );
		}
	endforeach;
	?>
</div>

	<?php
	Pagination::make()
	->attr( 'class', 'tutor-px-6 tutor-py-6 tutor-border-t' )
	->current( $current_page )
	->total( $total_items )
	->limit( $item_per_page )
	->render();
endif;
