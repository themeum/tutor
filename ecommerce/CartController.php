<?php
/**
 * Manage Cart
 *
 * @package Tutor\Ecommerce
 * @author Themeum
 * @link https://themeum.com
 * @since 3.0.0
 */

namespace Tutor\Ecommerce;

use TUTOR\Course;
use Tutor\Helpers\HttpHelper;
use TUTOR\Input;
use Tutor\Models\CartModel;
use Tutor\Traits\JsonResponse;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CartController class
 *
 * @since 3.0.0
 */
class CartController {

	/**
	 * Page slug for cart page
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	public const PAGE_SLUG = 'cart';

	/**
	 * Page slug for cart page
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	public const PAGE_ID_OPTION_NAME = 'tutor_cart_page_id';

	/**
	 * Cart model
	 *
	 * @since 3.0.0
	 *
	 * @var CartModel
	 */
	private $model;

	/**
	 * Trait for sending JSON response
	 */
	use JsonResponse;

	/**
	 * Constructor.
	 *
	 * Initializes the Cart class, sets the page title, and optionally registers
	 * hooks for handling AJAX requests related to cart data, bulk actions, cart updates,
	 * and cart deletions.
	 *
	 * @param bool $register_hooks Whether to register hooks for handling requests. Default is true.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function __construct( $register_hooks = true ) {
		$this->model = new CartModel();

		if ( $register_hooks ) {
			/**
			 * Handle AJAX request for adding course to cart
			 *
			 * @since 3.0.0
			 */
			add_action( 'wp_ajax_tutor_add_course_to_cart', array( $this, 'add_course_to_cart' ) );

			/**
			 * Handle AJAX request for deleting course from cart
			 *
			 * @since 3.0.0
			 */
			add_action( 'wp_ajax_tutor_delete_course_from_cart', array( $this, 'delete_course_from_cart' ) );

			add_filter( 'tutor_course_loop_add_to_cart_button', array( $this, 'restrict_add_to_cart_course_list' ), 10, 2 );
		}
	}

	/**
	 * Replace add to cart with buy now button on course list.
	 *
	 * @since 3.4.0
	 *
	 * @param string $add_to_cart_btn the button content.
	 * @param int    $course_id the course id.
	 *
	 * @return string
	 */
	public function restrict_add_to_cart_course_list( $add_to_cart_btn, $course_id ) {

		$selling_option = Course::get_selling_option( $course_id );
		$btn_class = apply_filters( 'tutor_enroll_required_login_class', ! is_user_logged_in() ? 'tutor-open-login-modal' : '' );

		if ( in_array( $selling_option, array( Course::SELLING_OPTION_BOTH, Course::SELLING_OPTION_SUBSCRIPTION, Course::SELLING_OPTION_MEMBERSHIP ), true ) ) {
			return $add_to_cart_btn;
		}

		if ( Settings::is_buy_now_enabled() ) {
			$checkout_page_url = add_query_arg( array( 'course_id' => $course_id ), CheckoutController::get_page_url() );
			ob_start();
			?>
			<a href="<?php echo esc_url( $checkout_page_url ); ?>" class="tutor-btn tutor-course-list-btn tutor-btn-outline-primary tutor-btn-block <?php echo esc_attr( $btn_class ); ?>">
				<?php esc_html_e( 'Buy Now', 'tutor' ); ?>
			</a>
			<?php

			$add_to_cart_btn = ob_get_clean();
		}

		return $add_to_cart_btn;
	}

	/**
	 * Create cart page
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public static function create_cart_page() {
		$page_id = self::get_page_id();
		if ( ! $page_id ) {
			$args = array(
				'post_title'   => ucfirst( self::PAGE_SLUG ),
				'post_content' => '',
				'post_type'    => 'page',
				'post_status'  => 'publish',
			);

			$page_id = wp_insert_post( $args );
			tutor_utils()->update_option( self::PAGE_ID_OPTION_NAME, $page_id );
		}
	}

	/**
	 * Get cart page url
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	public static function get_page_url() {
		return get_post_permalink( self::get_page_id() );
	}

	/**
	 * Get cart page ID
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	public static function get_page_id() {
		return (int) tutor_utils()->get_option( self::PAGE_ID_OPTION_NAME );
	}

	/**
	 * Get cart items
	 *
	 * @since 3.0.0
	 *
	 * @return array
	 */
	public function get_cart_items() {
		$user_id = tutils()->get_user_id();
		return apply_filters( 'tutor_cart_items', $this->model->get_cart_items( $user_id ), $user_id );
	}

	/**
	 * Get cart count.
	 *
	 * @since 3.0.0
	 *
	 * @param int $user_id logged in user_id.
	 *
	 * @return int
	 */
	public function get_user_cart_item_count( $user_id = 0 ) {
		if ( ! $user_id ) {
			$user_id = tutils()->get_user_id();
		}
		$cart_items = $this->model->get_cart_items( $user_id );
		$cart_count = $cart_items['courses']['total_count'];
		return $cart_count;
	}

	/**
	 * Add course to cart
	 *
	 * @since 3.0.0
	 *
	 * @return void JSON response
	 */
	public function add_course_to_cart() {
		if ( ! tutor_utils()->is_nonce_verified() ) {
			$this->json_response(
				tutor_utils()->error_message( 'nonce' ),
				null,
				HttpHelper::STATUS_BAD_REQUEST
			);
		}

		$user_id   = tutils()->get_user_id();
		$course_id = Input::post( 'course_id', 0, Input::TYPE_INT );

		if ( ! $course_id ) {
			$this->json_response(
				__( 'Invalid course id.', 'tutor' ),
				null,
				HttpHelper::STATUS_BAD_REQUEST
			);
		}

		// Check if the course already exists in the cart or not.
		$is_course_in_user_cart = $this->model->is_course_in_user_cart( $user_id, $course_id );
		if ( $is_course_in_user_cart ) {
			$this->json_response(
				__( 'The course is already in the cart.', 'tutor' ),
				null,
				HttpHelper::STATUS_BAD_REQUEST
			);
		}

		$response = $this->model->add_course_to_cart( $user_id, $course_id );

		if ( $response ) {
			$this->json_response(
				__( 'The course was added to the cart successfully.', 'tutor' ),
				array(
					'cart_page_url' => self::get_page_url(),
					'cart_count'    => self::get_user_cart_item_count( $user_id ),
				),
				HttpHelper::STATUS_CREATED
			);
		} else {
			$this->json_response(
				__( 'Failed to add to cart.', 'tutor' ),
				null,
				HttpHelper::STATUS_BAD_REQUEST
			);
		}
	}

	/**
	 * Delete course from cart
	 *
	 * @since 3.0.0
	 *
	 * @return void JSON response
	 */
	public function delete_course_from_cart() {
		if ( ! tutor_utils()->is_nonce_verified() ) {
			$this->json_response(
				tutor_utils()->error_message( 'nonce' ),
				null,
				HttpHelper::STATUS_BAD_REQUEST
			);
		}

		$user_id   = tutils()->get_user_id();
		$course_id = Input::post( 'course_id', 0, Input::TYPE_INT );

		if ( ! $course_id ) {
			$this->json_response(
				__( 'Invalid course id.', 'tutor' ),
				null,
				HttpHelper::STATUS_BAD_REQUEST
			);
		}

		$response = $this->model->delete_course_from_cart( $user_id, $course_id );

		if ( $response ) {
			ob_start();
			tutor_load_template( 'ecommerce.cart' );
			$cart_template = ob_get_clean();
			$data          = array(
				'cart_template' => $cart_template,
				'cart_count'    => self::get_user_cart_item_count( $user_id ),
			);
			$this->json_response(
				__( 'The course was removed successfully.', 'tutor' ),
				$data,
				HttpHelper::STATUS_OK
			);
		} else {
			$this->json_response(
				__( 'Course remove failed.', 'tutor' ),
				null,
				HttpHelper::STATUS_BAD_REQUEST
			);
		}
	}
}
