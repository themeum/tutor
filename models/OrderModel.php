<?php
/**
 * Order Model
 *
 * @package Tutor\Models
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.6
 */

namespace Tutor\Models;

use Tutor\Helpers\QueryHelper;

/**
 * OrderModel Class
 *
 * @since 2.0.6
 */
class OrderModel {
	/**
	 * WordPress order type name
	 *
	 * @var string
	 */
	const POST_TYPE = 'orders';

	const STATUS_PUBLISH    = 'publish';
	const STATUS_DRAFT      = 'draft';
	const STATUS_AUTO_DRAFT = 'auto-draft';
	const STATUS_PENDING    = 'pending';
	const STATUS_PRIVATE    = 'private';
	const STATUS_FUTURE     = 'future';

	/**
	 * Delete an order by ID
	 *
	 * @since 2.0.9
	 *
	 * @param int $order_id  order id that need to delete.
	 * @return bool
	 */
	public static function delete_course( $order_id ) {
		// if ( get_post_type( $post_id ) !== tutor()->course_post_type ) {
		// 	return false;
		// }

		// wp_delete_post( $post_id, true );
		return true;
	}
}
