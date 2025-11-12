<?php
/**
 * Handle ecommerce hooks
 *
 * @package Tutor\Ecommerce
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 3.0.0
 */

namespace Tutor\Ecommerce;

use TUTOR\Input;
use TUTOR\Course;
use TUTOR\Earnings;
use Tutor\Models\CartModel;
use Tutor\Models\OrderModel;
use Tutor\Helpers\QueryHelper;
use Tutor\Models\OrderMetaModel;
use Tutor\Models\OrderActivitiesModel;
use TutorPro\CourseBundle\Models\BundleModel;
use TutorPro\CourseBundle\CustomPosts\CourseBundle;

/**
 * Handle custom hooks
 */
class HooksHandler {

	/**
	 * OrderModel
	 *
	 * @since 3.0.0
	 *
	 * @var OrderModel
	 */
	private $order_model;

	/**
	 * OrderActivitiesModel
	 *
	 * @since 3.0.0
	 *
	 * @var OrderActivitiesModel
	 */
	private $order_activities_model;

	/**
	 * Coupon controller instance
	 *
	 * @since 3.5.0
	 *
	 * @var CouponController
	 */
	private $coupon_ctrl;

	/**
	 * Register hooks & resolve props
	 *
	 * @since 3.0.0
	 */
	public function __construct() {
		$this->order_activities_model = new OrderActivitiesModel();
		$this->order_model            = new OrderModel();
		$this->coupon_ctrl            = new CouponController( false );

		// Register hooks.
		add_filter( 'tutor_course_sell_by', array( $this, 'alter_course_sell_by' ) );
		add_filter( 'get_tutor_course_price', array( $this, 'alter_course_price' ), 10, 2 );

		// Order hooks.
		add_action( 'tutor_order_payment_updated', array( $this, 'handle_payment_updated_webhook' ) );

		add_action( 'tutor_order_payment_status_changed', array( $this, 'handle_payment_status_changed' ), 10, 3 );

		add_action( 'tutor_order_placement_success', array( $this, 'handle_order_placement_success' ) );

		/**
		 * Clear order menu badge count
		 *
		 * @since 3.0.0
		 */
		add_action( 'tutor_order_placed', array( $this, 'clear_order_badge_count' ) );
		add_action( 'tutor_order_payment_status_changed', array( $this, 'clear_order_badge_count' ) );
		add_action( 'tutor_before_order_bulk_action', array( $this, 'clear_order_badge_count' ) );
		add_filter( 'tutor_before_order_create', array( $this, 'update_order_data' ) );
		add_action( 'tutor_order_placed', array( $this, 'handle_free_checkout' ) );
		add_filter( 'tutor_redirect_url_after_checkout', array( $this, 'redirect_to_the_course' ), 10, 3 );

		/**
		 * Store customer billing information for each order.
		 *
		 * @since 3.5.0
		 */
		add_action( 'tutor_order_placed', array( $this, 'store_billing_address_for_order' ) );
		add_action( 'tutor_order_updated', array( $this, 'store_billing_address_for_order' ) );
	}

	/**
	 * Clear order menu badge count
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function clear_order_badge_count() {
		delete_transient( OrderModel::TRANSIENT_ORDER_BADGE_COUNT );
	}

	/**
	 * Store order activity before bulk action.
	 *
	 * @since 3.0.0
	 *
	 * @param string $bulk_action The bulk action being performed.
	 * @param array  $bulk_ids The IDs of the orders being acted upon.
	 *
	 * @return void
	 */
	public function after_order_bulk_action( $bulk_action, $bulk_ids ) {
		$order_status = $this->order_model->get_order_status_by_payment_status( $bulk_action );

		$cancel_reason = Input::post( 'cancel_reason', '' );
		foreach ( $bulk_ids as $order_id ) {
			try {
				$this->manage_earnings_and_enrollments( $order_status, $order_id );
				$data = (object) array(
					'order_id'   => $order_id,
					'meta_key'   => $this->order_activities_model::META_KEY_HISTORY,
					'meta_value' => "Order mark as {$bulk_action} {$cancel_reason}",
				);
				$this->order_activities_model->add_order_meta( $data );
			} catch ( \Throwable $th ) {
				// Log message with line & file.
				error_log( $th->getMessage() . ' in ' . $th->getFile() . ' at line ' . $th->getLine() );
			}
		}
	}

	/**
	 * Alter course sell by value
	 *
	 * @since 3.0.0
	 *
	 * @param mixed $sell_by Default sell by.
	 *
	 * @return mixed
	 */
	public function alter_course_sell_by( $sell_by ) {
		if ( tutor_utils()->is_monetize_by_tutor() ) {
			$sell_by = Ecommerce::MONETIZE_BY;
		}

		return $sell_by;
	}

	/**
	 * Alter course price to show price on the course
	 * entry box
	 *
	 * @since 3.0.0
	 *
	 * @param mixed $price Course price.
	 * @param int   $course_id Course id.
	 *
	 * @return mixed
	 */
	public function alter_course_price( $price, $course_id ) {
		$price_type = tutor_utils()->price_type( $course_id );
		if ( tutor_utils()->is_monetize_by_tutor() && Course::PRICE_TYPE_PAID === $price_type ) {
			$price = tutor_get_course_formatted_price_html( $course_id, false );
		}

		return $price;
	}

	/**
	 * Handle payment updated webhook
	 *
	 * @since 3.0.0
	 *
	 * @param object $res Response data.
	 * {order_id, transaction_id, payment_status, payment_method, redirectUrl}.
	 *
	 * @return void
	 */
	public function handle_payment_updated_webhook( $res ) {
		$order_id           = $res->id;
		$new_payment_status = $res->payment_status;
		$transaction_id     = $res->transaction_id;

		$order_details = $this->order_model->get_order_by_id( $order_id );
		if ( $order_details ) {
			$prev_payment_status = $order_details->payment_status;

			$order_data = array(
				'order_status'     => $order_details->order_status,
				'payment_status'   => $new_payment_status,
				'payment_method'   => $res->payment_method,
				'payment_payloads' => $res->payment_payload,
				'transaction_id'   => $transaction_id,
				'earnings'         => $res->earnings,
				'fees'             => $res->fees,
				'updated_at_gmt'   => current_time( 'mysql', true ),
			);

			switch ( $new_payment_status ) {
				case $this->order_model::PAYMENT_PAID:
					$order_data['order_status'] = $this->order_model::ORDER_COMPLETED;
					break;
				case $this->order_model::PAYMENT_FAILED:
				case $this->order_model::PAYMENT_REFUNDED:
					$order_data['order_status'] = $this->order_model::ORDER_CANCELLED;
					break;
			}

			$update = $this->order_model->update_order( $order_id, $order_data );
			if ( $update ) {
				// Provide hook after update order.
				do_action( 'tutor_order_payment_status_changed', $order_id, $prev_payment_status, $new_payment_status );
			}
		}
	}

	/**
	 * Update enrollment & earnings based on payment status
	 *
	 * @since 3.0.0
	 *
	 * @param int    $order_id Order id.
	 * @param string $prev_payment_status previous payment status.
	 * @param string $new_payment_status new payment status.
	 *
	 * @return void
	 */
	public function handle_payment_status_changed( $order_id, $prev_payment_status, $new_payment_status ) {

		$order_status = $this->order_model->get_order_status_by_payment_status( $new_payment_status );

		$cancel_reason     = Input::post( 'cancel_reason' );
		$remove_enrollment = Input::post( 'is_remove_enrolment', false, Input::TYPE_BOOL );

		// Store activity.
		$data = (object) array(
			'order_id'   => $order_id,
			'meta_key'   => $this->order_activities_model::META_KEY_HISTORY,
			'meta_value' => 'Order marked as ' . $new_payment_status,
		);

		if ( $cancel_reason ) {
			$meta_value       = array(
				'message'       => 'Order marked as ' . $new_payment_status,
				'cancel_reason' => $cancel_reason,
			);
			$data->meta_value = json_encode( $meta_value );
		}

		$this->order_activities_model->add_order_meta( $data );

		if ( $remove_enrollment ) {
			$order_status = OrderModel::ORDER_CANCELLED;
		}

		$this->manage_earnings_and_enrollments( $order_status, $order_id );

		// Store coupon usage.
		$this->coupon_ctrl->store_coupon_usage( $order_id );
	}

	/**
	 * Handle new order placement
	 *
	 * Clear cart items, managing enrollment & earnings
	 *
	 * @since 3.0.0
	 *
	 * @param int $order_id Order id.
	 *
	 * @return void
	 */
	public function handle_order_placement_success( int $order_id ) {
		$order_data = $this->order_model->get_order_by_id( $order_id );
		if ( $order_data ) {
			$user_id = $order_data->student->id;

			( new CartModel() )->clear_user_cart( $user_id );

			// Manage enrollment & earnings.
			$order          = ( new OrderModel() )->get_order_by_id( $order_id );
			$payment_status = $order->payment_status;

			$order_status = $this->order_model->get_order_status_by_payment_status( $payment_status );

			$this->manage_earnings_and_enrollments( $order_status, $order_id );
		}
	}

	/**
	 * Check if order is bundle order
	 *
	 * @since 3.0.0
	 *
	 * @param object $order order object.
	 * @param int    $object_id object id.
	 *
	 * @return boolean
	 */
	private function is_bundle_order( $order, $object_id ) {
		return tutor_utils()->is_addon_enabled( 'course-bundle' )
		&& in_array( $order->order_type, array( $this->order_model::TYPE_SINGLE_ORDER, $this->order_model::TYPE_SUBSCRIPTION ), true )
		&& 'course-bundle' === get_post_type( $object_id );
	}

	/**
	 * Manage earnings after order bulk action
	 *
	 * @since 3.0.0
	 *
	 * @param string $order_status Order status.
	 * @param int    $order_id Order ID.
	 *
	 * @return void
	 */
	public function manage_earnings_and_enrollments( string $order_status, int $order_id ) {
		$earnings   = Earnings::get_instance();
		$order      = $this->order_model->get_order_by_id( $order_id );
		$student_id = $order->student->id;

		$enrollment_status = ( OrderModel::ORDER_COMPLETED === $order_status ? 'completed' : ( OrderModel::ORDER_INCOMPLETE === $order->order_status ? 'pending' : 'cancel' ) );

		foreach ( $order->items as $item ) {
			$object_id    = $item->id; // It could be course/bundle/plan id.
			$is_gift_item = apply_filters( 'tutor_is_gift_item', false, $item->primary_id );
			if ( $is_gift_item ) {
				continue;
			}

			if ( $this->order_model::TYPE_SINGLE_ORDER !== $order->order_type ) {
				/**
				 * Do not process enrollment for membership plan.
				 *
				 * @since 3.2.0
				 */
				$plan_info = apply_filters( 'tutor_get_plan_info', null, $object_id );
				if ( $plan_info && isset( $plan_info->is_membership_plan ) && $plan_info->is_membership_plan ) {
					continue;
				} else {
					$object_id = apply_filters( 'tutor_subscription_course_by_plan', $item->id, $order );
				}

				/**
				 * Do not process enrollment for subscription order refund.
				 * It will be handled by subscription controller's handle_order_refund method.
				 *
				 * @since 3.3.0
				 */
				if ( Input::has( 'is_cancel_subscription' ) ) {
					continue;
				}
			}

			$has_enrollment = tutor_utils()->is_enrolled( $object_id, $student_id, false );
			if ( $has_enrollment ) {
				// Update enrollment status based on order status.
				$update = tutor_utils()->update_enrollments( $enrollment_status, array( $has_enrollment->ID ) );
				if ( $update ) {
					if ( $this->is_bundle_order( $order, $object_id ) && $this->order_model->is_single_order( $order ) ) {
						if ( 'completed' === $enrollment_status ) {
							BundleModel::enroll_to_bundle_courses( $object_id, $student_id );
						} else {
							BundleModel::disenroll_from_bundle_courses( $object_id, $student_id );
						}
					}

					/**
					 * For subscription, renewal no need to update order id.
					 */
					if ( $this->order_model->is_single_order( $order ) ) {
						update_post_meta( $has_enrollment->ID, '_tutor_enrolled_by_order_id', $order_id );

						/**
						 * Update enrollment expiry date if it is set in a course.
						 */
						if ( tutor()->course_post_type === get_post_type( $object_id ) ) {
							$is_set_enrollment_expiry  = (int) get_tutor_course_settings( $object_id, 'enrollment_expiry' );
							$enrollment_expiry_enabled = (bool) get_tutor_option( 'enrollment_expiry_enabled' );
							if ( $enrollment_expiry_enabled && $is_set_enrollment_expiry ) {
								global $wpdb;
								QueryHelper::update(
									$wpdb->posts,
									array(
										'post_date'     => current_time( 'mysql' ),
										'post_date_gmt' => current_time( 'mysql', true ),
									),
									array(
										'ID'        => $has_enrollment->ID,
										'post_type' => tutor()->enrollment_post_type,
									)
								);
							}
						}

						if ( OrderModel::ORDER_COMPLETED === $order_status ) {
							do_action( 'tutor_after_enrolled', $object_id, $student_id, $has_enrollment->ID );
						}
					}

					if ( 'completed' === $enrollment_status ) {
						do_action( 'tutor_order_enrolled', $order, $has_enrollment->ID );
					}
				}
			} else {
				if ( $order->order_status === $this->order_model::ORDER_COMPLETED ) {
					// Insert enrollment.
					add_filter( 'tutor_enroll_data', fn( $enroll_data) => array_merge( $enroll_data, array( 'post_status' => 'completed' ) ) );

					$enrollment_id = tutor_utils()->do_enroll( $object_id, $order_id, $student_id );
					if ( $enrollment_id ) {
						if ( $this->is_bundle_order( $order, $object_id ) && $this->order_model->is_single_order( $order ) ) {
							BundleModel::enroll_to_bundle_courses( $object_id, $student_id );
						}
						update_post_meta( $enrollment_id, '_tutor_enrolled_by_order_id', $order_id );

						do_action( 'tutor_order_enrolled', $order, $enrollment_id );
					} else {
						// Log error message with student id and course id.
						error_log( "Error updating enrollment for student {$student_id} and course {$object_id}" );
					}
				}
			}
		}

		// Update earnings.
		$earnings->prepare_order_earnings( $order_id );
		$earnings->remove_before_store_earnings();
	}

	/**
	 * Update order data for the free checkout
	 *
	 * @since 3.4.0
	 *
	 * @param array $order_data Order data.
	 *
	 * @return array
	 */
	public function update_order_data( array $order_data ) {
		if ( empty( $order_data['total_price'] ) && OrderModel::TYPE_SINGLE_ORDER === $order_data['order_type'] ) {
			$order_data['order_status']   = OrderModel::ORDER_COMPLETED;
			$order_data['payment_status'] = OrderModel::PAYMENT_PAID;
			$order_data['payment_method'] = 'free';
		}
		return $order_data;
	}

	/**
	 * Enroll user to the course when free checkout
	 *
	 * @since 3.4.0
	 *
	 * @param array $order_data Order data.
	 *
	 * @return array
	 */
	public function handle_free_checkout( array $order_data ) {
		if ( empty( $order_data['total_price'] ) && OrderModel::TYPE_SINGLE_ORDER === $order_data['order_type'] ) {
			$order_id = $order_data['id'];
			$user_id  = $order_data['user_id'];
			$items    = $order_data['items'];
			foreach ( $items as $item ) {
				add_filter( 'tutor_enroll_data', fn( $enroll_data ) => array_merge( $enroll_data, array( 'post_status' => 'completed' ) ) );

				$enrolled_id = tutor_utils()->do_enroll( $item['item_id'], $order_data['id'], $user_id );
				if ( $enrolled_id && tutor_utils()->is_addon_enabled( 'course-bundle' ) && get_post_type( $item['item_id'] ) === CourseBundle::POST_TYPE ) {
					BundleModel::enroll_to_bundle_courses( $item['item_id'], $user_id );
				}
			}

			// Store coupon usage.
			$this->coupon_ctrl->store_coupon_usage( $order_id );
		}
		return $order_data;
	}

	/**
	 * Redirect user to the course after free checkout when item is 1.
	 * If user checkout multiple items and keep the default behavior.
	 *
	 * @since 3.4.0
	 *
	 * @param string  $url Default redirect url.
	 * @param string  $status Order placement status.
	 * @param integer $order_id Order id.
	 *
	 * @return string
	 */
	public function redirect_to_the_course( string $url, string $status, int $order_id ): string {
		$user_id = get_current_user_id();
		if ( OrderModel::ORDER_PLACEMENT_SUCCESS === $status ) {
			$order = $this->order_model->get_order_by_id( $order_id );
			if ( $order && count( $order->items ) === 1 && empty( $order->total_price ) && OrderModel::TYPE_SINGLE_ORDER === $order->order_type ) {

				// Firing hook to clear cart.
				do_action( 'tutor_order_placement_success', $order_id );

				// Clear the alert message.
				delete_transient( CheckoutController::PAY_NOW_ALERT_MSG_TRANSIENT_KEY . $user_id );
				delete_transient( CheckoutController::PAY_NOW_ERROR_TRANSIENT_KEY . $user_id );
				$course_id = $order->items[0]->id;
				$url       = get_the_permalink( $course_id );
			}
		}
		return $url;
	}

	/**
	 * Store billing address for an order when order is placed.
	 *
	 * @since 3.5.0
	 *
	 * @param array $order_data order data.
	 *
	 * @return void
	 */
	public function store_billing_address_for_order( array $order_data ) {
		$order_id     = $order_data['id'];
		$user_id      = $order_data['user_id'];
		$billing_info = ( new BillingController( false ) )->get_billing_info( $user_id );

		/**
		 * JSON_UNESCAPED_UNICODE is used to ensure that the billing info is stored in a readable format.
		 * This is important for languages that use non-ASCII characters like ñ, á, é, í, ó, ú, ü, etc.
		 *
		 * @since 3.7.1
		 */
		$meta_value = '{}';
		if ( $billing_info ) {
			$meta_value = wp_json_encode( $billing_info, JSON_UNESCAPED_UNICODE );
		} else {
			/**
			 * Store user data as billing info
			 * If user has no billing info during order like manual enrollment from CSV.
			 */
			$user_data  = get_userdata( $user_id );
			$meta_value = wp_json_encode(
				array(
					'billing_first_name' => $user_data->first_name,
					'billing_last_name'  => $user_data->last_name,
					'billing_email'      => $user_data->user_email,
				),
				JSON_UNESCAPED_UNICODE
			);
		}

		OrderMetaModel::add_meta(
			$order_id,
			OrderModel::META_KEY_BILLING_ADDRESS,
			$meta_value
		);
	}
}
