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
		add_filter( 'tutor_admin_menu', array( $this, 'register_menu' ) );
	}

	/**
	 * Register menu
	 *
	 * @since 3.0.0
	 * @since 3.8.0 param menu added.
	 *
	 * @param array $menu menu.
	 *
	 * @return array
	 */
	public function register_menu( $menu ) {
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

		$menu['group_two']['orders'] = array(
			'parent_slug' => 'tutor',
			'page_title'  => __( 'Orders', 'tutor' ),
			'menu_title'  => $order_menu_title,
			'capability'  => 'manage_options',
			'menu_slug'   => OrderController::PAGE_SLUG,
			'callback'    => array( $this, 'orders_view' ),
		);

		$menu['group_two']['coupons'] = array(
			'parent_slug' => 'tutor',
			'page_title'  => __( 'Coupons', 'tutor' ),
			'menu_title'  => __( 'Coupons', 'tutor' ),
			'capability'  => 'manage_options',
			'menu_slug'   => CouponController::PAGE_SLUG,
			'callback'    => array( $this, 'coupons_view' ),
		);

		return $menu;
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

		include apply_filters( 'tutor_order_list_page_template', tutor()->path . 'views/pages/ecommerce/order-list.php' );
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
