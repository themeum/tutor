<?php
/**
 * Order Item Model
 * Handles CRUD operations for order item data.
 *
 * @package Tutor\Models
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 3.8.0
 */

namespace Tutor\Models;

use Tutor\Models\BaseModel;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * OrderItemModel class
 * Handles CRUD operations for order item data.
 *
 * @since 3.8.0
 */
class OrderItemModel extends BaseModel {

	/**
	 * Table name without prefix
	 *
	 * @since 3.8.0
	 *
	 * @var string
	 */
	protected $table_name = 'tutor_order_items';
}
