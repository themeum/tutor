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
			add_action( 'wp_ajax_tutor_add_course_to_cart', array( $this, 'tutor_add_course_to_cart' ) );

			/**
			 * Handle AJAX request for deleting course from cart
			 *
			 * @since 3.0.0
			 */
			add_action( 'wp_ajax_tutor_delete_course_from_cart', array( $this, 'tutor_delete_course_from_cart' ) );
		}
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
				'post_title'   => self::PAGE_SLUG,
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
		return $this->model->get_cart_items( $user_id );
	}

	/**
	 * Add course to cart
	 *
	 * @since 3.0.0
	 *
	 * @return void JSON response
	 */
	public function tutor_add_course_to_cart() {
		if ( ! tutor_utils()->is_nonce_verified() ) {
			wp_send_json_error( tutor_utils()->error_message( 'nonce' ) );
		}

		$user_id   = tutils()->get_user_id();
		$course_id = Input::post( 'course_id' );

		// Check if the course already exists in the cart or not.
		$is_course_in_user_cart = $this->model->is_course_in_user_cart( $user_id, $course_id );
		if ( $is_course_in_user_cart ) {
			wp_send_json_error( __( 'The course is already in the cart.', 'tutor' ) );
		}

		$response = $this->model->add_course_to_cart( $user_id, $course_id );

		if ( $response ) {
			wp_send_json_success(
				array(
					'message'       => __( 'The course was added to the cart successfully.', 'tutor' ),
					'cart_page_url' => self::get_page_url(),
				)
			);
		} else {
			wp_send_json_error( __( 'Failed to add to cart.', 'tutor' ) );
		}
	}

	/**
	 * Delete course from cart
	 *
	 * @since 3.0.0
	 *
	 * @return void JSON response
	 */
	public function tutor_delete_course_from_cart() {
		if ( ! tutor_utils()->is_nonce_verified() ) {
			wp_send_json_error( tutor_utils()->error_message( 'nonce' ) );
		}

		$user_id   = tutils()->get_user_id();
		$course_id = Input::post( 'course_id' );

		$response = $this->model->delete_course_from_cart( $user_id, $course_id );

		if ( $response ) {
			wp_send_json_success( __( 'Course removed successfully.', 'tutor' ) );
		} else {
			wp_send_json_error( __( 'Course remove failed.', 'tutor' ) );
		}
	}
}
