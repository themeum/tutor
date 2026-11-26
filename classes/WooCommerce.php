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

defined( 'ABSPATH' ) || exit;

use Tutor\Core\Input;
use Tutor\Helpers\QueryHelper;
use Tutor\Helpers\UrlHelper;
use Tutor\Models\EnrollmentModel;

/**
 * Handle woocommerce hooks
 *
 * @since 1.0.0
 */
class WooCommerce extends Tutor_Base {

	const MONETIZE_BY = 'wc';

	const TUTOR_WC_GUEST_CUSTOMER_ID   = '_tutor_wc_guest_customer_id';
	const WC_STORE_API_DRAFT_ORDER     = 'store_api_draft_order';
	const TUTOR_COURSE_PRODUCT_ID_META = '_tutor_course_product_id';

	const RECEIPT_QUERY_PARAM = 'tutor-wc-receipt';

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

		/**
		 * Is Course Purchasable
		 */
		add_filter( 'is_course_purchasable', array( $this, 'is_course_purchasable' ), 10, 2 );
		add_filter( 'get_tutor_course_price', array( $this, 'get_tutor_course_price' ), 10, 2 );
		add_filter( 'tutor_course_sell_by', array( $this, 'tutor_course_sell_by' ) );

		add_filter( 'product_type_options', array( $this, 'add_tutor_type_in_wc_product' ) );

		add_action( 'save_post_' . $this->course_post_type, array( $this, 'save_course_meta' ), 10, 2 );
		add_action( 'save_post_product', array( $this, 'save_wc_product_meta' ) );

		add_action( 'tutor_course/single/before/enroll', 'wc_print_notices' );

		/**
		 * After place new order
		 */
		add_action( 'woocommerce_new_order', array( $this, 'course_placing_order_from_admin' ), 10, 3 );
		add_action( 'woocommerce_new_order_item', array( $this, 'course_placing_order_from_customer' ), 10, 3 );
		add_action( 'woocommerce_order_status_changed', array( $this, 'handle_customer_order_by_block_checkout' ), 9, 3 );

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
		$woocommerce_path = WP_PLUGIN_DIR . '/woocommerce/woocommerce.php';
		register_deactivation_hook( $woocommerce_path, array( $this, 'woocommerce_deactivation_handler' ) );

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

		add_action( 'woocommerce_order_after_calculate_totals', array( $this, 'add_coupon_to_order' ), 10, 2 );

		add_action( 'woocommerce_guest_session_to_user_id', array( $this, 'enroll_guest_user' ), 10, 2 );

		add_filter( 'woocommerce_shortcode_products_query_results', array( $this, 'filter_products_query_results' ), 10, 2 );

		add_filter( 'woocommerce_shortcode_products_query', array( $this, 'filter_tutor_course_products' ) );

		// @since 4.0.0
		add_filter( 'tutor_order_history_card_template', fn( $template ) => tutor_get_template( 'dashboard.account.billing.wc-order-history-card' ) );
		add_filter( 'tutor_order_history_status_options', array( $this, 'filter_order_history_status_options' ), 10, 2 );
		add_filter( 'tutor_get_orders_by_user_id', array( $this, 'filter_tutor_get_orders_by_user_id' ), 10, 3 );
		add_filter( 'template_include', array( $this, 'load_wc_receipt_template' ), PHP_INT_MAX );
		add_filter( 'tutor_is_dashboard_page', array( $this, 'filter_is_dashboard_page_for_receipt' ) );
		add_filter( 'tutor_should_load_legacy_scripts', array( $this, 'filter_should_load_legacy_scripts_for_receipt' ) );
	}

	/**
	 * Filter tutor courses from shortcode shop page if enabled.
	 *
	 * @since 3.6.2
	 *
	 * @param array $query_args the query args.
	 *
	 * @return array
	 */
	public function filter_tutor_course_products( $query_args ) {
		$hide_course_from_shop_page = (bool) get_tutor_option( 'hide_course_from_shop_page' );

		if ( ! $hide_course_from_shop_page ) {
			return $query_args;
		}

		$course_ids = ( new Course() )->get_connected_wc_product_ids();

		$query_args['post__not_in'] = $course_ids;

		return $query_args;
	}

	/**
	 * Filter woocommerce shop query result to remove scheduled posts.
	 *
	 * @since 3.6.2
	 *
	 * @param \WP_Query $results the query result object.
	 * @param object    $wc_shortcode_products_obj the shortcode product class object.
	 *
	 * @return array
	 */
	public function filter_products_query_results( $results, $wc_shortcode_products_obj ) {
		$ids = $results->ids ?? array();

		if ( $ids ) {
			$filtered_ids = array_filter(
				$ids,
				function ( $val ) {
					$course_id = (int) $this->get_post_id_by_meta_key_and_value( self::TUTOR_COURSE_PRODUCT_ID_META, $val );
					if ( ! $course_id ) {
						return true;
					}

					if ( $course_id && 'future' !== get_post_status( $course_id ) ) {
						return true;
					}
				}
			);

			$results->ids = $filtered_ids;
		}

		return $results;
	}

	/**
	 * Enroll students to course after guest checkout.
	 *
	 * @since 3.2.0
	 *
	 * @param int $guest_customer_id the customer id of guest user.
	 * @param int $customer_id the customer id of registered user.
	 *
	 * @return void
	 */
	public function enroll_guest_user( $guest_customer_id, $customer_id ) {
		global $wpdb;
		$course_ids = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key=%s AND meta_value=%s",
				self::TUTOR_WC_GUEST_CUSTOMER_ID,
				$guest_customer_id
			)
		);

		$order_id = WC()->session->get( self::WC_STORE_API_DRAFT_ORDER, 0 );

		if ( $course_ids && $order_id ) {
			$wc_order = wc_get_order( $order_id );
			if ( ! $wc_order ) {
				return;
			}

			foreach ( $course_ids as $course_id ) {
				EnrollmentModel::do_enroll( $course_id, $order_id, $customer_id );
			}
		}
	}


	/**
	 * Add manual coupon discount to wc order items.
	 *
	 * @since 3.0.0
	 *
	 * @param bool   $taxes whether to consider the taxes.
	 * @param object $order the order object.
	 *
	 * @return void
	 */
	public function add_coupon_to_order( $taxes, $order ) {
		global $wpdb;
		$earning_id = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT earning_id FROM {$wpdb->prefix}tutor_earnings WHERE order_id=%d",
				$order->get_id()
			)
		);

		if ( $earning_id ) {
			$items = $order->get_items();
			foreach ( $items as $item ) {

				$item = new \WC_Order_Item_Product( $item );

				$product_id    = $item->get_product_id();
				$if_has_course = tutor_utils()->product_belongs_with_course( $product_id );
				if ( $if_has_course ) {
					$course_id    = $if_has_course->post_id;
					$user_id      = get_post_field( 'post_author', $course_id );
					$order_status = "wc-{$order->get_status()}";
					$total_price  = $item->get_total();

					list( $admin_amount, $instructor_amount ) = array_values( tutor_split_amounts( $total_price ) );

					$earnings     = Earnings::get_instance();
					$earning_data = apply_filters( 'tutor_new_earning_data', $earnings->prepare_earning_data( $total_price, $course_id, $order->get_id(), $order_status, $admin_amount, $instructor_amount ) );

					$wpdb->update( $wpdb->prefix . 'tutor_earnings', $earning_data, array( 'earning_id' => $earning_id ) );
				}
			}
		}
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
			$message = __( 'Since monetization is currently disabled, your courses are set to free. Enable Tutor LMS monetization to start selling your courses.', 'tutor' );
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
		$price_type     = tutor_utils()->price_type( $course_id );
		$has_product_id = tutor_utils()->get_course_product_id( $course_id );

		/**
		 * WC course bundle don't have free or paid price types.
		 * Check if the bundle has a WC product attached.
		 */
		if ( $has_product_id && tutor()->bundle_post_type === get_post_type( $course_id ) ) {
			return true;
		}

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

						$should_fire_hook = apply_filters( 'tutor_should_fire_after_enrolled_for_wc_order', true, $order_id, $enrolled_id );
						if ( $should_fire_hook ) {
							do_action( 'tutor_after_enrolled', $course_id, $user_id, $enrolled_id );
						}
					}
				}
			}
		}
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
			$arr[ self::MONETIZE_BY ] = __( 'WooCommerce', 'tutor' );
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

		$product_id = $item->get_product_id();
		$order      = wc_get_order( $order_id );
		if ( ! $order ) {
			return;
		}

		/**
		 * Prevent adding earning data when order is created with WC block checkout page.
		 *
		 * @since 3.9.14
		 *
		 * @since 4.0.5 Check session before using $order->has_status
		 */
		if ( WC()->session && $order->has_status( 'checkout-draft' ) ) {
			return;
		}

		$order_type = $order->get_type();

		if ( 'shop_subscription' === $order_type ) {
			return;
		}

		$if_has_course = tutor_utils()->product_belongs_with_course( $product_id );

		if ( $if_has_course && is_object( $order ) ) {
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

			list( $admin_amount, $instructor_amount ) = array_values( tutor_split_amounts( $total_price ) );

			$earnings     = Earnings::get_instance();
			$earning_data = apply_filters( 'tutor_new_earning_data', $earnings->prepare_earning_data( $total_price, $course_id, $order_id, $order_status, $admin_amount, $instructor_amount ) );

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
		/**
		 * Loop though each WC order item.
		 *
		 * @var WC_Order_Item $item WC order item object.
		 */
		foreach ( $order->get_items() as $item ) {
			$product_id    = $item->get_product_id();
			$if_has_course = tutor_utils()->product_belongs_with_course( $product_id );
			if ( $if_has_course ) {
				$course_id   = $if_has_course->post_id;
				$customer_id = $order->get_customer_id();
				EnrollmentModel::do_enroll( $course_id, $order_id, $customer_id );
			}
		}
	}

	/**
	 * Handle checkout order item.
	 *
	 * @since 3.9.14
	 *
	 * @param object $order the order object.
	 * @param object $item the order item object.
	 * @param string $context the context of checkout page.
	 *
	 * @return void
	 */
	private function handle_checkout_order_item( $order, $item, $context = 'classic_checkout' ) {
		if ( ! $order instanceof \WC_Order || ! $item instanceof \WC_Order_Item_Product ) {
			return;
		}

		$order_id     = $order->get_id();
		$is_gift_item = apply_filters( 'tutor_is_gift_item', false, $item );
		if ( $is_gift_item ) {
			return;
		}

		$product_id    = $item->get_product_id();
		$if_has_course = tutor_utils()->product_belongs_with_course( $product_id );

		if ( $if_has_course ) {
			$should_process = apply_filters( 'tutor_wc_should_process_checkout_order_item', true, $item, $order );
			if ( ! $should_process ) {
				return;
			}

			$course_id   = $if_has_course->post_id;
			$customer_id = $order->get_customer_id();

			// Handle course enrollment.
			if ( ! $customer_id && WC()->session && WC()->session->has_session() ) {
				$guest_customer_id = WC()->session->get_customer_unique_id();
				update_post_meta( $course_id, self::TUTOR_WC_GUEST_CUSTOMER_ID, $guest_customer_id );
			} else {
				$has_enrollment = tutor_utils()->is_enrolled( $course_id, $customer_id, true );
				if ( ! $has_enrollment ) {
					tutor_utils()->do_enroll( $course_id, $order_id, $customer_id );
				}
			}

			if ( 'block_checkout' === $context ) {
				$this->add_earning_data( $item->get_id(), $item, $order_id );
			}
		}
	}


	/**
	 * Course placing order from customer
	 *
	 * @since 1.6.7
	 * @since 3.8.0 Filter hook: tutor_is_gift_item added
	 * @since 4.0.0 Filter hook: tutor_wc_should_process_checkout_order_item added
	 *              Prevent duplicate enrollment on checkout.
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

		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return;
		}

		/**
		 * Prevent when order is created with WC block checkout page.
		 *
		 * @since 3.9.14
		 *
		 * @since 4.0.5 Check session before using $order->has_status
		 */
		if ( WC()->session && $order->has_status( 'checkout-draft' ) ) {
			return;
		}

		$this->handle_checkout_order_item( $order, $item, 'classic_checkout' );
	}

	/**
	 * Handle order status change by block checkout page.
	 *
	 * @since 3.9.14
	 *
	 * @param int    $order_id    WooCommerce order ID.
	 * @param string $status_from Previous order status.
	 * @param string $status_to   New order status.
	 *
	 * @return void
	 */
	public function handle_customer_order_by_block_checkout( $order_id, $status_from, $status_to ) {
		if ( 'checkout-draft' !== $status_from ) {
			return;
		}

		// If transitioning to another draft or invalid status, do nothing.
		if ( in_array( $status_to, array( 'checkout-draft', 'draft' ), true ) ) {
			return;
		}

		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return;
		}

		foreach ( $order->get_items() as $item ) {
			$this->handle_checkout_order_item( $order, $item, 'block_checkout' );
		}
	}

	/**
	 * Handle disabling WooCommerce monetization on WooCommerce plugin deactivation
	 *
	 * @since 1.7.8
	 *
	 * @return void
	 */
	public function woocommerce_deactivation_handler() {
		if ( tutor_utils()->get_option( 'monetize_by' ) === 'wc' ) {
			tutor_utils()->update_option( 'monetize_by', 'free' );
			/**
			 * Show a reminder to re-enable Tutor monetization to
			 * monetize courses after re-activating WooCommerce:
			 *
			 * Possible follow-up fix: Only show a notice when
			 * WooCommerce was re-activated after this forced
			 * disabling of WooCommerce monetization took place:
			 */
			update_option( 'tutor_show_woocommerce_notice', true );
		}
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
		$url           = tutor_utils()->tutor_dashboard_url() . 'courses/';

		/**
		 * Loop though each WC order item.
		 *
		 * @var WC_Order_Item $item WC order item object.
		 */
		foreach ( $order->get_items() as $item ) {
			$product_id = $item->get_product_id();
			// check if product associated with tutor course.
			$if_has_course = tutor_utils()->product_belongs_with_course( $product_id );
			if ( $if_has_course ) {
				$tutor_product = true;
			}
		}

		// filter redirection url after woocommerce product purchase.
		$redirect_url = apply_filters( 'tutor_woocommerce_redirect_url', $url );

		// if tutor product & order status completed.
		if ( $order->has_status( 'completed' ) && $tutor_product ) {
			wp_safe_redirect( $redirect_url );
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

	/**
	 * Count orders for a user.
	 *
	 * @since 4.0.0
	 *
	 * @param array $args  array of arguments.
	 *
	 * @return int  return count of orders.
	 */
	public static function get_count( $args = array() ): int {
		$query = wc_get_orders(
			array(
				'customer'     => $args['user_id'] ?? 0,
				'status'       => $args['status'] ?? 'all',
				'date_created' => $args['date_created'] ?? null,
				'meta_query'   => array(
					array(
						'key'     => '_is_tutor_order_for_course',
						'compare' => 'EXISTS',
					),
				),
				// Limit 1 for query performance.
				'limit'        => 1,
				'paginate'     => true,
			)
		);

		return $query->total;
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

		$statuses = array_merge( array( 'all' => __( 'All', 'tutor' ) ), wc_get_order_statuses() );
		if ( isset( $statuses['wc-checkout-draft'] ) ) {
			unset( $statuses['wc-checkout-draft'] );
		}

		$start_date = Input::get( 'start_date' );
		$end_date   = Input::get( 'end_date' );

		$options = array();

		foreach ( $statuses as $key => $status ) {
			$params = array(
				'user_id' => $user_id,
				'status'  => $key,
			);

			if ( ! empty( $start_date ) && ! empty( $end_date ) ) {
				$params['date_created'] = $start_date . '...' . $end_date;
			}

			$options[] = array(
				'label'  => $status,
				'value'  => $key,
				'count'  => self::get_count( $params ),
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
	 * @param object $data  data.
	 * @param int    $user_id  user id.
	 * @param array  $args  array of arguments.
	 *
	 * @return object {results: array, total_count: int}
	 */
	public function filter_tutor_get_orders_by_user_id( $data, $user_id, $args ) {
		if ( ! $user_id ) {
			return $data;
		}

		global $wpdb;

		$period     = Input::sanitize( $args['period'] ?? '' );
		$status     = Input::sanitize( $args['status'] ?? 'all' );
		$start_date = Input::sanitize( $args['start_date'] ?? '' );
		$end_date   = Input::sanitize( $args['end_date'] ?? '' );
		$order      = QueryHelper::get_valid_sort_order( $args['order'] ?? 'DESC' );
		$limit      = intval( $args['limit'] ?? 0 );
		$offset     = intval( $args['offset'] ?? 0 );

		$post_type = 'shop_order';
		$user_meta = '_customer_user';
		$wc_hpos   = self::hpos_enabled();
		$dt_column = $wc_hpos ? 'date_created_gmt' : 'post_date';

		$period_query = '';
		if ( ! empty( $period ) ) {
			if ( 'today' === $period ) {
				$period_query = ' AND  DATE(' . $dt_column . ') = CURDATE() ';
			} elseif ( 'monthly' === $period ) {
				$period_query = ' AND  MONTH(' . $dt_column . ') = MONTH(CURDATE()) ';
			} else {
				$period_query = ' AND  YEAR(' . $dt_column . ') = YEAR(CURDATE()) ';
			}
		}

		if ( ! empty( $start_date ) && ! empty( $end_date ) ) {
			$period_query = $wpdb->prepare(
				//phpcs:ignore -- $dt_column is static value
				" AND  DATE($dt_column) BETWEEN CAST(%s AS DATE) AND CAST(%s AS DATE) ",
				$start_date,
				$end_date
			);
		}

		$offset_limit_query = '';
		if ( $offset && $limit ) {
			$offset_limit_query = "LIMIT {$offset}, {$limit}";
		}

		$status_query = '';
		if ( 'all' !== $status ) {
			$status_query = $wc_hpos
							? $wpdb->prepare( 'AND status = %s', $status )
							: $wpdb->prepare( 'AND post_status = %s', $status );
		}

		if ( $wc_hpos ) {
			//phpcs:disable
			$results = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT SQL_CALC_FOUND_ROWS orders.id AS ID, orders.status AS post_status, orders.date_created_gmt AS post_date, orders.* 
					FROM 	{$wpdb->prefix}wc_orders orders 
							INNER JOIN {$wpdb->prefix}wc_orders_meta order_meta 
									ON orders.id = order_meta.order_id
									AND order_meta.meta_key = '_is_tutor_order_for_course' 
					WHERE 	orders.type = %s 
							AND orders.customer_id = %d 
							{$status_query}
							{$period_query}
					ORDER BY orders.id {$order}
					{$offset_limit_query}",
					$post_type,
					$user_id
				)
			);
			//phpcs:enable
		} else {
			//phpcs:disable
			$results = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT SQL_CALC_FOUND_ROWS {$wpdb->posts}.*
					FROM	{$wpdb->posts}
							INNER JOIN {$wpdb->postmeta} customer
									ON id = customer.post_id
								AND customer.meta_key = '{$user_meta}'
							INNER JOIN {$wpdb->postmeta} tutor_order
									ON id = tutor_order.post_id
								AND tutor_order.meta_key = '_is_tutor_order_for_course'
					WHERE	post_type = %s
							AND customer.meta_value = %d
							{$status_query}
							{$period_query}
					ORDER BY {$wpdb->posts}.id {$order}
					{$offset_limit_query}
					",
					$post_type,
					$user_id
				)
			);
			//phpcs:enable
		}

		$total_count = (int) $wpdb->get_var( 'SELECT FOUND_ROWS()' );

		$data->results     = $results;
		$data->total_count = $total_count;

		return $data;
	}

	/**
	 * Get allowed WooCommerce order statuses eligible for a receipt.
	 *
	 * @since 4.3.0
	 *
	 * @param \WC_Order $order WooCommerce order object.
	 *
	 * @return array
	 */
	public static function get_receipt_allowed_statuses( \WC_Order $order ): array {
		$statuses = apply_filters( 'tutor_wc_receipt_allowed_statuses', array( 'completed' ), $order );
		if ( ! is_array( $statuses ) ) {
			return array( 'completed' );
		}

		return array_values( array_filter( array_map( 'strval', $statuses ) ) );
	}

	/**
	 * Determine whether a WooCommerce order is eligible for a receipt.
	 *
	 * @since 4.3.0
	 *
	 * @param \WC_Order $order WooCommerce order object.
	 *
	 * @return bool
	 */
	public static function is_receipt_available( \WC_Order $order ): bool {
		return in_array( $order->get_status(), self::get_receipt_allowed_statuses( $order ), true );
	}

	/**
	 * Build the URL for the standalone WooCommerce receipt page.
	 *
	 * @since 4.3.0
	 *
	 * @param int $order_id WooCommerce order ID.
	 *
	 * @return string
	 */
	public static function get_receipt_url( int $order_id ): string {
		$params = array(
			'dashboard-page'          => Dashboard::BILLING_PAGE_SLUG,
			self::RECEIPT_QUERY_PARAM => $order_id,
		);

		$receipt_url = UrlHelper::add_query_params( home_url( '/' ), $params );

		return (string) apply_filters( 'tutor_wc_receipt_url', $receipt_url, $order_id );
	}

	/**
	 * Check whether the current request targets the WooCommerce receipt page.
	 *
	 * @since 4.3.0
	 *
	 * @return bool
	 */
	public static function is_receipt_page(): bool {
		return (bool) Input::get( self::RECEIPT_QUERY_PARAM, 0, Input::TYPE_INT );
	}

	/**
	 * Mark receipt requests as dashboard pages so Tutor enqueues dashboard assets.
	 *
	 * @since 4.3.0
	 *
	 * @param bool $is_dashboard Whether the current page is the Tutor dashboard.
	 *
	 * @return bool
	 */
	public function filter_is_dashboard_page_for_receipt( bool $is_dashboard ): bool {
		return self::is_receipt_page() ? true : $is_dashboard;
	}

	/**
	 * Disable legacy scripts when rendering the WooCommerce receipt page.
	 *
	 * @since 4.3.0
	 *
	 * @param bool $load Whether legacy scripts should load.
	 *
	 * @return bool
	 */
	public function filter_should_load_legacy_scripts_for_receipt( bool $load ): bool {
		return self::is_receipt_page() ? false : $load;
	}

	/**
	 * Validate access and load the WooCommerce receipt template when requested.
	 *
	 * @since 4.3.0
	 *
	 * @param string $template Resolved template path.
	 *
	 * @return string
	 */
	public function load_wc_receipt_template( string $template ): string {
		if ( ! self::is_receipt_page() || ! tutor_utils()->has_wc() ) {
			return $template;
		}

		$receipt_template = tutor_get_template( 'dashboard.account.billing.wc-receipt' );
		if ( ! is_string( $receipt_template ) || ! file_exists( $receipt_template ) ) {
			return $template;
		}

		if ( function_exists( 'wc_maybe_define_constant' ) ) {
			wc_maybe_define_constant( 'DONOTCACHEPAGE', true );
		}

		nocache_headers();
		if ( ! headers_sent() ) {
			header( 'X-Robots-Tag: noindex, nofollow, noarchive', true );
		}

		$order_id = Input::get( self::RECEIPT_QUERY_PARAM, 0, Input::TYPE_INT );
		$order    = self::validate_receipt_access( $order_id );

		if ( is_wp_error( $order ) ) {
			$error_data = $order->get_error_data();
			$status     = is_array( $error_data ) && isset( $error_data['status'] )
				? absint( $error_data['status'] )
				: 403;

			wp_die(
				esc_html( $order->get_error_message() ),
				esc_html( get_status_header_desc( $status ) ),
				array( 'response' => absint( $status ) )
			);
		}

		status_header( 200 );
		Dashboard::set_document_title(
			sprintf(
				/* translators: 1: order number, 2: site name. */
				__( 'Order Receipt #%1$s - %2$s', 'tutor' ),
				(string) $order->get_order_number(),
				(string) get_bloginfo( 'name' )
			)
		);

		set_query_var( 'tutor_wc_receipt_order', $order );
		set_query_var( 'tutor_wc_receipt_data', self::build_receipt_data( $order ) );

		return $receipt_template;
	}

	/**
	 * Validate authentication, ownership, and order eligibility for receipt access.
	 *
	 * @since 4.3.0
	 *
	 * @param int $order_id WooCommerce order ID.
	 *
	 * @return \WC_Order|\WP_Error Validated order object or WP_Error with HTTP status.
	 */
	public static function validate_receipt_access( int $order_id ) {
		if ( ! is_user_logged_in() ) {
			return new \WP_Error(
				'tutor_wc_receipt_unauthorized',
				__( 'You must be logged in to view this receipt.', 'tutor' ),
				array( 'status' => 401 )
			);
		}

		$order = $order_id && function_exists( 'wc_get_order' ) ? wc_get_order( $order_id ) : false;
		if ( ! ( $order instanceof \WC_Order ) || ( $order instanceof \WC_Order_Refund ) ) {
			return new \WP_Error(
				'tutor_wc_receipt_not_found',
				__( 'Order not found.', 'tutor' ),
				array( 'status' => 404 )
			);
		}

		$current_user_id = get_current_user_id();
		$is_owner        = $current_user_id && (int) $order->get_customer_id() === $current_user_id;
		$is_admin        = User::can( 'manage_options' ) || User::can( 'manage_woocommerce' );

		if ( ! $is_owner && ! $is_admin ) {
			return new \WP_Error(
				'tutor_wc_receipt_forbidden',
				__( 'You do not have permission to view this receipt.', 'tutor' ),
				array( 'status' => 403 )
			);
		}

		$require_order = (bool) apply_filters( 'tutor_wc_receipt_require_tutor_order', true, $order );
		if ( $require_order && ! tutor_utils()->is_tutor_order( $order_id ) ) {
			return new \WP_Error(
				'tutor_wc_receipt_not_tutor_order',
				__( 'Receipt is unavailable for this order.', 'tutor' ),
				array( 'status' => 403 )
			);
		}

		if ( ! self::is_receipt_available( $order ) ) {
			return new \WP_Error(
				'tutor_wc_receipt_status_ineligible',
				__( 'Receipt is not available for this order status.', 'tutor' ),
				array( 'status' => 403 )
			);
		}

		return $order;
	}

	/**
	 * Build normalized Company, Customer, and Order Details payload for a WooCommerce order.
	 *
	 * @since 4.3.0
	 *
	 * @param \WC_Order $order WooCommerce order object.
	 *
	 * @return array
	 */
	public static function build_receipt_data( \WC_Order $order ): array {
		$data = array(
			'company'  => self::get_receipt_company_data( $order ),
			'customer' => self::get_receipt_customer_data( $order ),
			'order'    => self::get_receipt_order_details( $order ),
		);

		return (array) apply_filters( 'tutor_wc_receipt_data', $data, $order );
	}

	/**
	 * Build normalized company information for a WooCommerce receipt.
	 *
	 * @since 4.3.0
	 *
	 * @param \WC_Order $order WooCommerce order object.
	 *
	 * @return array
	 */
	private static function get_receipt_company_data( \WC_Order $order ): array {
		$store_address = '';
		if ( function_exists( 'WC' ) && WC()->countries ) {
			$countries     = WC()->countries;
			$address_parts = array(
				'address_1' => (string) $countries->get_base_address(),
				'address_2' => (string) $countries->get_base_address_2(),
				'city'      => (string) $countries->get_base_city(),
				'state'     => (string) $countries->get_base_state(),
				'postcode'  => (string) $countries->get_base_postcode(),
				'country'   => (string) $countries->get_base_country(),
			);

			$has_address = '' !== $address_parts['address_1']
				|| '' !== $address_parts['city']
				|| '' !== $address_parts['postcode'];

			if ( $has_address ) {
				$store_address = (string) $countries->get_formatted_address( $address_parts, '<br>' );
			}
		}

		$logo_url = '';
		$logo_ids = array(
			absint( get_tutor_option( 'brand_logo_light', 0 ) ),
			absint( get_theme_mod( 'custom_logo', 0 ) ),
		);

		foreach ( $logo_ids as $logo_id ) {
			if ( $logo_id && wp_attachment_is_image( $logo_id ) ) {
				$url = wp_get_attachment_image_url( $logo_id, 'full' );
				if ( is_string( $url ) && '' !== $url ) {
					$logo_url = $url;
					break;
				}
			}
		}

		$company_email = (string) tutor_utils()->get_option( 'email_from_address', '' );
		if ( '' === $company_email ) {
			$company_email = (string) get_option( 'admin_email', '' );
		}

		$company = array(
			'name'              => (string) get_bloginfo( 'name' ),
			'logo_url'          => $logo_url,
			'formatted_address' => $store_address,
			'email'             => $company_email,
			'website'           => home_url( '/' ),
			'phone'             => '',
			'tax_id'            => '',
			'footer_note'       => '',
		);

		return (array) apply_filters( 'tutor_wc_receipt_company_data', $company, $order );
	}

	/**
	 * Build normalized customer billing information for a WooCommerce receipt.
	 *
	 * @since 4.3.0
	 *
	 * @param \WC_Order $order WooCommerce order object.
	 *
	 * @return array
	 */
	private static function get_receipt_customer_data( \WC_Order $order ): array {
		$identity = self::get_order_customer_identity( $order );
		$customer = array(
			'user_id'           => (int) $order->get_customer_id(),
			'name'              => $identity['name'],
			'company'           => (string) $order->get_billing_company(),
			'formatted_address' => (string) $order->get_formatted_billing_address(),
			'email'             => $identity['email'],
			'phone'             => (string) $order->get_billing_phone(),
			'note'              => (string) $order->get_customer_note(),
			'tax_id'            => '',
		);

		return (array) apply_filters( 'tutor_wc_receipt_customer_data', $customer, $order );
	}

	/**
	 * Build normalized order metadata, line items, and totals for a WooCommerce receipt.
	 *
	 * @since 4.3.0
	 *
	 * @param \WC_Order $order WooCommerce order object.
	 *
	 * @return array
	 */
	private static function get_receipt_order_details( \WC_Order $order ): array {
		$currency    = (string) $order->get_currency();
		$status_slug = (string) $order->get_status();
		$items       = array();

		foreach ( $order->get_items( 'line_item' ) as $item ) {
			if ( ! ( $item instanceof \WC_Order_Item_Product ) ) {
				continue;
			}

			$product       = $item->get_product();
			$line_subtotal = (float) $item->get_subtotal();
			$line_total    = (float) $item->get_total();
			$course_id     = self::get_receipt_item_course_id( $item );

			$items[] = array(
				'name'               => (string) $item->get_name(),
				'course_id'          => $course_id,
				'is_course'          => (bool) $course_id,
				'image'              => self::get_receipt_course_thumbnail( $course_id ),
				'sku'                => ( $product instanceof \WC_Product ) ? (string) $product->get_sku() : '',
				'quantity'           => (int) $item->get_quantity(),
				'formatted_subtotal' => self::format_wc_price_html( $line_subtotal, $currency ),
				'formatted_total'    => self::format_wc_price_html( $line_total, $currency ),
				'has_item_discount'  => $line_subtotal > $line_total,
			);
		}

		$order_total    = (float) $order->get_total();
		$total_refunded = (float) $order->get_total_refunded();
		$net_total      = max( 0.0, $order_total - $total_refunded );
		$subtotal_html  = self::format_wc_price_html( (float) $order->get_subtotal(), $currency );
		$total_html     = self::format_wc_price_html( $order_total, $currency );
		$refunded_html  = self::format_wc_price_html( $total_refunded, $currency );
		$net_total_html = self::format_wc_price_html( $net_total, $currency );

		return array(
			'id'                   => (int) $order->get_id(),
			'number'               => (string) $order->get_order_number(),
			'status'               => $status_slug,
			'status_label'         => self::get_order_status_label( $status_slug, ucfirst( $status_slug ) ),
			'currency'             => $currency,
			'date_created'         => self::format_wc_datetime( $order->get_date_created() ),
			'date_paid'            => self::format_wc_datetime( $order->get_date_paid() ),
			'date_completed'       => self::format_wc_datetime( $order->get_date_completed() ),
			'payment_method_title' => (string) $order->get_payment_method_title(),
			'transaction_id'       => (string) $order->get_transaction_id(),
			'coupon_codes'         => array_values( array_map( 'strval', $order->get_coupon_codes() ) ),
			'total_refunded'       => $total_refunded,
			'net_total'            => $net_total,
			'items'                => $items,
			'totals_rows'          => $order->get_order_item_totals(),
			'formatted_subtotal'   => $subtotal_html,
			'formatted_total'      => $total_html,
			'formatted_refunded'   => $refunded_html,
			'formatted_net_total'  => $net_total_html,
		);
	}

	/**
	 * Resolve a human-readable WooCommerce order status label.
	 *
	 * @since 4.3.0
	 *
	 * @param string $status_slug WooCommerce order status slug.
	 * @param string $fallback    Fallback label when helper is unavailable.
	 *
	 * @return string
	 */
	private static function get_order_status_label( string $status_slug, string $fallback = '' ): string {
		if ( function_exists( 'wc_get_order_status_name' ) ) {
			return (string) wc_get_order_status_name( $status_slug );
		}

		return '' !== $fallback ? $fallback : $status_slug;
	}

	/**
	 * Format a WooCommerce date/time object safely.
	 *
	 * @since 4.3.0
	 *
	 * @param mixed $date WooCommerce date/time instance or null.
	 *
	 * @return string
	 */
	private static function format_wc_datetime( $date ): string {
		if ( $date && function_exists( 'wc_format_datetime' ) ) {
			return (string) wc_format_datetime( $date );
		}

		return '';
	}

	/**
	 * Resolve display name and email from an order with WordPress user fallbacks.
	 *
	 * @since 4.3.0
	 *
	 * @param \WC_Order $order WooCommerce order object.
	 *
	 * @return array
	 */
	private static function get_order_customer_identity( \WC_Order $order ): array {
		$user  = $order->get_user();
		$name  = trim( (string) $order->get_formatted_billing_full_name() );
		$email = (string) $order->get_billing_email();

		if ( '' === $name && $user instanceof \WP_User ) {
			$name = (string) $user->display_name;
		}

		if ( '' === $email && $user instanceof \WP_User ) {
			$email = (string) $user->user_email;
		}

		return array(
			'name'  => $name,
			'email' => $email,
		);
	}

	/**
	 * Resolve the linked Tutor course ID for a WooCommerce product order item.
	 *
	 * @since 4.3.0
	 *
	 * @param \WC_Order_Item_Product $item WooCommerce product order item.
	 *
	 * @return int
	 */
	private static function get_receipt_item_course_id( \WC_Order_Item_Product $item ): int {
		$product_id  = (int) $item->get_product_id();
		$course_link = $product_id ? tutor_utils()->product_belongs_with_course( $product_id ) : null;

		return is_object( $course_link ) && ! empty( $course_link->post_id )
			? (int) $course_link->post_id
			: 0;
	}

	/**
	 * Resolve a thumbnail URL for a linked Tutor course on the receipt.
	 *
	 * @since 4.3.0
	 *
	 * @param int $course_id Linked Tutor course post ID.
	 *
	 * @return string
	 */
	private static function get_receipt_course_thumbnail( int $course_id ): string {
		if ( ! $course_id ) {
			return tutor()->url . 'assets/images/placeholder.svg';
		}

		return (string) get_tutor_course_thumbnail_src( 'post-thumbnail', $course_id );
	}

	/**
	 * Format an amount with WooCommerce currency HTML.
	 *
	 * @since 4.3.0
	 *
	 * @param float  $amount   Numeric price amount.
	 * @param string $currency Three-letter currency code.
	 *
	 * @return string
	 */
	public static function format_wc_price_html( float $amount, string $currency = '' ): string {
		if ( function_exists( 'wc_price' ) ) {
			$args = '' !== $currency ? array( 'currency' => $currency ) : array();

			return (string) wc_price( $amount, $args );
		}

		return (string) tutor_get_formatted_price( $amount );
	}
}
