<?php

/**
 * Tutor Course attachments Main Class
 */

namespace TUTOR;

if (!defined('ABSPATH'))
	exit;

class Studiocart extends Tutor_Base {

	public function __construct() {
		parent::__construct();

		//Add Tutor Option
		add_filter('tutor_monetization_options', array($this, 'tutor_monetization_options'));

		$monetize_by = tutils()->get_option('monetize_by');

		if ($monetize_by !== 'sc') {
			return;
		}

		add_action('add_meta_boxes', array($this, 'register_meta_box'));
		add_action('save_post_' . $this->course_post_type, array($this, 'save_course_meta'));

		/**
		 * Is Course Purchasable
		 */
		add_filter('is_course_purchasable', array($this, 'is_course_purchasable'), 10, 2);
		add_filter('get_tutor_course_price', array($this, 'get_tutor_course_price'), 10, 2);
		add_filter('tutor_course_sell_by', array($this, 'tutor_course_sell_by'));
        add_filter('get_tutor_currency_symbol', array($this, 'tutor_symbol'));
        
        add_filter('tutor_get_earnings_completed_statuses', array($this, 'sc_add_paid_status'));
        
        add_action('sc_order_complete', array($this, 'add_earning_data'), 10, 2 );

	}

	/**
	 * @param $arr
	 *
	 * @return mixed
	 *
	 * Returning monetization options
	 *
	 * @since v.1.3.5
	 */
    
    public function sc_add_paid_status($statuses) {
        $statuses[] = 'paid';
        return $statuses;
    }
    
	public function add_earning_data($order_info, $order_type) {
                
		global $wpdb;
        
        $order_id = $order_info['ID'];
        $sc_product_id = $order_info['product_id'];
        $trigger = 'purchased';
        $plan_id = $order_info['plan_id'];
        $total_price = $order_info['amount'];
            
        // check if tutor integration exists for this product
        $integrations = get_post_meta($sc_product_id, '_sc_integrations', true );
        if( $integrations ){			

            foreach ( $integrations as $ind=>$intg ) {

                $_sc_services = isset($intg['services']) ? $intg['services'] : "";
                $_sc_service_trigger = isset($intg['service_trigger']) ? $intg['service_trigger'] : "";
                $_sc_plan_id = isset($intg['int_plan']) ? $intg['int_plan'] : "";

                if (    $_sc_service_trigger == $trigger && 
                        ( $_sc_plan_id == '' || $_sc_plan_id == $plan_id ) &&
                        $_sc_services == "tutor" && 
                        $intg['tutor_course'] != ''
                   ) {

                    // revenue sharing
                    $enable_tutor_earning = tutor_utils()->get_option('enable_tutor_earning');
                    if ($enable_tutor_earning) {

                        $course_id = $intg['tutor_course'];
                        $user_id = $wpdb->get_var("SELECT post_author FROM {$wpdb->posts} WHERE ID = {$course_id} ");
                        $order_status = $wpdb->get_var("SELECT post_status from {$wpdb->posts} where ID = {$order_id} ");
                        $order_status = ($order_status == 'paid') ? 'completed' : $order_status;

                        $fees_deduct_data = array();
                        $tutor_earning_fees = tutor_utils()->get_option('tutor_earning_fees');
                        $enable_fees_deducting = tutor_utils()->avalue_dot('enable_fees_deducting', $tutor_earning_fees);

                        $course_price_grand_total = $total_price;

                        if ($enable_fees_deducting) {
                            $fees_name = tutor_utils()->avalue_dot('fees_name', $tutor_earning_fees);
                            $fees_amount = (int) tutor_utils()->avalue_dot('fees_amount', $tutor_earning_fees);
                            $fees_type = tutor_utils()->avalue_dot('fees_type', $tutor_earning_fees);

                            if ($fees_amount > 0) {
                                if ($fees_type === 'percent') {
                                    $fees_amount = ($total_price * $fees_amount) / 100;
                                }

                                /*
                                if ( $fees_type === 'fixed' ) {
                                    $course_price_grand_total = $total_price - $fees_amount;
                                }*/

                                $course_price_grand_total = $total_price - $fees_amount;
                            }

                            $fees_deduct_data = array(
                                'deduct_fees_amount'    => $fees_amount,
                                'deduct_fees_name'      => $fees_name,
                                'deduct_fees_type'      => $fees_type,
                            );
                        }

                        $instructor_rate = tutor_utils()->get_option('earning_instructor_commission');
                        $admin_rate = tutor_utils()->get_option('earning_admin_commission');

                        $instructor_amount = 0;
                        if ($instructor_rate > 0) {
                            $instructor_amount = ($course_price_grand_total * $instructor_rate) / 100;
                        }

                        $admin_amount = 0;
                        if ($admin_rate > 0) {
                            $admin_amount = ($course_price_grand_total * $admin_rate) / 100;
                        }

                        $commission_type = 'percent';

                        // (Use Pro Filter - Start)
                        // The response must be same array structure.
                        // Do not change used variable names here, or change in both of here and pro plugin
                        $pro_arg = [
                            'user_id' => $user_id,
                            'instructor_rate' => $instructor_rate,
                            'admin_rate' => $admin_rate,
                            'instructor_amount' => $instructor_amount,
                            'admin_amount' => $admin_amount,
                            'course_price_grand_total'  => $course_price_grand_total,
                            'commission_type' => $commission_type
                        ];
                        $pro_calculation = apply_filters('tutor_pro_earning_calculator', $pro_arg);
                        extract($pro_calculation);
                        // (Use Pro Filter - End) 

                        $earning_data = array(
                            'user_id'                   => $user_id,
                            'course_id'                 => $course_id,
                            'order_id'                  => $order_id,
                            'order_status'              => $order_status,
                            'course_price_total'        => $total_price,
                            'course_price_grand_total'  => $course_price_grand_total,

                            'instructor_amount'         => $instructor_amount,
                            'instructor_rate'           => $instructor_rate,
                            'admin_amount'              => $admin_amount,
                            'admin_rate'                => $admin_rate,

                            'commission_type'           => $commission_type,
                            'process_by'                => 'studiocart',
                            'created_at'                => date('Y-m-d H:i:s', tutor_time()),
                        );
                        $earning_data = apply_filters('tutor_new_earning_data', array_merge($earning_data, $fees_deduct_data));

                        $wpdb->insert($wpdb->prefix . 'tutor_earnings', $earning_data);
                    }
                }
            }
        }
	}
    
    public function tutor_monetization_options($arr) {
		$has_edd = tutils()->has_edd();
		if ($has_edd) {
			$arr['sc'] = __('Studiocart', 'tutor');
		}
		return $arr;
	}

	public function register_meta_box() {
		add_meta_box('tutor-attached-sc-product', __('Add Product', 'tutor'), array($this, 'course_add_product_metabox'), $this->course_post_type, 'advanced', 'high');
	}
    
    public function tutor_symbol() {
        global $sc_currency_symbol;
        return $sc_currency_symbol;
    }

	/**
	 * @param $post
	 * MetaBox for Lesson Modal Edit Mode
	 */
	public function course_add_product_metabox() {
		$_tutor_course_price_type = tutils()->price_type();
        ?>

        <div class="tutor-option-field-row">
            <div class="tutor-option-field-label">
                <label for="">
                    <?php _e('Select product', 'tutor'); ?> <br />
                    <p class="text-muted">(<?php _e('When selling the course', 'tutor'); ?>)</p>
                </label>
            </div>
            <div class="tutor-option-field">
                <?php
        
                $args = array(
                    'posts_per_page' => -1,
                    'post_type' => 'sc_product'
                );

                $products = get_posts($args);
                $product_id = tutor_utils()->get_course_product_id();
                ?>

                <select id="_sc_product_id" name="_tutor_course_product_id" class="tutor_select2" style="min-width: 300px;">
                    <option value="-1"><?php _e('Select a Product'); ?></option>
                    <?php
                    foreach ($products as $product){
                        echo "<option value='{$product->ID}' ".selected($product->ID, $product_id)." >{$product->post_title}</option>";
                    }
                    ?>
                </select>

                <p class="desc">
                    <?php _e('Sell your product, process by Studiocart', 'tutor'); ?>
                </p>

            </div>
        </div>


        <div class="tutor-option-field-row">
            <div class="tutor-option-field-label">
                <label for="">
                    <?php _e('Course Type', 'tutor'); ?> <br />
                </label>
            </div>
            <div class="tutor-option-field">

                <label>
                    <input id="tutor_course_price_type_pro" type="radio" name="tutor_course_price_type" value="paid" <?php checked($_tutor_course_price_type, 'paid'); ?> >
                    <?php _e('Paid', 'tutor'); ?>
                </label>
                <label>
                    <input type="radio" name="tutor_course_price_type" value="free"  <?php $_tutor_course_price_type ? checked($_tutor_course_price_type, 'free') : checked('true', 'true'); ?> >
                    <?php _e('Free', 'tutor'); ?>
                </label>
            </div>
        </div>
        <?php
	}

	public function save_course_meta($post_ID) {
		$product_id = tutor_utils()->avalue_dot('_tutor_course_product_id', $_POST);

		if ($product_id !== '-1') {
			$product_id = (int) $product_id;
			if ($product_id) {
				update_post_meta($post_ID, '_tutor_course_product_id', $product_id);
				update_post_meta($product_id, '_tutor_product', 'yes');
			}
		} else {
			delete_post_meta($post_ID, '_tutor_course_product_id');
		}
	}

	public function is_course_purchasable($bool, $course_id) {
		$course_id = tutor_utils()->get_post_id($course_id);
		$has_product_id = get_post_meta($course_id, '_tutor_course_product_id', true);
		if ($has_product_id) {
			return true;
		}
		return false;
	}

	public function get_tutor_course_price($price, $course_id) {
        global $sc_currency_symbol;
        
		$product_id = tutor_utils()->get_course_product_id($course_id);
        
        $scp = sc_setup_product($product_id);

        // Find selected pay option
        $product_plan_data = $scp->pay_options;

        foreach ( $product_plan_data as $val ) {
            if ( $sc_option_id == $val['option_id'] ) {
                $option = $val;
            }
        }

        $_amount = $product_plan_data[0]['price'];

		return $sc_currency_symbol . number_format($_amount, 2, '.', '');
	}

	public function tutor_course_sell_by() {
		return 'sc';
	}
}