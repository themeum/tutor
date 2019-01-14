<?php
/**
 * Created by PhpStorm.
 * User: mhshohel
 * Date: 1/10/18
 * Time: 3:01 PM
 */

namespace TUTOR_WC;
use TUTOR\Tutor_Base;

class WooCommerce extends Tutor_Base {

	public function __construct() {
		parent::__construct();


		/**
		 * Is Course Purchasable
		 */
		add_filter('is_course_purchasable', array($this, 'is_course_purchasable'), 10, 2);
		add_filter('get_tutor_course_price', array($this, 'get_tutor_course_price'), 10, 2);
		add_filter('tutor_course_sell_by', array($this, 'tutor_course_sell_by'));

		add_filter('product_type_options', array($this, 'add_tutor_type_in_wc_product'));

		add_action( 'add_meta_boxes', array($this, 'register_meta_box') );
		add_action('save_post_'.$this->course_post_type, array($this, 'save_course_meta'));
		add_action('save_post_product', array($this, 'save_wc_product_meta'));

		add_action('tutor_course/single/before/enroll', 'wc_print_notices');

		add_action('woocommerce_new_order_item', array($this, 'course_placing_order'), 10, 3);


		/**
		 * Order Status Hook
		 *
		 * Remove course from active courses if an order is cancelled or refunded
		 */
		add_action( 'woocommerce_order_status_changed', array( $this, 'enrolled_courses_status_change' ), 10, 3 );
	}

	public function is_course_purchasable($bool, $course_id){
		if ( ! tutor_utils()->has_wc()){
			return false;
		}
		$course_sell = tutor_utils()->get_option('enable_course_sell_by_woocommerce');
		if ( ! $course_sell){
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
		$product = wc_get_product( $product_id );

		if ($product) {
			ob_start();
			?>
			<p class="price">
				<?php echo $product->get_price_html(); ?>
			</p>
			<?php
			return ob_get_clean();
		}
		return false;
	}

	public function tutor_course_sell_by(){
		return 'woocommerce';
	}

	public function add_tutor_type_in_wc_product($types){
		$types['tutor_product'] =  array(
			'id'            => '_tutor_product',
			'wrapper_class' => 'show_if_simple',
			'label'         => __( 'For Tutor', 'tutor' ),
			'description'   => __( 'This checkmark ensure that you will sell a specif course via this product.', 'tutor' ),
			'default'       => 'no',
		);

		return $types;
	}

	public function register_meta_box(){
		add_meta_box( 'tutor-attach-product', __( 'Add Product', 'tutor' ), array($this, 'course_add_product_metabox'), $this->course_post_type, 'advanced', 'high' );
	}

	public function course_add_product_metabox(){
		include  tutor()->path.'views/metabox/course-add-product-metabox.php';
	}

	/**
	 * @param $post_ID
	 *
	 * Save course meta for attaching product
	 */
	public function save_course_meta($post_ID){
		$product_id = (int) tutor_utils()->avalue_dot('_tutor_course_product_id', $_POST);
		if ($product_id){
			update_post_meta($post_ID, '_tutor_course_product_id', $product_id);
			//Mark product for woocommerce
			update_post_meta($product_id, '_virtual', 'yes');
			update_post_meta($product_id, '_tutor_product', 'yes');
		}else{
			delete_post_meta($post_ID, '_tutor_course_product_id');
		}
	}



	public function save_wc_product_meta($post_ID){
		$is_tutor_product = tutor_utils()->avalue_dot('_tutor_product', $_POST);
		if ($is_tutor_product === 'on'){
			update_post_meta($post_ID, '_tutor_product', 'yes');
		}else{
			delete_post_meta($post_ID, '_tutor_product');
		}
	}

	/**
	 * Do something after course order place
	 */

	public function course_placing_order( $item_id, $item, $order_id){
		//$item = new \WC_Order_Item_Product($item);

		$product_id = $item->get_product_id();
		$if_has_course = tutor_utils()->product_belongs_with_course($product_id);

		if ($if_has_course){
			$course_id = $if_has_course->post_id;
			tutor_utils()->do_enroll($course_id, $order_id);
		}
	}


	/**
	 *
	 * Take enrolled course action based on order status change
	 */

	public function enrolled_courses_status_change($order_id, $status_from, $status_to){
		if ( ! tutor_utils()->is_tutor_order($order_id)){
			return;
		}
		global $wpdb;

		$enrolled_ids_with_course = $this->get_course_enrolled_ids_by_order_id($order_id);

		if ($enrolled_ids_with_course){
			$enrolled_ids = wp_list_pluck($enrolled_ids_with_course, 'enrolled_id');

			if (is_array($enrolled_ids) && count($enrolled_ids)){
				foreach ($enrolled_ids as $enrolled_id){
					$wpdb->update( $wpdb->posts, array( 'post_status' => $status_to ), array( 'ID' => $enrolled_id ) );
				}
			}
		}
	}

	/**
	 * @param $order_id
	 *
	 * @return array|bool
	 */
	public function get_course_enrolled_ids_by_order_id($order_id){
		global $wpdb;
		//Getting all of courses ids within this order

		$courses_ids = $wpdb->get_results("SELECT * FROM {$wpdb->postmeta} WHERE post_id = {$order_id} AND meta_key LIKE '_tutor_order_for_course_id_%' ");

		if (is_array($courses_ids) && count($courses_ids)){
			$course_enrolled_by_order = array();
			foreach ($courses_ids as $courses_id){
				$course_id = str_replace('_tutor_order_for_course_id_', '',$courses_id->meta_key);
				//array(order_id =>  array('course_id' => $course_id, 'enrolled_id' => enrolled_id))
				$course_enrolled_by_order[$courses_id->post_id] = array('course_id' => $course_id, 'enrolled_id' => $courses_id->meta_value);
			}
			return $course_enrolled_by_order;
		}
		return false;
	}

	/**
	 * Remove course
	 *
	 * TODO: right now it's unused
	 */
	public function remove_active_course($order_id){
		global $wpdb;
		//Getting all of courses ids within this order

		$courses_ids = $wpdb->get_results("SELECT * FROM {$wpdb->postmeta} WHERE post_id = {$order_id} meta_key LIKE '_tutor_order_for_course_id_%' ");
	}


}