<?php
/**
 * Withdrawal History Filters
 *
 * @package Tutor\Templates
 * @subpackage Dashboard
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use Tutor\Components\Button;
use Tutor\Components\Constants\Size;
use Tutor\Components\Constants\Variant;
use Tutor\Components\DateFilter;
use Tutor\Components\DropdownFilter;
use Tutor\Components\Sorting;
use TUTOR\Dashboard;
use TUTOR\Input;
use Tutor\Models\WithdrawModel;

$status_filter_options = WithdrawModel::get_status_filter_options();
$dropdown_options      = array_map(
	function ( $item ) {
		return array(
			'label' => $item['title'],
			'value' => '' === $item['key'] ? 'all' : $item['key'],
			'count' => (int) $item['value'],
		);
	},
	$status_filter_options
);

// Use Tutor's current URL so dropdown and sort links preserve other filter params (cumulative filtering).
$withdrawals_base_url = tutor()->current_url;

// Reset pagination when changing filters or sorting.
$withdrawals_base_url = remove_query_arg( 'current_page', $withdrawals_base_url );
?>
<div class="tutor-flex tutor-items-center tutor-justify-between tutor-gap-4 tutor-flex-wrap tutor-px-6 tutor-py-5 tutor-border-b">
	<?php
	DropdownFilter::make()
		->options( $dropdown_options )
		->query_param( 'data' )
		->variant( Variant::LINK )
		->size( Size::X_SMALL )
		->popover_size( Size::SMALL )
		->base_url( $withdrawals_base_url )
		->render();
	?>

	<div class="tutor-flex tutor-items-center tutor-gap-4 tutor-ml-auto">
		<?php
		$query_params = array( 'data', 'order', 'start_date', 'end_date' );
		if ( Input::has_any( $query_params, Input::GET_REQUEST ) ) {
			Button::make()
				->tag( 'a' )
				->attr( 'href', Dashboard::get_account_page_url( 'withdrawals' ) )
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

		Sorting::make()
			->order( $order_filter )
			->base_url( $withdrawals_base_url )
			->render();
		?>
	</div>
</div>

