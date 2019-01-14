<?php
/**
 * Tutor Course attachments Main Class
 */

namespace TUTOR_EDD;

use TUTOR\Tutor_Base;

class TutorEDD extends Tutor_Base {

	public function __construct() {
		parent::__construct();

		add_action( 'add_meta_boxes', array($this, 'register_meta_box') );
		add_action('save_post_'.$this->course_post_type, array($this, 'save_course_meta'));

		/**
		 * Is Course Purchasable
		 */
		add_filter('is_course_purchasable', array($this, 'is_course_purchasable'), 10, 2);
		add_filter('get_tutor_course_price', array($this, 'get_tutor_course_price'), 10, 2);
		add_filter('tutor_course_sell_by', array($this, 'tutor_course_sell_by'));

		add_action('edd_update_payment_status', array($this, 'edd_update_payment_status'), 10, 3);

	}

	public function register_meta_box(){
		add_meta_box( 'tutor-attached-edd-product', __( 'Add Product', 'tutor' ), array($this, 'course_add_product_metabox'), $this->course_post_type, 'advanced', 'high' );
	}

	/**
	 * @param $post
	 * MetaBox for Lesson Modal Edit Mode
	 */
	public function course_add_product_metabox(){
		include  TUTOR_EDD()->path.'views/metabox/course-add-product-metabox.php';
	}

	public function save_course_meta($post_ID){
		$product_id = (int) tutor_utils()->avalue_dot('_tutor_course_product_id', $_POST);
		if ($product_id){
			update_post_meta($post_ID, '_tutor_course_product_id', $product_id);
			update_post_meta($product_id, '_tutor_product', 'yes');
		}else{
			delete_post_meta($post_ID, '_tutor_course_product_id');
		}
	}

	public function is_course_purchasable($bool, $course_id){
		if ( ! tutor_utils()->has_edd()){
			return false;
		}

		$course_id = tutor_utils()->get_post_id($course_id);
		$has_product_id = get_post_meta($course_id, '_tutor_course_product_id', true);
		if ($has_product_id){
			return true;
		}
		return false;
	}

	public function get_tutor_course_price($price, $course_id){
		$product_id = tutor_utils()->get_course_product_id($course_id);

		return edd_price($product_id, false);
	}
	public function tutor_course_sell_by(){
		return 'edd';
	}

	public function edd_update_payment_status($payment_id, $new_status, $old_status ){
		if ($new_status !== 'publish'){
			return;
		}

		$payment = new \EDD_Payment( $payment_id );
		$cart_details   = $payment->cart_details;

		if ( is_array( $cart_details ) ) {
			foreach ( $cart_details as $cart_index => $download ) {

				$if_has_course = tutor_utils()->product_belongs_with_course($download['id']);
				if ($if_has_course){
					$course_id = $if_has_course->post_id;
					$has_any_enrolled = tutor_utils()->has_any_enrolled($course_id);
					if ( ! $has_any_enrolled){
						tutor_utils()->do_enroll($course_id, $payment_id);
					}
				}
			}
			tutor_utils()->complete_course_enroll($payment_id);
		}

	}

}