<?php
/**
 * Tutor add to cart for WC product that will be visible on the course details page
 *
 * @package Tutor\Templates
 * @subpackage Single\Course
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.4.3
 */

use Tutor\Ecommerce\CartController;
use Tutor\Ecommerce\Tax;
use Tutor\Models\CartModel;

$course_id                = get_the_ID();
$is_logged_in             = is_user_logged_in();
$user_id                  = get_current_user_id();
$enable_guest_course_cart = false;
$required_loggedin_class  = 'tutor-native-add-to-cart';
if ( ! $is_logged_in && ! $enable_guest_course_cart ) {
	$required_loggedin_class = apply_filters( 'tutor_enroll_required_login_class', 'tutor-open-login-modal' );
}

$is_course_in_user_cart = CartModel::is_course_in_user_cart( $user_id, $course_id );
$cart_page_url          = CartController::get_page_url();

$course_price  = tutor_utils()->get_raw_course_price( $course_id );
$regular_price = $course_price->regular_price;
$sale_price    = $course_price->sale_price;

$display_price       = $sale_price ? $sale_price : $regular_price;
$show_price_with_tax = Tax::show_price_with_tax();
$user_logged_in      = is_user_logged_in();

$tax_amount = 0;
if ( $show_price_with_tax && is_numeric( $display_price ) && ! Tax::is_tax_included_in_price() ) {
	$tax_rate       = $user_logged_in ? Tax::get_user_tax_rate() : 0;
	$tax_amount     = Tax::calculate_tax( $display_price, $tax_rate );
	$display_price += $tax_amount;
}

?>
	<div>
		<div class="tutor-course-sidebar-card-pricing tutor-d-flex tutor-align-end tutor-justify-between">
			<?php ob_start(); ?>
				<div>
					<span class="tutor-fs-4 tutor-fw-bold tutor-color-black">
					<?php tutor_print_formatted_price( $display_price ); ?>
					</span>
				<?php if ( $regular_price && $sale_price && $sale_price !== $regular_price ) : ?>
						<del class="tutor-fs-7 tutor-color-muted tutor-ml-8">
						<?php tutor_print_formatted_price( $regular_price ); ?>
						</del>
					<?php endif; ?>
				</div>
		</div>
		<?php if ( $user_logged_in && $show_price_with_tax ) : ?>
			<div class="tutor-course-price-tax tutor-fs-8 tutor-fw-normal tutor-color-black"><?php esc_html_e( 'Incl. tax', 'tutor' ); ?></div>
		<?php endif; ?>
		<?php
		/**
		 * Added to show info about price.
		 *
		 * @since 2.2.0
		 */
		do_action( 'tutor_after_course_details_tutor_cart_price', $course_id );
		?>
        <?php echo apply_filters( 'tutor_after_course_details_tutor_add_to_cart_price', ob_get_clean(), $course_id ); //phpcs:ignore ?>
	</div>
	<?php if ( $is_course_in_user_cart ) { ?>
		<a href="<?php echo esc_url( $cart_page_url ? $cart_page_url : '#' ); ?>" class="tutor-btn tutor-btn-outline-primary tutor-mt-24 tutor-btn-lg tutor-btn-block <?php echo esc_attr( $cart_page_url ? '' : 'tutor-cart-page-not-configured' ); ?>">
			<?php esc_html_e( 'View Cart', 'tutor' ); ?>
		</a>
	<?php } else { ?>
	<div class="tutor-mt-24">
		<button type="button" class="tutor-btn tutor-btn-primary tutor-btn-lg tutor-btn-block <?php echo esc_attr( $required_loggedin_class ); ?>" data-course-id="<?php echo esc_attr( $course_id ); ?>" data-course-single>
			<span class="tutor-icon-cart-line tutor-mr-8"></span>
			<span><?php esc_html_e( 'Add to Cart', 'tutor' ); ?></span>
		</button>
	</div>
		<?php
	}
