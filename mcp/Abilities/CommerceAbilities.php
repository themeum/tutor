<?php
/**
 * Tutor MCP native ecommerce tools (orders, coupons, course price).
 *
 * @package Tutor\Mcp
 * @author  Themeum <support@themeum.com>
 * @link    https://themeum.com
 * @since   4.3.0
 */

namespace Tutor\Mcp\Abilities;

use Tutor\Models\CouponModel;
use Tutor\Models\OrderModel;
use Tutor\Mcp\Registrar;
use Tutor\Mcp\Support\AbilityFactory;
use Tutor\Mcp\Support\Pagination;
use Tutor\Mcp\Support\Permissions;
use TUTOR\Course;

defined( 'ABSPATH' ) || exit;

/**
 * Native ecommerce MCP abilities.
 *
 * @since 4.3.0
 */
class CommerceAbilities {

	/**
	 * Register commerce abilities.
	 *
	 * @since 4.3.0
	 *
	 * @return void
	 */
	public function register(): void {
		AbilityFactory::tool(
			'tutor-mcp/list-orders',
			'tutor-mcp-commerce',
			__( 'List Orders', 'tutor' ),
			__( 'List Tutor native ecommerce orders. Unsupported when monetize_by is WooCommerce/EDD.', 'tutor' ),
			array(
				'type'       => 'object',
				'properties' => array(
					'status'   => array( 'type' => 'string' ),
					'search'   => array( 'type' => 'string' ),
					'page'     => array(
						'type'    => 'integer',
						'minimum' => 1,
						'default' => 1,
					),
					'per_page' => array(
						'type'    => 'integer',
						'minimum' => 1,
						'maximum' => 50,
						'default' => 20,
					),
				),
			),
			static function (): bool {
				return current_user_can( 'manage_options' );
			},
			array( $this, 'list_orders' )
		);

		AbilityFactory::tool(
			'tutor-mcp/get-order',
			'tutor-mcp-commerce',
			__( 'Get Order', 'tutor' ),
			__( 'Get a Tutor native order with line items. No gateway secrets.', 'tutor' ),
			array(
				'type'       => 'object',
				'required'   => array( 'order_id' ),
				'properties' => array(
					'order_id' => array(
						'type'    => 'integer',
						'minimum' => 1,
					),
				),
			),
			static function (): bool {
				return current_user_can( 'manage_options' );
			},
			array( $this, 'get_order' )
		);

		AbilityFactory::tool(
			'tutor-mcp/mark-order-paid',
			'tutor-mcp-commerce',
			__( 'Mark Order Paid', 'tutor' ),
			__( 'Mark a native order as paid (may enroll, create earnings, send email). Requires confirm=true.', 'tutor' ),
			array(
				'type'       => 'object',
				'required'   => array( 'order_id', 'confirm' ),
				'properties' => array(
					'order_id' => array(
						'type'    => 'integer',
						'minimum' => 1,
					),
					'note'     => array( 'type' => 'string' ),
					'confirm'  => array(
						'type'    => 'boolean',
						'default' => false,
					),
				),
			),
			static function (): bool {
				return ! Registrar::is_read_only() && current_user_can( 'manage_options' );
			},
			array( $this, 'mark_order_paid' ),
			AbilityFactory::WRITE_HINTS
		);

		AbilityFactory::tool(
			'tutor-mcp/list-coupons',
			'tutor-mcp-commerce',
			__( 'List Coupons', 'tutor' ),
			__( 'List Tutor native ecommerce coupons.', 'tutor' ),
			array(
				'type'       => 'object',
				'properties' => array(
					'status'   => array( 'type' => 'string' ),
					'search'   => array( 'type' => 'string' ),
					'page'     => array(
						'type'    => 'integer',
						'minimum' => 1,
						'default' => 1,
					),
					'per_page' => array(
						'type'    => 'integer',
						'minimum' => 1,
						'maximum' => 50,
						'default' => 20,
					),
				),
			),
			static function (): bool {
				return current_user_can( 'manage_options' );
			},
			array( $this, 'list_coupons' )
		);

		AbilityFactory::tool(
			'tutor-mcp/create-coupon',
			'tutor-mcp-commerce',
			__( 'Create Coupon', 'tutor' ),
			__( 'Create a Tutor native coupon (code type).', 'tutor' ),
			array(
				'type'       => 'object',
				'required'   => array( 'coupon_code', 'discount_type', 'discount_amount' ),
				'properties' => array(
					'coupon_code'     => array( 'type' => 'string' ),
					'coupon_title'    => array( 'type' => 'string' ),
					'discount_type'   => array(
						'type' => 'string',
						'enum' => array( CouponModel::DISCOUNT_TYPE_PERCENTAGE, CouponModel::DISCOUNT_TYPE_FLAT ),
					),
					'discount_amount' => array( 'type' => 'number' ),
					'applies_to'      => array( 'type' => 'string' ),
					'start_date_gmt'  => array( 'type' => 'string' ),
					'expire_date_gmt' => array( 'type' => 'string' ),
				),
			),
			static function (): bool {
				return ! Registrar::is_read_only() && current_user_can( 'manage_options' );
			},
			array( $this, 'create_coupon' ),
			AbilityFactory::WRITE_HINTS
		);

		AbilityFactory::tool(
			'tutor-mcp/update-coupon',
			'tutor-mcp-commerce',
			__( 'Update Coupon', 'tutor' ),
			__( 'Update fields on a Tutor native coupon.', 'tutor' ),
			array(
				'type'       => 'object',
				'required'   => array( 'coupon_id' ),
				'properties' => array(
					'coupon_id'       => array(
						'type'    => 'integer',
						'minimum' => 1,
					),
					'coupon_title'    => array( 'type' => 'string' ),
					'discount_type'   => array(
						'type' => 'string',
						'enum' => array( CouponModel::DISCOUNT_TYPE_PERCENTAGE, CouponModel::DISCOUNT_TYPE_FLAT ),
					),
					'discount_amount' => array( 'type' => 'number' ),
					'coupon_status'   => array( 'type' => 'string' ),
					'expire_date_gmt' => array( 'type' => 'string' ),
				),
			),
			static function (): bool {
				return ! Registrar::is_read_only() && current_user_can( 'manage_options' );
			},
			array( $this, 'update_coupon' ),
			AbilityFactory::WRITE_HINTS
		);

		AbilityFactory::tool(
			'tutor-mcp/expire-coupon',
			'tutor-mcp-commerce',
			__( 'Expire Coupon', 'tutor' ),
			__( 'Set a coupon status to expired.', 'tutor' ),
			array(
				'type'       => 'object',
				'required'   => array( 'coupon_id' ),
				'properties' => array(
					'coupon_id' => array(
						'type'    => 'integer',
						'minimum' => 1,
					),
				),
			),
			static function (): bool {
				return ! Registrar::is_read_only() && current_user_can( 'manage_options' );
			},
			array( $this, 'expire_coupon' ),
			AbilityFactory::DESTRUCTIVE_HINTS
		);

		AbilityFactory::tool(
			'tutor-mcp/get-course-price',
			'tutor-mcp-commerce',
			__( 'Get Course Price', 'tutor' ),
			__( 'Get course price type and amounts for the active monetization mode.', 'tutor' ),
			array(
				'type'       => 'object',
				'required'   => array( 'course_id' ),
				'properties' => array(
					'course_id' => array(
						'type'    => 'integer',
						'minimum' => 1,
					),
				),
			),
			static function ( array $input ): bool {
				return Permissions::can_edit_course( absint( $input['course_id'] ?? 0 ) );
			},
			array( $this, 'get_course_price' )
		);

		AbilityFactory::tool(
			'tutor-mcp/set-course-price',
			'tutor-mcp-commerce',
			__( 'Set Course Price', 'tutor' ),
			__( 'Set Tutor-native course price type and amounts. Unsupported for WooCommerce/EDD.', 'tutor' ),
			array(
				'type'       => 'object',
				'required'   => array( 'course_id', 'price_type' ),
				'properties' => array(
					'course_id'     => array(
						'type'    => 'integer',
						'minimum' => 1,
					),
					'price_type'    => array(
						'type' => 'string',
						'enum' => array( Course::PRICE_TYPE_FREE, Course::PRICE_TYPE_PAID ),
					),
					'regular_price' => array( 'type' => 'number' ),
					'sale_price'    => array( 'type' => 'number' ),
				),
			),
			static function ( array $input ): bool {
				return ! Registrar::is_read_only() && Permissions::can_edit_course( absint( $input['course_id'] ?? 0 ) );
			},
			array( $this, 'set_course_price' ),
			AbilityFactory::WRITE_HINTS
		);
	}

	/**
	 * Require Tutor native monetization.
	 *
	 * @since 4.3.0
	 *
	 * @return true|\WP_Error
	 */
	private function require_native_ecommerce() {
		if ( tutor_utils()->is_monetize_by_tutor() ) {
			return true;
		}

		return new \WP_Error(
			'tutor_mcp_monetization_unsupported',
			__( 'This tool requires Tutor native ecommerce.', 'tutor' ),
			array(
				'monetize_by' => (string) tutor_utils()->get_option( 'monetize_by' ),
			)
		);
	}

	/**
	 * List orders.
	 *
	 * @since 4.3.0
	 *
	 * @param array $input Input.
	 * @return array|\WP_Error
	 */
	public function list_orders( array $input ) {
		$gate = $this->require_native_ecommerce();
		if ( is_wp_error( $gate ) ) {
			return $gate;
		}

		$bounds = Pagination::bounds( $input );
		$where  = array();
		if ( ! empty( $input['status'] ) ) {
			$where['order_status'] = sanitize_text_field( (string) $input['status'] );
		}

		$model  = new OrderModel();
		$result = $model->get_orders(
			$where,
			sanitize_text_field( (string) ( $input['search'] ?? '' ) ),
			$bounds['per_page'],
			$bounds['offset']
		);

		$items = isset( $result['results'] ) ? $result['results'] : array();
		$total = isset( $result['total_count'] ) ? (int) $result['total_count'] : count( (array) $items );

		return array(
			'items'    => json_decode( wp_json_encode( $items ), true ),
			'total'    => $total,
			'page'     => $bounds['page'],
			'per_page' => $bounds['per_page'],
		);
	}

	/**
	 * Get order details (PII-minimized).
	 *
	 * @since 4.3.0
	 *
	 * @param array $input Input.
	 * @return array|\WP_Error
	 */
	public function get_order( array $input ) {
		$gate = $this->require_native_ecommerce();
		if ( is_wp_error( $gate ) ) {
			return $gate;
		}

		$order_id = absint( $input['order_id'] ?? 0 );
		$order    = ( new OrderModel() )->get_order_by_id( $order_id );
		if ( ! $order ) {
			return new \WP_Error( 'tutor_mcp_not_found', __( 'Order not found.', 'tutor' ) );
		}

		$decoded = json_decode( wp_json_encode( $order ), true );
		if ( ! is_array( $decoded ) ) {
			return array();
		}

		if ( isset( $decoded['student'] ) && is_array( $decoded['student'] ) ) {
			unset( $decoded['student']['email'], $decoded['student']['phone'], $decoded['student']['billing_address'] );
		}

		return $decoded;
	}

	/**
	 * Mark order paid.
	 *
	 * @since 4.3.0
	 *
	 * @param array $input Input.
	 * @return array|\WP_Error
	 */
	public function mark_order_paid( array $input ) {
		$deny = Permissions::allow_write();
		if ( is_wp_error( $deny ) ) {
			return $deny;
		}

		$gate = $this->require_native_ecommerce();
		if ( is_wp_error( $gate ) ) {
			return $gate;
		}

		if ( empty( $input['confirm'] ) ) {
			return new \WP_Error( 'tutor_mcp_confirm_required', __( 'Marking paid requires confirm=true.', 'tutor' ) );
		}

		$order_id = absint( $input['order_id'] ?? 0 );
		$note     = sanitize_textarea_field( (string) ( $input['note'] ?? '' ) );
		$model    = new OrderModel();
		$order    = $model->get_order_by_id( $order_id );
		if ( ! $order ) {
			return new \WP_Error( 'tutor_mcp_not_found', __( 'Order not found.', 'tutor' ) );
		}

		$ok = $model->mark_as_paid( $order_id, $note, true );
		if ( ! $ok ) {
			return new \WP_Error( 'tutor_mcp_failed', __( 'Could not mark order as paid.', 'tutor' ) );
		}

		$updated = $model->get_order_by_id( $order_id );

		return array(
			'order_id'       => $order_id,
			'payment_status' => $updated->payment_status ?? OrderModel::PAYMENT_PAID,
			'order_status'   => $updated->order_status ?? OrderModel::ORDER_COMPLETED,
		);
	}

	/**
	 * List coupons.
	 *
	 * @since 4.3.0
	 *
	 * @param array $input Input.
	 * @return array|\WP_Error
	 */
	public function list_coupons( array $input ) {
		$gate = $this->require_native_ecommerce();
		if ( is_wp_error( $gate ) ) {
			return $gate;
		}

		$bounds = Pagination::bounds( $input );
		$where  = array();
		if ( ! empty( $input['status'] ) ) {
			$where['coupon_status'] = sanitize_text_field( (string) $input['status'] );
		}

		$result = ( new CouponModel() )->get_coupons(
			$where,
			sanitize_text_field( (string) ( $input['search'] ?? '' ) ),
			$bounds['per_page'],
			$bounds['offset']
		);

		$items = isset( $result['results'] ) ? $result['results'] : array();
		$total = isset( $result['total_count'] ) ? (int) $result['total_count'] : count( (array) $items );

		return array(
			'items'    => json_decode( wp_json_encode( $items ), true ),
			'total'    => $total,
			'page'     => $bounds['page'],
			'per_page' => $bounds['per_page'],
		);
	}

	/**
	 * Create coupon.
	 *
	 * @since 4.3.0
	 *
	 * @param array $input Input.
	 * @return array|\WP_Error
	 */
	public function create_coupon( array $input ) {
		$deny = Permissions::allow_write();
		if ( is_wp_error( $deny ) ) {
			return $deny;
		}

		$gate = $this->require_native_ecommerce();
		if ( is_wp_error( $gate ) ) {
			return $gate;
		}

		$code   = sanitize_text_field( (string) ( $input['coupon_code'] ?? '' ) );
		$type   = sanitize_text_field( (string) ( $input['discount_type'] ?? '' ) );
		$amount = (float) ( $input['discount_amount'] ?? 0 );
		if ( '' === $code || $amount <= 0 ) {
			return new \WP_Error( 'tutor_mcp_invalid', __( 'coupon_code and positive discount_amount are required.', 'tutor' ) );
		}
		if ( ! in_array( $type, array( CouponModel::DISCOUNT_TYPE_PERCENTAGE, CouponModel::DISCOUNT_TYPE_FLAT ), true ) ) {
			return new \WP_Error( 'tutor_mcp_invalid', __( 'Invalid discount_type.', 'tutor' ) );
		}

		$model = new CouponModel();
		if ( $model->get_coupon( array( 'coupon_code' => $code ) ) ) {
			return new \WP_Error( 'tutor_mcp_invalid', __( 'Coupon code already exists!', 'tutor' ) );
		}

		$data = array(
			'coupon_code'     => $code,
			'coupon_title'    => sanitize_text_field( (string) ( $input['coupon_title'] ?? $code ) ),
			'coupon_type'     => CouponModel::TYPE_CODE,
			'coupon_status'   => CouponModel::STATUS_ACTIVE,
			'discount_type'   => $type,
			'discount_amount' => $amount,
			'applies_to'      => sanitize_text_field( (string) ( $input['applies_to'] ?? CouponModel::APPLIES_TO_ALL_COURSES ) ),
			'start_date_gmt'  => sanitize_text_field( (string) ( $input['start_date_gmt'] ?? current_time( 'mysql', true ) ) ),
			'created_by'      => get_current_user_id(),
			'created_at_gmt'  => current_time( 'mysql', true ),
			'updated_at_gmt'  => current_time( 'mysql', true ),
		);
		if ( ! empty( $input['expire_date_gmt'] ) ) {
			$data['expire_date_gmt'] = sanitize_text_field( (string) $input['expire_date_gmt'] );
		}

		try {
			$coupon_id = $model->create_coupon( $data );
		} catch ( \Throwable $th ) {
			return new \WP_Error( 'tutor_mcp_failed', $th->getMessage() );
		}

		if ( ! $coupon_id ) {
			return new \WP_Error( 'tutor_mcp_failed', __( 'Failed to create coupon.', 'tutor' ) );
		}

		return array(
			'coupon_id'   => (int) $coupon_id,
			'coupon_code' => $code,
		);
	}

	/**
	 * Update coupon.
	 *
	 * @since 4.3.0
	 *
	 * @param array $input Input.
	 * @return array|\WP_Error
	 */
	public function update_coupon( array $input ) {
		$deny = Permissions::allow_write();
		if ( is_wp_error( $deny ) ) {
			return $deny;
		}

		$gate = $this->require_native_ecommerce();
		if ( is_wp_error( $gate ) ) {
			return $gate;
		}

		$coupon_id = absint( $input['coupon_id'] ?? 0 );
		$data      = array( 'updated_at_gmt' => current_time( 'mysql', true ) );
		foreach ( array( 'coupon_title', 'discount_type', 'coupon_status', 'expire_date_gmt' ) as $field ) {
			if ( isset( $input[ $field ] ) ) {
				$data[ $field ] = sanitize_text_field( (string) $input[ $field ] );
			}
		}
		if ( isset( $input['discount_amount'] ) ) {
			$data['discount_amount'] = (float) $input['discount_amount'];
		}

		$ok = ( new CouponModel() )->update_coupon( $coupon_id, $data );
		if ( ! $ok ) {
			return new \WP_Error( 'tutor_mcp_failed', __( 'Failed to update coupon.', 'tutor' ) );
		}

		return array( 'coupon_id' => $coupon_id );
	}

	/**
	 * Expire coupon.
	 *
	 * @since 4.3.0
	 *
	 * @param array $input Input.
	 * @return array|\WP_Error
	 */
	public function expire_coupon( array $input ) {
		$deny = Permissions::allow_write();
		if ( is_wp_error( $deny ) ) {
			return $deny;
		}

		$gate = $this->require_native_ecommerce();
		if ( is_wp_error( $gate ) ) {
			return $gate;
		}

		$coupon_id = absint( $input['coupon_id'] ?? 0 );
		$ok        = ( new CouponModel() )->update_coupon(
			$coupon_id,
			array(
				'coupon_status'  => CouponModel::STATUS_EXPIRED,
				'updated_at_gmt' => current_time( 'mysql', true ),
			)
		);
		if ( ! $ok ) {
			return new \WP_Error( 'tutor_mcp_failed', __( 'Failed to expire coupon.', 'tutor' ) );
		}

		return array(
			'coupon_id' => $coupon_id,
			'expired'   => true,
		);
	}

	/**
	 * Get course price summary.
	 *
	 * @since 4.3.0
	 *
	 * @param array $input Input.
	 * @return array|\WP_Error
	 */
	public function get_course_price( array $input ) {
		$course_id = absint( $input['course_id'] ?? 0 );
		if ( tutor()->course_post_type !== get_post_type( $course_id ) ) {
			return new \WP_Error( 'tutor_mcp_not_found', __( 'Course not found.', 'tutor' ) );
		}

		$prices = tutor_utils()->get_raw_course_price( $course_id );

		return array(
			'course_id'     => $course_id,
			'monetize_by'   => (string) tutor_utils()->get_option( 'monetize_by' ),
			'price_type'    => (string) get_post_meta( $course_id, Course::COURSE_PRICE_TYPE_META, true ),
			'regular_price' => (float) ( $prices->regular_price ?? 0 ),
			'sale_price'    => (float) ( $prices->sale_price ?? 0 ),
		);
	}

	/**
	 * Set Tutor-native course price.
	 *
	 * @since 4.3.0
	 *
	 * @param array $input Input.
	 * @return array|\WP_Error
	 */
	public function set_course_price( array $input ) {
		$deny = Permissions::allow_write();
		if ( is_wp_error( $deny ) ) {
			return $deny;
		}

		$gate = $this->require_native_ecommerce();
		if ( is_wp_error( $gate ) ) {
			return $gate;
		}

		$course_id  = absint( $input['course_id'] ?? 0 );
		$price_type = sanitize_text_field( (string) ( $input['price_type'] ?? '' ) );
		if ( tutor()->course_post_type !== get_post_type( $course_id ) ) {
			return new \WP_Error( 'tutor_mcp_not_found', __( 'Course not found.', 'tutor' ) );
		}
		if ( ! in_array( $price_type, array( Course::PRICE_TYPE_FREE, Course::PRICE_TYPE_PAID ), true ) ) {
			return new \WP_Error( 'tutor_mcp_invalid', __( 'Invalid price_type.', 'tutor' ) );
		}

		$regular = isset( $input['regular_price'] ) ? (float) $input['regular_price'] : 0;
		$sale    = isset( $input['sale_price'] ) ? (float) $input['sale_price'] : 0;

		if ( Course::PRICE_TYPE_PAID === $price_type ) {
			if ( $regular <= 0 ) {
				return new \WP_Error( 'tutor_mcp_invalid', __( 'Paid courses require a positive regular_price.', 'tutor' ) );
			}
			if ( $sale > 0 && $sale >= $regular ) {
				return new \WP_Error( 'tutor_mcp_invalid', __( 'Sale price must be less than regular price.', 'tutor' ) );
			}
		}

		update_post_meta( $course_id, Course::COURSE_PRICE_TYPE_META, $price_type );
		if ( Course::PRICE_TYPE_PAID === $price_type ) {
			update_post_meta( $course_id, Course::COURSE_PRICE_META, $regular );
			update_post_meta( $course_id, Course::COURSE_SALE_PRICE_META, $sale > 0 ? $sale : '' );
		} else {
			update_post_meta( $course_id, Course::COURSE_PRICE_META, 0 );
			delete_post_meta( $course_id, Course::COURSE_SALE_PRICE_META );
		}

		return $this->get_course_price( array( 'course_id' => $course_id ) );
	}
}
