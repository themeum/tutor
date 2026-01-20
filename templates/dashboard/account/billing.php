<?php
/**
 * Billing Template for Account
 *
 * @package Tutor\Templates
 * @subpackage Dashboard
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use Tutor\Components\Nav;
use TUTOR\Dashboard;
use TUTOR\Icon;
use TUTOR\Input;

$user_id       = get_current_user_id();
$current_tab   = Input::get( 'tab', 'order-history' );
$replies       = Input::get( 'replies', 0, Input::TYPE_INT );
$order_filter  = Input::get( 'order', 'DESC' );
$current_page  = max( 1, Input::get( 'current_page', 1, Input::TYPE_INT ) );
$item_per_page = tutor_utils()->get_option( 'pagination_per_page', 10 );
$offset        = ( $current_page - 1 ) * $item_per_page;


$billing_url    = Dashboard::get_account_page_url( 'billing' );
$page_nav_items = array(
	array(
		'type'   => 'link',
		'label'  => __( 'Order History', 'tutor' ),
		'icon'   => Icon::HISTORY,
		'url'    => $billing_url,
		'active' => 'order-history' === $current_tab || empty( $current_tab ),
	),
);

$tab_template   = tutor_get_template( 'dashboard.account.billing.order-history' );
$tab_template   = apply_filters( 'tutor_dashboard_account_billing_tab_template', $tab_template, $current_tab );
$show_tab_nav   = apply_filters( 'tutor_dashboard_account_billing_show_tab_nav', true );
$page_nav_items = apply_filters( 'tutor_dashboard_account_billing_page_nav_items', $page_nav_items );
?>

<div class="tutor-billing-wrapper">
	<?php require_once tutor_get_template( 'account-header' ); ?>

	<div class="tutor-billing-container">
		<div class="tutor-flex tutor-flex-column tutor-gap-5 tutor-mt-9">
			<div class="tutor-surface-l1 tutor-border tutor-rounded-2xl">
				<?php if ( $show_tab_nav ) { ?>
				<div class="tutor-p-6 tutor-border-b">
					<?php Nav::make()->items( $page_nav_items )->render(); ?>
				</div>
				<?php } ?>
				<div class="tutor-sm-border tutor-sm-rounded-2xl tutor-sm-mt-4">
					<?php
					if ( file_exists( $tab_template ) ) {
						require_once $tab_template;
					}
					?>
				</div>
			</div>
		</div>
	</div>
</div>	
