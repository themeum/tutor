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

use Tutor\Models\OrderActivitiesModel;
use TUTOR\Earnings;
use Tutor\Models\CartModel;
use Tutor\Models\OrderModel;
use TutorPro\CourseBundle\Models\BundleModel;

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
	 * Register hooks & resolve props
	 *
	 * @since 3.0.0
	 */
	public function __construct() {
		$this->order_activities_model = new OrderActivitiesModel();
		$this->order_model            = new OrderModel();

		// Register hooks.
		add_filter( 'tutor_course_sell_by', array( $this, 'alter_course_sell_by' ) );
		add_filter( 'get_tutor_course_price', array( $this, 'alter_course_price' ), 10, 2 );

		// Order hooks.
		add_action( 'tutor_after_order_bulk_action', array( $this, 'after_order_bulk_action' ), 10, 2 );
		add_action( 'tutor_after_order_mark_as_paid', array( $this, 'after_order_mark_as_paid' ), 10 );

		add_action( 'tutor_order_payment_updated', array( $this, 'handle_payment_updated_webhook' ) );

		add_action( 'tutor_order_payment_status_changed', array( $this, 'handle_payment_status_changed' ), 10, 4 );

		add_action( 'tutor_order_placement_success', array( $this, 'handle_order_placement_success' ) );

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

		foreach ( $bulk_ids as $order_id ) {
			try {
				$this->manage_earnings_and_enrollments( $order_status, $order_id );
				$data = (object) array(
					'order_id'   => $order_id,
					'meta_key'   => $this->order_activities_model::META_KEY_HISTORY,
					'meta_value' => "Order mark as {$bulk_action}",
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
		if ( tutor_utils()->is_monetize_by_tutor() ) {
			$price = tutor_get_formatted_price_html( $course_id, false );
		}

		return $price;
	}

	/**
	 * Handle after order mark as paid event
	 *
	 * @since 3.0.0
	 *
	 * @param int $order_id Order ID.
	 *
	 * @return void
	 */
	public function after_order_mark_as_paid( $order_id ) {
		$order = $this->order_model->get_order_by_id( $order_id );
		if ( $order ) {
			$this->handle_payment_status_changed( $order->id, $this->order_model::PAYMENT_UNPAID, $order->payment_status );
		}
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
				'payment_payloads' => $res->payment_payload,
				'transaction_id'   => $transaction_id,
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
	 * @param string $cancel_enrollment Will be only applied if payment refunded.
	 *
	 * @return void
	 */
	public function handle_payment_status_changed( $order_id, $prev_payment_status, $new_payment_status, $cancel_enrollment = true ) {

		$order_status = $this->order_model->get_order_status_by_payment_status( $new_payment_status );

		// Store activity.
		$data = (object) array(
			'order_id'   => $order_id,
			'meta_key'   => $this->order_activities_model::META_KEY_HISTORY,
			'meta_value' => 'Order mark as ' . $new_payment_status,
		);

		$this->order_activities_model->add_order_meta( $data );

		$this->manage_earnings_and_enrollments( $order_status, $order_id, $cancel_enrollment );
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
		$user_id    = $order_data->student->id;

		( new CartModel() )->clear_user_cart( $user_id );

		// Manage enrollment & earnings.
		$order          = ( new OrderModel() )->get_order_by_id( $order_id );
		$payment_status = $order->payment_status;

		$order_status = $this->order_model->get_order_status_by_payment_status( $payment_status );

		$this->manage_earnings_and_enrollments( $order_status, $order_id );
	}

	/**
	 * Manage earnings after order bulk action
	 *
	 * @since 3.0.0
	 *
	 * @param string $order_status Order status.
	 * @param int    $order_id Order ID.
	 * @param bool   $cancel_enrollment If false then enrollment will not
	 * be cancelled regardless of order status.
	 *
	 * @return void
	 */
	public function manage_earnings_and_enrollments( string $order_status, int $order_id, $cancel_enrollment = true ) {
		$earnings   = Earnings::get_instance();
		$order      = $this->order_model->get_order_by_id( $order_id );
		$student_id = $order->student->id;

		$enrollment_status = OrderModel::ORDER_COMPLETED === $order_status ? 'completed' : 'cancelled';
		if ( ! $cancel_enrollment ) {
			$enrollment_status = 'completed';
		}

		foreach ( $order->items as $item ) {
			$course_id = $item->id; // It could be course/bundle/plan id.
			if ( $this->order_model::TYPE_SUBSCRIPTION === $order->order_type ) {
				$course_id = apply_filters( 'tutor_subscription_course_by_plan', $item->id, $order );
			}

			$has_enrollment = tutor_utils()->is_enrolled( $course_id, $student_id, false );
			if ( $has_enrollment ) {
				// Update enrollment.
				$update = tutor_utils()->update_enrollments( $enrollment_status, array( $has_enrollment->ID ) );

				if ( $update ) {
					if ( tutor_utils()->is_addon_enabled( 'tutor-pro/addons/course-bundle/course-bundle.php' ) && 'course-bundle' === get_post_type( $course_id ) ) {
						BundleModel::enroll_to_bundle_courses( $course_id, $student_id );
					}

					update_post_meta( $has_enrollment->ID, '_tutor_enrolled_by_order_id', $order_id );
				}
			} else {
				if ( $this->order_model::ORDER_COMPLETED ) {
					// Insert enrollment.
					add_filter(
						'tutor_enroll_data',
						function( $enroll_data ) {
							$enroll_data['post_status'] = 'completed';
							return $enroll_data;
						}
					);

					$enrollment_id = tutor_utils()->do_enroll( $course_id, $order_id, $student_id );
					if ( $enrollment_id ) {
						if ( tutor_utils()->is_addon_enabled( 'tutor-pro/addons/course-bundle/course-bundle.php' ) && 'course-bundle' === get_post_type( $course_id ) ) {
							BundleModel::enroll_to_bundle_courses( $course_id, $student_id );
						}
						update_post_meta( $enrollment_id, '_tutor_enrolled_by_order_id', $order_id );

						// Check if action already added.
						$fired = did_action( 'tutor_after_enrolled' );
						if ( ! $fired ) {
							do_action( 'tutor_after_enrolled', $course_id, $student_id, $enrollment_id );
						}
					} else {
						// Log error message with student id and course id.
						error_log( "Error updating enrollment for student {$student_id} and course {$course_id}" );
					}
				}
			}

			// Update earnings.
			$earnings->prepare_order_earnings( $order_id );
			$earnings->remove_before_store_earnings();
		}
	}

}

