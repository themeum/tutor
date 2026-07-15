<?php
/**
 * Integrate EDD
 *
 * @package Tutor\PaymentIntegration
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.0.0
 */

namespace TUTOR;

defined( 'ABSPATH' ) || exit;

use Tutor\Helpers\QueryHelper;
use Tutor\Helpers\UrlHelper;
use Tutor\Models\EnrollmentModel;

/**
 * Manage EDD integration
 *
 * @since 1.0.0
 */
class TutorEDD extends Tutor_Base {

	/**
	 * Register hooks
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		parent::__construct();

		add_filter( 'tutor_monetization_options', array( $this, 'tutor_monetization_options' ) );

		$monetize_by = tutils()->get_option( 'monetize_by' );
		if ( 'edd' !== $monetize_by ) {
			return;
		}

		$edd_path = WP_PLUGIN_DIR . '/easy-digital-downloads/easy-digital-downloads.php';
		register_deactivation_hook( $edd_path, array( $this, 'edd_deactivation_handler' ) );

		add_filter( 'tutor_course_sell_by', fn() => 'edd' );
		add_action( 'save_post_' . $this->course_post_type, array( $this, 'save_course_meta' ) );

		/**
		 * Is Course Purchasable
		 */
		add_filter( 'is_course_purchasable', array( $this, 'is_course_purchasable' ), 10, 2 );
		add_filter( 'get_tutor_course_price', array( $this, 'get_tutor_course_price' ), 10, 2 );
		add_action( 'edd_insert_payment', array( $this, 'edd_order_created' ), 10, 2 );
		add_action( 'edd_update_payment_status', array( $this, 'edd_update_payment_status' ), 10, 3 );

		// @since 4.0.0
		add_filter( 'tutor_order_history_card_template', fn( $template ) => tutor_get_template( 'dashboard.account.billing.edd-order-history-card' ) );
		add_filter( 'tutor_order_history_status_options', array( $this, 'filter_order_history_status_options' ), 10, 2 );
		add_filter( 'tutor_get_orders_by_user_id', array( $this, 'filter_tutor_get_orders_by_user_id' ), 10, 3 );
	}

	/**
	 * Handle EDD deactivation.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function edd_deactivation_handler() {
		if ( 'edd' === tutor_utils()->get_option( 'monetize_by' ) ) {
			tutor_utils()->update_option( 'monetize_by', 'free' );
		}
	}

	/**
	 * Add Option for tutor
	 *
	 * @since 1.0.0
	 *
	 * @param array $attr option attrs.
	 *
	 * @return mixed
	 */
	public function add_options( $attr ) {
		$attr['tutor_edd'] = array(
			'label'    => __( 'EDD', 'tutor' ),

			'sections' => array(
				'general' => array(
					'label'  => __( 'General', 'tutor' ),
					'desc'   => __( 'Tutor Course Attachments Settings', 'tutor' ),
					'fields' => array(
						'enable_tutor_edd' => array(
							'type'  => 'checkbox',
							'label' => __( 'Enable EDD', 'tutor' ),
							'desc'  => __( 'This will enable sell your product via EDD', 'tutor' ),
						),
					),
				),
			),
		);
		return $attr;
	}

	/**
	 * Returning monetization options
	 *
	 * @since 1.3.5
	 *
	 * @param array $arr monetization attrs.
	 *
	 * @return array
	 */
	public function tutor_monetization_options( $arr ) {
		$has_edd = tutils()->has_edd();
		if ( $has_edd ) {
			$arr['edd'] = __( 'Easy Digital Downloads', 'tutor' );
		}
		return $arr;
	}

	/**
	 * Save course meta
	 *
	 * @since 1.0.0
	 *
	 * @param int $post_ID post id.
	 *
	 * @return void
	 */
	public function save_course_meta( $post_ID ) {

		$product_id = Input::post( Course::COURSE_PRODUCT_ID_META, '' );

		if ( '-1' !== $product_id ) {
			$product_id = (int) $product_id;
			if ( $product_id ) {
				update_post_meta( $post_ID, Course::COURSE_PRODUCT_ID_META, $product_id );
				update_post_meta( $product_id, '_tutor_product', 'yes' );
			}
		} else {
			delete_post_meta( $post_ID, Course::COURSE_PRODUCT_ID_META );
		}

		do_action( 'save_tutor_course', $post_ID, get_post( $post_ID ) );
	}

	/**
	 * Check if course is purchase able
	 *
	 * @param bool $bool default value.
	 * @param int  $course_id course id.
	 *
	 * @return boolean
	 */
	public function is_course_purchasable( $bool, $course_id ) {
		if ( ! tutor_utils()->has_edd() ) {
			return false;
		}

		$course_id      = tutor_utils()->get_post_id( $course_id );
		$price_type     = tutor_utils()->price_type( $course_id );
		$has_product_id = tutor_utils()->get_course_product_id( $course_id );

		if ( Course::PRICE_TYPE_PAID === $price_type && $has_product_id ) {
			return true;
		}

		return false;
	}

	/**
	 * Get course price
	 *
	 * @since 1.0.0
	 *
	 * @param string $price course price.
	 * @param int    $course_id course id.
	 *
	 * @return mixed
	 */
	public function get_tutor_course_price( $price, $course_id ) {
		$product_id = tutor_utils()->get_course_product_id( $course_id );
		if ( tutils()->has_edd() ) {
			return edd_price( $product_id, false );
		}
	}

	/**
	 * Prepare enrollment status.
	 *
	 * @since 4.0.0
	 *
	 * @param string $order_status order status.
	 *
	 * @return string
	 */
	private static function prepare_enrollment_status( $order_status ) {
		return 'complete' === $order_status
				? EnrollmentModel::STATUS_COMPLETED
				: ( 'pending' === $order_status ? EnrollmentModel::STATUS_PENDING : EnrollmentModel::STATUS_CANCEL );
	}

	/**
	 * Handle enrollment.
	 *
	 * @since 4.0.0
	 *
	 * @param int    $order_id          The order ID.
	 * @param string $enrollment_type   The enrollment type.
	 * @param string $enrollment_status The enrollment status.
	 *
	 * @return void
	 */
	private static function handle_enrollment( int $order_id, string $enrollment_type, string $enrollment_status ): void {
		$order = edd_get_order( $order_id );
		if ( ! $order || ! is_object( $order ) ) {
			return;
		}

		$order_items = edd_get_order_items( array( 'order_id' => $order->id ) );
		if ( empty( $order_items ) ) {
			return;
		}

		$user_id = $order->user_id;

		foreach ( $order_items as $order_item ) {
			$product_id         = $order_item->product_id;
			$product_has_course = tutor_utils()->product_belongs_with_course( $product_id );

			if ( ! $product_has_course ) {
				continue;
			}

			$course_id = (int) $product_has_course->post_id;

			if ( 'new' === $enrollment_type ) {
				// New enrollment.
				add_filter( 'tutor_enroll_data', fn( $data ) => array_merge( $data, array( 'post_status' => $enrollment_status ) ) );
				EnrollmentModel::do_enroll( $course_id, $order_id, $user_id );
			} else {
				// Update enrollment.
				$is_enrolled = EnrollmentModel::is_enrolled( $course_id, $user_id, false );
				if ( $is_enrolled ) {
					EnrollmentModel::update_enrollments( $enrollment_status, array( $is_enrolled->ID ) );
				}
			}
		}
	}

	/**
	 * Handle enrollment when a new EDD order is created.
	 *
	 * Creates enrollment with appropriate status when EDD 3.0+ has fully
	 * built the order with its items. This avoids the issue where
	 * cart_details/order_items are empty during the initial
	 * edd_update_payment_status hook (old_status = 'new').
	 *
	 * @since 4.0.0
	 *
	 * @param int   $order_id   The order ID.
	 * @param array $order_data The order data array containing cart details.
	 *
	 * @return void
	 */
	public function edd_order_created( $order_id, $order_data ) {
		if ( ! $order_id ) {
			return;
		}

		$enrollment_status = self::prepare_enrollment_status( $order_data['status'] );
		self::handle_enrollment( $order_id, 'new', $enrollment_status );
	}

	/**
	 * Update enrollment status when EDD payment status changes.
	 *
	 * Skips the initial order creation (old_status = 'new') since enrollment
	 * is handled by edd_order_created. Uses EDD 3.0+ API to retrieve order
	 * items reliably.
	 *
	 * @since 1.0.0
	 * @since 4.0.0 Refactored to use EDD 3.0+ API and skip initial order creation.
	 *
	 * @param int    $payment_id payment id.
	 * @param string $new_status payment's new status.
	 * @param string $old_status payment's old status.
	 *
	 * @return void
	 */
	public function edd_update_payment_status( $payment_id, $new_status, $old_status ) {
		if ( empty( $new_status ) || ! $payment_id ) {
			return;
		}

		/**
		 * If 'new' === $old_status, EDD does not store cart_details.
		 * So skip the action, as enrollment is handled by edd_order_created method.
		 *
		 * @since 4.0.0
		 */
		if ( 'new' === $old_status ) {
			return;
		}

		$enrollment_status = self::prepare_enrollment_status( $new_status );
		self::handle_enrollment( $payment_id, 'update', $enrollment_status );
	}

	/**
	 * Filter order history status options.
	 *
	 * @since 4.0.0
	 *
	 * @param array  $options the status options.
	 * @param string $seleted the selected status.
	 *
	 * @return array<array{label: string, value: string, count: int, url: string, active: bool}>
	 */
	public function filter_order_history_status_options( $options, $seleted ) {
		$url     = get_pagenum_link();
		$user_id = get_current_user_id();

		$statuses = array_merge( array( 'all' => 'All' ), edd_get_payment_statuses() );
		$options  = array();

		foreach ( $statuses as $key => $status ) {
			$params = array(
				'meta_key' => Course::IS_TUTOR_ORDER_FOR_COURSE_META,
				'user_id'  => $user_id,
				'status'   => $key,
				'count'    => true,
			);

			$count_query = new \EDD_Payments_Query( $params );
			$count       = $count_query->get_payments();

			$options[] = array(
				'label'  => $status,
				'value'  => $key,
				'count'  => $count,
				'url'    => UrlHelper::add_query_params( $url, array( 'data' => $key ) ),
				'active' => $key === $seleted || ( empty( $key ) && 'all' === $seleted ),
			);
		}

		return $options;
	}

	/**
	 * Filter tutor get orders by user id.
	 *
	 * @since 4.0.0
	 *
	 * @param object $data data.
	 * @param int    $user_id the user id.
	 * @param array  $args the query args.
	 *
	 * @return object
	 */
	public function filter_tutor_get_orders_by_user_id( $data, $user_id, $args ) {
		if ( ! $user_id ) {
			return $data;
		}

		$status     = Input::sanitize( $args['status'] ?? '' );
		$start_date = Input::sanitize( $args['start_date'] ?? '' );
		$end_date   = Input::sanitize( $args['end_date'] ?? '' );
		$order      = QueryHelper::get_valid_sort_order( $args['order'] ?? 'DESC' );
		$limit      = intval( $args['limit'] ?? 0 );
		$offset     = intval( $args['offset'] ?? 0 );

		$params = array(
			'meta_key'   => Course::IS_TUTOR_ORDER_FOR_COURSE_META,
			'user'       => $user_id,
			'status'     => $status,
			'start_date' => $start_date,
			'end_date'   => $end_date,
			'limit'      => $limit,
			'offset'     => $offset,
			'order'      => $order,
		);

		if ( empty( $status ) || 'all' === $status ) {
			unset( $params['status'] );
		}

		$edd_query = new \EDD_Payments_Query( $params );
		$results   = $edd_query->get_payments();

		$params['count']   = true;
		$total_count_query = new \EDD_Payments_Query( $params );
		$total_count       = $total_count_query->get_payments();

		$data->results     = $results;
		$data->total_count = $total_count;

		return $data;
	}
}
