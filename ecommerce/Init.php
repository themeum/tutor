<?php
/**
 * Init
 *
 * @package Tutor\Ecommerce
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 3.0.0
 */

namespace Tutor\Ecommerce;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * OrderController class
 *
 * @since 2.0.0
 */
class Init {
	public function __construct()
	{
		new OrderController();
	}
}