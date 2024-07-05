<?php
/**
 * Manage Coupon
 *
 * @package Tutor\Ecommerce
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 3.0.0
 */

namespace Tutor\Ecommerce;

use TUTOR\Backend_Page_Trait;
use Tutor\Helpers\HttpHelper;
use Tutor\Helpers\QueryHelper;
use TUTOR\Input;
use Tutor\Models\CouponModel;
use Tutor\Models\CourseModel;
use Tutor\Models\OrderModel;
use Tutor\Traits\JsonResponse;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * CouponController class
 *
 * @since 3.0.0
 */
class CouponController {

	/**
	 * Coupon model
	 *
	 * @since 3.0.0
	 *
	 * @var Object
	 */
	private $model;

	/**
	 * Trait for utilities
	 *
	 * @var $page_title
	 */
	use Backend_Page_Trait;

	/**
	 * Trait for sending JSON response
	 */
	use JsonResponse;

	/**
	 * Page Title
	 *
	 * @var $page_title
	 */
	public $page_title;

	/**
	 * Bulk Action
	 *
	 * @var $bulk_action
	 */
	public $bulk_action = true;

	/**
	 * Constructor.
	 *
	 * Initializes the Coupons class, sets the page title, and optionally registers
	 * hooks for handling AJAX requests related to coupon data, bulk actions, coupon status updates,
	 * and coupon deletions.
	 *
	 * @param bool $register_hooks Whether to register hooks for handling requests. Default is true.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function __construct( $register_hooks = true ) {
		$this->page_title = __( 'Coupons', 'tutor' );
		$this->model      = new CouponModel();

		if ( $register_hooks ) {
			// Register hooks here.
		}
	}

	/**
	 * Prepare bulk actions that will show on dropdown options
	 *
	 * @return array
	 * @since 3.0.0
	 */
	public function prepare_bulk_actions(): array {
		$actions = array(
			$this->bulk_action_default(),
			$this->bulk_action_publish(),
			$this->bulk_action_pending(),
			$this->bulk_action_draft(),
		);

		$active_tab = Input::get( 'data', '' );

		if ( 'trash' === $active_tab ) {
			array_push( $actions, $this->bulk_action_delete() );
		}
		if ( 'trash' !== $active_tab ) {
			array_push( $actions, $this->bulk_action_trash() );
		}
		return apply_filters( 'tutor_order_bulk_actions', $actions );
	}

	/**
	 * Available tabs that will visible on the right side of page navbar
	 *
	 * @return array
	 *
	 * @since 3.0.0
	 */
	public function tabs_key_value(): array {
		$url = get_pagenum_link();

		$date           = Input::get( 'date', '' );
		$payment_status = Input::get( 'payment-status', '' );
		$search         = Input::get( 'search', '' );

		$where = array();

		if ( '' !== $date ) {
			$where['created_at_gmt'] = tutor_get_formated_date( 'Y-m-d', $date );
		}

		if ( '' !== $payment_status ) {
			$where['payment_status'] = $payment_status;
		}

		$order_status = $this->model->get_coupon_status();

		$tabs = array();

		$tabs [] = array(
			'key'   => 'all',
			'title' => __( 'All', 'tutor' ),
			'value' => $this->model->get_coupon_count( $where, $search ),
			'url'   => $url . '&data=all',
		);

		foreach ( $order_status as $key => $value ) {
			$where['coupon_status'] = $key;

			$tabs[] = array(
				'key'   => $key,
				'title' => $value,
				'value' => $this->model->get_coupon_count( $where, $search ),
				'url'   => $url . '&data=' . $key,
			);
		}

		return apply_filters( 'tutor_coupon_tabs', $tabs );
	}

	/**
	 * Get coupons
	 *
	 * @since 3.0.0
	 *
	 * @param integer $limit List limit.
	 * @param integer $offset List offset.
	 *
	 * @return array
	 */
	public function get_coupons( $limit = 10, $offset = 0 ) {

		$active_tab = Input::get( 'data', 'all' );

		$date           = Input::get( 'date', '' );
		$search_term    = Input::get( 'search', '' );
		$payment_status = Input::get( 'payment-status', '' );

		$where_clause = array();

		// if ( $date ) {
		// 	$where_clause['date(o.created_at_gmt)'] = tutor_get_formated_date( '', $date );
		// }

		// if ( $payment_status ) {
		// 	$where_clause['o.payment_status'] = $payment_status;
		// }

		// if ( 'all' !== $active_tab ) {
		// 	$where_clause['o.order_status'] = $active_tab;
		// }

		$list_order    = Input::get( 'coupon', 'DESC' );
		$list_order_by = 'id';

		return $this->model->get_coupons( $where_clause, $search_term, $limit, $offset, $list_order_by, $list_order );
	}
}
