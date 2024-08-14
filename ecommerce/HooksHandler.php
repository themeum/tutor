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
use Tutor\Models\OrderModel;

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
		add_action( 'tutor_after_order_bulk_action', array( $this, 'store_order_activity_after' ), 10, 2 );

		add_action( 'tutor_after_order_bulk_action', array( $this, 'manage_earnings' ), 10, 2 );
		add_action( 'tutor_after_order_mark_as_paid', array( $this, 'after_order_mark_as_paid' ), 10 );
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
	public function store_order_activity_after( $bulk_action, $bulk_ids ) {
		foreach ( $bulk_ids as $order_id ) {
			$data = (object) array(
				'order_id'   => $order_id,
				'meta_key'   => $this->order_activities_model::META_KEY_HISTORY,
				'meta_value' => "Order mark as {$bulk_action}",
			);

			try {
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
	 * Manage earnings after order bulk action
	 *
	 * @since 3.0.0
	 *
	 * @param string $bulk_action Action name.
	 * @param array  $order_ids Order ID.
	 *
	 * @return void
	 */
	public function manage_earnings( $bulk_action, $order_ids ) {
		$actions = array(
			$this->order_model::PAYMENT_PAID,
			$this->order_model::PAYMENT_UNPAID,
		);

		if ( in_array( $bulk_action, $actions ) ) {
			foreach ( $order_ids as $order_id ) {
				$earnings = Earnings::get_instance();
				$earnings->prepare_order_earnings( $order_id );
				try {
					$earning_id = $earnings->remove_before_store_earnings();
					if ( $earning_id ) {
						do_action( 'tutor_ecommerce_after_earning_stored', $earning_id, $earnings->earning_data );
					}
				} catch ( \Throwable $th ) {
					error_log( $th->getMessage() );
				}
			}
		}
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
		$earnings = Earnings::get_instance();

		$data = (object) array(
			'order_id'   => $order_id,
			'meta_key'   => $this->order_activities_model::META_KEY_HISTORY,
			'meta_value' => 'Order mark as paid',
		);

		$this->order_activities_model->add_order_meta( $data );

		// Update enrollment.
		$order = $this->order_model->get_order_by_id( $order_id );
		if ( $order ) {
			$order_type = $order->order_type;
			$student_id = $order->student->id;
			$items      = $order->courses;

			if ( $this->order_model::TYPE_SINGLE_ORDER === $order_type ) {
				foreach ( $items as $item ) {
					$course_id      = $item->id;
					$has_enrollment = tutor_utils()->is_enrolled( $course_id, $student_id, false );
					if ( $has_enrollment ) {
						// Update enrollment.
						$update = tutor_utils()->update_enrollments( 'completed', array( $has_enrollment->ID ), 'enrolled' );

						if ( $update ) {
							$earnings->prepare_order_earnings( $order_id );
							$earnings->store_earnings();
						} else {
							// Log error message with student id and course id.
							error_log( "Error updating enrollment for student {$student_id} and course {$course_id}" );
						}
					} else {
						// Insert enrollment.
						add_filter(
							'tutor_enroll_data',
							function( $enroll_data ) {
								$enroll_data['enroll_status'] = 'completed';
								return $enroll_data;
							}
						);

						$enrollment_id = tutor_utils()->do_enroll( $course_id, $order_id, $student_id );
						if ( $enrollment_id ) {
							$earnings->prepare_order_earnings( $order_id );
							$earnings->store_earnings();
						} else {
							// Log error message with student id and course id.
							error_log( "Error updating enrollment for student {$student_id} and course {$course_id}" );
						}
					}
				}
			} else {
				// @TODO need to handle subscription order.
			}
		}
	}
}

