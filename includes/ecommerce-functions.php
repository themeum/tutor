<?php
/**
 * Tutor ecommerce functions
 *
 * @package TutorFunctions
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 3.5.0
 */

defined( 'ABSPATH' ) || exit;

use Tutor\Components\Constants\Size;
use Tutor\Components\SvgIcon;
use Tutor\Ecommerce\Cart\CartFactory;
use TUTOR\Icon;
use TutorPro\Ecommerce\GuestCheckout\GuestCheckout;

if ( ! function_exists( 'tutor_add_to_cart' ) ) {
	/**
	 * Handle add to cart functionalities
	 *
	 * @since 3.5.0
	 *
	 * @param int $item_id Item id.
	 *
	 * @return object {success, message, data: {cart_url, items, total_count} }
	 */
	function tutor_add_to_cart( int $item_id ) {
		$response          = new stdClass();
		$response->success = true;
		$response->message = __( 'Course added to cart', 'tutor' );
		$response->data    = null;

		$user_id                   = get_current_user_id();
		$is_guest_checkout_enabled = tutor_is_guest_checkout_enabled();

		if ( ! $user_id && ! $is_guest_checkout_enabled ) {
			return array(
				'success'  => false,
				'message'  => __( 'Guest checkout is not enabled', 'tutor' ),
				'data'     => tutor_utils()->tutor_dashboard_url(),
				'redirect' => true,
			);
		}

		try {
			$cart = tutor_get_cart_object();
			if ( $cart->add( $item_id ) ) {
				// Prepare data.
				$cart_url = $cart->get_cart_url();
				$items    = $cart->get_cart_items();
				$data     = (object) array(
					'cart_url'    => $cart_url,
					'items'       => $items,
					'total_count' => count( $items ),
				);

				$response->data = $data;
			} else {
				$response->success = false;
				$response->message = $cart->get_error();
			}
		} catch ( \Throwable $th ) {
			$response->success = false;
			$response->message = $th->getMessage();
		}

		return $response;
	}
}

if ( ! function_exists( 'tutor_get_cart_url' ) ) {
	/**
	 * Get the cart page URL
	 *
	 * @since 3.5.0
	 *
	 * @return string
	 */
	function tutor_get_cart_url() {
		try {
			$cart = tutor_get_cart_object();
			return $cart->get_cart_url();
		} catch ( \Throwable $th ) {
			return $th->getMessage();
		}
	}
}

if ( ! function_exists( 'tutor_get_cart_items' ) ) {
	/**
	 * Get cart items
	 *
	 * @since 3.5.0
	 *
	 * @return array
	 */
	function tutor_get_cart_items() {
		$items = array();
		try {
			$cart  = tutor_get_cart_object();
			$items = $cart->get_cart_items();
		} catch ( \Throwable $th ) {
			error_log( $th->getMessage() );
		}

		return $items;
	}
}

if ( ! function_exists( 'tutor_is_item_in_cart' ) ) {
	/**
	 * Get cart items
	 *
	 * @since 3.5.0
	 *
	 * @param int $item_id Item id to check.
	 *
	 * @return bool
	 */
	function tutor_is_item_in_cart( int $item_id ) {
		try {
			return tutor_get_cart_object()->is_item_exists( $item_id );
		} catch ( \Throwable $th ) {
			return false;
		}
	}
}

if ( ! function_exists( 'tutor_remove_cart_item' ) ) {
	/**
	 * Get cart items
	 *
	 * @since 3.7.2
	 *
	 * @param int $item_id Item id to check.
	 *
	 * @return bool
	 */
	function tutor_remove_cart_item( int $item_id ) {
		return tutor_get_cart_object()->remove( $item_id );
	}
}

if ( ! function_exists( 'tutor_get_cart_object' ) ) {
	/**
	 * Get cart items
	 *
	 * @since 3.5.0
	 *
	 * @throws \Throwable If cart object creation failed.
	 *
	 * @return object CartInterface
	 */
	function tutor_get_cart_object() {
		$monetization = tutor_utils()->get_option( 'monetize_by' );
		try {
			return CartFactory::create_cart( $monetization );
		} catch ( \Throwable $th ) {
			throw $th;
		}
	}
}

if ( ! function_exists( 'tutor_is_guest_checkout_enabled' ) ) {
	/**
	 * Get cart items
	 *
	 * @since 3.7.2
	 *
	 * @return bool
	 */
	function tutor_is_guest_checkout_enabled() {
		$monetization = tutor_utils()->get_option( 'monetize_by' );
		if ( tutor_utils()->is_monetize_by_tutor() ) {
			return function_exists( 'tutor_pro' ) && GuestCheckout::is_enable();
		} elseif ( 'wc' === $monetization ) {
			return tutor_utils()->get_option( 'enable_guest_course_cart', false );
		}
	}
}

if ( ! function_exists( 'tutor_ecommerce_cart_button' ) ) {
	/**
	 * Display global cart button for Tutor native ecommerce
	 * This function can be used by any theme to display the cart button in header
	 * Similar to woocommerce_header_cart()
	 *
	 * @since 4.1.0
	 *
	 * @param array $args {
	 *     Optional. Array of arguments for customizing the cart button.
	 *
	 *     @type string $class        CSS class for the cart button. Default 'cart-contents'.
	 *     @type string $title        Title attribute for the cart link. Default 'View your shopping cart'.
	 *     @type bool   $show_icon    Whether to show the cart icon. Default true.
	 *     @type string $show_count   When to show the cart item count: 'always', 'if_has_items', or 'never'. Default 'if_has_items'.
	 *     @type string $icon_svg     Custom SVG icon. If not provided, default cart icon will be used.
	 *     @type string $before_count Text before cart count. Default '('.
	 *     @type string $after_count  Text after cart count. Default ')'.
	 * }
	 *
	 * @return void
	 */
	function tutor_ecommerce_cart_button( $args = array() ) {
		// Only show if Tutor native ecommerce is active and monetization is set to 'tutor'.
		if ( ! tutor_utils()->is_monetize_by_tutor() ) {
			return;
		}

		$defaults = array(
			'class'        => 'tutor-cart-button',
			'title'        => __( 'View your shopping cart', 'tutor' ),
			'show_icon'    => true,
			'show_count'   => 'if_has_items',
			'icon_svg'     => '',
			'before_count' => '',
			'after_count'  => '',
		);

		$args = wp_parse_args( $args, $defaults );

		$args = apply_filters( 'tutor_ecommerce_cart_button_args', $args );

		$cart_url   = tutor_get_cart_url();
		$cart_items = tutor_get_cart_items();

		$cart_count = is_array( $cart_items ) ? count( $cart_items ) : 0;

		if ( empty( $args['icon_svg'] ) && $args['show_icon'] ) {
			$args['icon_svg'] = SvgIcon::make()->name( Icon::CART )->size( Size::SIZE_20 )->get();
		}

		ob_start();
		?>
		<a class="<?php echo esc_attr( $args['class'] ); ?>" href="<?php echo esc_url( $cart_url ); ?>"
			title="<?php echo esc_attr( $args['title'] ); ?>">
			<?php if ( $args['show_icon'] ) : ?>
				<span class="tutor-btn-cart">
					<?php echo $args['icon_svg']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<?php
					$show_count = $args['show_count'];
					if ( 'never' !== $show_count ) :
						$hidden = ( 'if_has_items' === $show_count && 0 === $cart_count );
						?>
						<span class="tutor-cart-count"
							data-show-count="<?php echo esc_attr( $show_count ); ?>"
							<?php
							if ( $hidden ) :
								?>
								style="display:none;"<?php endif; ?>>
							<?php echo esc_html( $cart_count ); ?>
						</span>
					<?php endif; ?>
				</span>
			<?php endif; ?>
		</a>
		<?php
		echo ob_get_clean(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}

if ( ! function_exists( 'tutor_ecommerce_get_cart_count' ) ) {
	/**
	 * Get cart item count for Tutor native ecommerce
	 *
	 * @since 4.1.0
	 *
	 * @return int Cart item count
	 */
	function tutor_ecommerce_get_cart_count() {
		if ( ! tutor_utils()->is_monetize_by_tutor() ) {
			return 0;
		}

		$cart_items = tutor_get_cart_items();
		return is_array( $cart_items ) ? count( $cart_items ) : 0;
	}
}
