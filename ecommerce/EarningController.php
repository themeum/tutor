<?php
/**
 * Manage earnings
 *
 * @package Tutor\Ecommerce
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 3.0.0
 */

namespace Tutor\Ecommerce;

/**
 * Manage earnings
 */
class EarningController {

	public static $instance = null;

	private $earning_data = array();

	private function __construct() {

	}

	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
	}

	public function prepare_order_earnings( $order_ids ) {

	}

	public function store_earnings() {

	}
}
