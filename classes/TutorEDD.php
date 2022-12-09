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

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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
		// Add Tutor Option.
		add_filter( 'tutor_monetization_options', array( $this, 'tutor_monetization_options' ) );

		$monetize_by = tutils()->get_option( 'monetize_by' );
		if ( 'edd' !== $monetize_by ) {
			return;
		}

		add_action( 'add_meta_boxes', array( $this, 'register_meta_box' ) );
		add_action( 'save_post_' . $this->course_post_type, array( $this, 'save_course_meta' ) );

		/**
		 * Is Course Purchasable
		 */
		add_filter( 'is_course_purchasable', array( $this, 'is_course_purchasable' ), 10, 2 );
		add_filter( 'get_tutor_course_price', array( $this, 'get_tutor_course_price' ), 10, 2 );
		add_filter( 'tutor_course_sell_by', array( $this, 'tutor_course_sell_by' ) );

		add_action( 'edd_update_payment_status', array( $this, 'edd_update_payment_status' ), 10, 3 );
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
			'label'    => __( 'EDD', 'tutor-edd' ),

			'sections' => array(
				'general' => array(
					'label'  => __( 'General', 'tutor-edd' ),
					'desc'   => __( 'Tutor Course Attachments Settings', 'tutor-edd' ),
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
	 * Register meta box
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function register_meta_box() {
		tutor_meta_box_wrapper( 'tutor-attached-edd-product', __( 'Add Product', 'tutor' ), array( $this, 'course_add_product_metabox' ), $this->course_post_type, 'advanced', 'high', 'tutor-admin-post-meta' );
	}

	/**
	 * MetaBox for Lesson Modal Edit Mode
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function course_add_product_metabox() {
		include tutor()->path . 'views/metabox/course-add-edd-product-metabox.php';
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

		$product_id = Input::post( '_tutor_course_product_id', '' );

		if ( '-1' !== $product_id ) {
			$product_id = (int) $product_id;
			if ( $product_id ) {
				update_post_meta( $post_ID, '_tutor_course_product_id', $product_id );
				update_post_meta( $product_id, '_tutor_product', 'yes' );
			}
		} else {
			delete_post_meta( $post_ID, '_tutor_course_product_id' );
		}
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
		$has_product_id = get_post_meta( $course_id, '_tutor_course_product_id', true );
		if ( $has_product_id ) {
			return true;
		}
		return false;
	}

	/**
	 * Get course price
	 *
	 * @param string $price course price.
	 * @param int    $course_id course id.
	 *
	 * @return mixed
	 */
	public function get_tutor_course_price( $price, $course_id ) {
		$product_id = tutor_utils()->get_course_product_id( $course_id );

		return edd_price( $product_id, false );
	}

	/**
	 * Course sell by
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function tutor_course_sell_by() {
		return 'edd';
	}

	/**
	 * Update payment status
	 *
	 * @param int    $payment_id payment id.
	 * @param string $new_status payment's new status.
	 * @param string $old_status payment's old status.
	 *
	 * @return void
	 */
	public function edd_update_payment_status( $payment_id, $new_status, $old_status ) {
		if ( 'publish' !== $new_status ) {
			return;
		}

		$payment      = new \EDD_Payment( $payment_id );
		$cart_details = $payment->cart_details;
		$user_id      = $payment->user_info['id'];

		if ( is_array( $cart_details ) ) {
			foreach ( $cart_details as $cart_index => $download ) {

				$if_has_course = tutor_utils()->product_belongs_with_course( $download['id'] );
				if ( $if_has_course ) {
					$course_id        = $if_has_course->post_id;
					$has_any_enrolled = tutor_utils()->has_any_enrolled( $course_id, $user_id );
					if ( ! $has_any_enrolled ) {
						tutor_utils()->do_enroll( $course_id, $payment_id, $user_id );
					}
				}
			}
			tutor_utils()->complete_course_enroll( $payment_id );
		}
	}
}
