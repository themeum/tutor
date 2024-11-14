<?php
/**
 * AdminMenu class for registering menu
 *
 * @package Tutor\Ecommerce
 * @author Themeum
 * @link https://themeum.com
 * @since 3.0.0
 */

namespace Tutor\Ecommerce;

use TUTOR\Input;
use Tutor\Models\OrderModel;

/**
 * Register ecommerce menu
 */
class AdminMenu {
	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'tutor_after_courses_admin_menu', array( $this, 'register_menu' ) );
	}

	/**
	 * Register menu
	 *
	 * @return void
	 */
	public function register_menu() {
		$order_menu_title  = __( 'Orders', 'tutor' );
		$order_badge_count = get_transient( OrderModel::TRANSIENT_ORDER_BADGE_COUNT );

		if ( false === $order_badge_count ) {
			$order_badge_count = ( new OrderModel() )->get_order_count(
				array(
					'payment_status' => OrderModel::PAYMENT_UNPAID,
					'order_type'     => OrderModel::TYPE_SINGLE_ORDER,
				)
			);
			set_transient( OrderModel::TRANSIENT_ORDER_BADGE_COUNT, $order_badge_count, HOUR_IN_SECONDS );
		}

		if ( $order_badge_count ) {
			$order_menu_title .= ' <span class="update-plugins"><span class="plugin-count">' . $order_badge_count . '</span></span>';
		}

		add_submenu_page( 'tutor', __( 'Orders', 'tutor' ), $order_menu_title, 'manage_options', OrderController::PAGE_SLUG, array( $this, 'orders_view' ) );
		do_action( 'tutor_after_orders_admin_menu' );
		add_submenu_page( 'tutor', __( 'Coupons', 'tutor' ), __( 'Coupons', 'tutor' ), 'manage_options', CouponController::PAGE_SLUG, array( $this, 'coupons_view' ) );
	}

	/**
	 * Orders view page
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function orders_view() {
		$current_page = Input::get( 'page' );
		$action       = Input::get( 'action' );

		if ( OrderController::PAGE_SLUG === $current_page && 'edit' === $action ) {
			?>
				<div class="tutor-admin-wrap tutor-order-details-wrapper">
					<div id="tutor-order-details-root">
					</div>
				</div>
			<?php
			return;
		}

		include tutor()->path . 'views/pages/ecommerce/order-list.php';
	}

	/**
	 * Coupons view page
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function coupons_view() {
		$action = Input::get( 'action' );
		if ( in_array( $action, array( 'add_new', 'edit' ) ) ) {
			?>
				<div class="tutor-admin-wrap">
					<div id="tutor-coupon-root">
					</div>
				</div>
			<?php
			return;
		}
		include tutor()->path . 'views/pages/ecommerce/coupon-list.php';
	}
}
