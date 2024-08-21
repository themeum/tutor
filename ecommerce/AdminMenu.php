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
		add_submenu_page( 'tutor', __( 'Orders', 'tutor' ), __( 'Orders', 'tutor' ), 'manage_options', OrderController::PAGE_SLUG, array( $this, 'orders_view' ) );
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
