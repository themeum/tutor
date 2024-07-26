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

$course_id                = get_the_ID();
$is_logged_in             = is_user_logged_in();
$enable_guest_course_cart = false;
$required_loggedin_class  = '';
if ( ! $is_logged_in && ! $enable_guest_course_cart ) {
	$required_loggedin_class = apply_filters( 'tutor_enroll_required_login_class', 'tutor-open-login-modal' );
}
$has_course_in_cart = false; // @TODO
$cart_page_url      = CartController::get_page_url();
if ( $has_course_in_cart ) {
	?>
	<a href="<?php echo esc_url( $cart_page_url ); ?>" class="tutor-btn tutor-btn-outline-primary tutor-btn-lg tutor-btn-block tutor-native-view-cart">
	<?php esc_html_e( 'View Cart', 'tutor' ); ?>
	</a>
	<?php
} else {
	$course_price  = tutor_utils()->get_raw_course_price( $course_id );
	$regular_price = $course_price->regular_price;
	$sale_price    = $course_price->sale_price;
	$tax_display   = false; // @TODO
	?>
	<div>
		<div class="tutor-course-sidebar-card-pricing tutor-d-flex tutor-align-end tutor-justify-between">
			<?php ob_start(); ?>
				<div>
					<span class="tutor-fs-4 tutor-fw-bold tutor-color-black">
                    <?php echo tutor_get_formatted_price( $sale_price ? $sale_price : $regular_price ); //phpcs:ignore?>
					</span>
				<?php if ( $regular_price && $sale_price && $sale_price !== $regular_price ) : ?>
						<del class="tutor-fs-7 tutor-color-muted tutor-ml-8">
                        <?php echo tutor_get_formatted_price( $regular_price ); //phpcs:ignore?>
						</del>
					<?php endif; ?>
				</div>
		</div>
		<div class="tutor-color-muted">
		<?php
		if ( 'incl' === $tax_display ) {
			echo wp_kses(
				$product->get_price_suffix(),
				array( 'small' => array( 'class' => true ) )
			);
		}
		?>
		</div>
		<?php
		/**
		 * Added to show info about price.
		 *
		 * @since 2.2.0
		 */
		do_action( 'tutor_after_course_details_tutor_cart_price', $course_id );
		?>
        <?php echo apply_filters( 'tutor_after_course_details_tutor_cart_price', ob_get_clean(), $course_id ); //phpcs:ignore ?>
	</div>
	<form method="post" enctype="multipart/form-data" id="tutor-native-add-to-cart-form">
		<button type="submit" name="add-to-cart-btn" class="tutor-btn tutor-btn-primary tutor-btn-lg tutor-btn-block tutor-mt-24 tutor-add-to-cart-button <?php echo esc_attr( $required_loggedin_class ); ?>" data-course-id="<?php echo esc_attr( $course_id ); ?>">
			<span class="tutor-icon-cart-line tutor-mr-8"></span>
			<span><?php esc_html_e( 'Add to cart', 'tutor' ); ?></span>
		</button>
	</form>
	<?php
}
