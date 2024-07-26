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

$conditional_class = is_user_logged_in() ? 'tutor-native-add-to-cart' : 'tutor-open-login-modal';
?>
<div class="list-item-button"> 
	<button data-quantity="1" class="tutor-btn tutor-btn-outline-primary tutor-btn-md tutor-btn-block <?php echo esc_attr( $conditional_class ); ?>" data-course-id="<?php the_ID(); ?>" rel="nofollow"><span class="tutor-icon-cart-line tutor-mr-8"></span><span class="cart-text"><?php esc_html_e( 'Add to cart', 'tutor' ); ?></span></button> 
</div>
