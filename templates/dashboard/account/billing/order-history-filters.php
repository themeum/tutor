<?php
/**
 * Order History Filters
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
use Tutor\Components\Sorting;
use TUTOR\Dashboard;
use Tutor\Ecommerce\OrderController;
use TUTOR\Input;

$filter_options = ( new OrderController( false ) )->tabs_key_value( 'dashboard' );

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
