<?php
/**
 * A single course loop add to cart
 *
 * @package Tutor\Templates
 * @subpackage WooCommerceIntegration
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.4.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
use Tutor\Ecommerce\CartController;
use Tutor\Models\CartModel;

$course_id = get_the_ID();
$user_id   = get_current_user_id();

$is_course_in_user_cart = CartModel::is_course_in_user_cart( $user_id, $course_id );
$cart_page_url          = CartController::get_page_url();

$conditional_class = is_user_logged_in() ? 'tutor-native-add-to-cart' : 'tutor-open-login-modal';

ob_start();

if ( $is_course_in_user_cart ) {
	?>
	<a href="<?php echo esc_url( $cart_page_url ? $cart_page_url : '#' ); ?>" class="tutor-btn tutor-btn-outline-primary tutor-btn-md <?php echo esc_attr( $cart_page_url ? '' : 'tutor-cart-page-not-configured' ); ?>">
		<?php esc_html_e( 'View Cart', 'tutor' ); ?>
	</a>
	<?php
} else {
	?>
	<div class="list-item-button"> 
		<button data-quantity="1" class="tutor-btn tutor-btn-outline-primary tutor-btn-md tutor-btn-block <?php echo esc_attr( $conditional_class ); ?>" data-course-id="<?php the_ID(); ?>" rel="nofollow">
			<span class="tutor-icon-cart-line tutor-mr-8"></span>
			<span class="cart-text"><?php esc_html_e( 'Add to Cart', 'tutor' ); ?></span>
		</button> 
	</div>
	<?php
}

echo apply_filters( 'tutor_course_loop_add_to_cart_button', ob_get_clean(), $course_id ); //phpcs:ignore
