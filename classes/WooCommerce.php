<?php
/**
 * Manage WooCommerce integration
 *
 * @package Tutor\WooCommerce
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.0.0
 */

namespace TUTOR;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handle woocommerce hooks
 *
 * @since 1.0.0
 */
class WooCommerce extends Tutor_Base {

	/**
	 * Register hooks
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		parent::__construct();

		add_action( 'admin_notices', array( $this, 'notice_on_disabled_wc' ) );

		// Add option settings.
		add_filter( 'tutor_monetization_options', array( $this, 'tutor_monetization_options' ) );

		$monetize_by = tutor_utils()->get_option( 'monetize_by' );
		if ( 'wc' !== $monetize_by ) {
			return;
		}

		add_filter( 'tutor/options/attr', array( $this, 'add_options' ) );

		/**
		 * Is Course Purchasable
		 */
		add_filter( 'is_course_purchasable', array( $this, 'is_course_purchasable' ), 10, 2 );
		add_filter( 'get_tutor_course_price', array( $this, 'get_tutor_course_price' ), 10, 2 );
		add_filter( 'tutor_course_sell_by', array( $this, 'tutor_course_sell_by' ) );

		add_filter( 'product_type_options', array( $this, 'add_tutor_type_in_wc_product' ) );

		add_action( 'add_meta_boxes', array( $this, 'register_meta_box' ) );
		add_action( 'save_post_' . $this->course_post_type, array( $this, 'save_course_meta' ), 10, 2 );
		add_action( 'save_post_product', array( $this, 'save_wc_product_meta' ) );

		add_action( 'tutor_course/single/before/enroll', 'wc_print_notices' );

		/**
		 * After place new order
		 */
		add_action( 'woocommerce_new_order', array( $this, 'course_placing_order_from_admin' ), 10, 3 );
		add_action( 'woocommerce_new_order_item', array( $this, 'course_placing_order_from_customer' ), 10, 3 );

		/**
		 * Order Status Hook
		 *
		 * Remove course from active courses if an order is cancelled or refunded
		 */
		add_action( 'woocommerce_order_status_changed', array( $this, 'enrolled_courses_status_change' ), 10, 3 );

		/**
		 * Add Earning Data
		 */
		add_action( 'woocommerce_new_order_item', array( $this, 'add_earning_data' ), 10, 3 );
		add_action( 'woocommerce_order_status_changed', array( $this, 'add_earning_data_status_change' ), 10, 3 );

		/**
		 * WC Print Notices After Enroll
		 *
		 * @since 1.3.5
		 */
		if ( tutor_utils()->has_wc() ) {
			add_action( 'tutor_course/single/before/inner-wrap', 'wc_print_notices', 10 );
			add_action( 'tutor_course/single/enrolled/before/inner-wrap', 'wc_print_notices', 10 );
		}

		/**
		 * Manage WooCommerce plugin dependency
		 *
		 * @since 1.7.8
		 */
		$woocommerce_path = dirname( dirname( __DIR__ ) ) . DIRECTORY_SEPARATOR . 'woocommerce' . DIRECTORY_SEPARATOR . 'woocommerce.php';
		register_deactivation_hook( $woocommerce_path, array( $this, 'disable_tutor_monetization' ) );
		/**
		 * Redirect student on enrolled courses after course
		 * Enrollment complete
		 *
		 * @since 1.9.0
		*/
		add_action( 'woocommerce_thankyou', array( $this, 'redirect_to_enrolled_courses' ) );

		/**
		 * Change woo commerce cart product link if it is tutor product
		 */
		add_filter( 'woocommerce_cart_item_permalink', array( $this, 'tutor_update_product_url' ), 10, 2 );
		add_filter( 'woocommerce_order_item_permalink', array( $this, 'filter_order_item_permalink_callback' ), 10, 3 );

		/**
		 * On WC product delete clear course linked product
		 *
		 * @since 2.0.7
		 */
		add_action( 'delete_post', array( $this, 'clear_course_linked_product' ) );

		add_action( 'before_woocommerce_init', array( $this, 'declare_tutor_compatibility_with_hpos' ) );
	}

	/**
	 * Show admin notice if user disable the WC plugin.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function notice_on_disabled_wc() {
		$show = get_option( 'tutor_show_woocommerce_notice' ) && 'free' === tutor_utils()->get_option( 'monetize_by', 'free' );

		if ( $show ) {
			$message = __( 'Since WooCommerce is disabled, your monetized courses have been set to free. Please make sure to enable Tutor LMS monetization if you decide to re-enable WooCommerce.', 'tutor' );
			echo '<div class="notice notice-error"><p>' . esc_html( $message ) . '</p></div>';
		}
	}

	/**
	 * Check HPOS feature enabled.
	 * WC declared HPOS (Hight Performance Order Storage) feature stable on october 2023 from WC v8.2
	 *
	 * @see https://woo.com/document/high-performance-order-storage
	 *
	 * @since 2.6.0
	 *
	 * @return bool
	 */
	public static function hpos_enabled() {
		$hpos = false;

		if ( tutor_utils()->has_wc() && version_compare( WC()->version, '8.2.0', '>=' ) ) {
			$hpos = 'yes' === get_option( 'woocommerce_custom_orders_table_enabled' );
		}

		return $hpos;
	}

	/**
	 * Declare tutor compatibility with WC HPOS feature
	 *
	 * @since 2.6.0
	 *
	 * @return void
	 */
	public function declare_tutor_compatibility_with_hpos() {
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', TUTOR_FILE, true );
		}
	}

	/**
	 * On WC product delete, clear course linked product
	 *
	 * @since 2.0.7
	 *
	 * @param int $post_id post id.
	 *
	 * @return void
	 */
	public function clear_course_linked_product( $post_id ) {
		if ( get_post_type( $post_id ) === 'product' ) {
			global $wpdb;
			$wpdb->query(
				$wpdb->prepare( "DELETE FROM {$wpdb->postmeta} WHERE meta_key=%s AND meta_value=%d", '_tutor_course_product_id', $post_id )
			);
		}
	}

	/**
	 * Order item callback handler
	 *
	 * @since 1.0.0
	 *
	 * @param string $product_permalink permalink.
	 * @param mixed  $item order item.
	 * @param mixed  $order orders.
	 *
	 * @return string permalink of course
	 */
	public function filter_order_item_permalink_callback( $product_permalink, $item, $order ) {

		// For product variations.
		if ( $item->get_variation_id() > 0 ) {
			$product = $item->get_product();

			$is_visible = $product && $product->is_visible();

			// Get the instance of the parent variable product Object.
			$parent_product = wc_get_product( $item->get_product_id() );

			// Return the parent product permalink (if product is visible).
			return $is_visible ? $parent_product->get_permalink() : '';
		}

		$course_id = $this->get_post_id_by_meta_key_and_value( '_tutor_course_product_id', $item->get_product_id() );

		return get_permalink( $course_id );
	}

	/**
	 * Get post id my meta key & value
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $key meta key.
	 * @param mixed $value meta value.
	 *
	 * @return mixed post id on success, false on failure
	 */
	public function get_post_id_by_meta_key_and_value( $key, $value ) {
		global $wpdb;
		$meta = $wpdb->get_results( 'SELECT * FROM `' . $wpdb->postmeta . "` WHERE meta_key='" . esc_sql( $key ) . "' AND meta_value='" . esc_sql( $value ) . "'" );
		if ( is_array( $meta ) && ! empty( $meta ) && isset( $meta[0] ) ) {
			$meta = $meta[0];
		}
		if ( is_object( $meta ) ) {
			return $meta->post_id;
		} else {
			return false;
		}
	}

	/**
	 * Check if course is purchase able
	 *
	 * @since 1.0.0
	 *
	 * @param bool $bool default value.
	 * @param int  $course_id course id.
	 *
	 * @return boolean
	 */
	public function is_course_purchasable( $bool, $course_id ) {
		if ( ! tutor_utils()->has_wc() ) {
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
	 * @since 1.0.0
	 *
	 * @param mixed $price course price.
	 * @param int   $course_id course id.
	 *
	 * @return string
	 */
	public function get_tutor_course_price( $price, $course_id ) {
		$price = null;

		if ( tutor_utils()->is_course_purchasable( $course_id ) ) {
			if ( tutor_utils()->has_wc() ) {
				$product_id = tutor_utils()->get_course_product_id( $course_id );
				$product    = wc_get_product( $product_id );

				if ( $product ) {
					ob_start();
					?>
					<div class="price">
						<?php echo $product->get_price_html(); //phpcs:ignore ?>
					</div>
					<?php
					return ob_get_clean();
				}
			}
		}

		return $price;
	}

	/**
	 * Sell by filter handler
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function tutor_course_sell_by() {
		return 'woocommerce';
	}

	/**
	 * Add tutor type in WC product
	 *
	 * @since 1.0.0
	 *
	 * @param array $types types.
	 *
	 * @return array
	 */
	public function add_tutor_type_in_wc_product( $types ) {
		$types['tutor_product'] = array(
			'id'            => '_tutor_product',
			'wrapper_class' => 'show_if_simple',
			'label'         => __( 'For Tutor', 'tutor' ),
			'description'   => __( 'This checkmark ensure that you will sell a specific course via this product.', 'tutor' ),
			'default'       => 'no',
		);

		return $types;
	}

	/**
	 * Save course meta for attaching WC product
	 *
	 * @since 1.0.0
	 *
	 * @param int   $post_ID this is course ID.
	 * @param mixed $post    course details.
	 *
	 * @return void
	 */
	public function save_course_meta( $post_ID, $post ) {
		do_action( 'save_tutor_course', $post_ID, $post );
	}

	/**
	 * Register meta box
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function register_meta_box() {
		tutor_meta_box_wrapper( 'tutor-attach-product', __( 'Add Product', 'tutor' ), array( $this, 'course_add_product_metabox' ), $this->course_post_type, 'advanced', 'high', 'tutor-admin-post-meta' );
	}

	/**
	 * Meta box view
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function course_add_product_metabox() {
		include tutor()->path . 'views/metabox/course-add-product-metabox.php';
	}

	/**
	 * Save WC product meta
	 *
	 * @since 1.0.0
	 *
	 * @param int $post_ID post id.
	 *
	 * @return void
	 */
	public function save_wc_product_meta( $post_ID ) {
		$is_tutor_product = Input::post( '_tutor_product', '' );
		if ( 'on' === $is_tutor_product ) {
			update_post_meta( $post_ID, '_tutor_product', 'yes' );
		} else {
			delete_post_meta( $post_ID, '_tutor_product' );
		}
	}

	/**
	 * Take enrolled course action based on order status change
	 *
	 * Order auto complete
	 *
	 * @param int    $order_id  wc order id.
	 * @param string $status_from  from status.
	 * @param string $status_to  to status.
	 *
	 * @return void
	 */
	public function enrolled_courses_status_change( $order_id, $status_from, $status_to ) {
		if ( ! tutor_utils()->is_tutor_order( $order_id ) ) {
			return;
		}

		$enrolled_ids_with_course = tutor_utils()->get_course_enrolled_ids_by_order_id( $order_id );

		if ( $enrolled_ids_with_course ) {
			$enrolled_ids = wp_list_pluck( $enrolled_ids_with_course, 'enrolled_id' );

			if ( is_array( $enrolled_ids ) && count( $enrolled_ids ) ) {
				foreach ( $enrolled_ids as $enrolled_id ) {
					/**
					 * If order status is processing and payment is not cash on
					 * delivery then mark enrollment as completed.
					 *
					 * Note: Order status processing simply mean customer have done
					 * payment.
					 *
					 * @since v2.0.5
					 */
					if ( self::should_order_auto_complete( $order_id ) ) {
						// Mark enrollment as completed.
						tutor_utils()->course_enrol_status_change( $enrolled_id, 'completed' );
						// Mark WC order as completed.
						self::mark_order_complete( $order_id );
					} else {
						tutor_utils()->course_enrol_status_change( $enrolled_id, $status_to );
					}

					// Invoke enrolled hook.
					if ( 'completed' === $status_to ) {
						$user_id   = get_post_field( 'post_author', $enrolled_id );
						$course_id = get_post_field( 'post_parent', $enrolled_id );
						do_action( 'tutor_after_enrolled', $course_id, $user_id, $enrolled_id );
					}
				}
			}
		}
	}

	/**
	 * Add option for WooCommerce settings
	 *
	 * @since 1.0.0
	 *
	 * @param array $attr option attrs.
	 *
	 * @return mixed
	 */
	public function add_options( $attr ) {
		$attr['monetization']['blocks']['block_options']['fields'][] = array(
			'key'         => 'enable_guest_course_cart',
			'type'        => 'toggle_switch',
			'label'       => __( 'Enable Guest Mode', 'tutor' ),
			'label_title' => '',
			'default'     => 'off',
			'desc'        => __( 'Allow customers to place orders without an account.', 'tutor' ),
		);

		return $attr;
	}

	/**
	 * Returning monetization options
	 *
	 * @since v.1.3.5
	 *
	 * @param array $arr attrs.
	 *
	 * @return mixed
	 */
	public function tutor_monetization_options( $arr ) {
		$has_wc = tutor_utils()->has_wc();
		if ( $has_wc ) {
			$arr['wc'] = __( 'WooCommerce', 'tutor' );
		}
		return $arr;
	}

	/**
	 * Adding Earning Data processing WooCommerce
	 *
	 * @param int   $item_id item id.
	 * @param mixed $item item.
	 * @param int   $order_id order id.
	 *
	 * @since 1.1.2
	 */
	public function add_earning_data( $item_id, $item, $order_id ) {

		if ( 'wc' !== tutor_utils()->get_option( 'monetize_by' ) ) {
			return;
		}

		global $wpdb;
		$item = new \WC_Order_Item_Product( $item );

		$product_id    = $item->get_product_id();
		$if_has_course = tutor_utils()->product_belongs_with_course( $product_id );

		if ( $if_has_course ) {
			$order        = wc_get_order( $order_id );
			$course_id    = $if_has_course->post_id;
			$user_id      = get_post_field( 'post_author', $course_id );
			$order_status = "wc-{$order->get_status()}";

			/**
			 * Return here if already added this product from this order
			 *
			 * @since v1.9.7
			 */
			$exist_count = (int) $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(earning_id)
				FROM {$wpdb->prefix}tutor_earnings
				WHERE course_id=%d
					AND order_id=%d
					AND user_id=%d",
					$course_id,
					$order_id,
					$user_id
				)
			);

			if ( $exist_count > 0 ) {
				return;
			}

			$total_price = $item->get_total();

			$fees_deduct_data      = array();
			$tutor_earning_fees    = tutor_utils()->get_option( 'fee_amount_type' );
			$enable_fees_deducting = tutor_utils()->get_option( 'enable_fees_deducting' );

			$course_price_grand_total = $total_price;

			// Deduct predefined amount (percent or fixed).
			if ( $enable_fees_deducting ) {
				$fees_name   = tutor_utils()->get_option( 'fees_name', '' );
				$fees_amount = (int) tutor_utils()->avalue_dot( 'fees_amount', $tutor_earning_fees );
				$fees_type   = tutor_utils()->avalue_dot( 'fees_type', $tutor_earning_fees );

				if ( $fees_amount > 0 ) {
					if ( 'percent' === $fees_type ) {
						$fees_amount = ( $total_price * $fees_amount ) / 100;
					}

					$course_price_grand_total = $total_price - $fees_amount;
				}

				$fees_deduct_data = array(
					'deduct_fees_amount' => $fees_amount,
					'deduct_fees_name'   => $fees_name,
					'deduct_fees_type'   => $fees_type,
				);
			}

			// Distribute amount between admin and instructor.
			$sharing_enabled   = tutor_utils()->get_option( 'enable_revenue_sharing' );
			$instructor_rate   = $sharing_enabled ? tutor_utils()->get_option( 'earning_instructor_commission' ) : 0;
			$admin_rate        = $sharing_enabled ? tutor_utils()->get_option( 'earning_admin_commission' ) : 100;
			$commission_type   = 'percent';
			$instructor_amount = $instructor_rate > 0 ? ( ( $course_price_grand_total * $instructor_rate ) / 100 ) : 0;
			$admin_amount      = $admin_rate > 0 ? ( ( $course_price_grand_total * $admin_rate ) / 100 ) : 0;

			// (Use Pro Filter - Start)
			// The response must be same array structure.
			// Do not change used variable names here, or change in both of here and pro plugin
			$pro_arg         = array(
				'user_id'                  => $user_id,
				'instructor_rate'          => $instructor_rate,
				'admin_rate'               => $admin_rate,
				'instructor_amount'        => $instructor_amount,
				'admin_amount'             => $admin_amount,
				'course_price_grand_total' => $course_price_grand_total,
				'commission_type'          => $commission_type,
			);
			$pro_calculation = apply_filters( 'tutor_pro_earning_calculator', $pro_arg );
			extract( $pro_calculation ); //phpcs:ignore
			// (Use Pro Filter - End).

			// Prepare insertable earning data.
			$earning_data = array(
				'user_id'                  => $user_id,
				'course_id'                => $course_id,
				'order_id'                 => $order_id,
				'order_status'             => $order_status,
				'course_price_total'       => $total_price,
				'course_price_grand_total' => $course_price_grand_total,

				'instructor_amount'        => $instructor_amount,
				'instructor_rate'          => $instructor_rate,
				'admin_amount'             => $admin_amount,
				'admin_rate'               => $admin_rate,

				'commission_type'          => $commission_type,
				'process_by'               => 'woocommerce',
				'created_at'               => gmdate( 'Y-m-d H:i:s', tutor_time() ),
			);
			$earning_data = apply_filters( 'tutor_new_earning_data', array_merge( $earning_data, $fees_deduct_data ) );

			$wpdb->insert( $wpdb->prefix . 'tutor_earnings', $earning_data );
		}
	}

	/**
	 * Change Earning data status
	 *
	 * @since 1.0.0
	 *
	 * @param int    $order_id wc order id.
	 * @param string $status_from previous status.
	 * @param string $status_to current status.
	 *
	 * @return void
	 */
	public function add_earning_data_status_change( $order_id, $status_from, $status_to ) {
		if ( ! tutor_utils()->is_tutor_order( $order_id ) ) {
			tutor_log( 'not tutor order' );
			return;
		}

		/**
		 * If it is auto complete order then make earning status complete
		 * to reflect earning for admin & instructor
		 *
		 * @since 2.0.9
		 */
		if ( self::should_order_auto_complete( $order_id ) ) {
			$status_to = 'completed';
		}

		tutor_utils()->change_earning_status( $order_id, $status_to );
	}

	/**
	 * Course placing order from admin
	 *
	 * @since v.1.6.7
	 *
	 * @param int $order_id wc order id.
	 *
	 * @return void
	 */
	public function course_placing_order_from_admin( $order_id ) {
		if ( ! is_admin() ) {
			return;
		}

		$order = wc_get_order( $order_id );
		foreach ( $order->get_items() as $item ) {
			$product_id    = $item->get_product_id();
			$if_has_course = tutor_utils()->product_belongs_with_course( $product_id );
			if ( $if_has_course ) {
				$course_id   = $if_has_course->post_id;
				$customer_id = $order->get_customer_id();
				tutor_utils()->do_enroll( $course_id, $order_id, $customer_id );
			}
		}
	}

	/**
	 * Course placing order from customer
	 *
	 * @since 1.6.7
	 *
	 * @param int   $item_id item id.
	 * @param mixed $item order item.
	 * @param int   $order_id wc order id.
	 *
	 * @return void
	 */
	public function course_placing_order_from_customer( $item_id, $item, $order_id ) {
		if ( is_admin() ) {
			return;
		}

		$item          = new \WC_Order_Item_Product( $item );
		$product_id    = $item->get_product_id();
		$if_has_course = tutor_utils()->product_belongs_with_course( $product_id );

		if ( $if_has_course ) {
			$order = wc_get_order( $order_id );

			/**
			 * Get customer ID from from order
			 *
			 * @since 2.1.7
			 */
			$customer_id = $order->get_customer_id();
			$course_id   = $if_has_course->post_id;
			tutor_utils()->do_enroll( $course_id, $order_id, $customer_id );
		}
	}

	/**
	 * Disable course monetization on woocommerce deactivation
	 *
	 * @since 1.7.8
	 *
	 * @return void
	 */
	public function disable_tutor_monetization() {
		tutor_utils()->update_option( 'monetize_by', 'free' );
		update_option( 'tutor_show_woocommerce_notice', true );
	}

	/**
	 * Redirect student on enrolled courses after course
	 * enrollment complete if course is purchasable
	 *
	 * @since 1.0.0
	 *
	 * @param int $order_id wc order id.
	 *
	 * @return void
	 */
	public function redirect_to_enrolled_courses( $order_id ) {
		if ( ! tutor_utils()->get_option( 'wc_automatic_order_complete_redirect_to_courses' ) ) {
			// Since 1.9.1.
			return;
		}

		// get woo order details.
		$order         = wc_get_order( $order_id );
		$tutor_product = false;
		$url           = tutor_utils()->tutor_dashboard_url() . 'enrolled-courses/';

		foreach ( $order->get_items() as $item ) {
			$product_id = $item->get_product_id();
			// check if product associated with tutor course.
			$if_has_course = tutor_utils()->product_belongs_with_course( $product_id );
			if ( $if_has_course ) {
				$tutor_product = true;
			}
		}

		// if tutor product & order status completed.
		if ( $order->has_status( 'completed' ) && $tutor_product ) {
			wp_safe_redirect( $url );
			exit;
		}
	}

	/**
	 * Change product url on cart page if product is tutor course
	 *
	 * @since 1.9.8
	 *
	 * @param string $permalink permalink.
	 * @param mixed  $cart_item cart item.
	 *
	 * @return mixed
	 */
	public function tutor_update_product_url( $permalink, $cart_item ) {

		$woo_product_id = $cart_item['product_id'];
		$product_meta   = get_post_meta( $woo_product_id );

		if ( isset( $product_meta['_tutor_product'] ) && $product_meta['_tutor_product'][0] ) {

			global $wpdb;
			$table   = $wpdb->base_prefix . 'postmeta';
			$post_id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM {$table} WHERE meta_key = '_tutor_course_product_id' AND meta_value = %d ", $woo_product_id ) ); //phpcs:ignore

			if ( $post_id ) {
				$data = get_post_permalink( $post_id );
				return $data;
			}
		}
	}

	/**
	 * Mark woocommerce order as complete only from the
	 * client side.
	 *
	 * @since 2.0.5
	 *
	 * @param int $order_id wc order id.
	 *
	 * @return bool
	 */
	public static function mark_order_complete( int $order_id ): bool {
		if ( is_admin() ) {
			return false;
		}

		$order = \wc_get_order( $order_id );
		$order->set_status( 'completed' );
		$update = $order->save();

		return (bool) $update;
	}

	/**
	 * Check if order should auto complete
	 *
	 * @since 2.0.9
	 *
	 * Bank transfer & check payments will consider as manual
	 * payments. Hence, if user pay with bank & check will not
	 * be auto complete
	 *
	 * @since 2.1.4
	 *
	 * @param int $order_id  wc order id.
	 *
	 * @return boolean  return true if it should auto complete, otherwise false
	 */
	public static function should_order_auto_complete( int $order_id ): bool {
		$auto_complete = false;

		$order      = wc_get_order( $order_id );
		$order_data = is_object( $order ) && method_exists( $order, 'get_data' ) ? $order->get_data() : array();

		$payment_method = isset( $order_data['payment_method'] ) ? $order_data['payment_method'] : '';
		$monetize_by    = tutor_utils()->get_option( 'monetize_by' );

		$should_auto_complete     = tutor_utils()->get_option( 'tutor_woocommerce_order_auto_complete' );
		$is_enabled_auto_complete = 'wc' === $monetize_by && $should_auto_complete ? true : false;

		$manual_payments = array( 'cod', 'cheque', 'bacs' );
		$order_status    = method_exists( $order, 'get_status' ) ? $order->get_status() : '';

		if ( 'completed' !== $order_status ) {
			$is_tutor_order = tutor_utils()->is_tutor_order( $order->get_id() );

			/**
			 * Is tutor order condition added with other conditions,
			 * to prevent order other than Tutor get completed
			 *
			 * @since 2.1.6
			 */
			if ( ! is_admin() && $is_enabled_auto_complete && 'processing' === $order_status && ! in_array( $payment_method, $manual_payments ) && $is_tutor_order ) {
				$auto_complete = true;
			}
		} else {
			$auto_complete = true;
		}

		return $auto_complete;
	}
}

