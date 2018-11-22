<?php

/**
 * Display single course add to cart
 *
 * @since v.1.0.0
 * @author themeum
 * @url https://themeum.com
 */

if ( ! defined( 'ABSPATH' ) )
	exit;

$dozent_form_class = apply_filters( 'dozent_enroll_form_classes', array(
	'dozent-enroll-form',
) );

$is_purchasable = dozent_utils()->is_course_purchasable();

do_action('dozent_course/single/add-to-cart/before');
?>

<div class="dozent-single-add-to-cart-box">
	<?php
	if ($is_purchasable){
		$product_id = dozent_utils()->get_course_product_id();
		$product = wc_get_product( $product_id );
		if ($product) {
			?>

			<div class="dozent-course-purchase-box">

				<form class="cart"
				      action="<?php echo esc_url( apply_filters( 'dozent_course_add_to_cart_form_action', get_permalink( get_the_ID() ) ) ); ?>"
				      method="post" enctype='multipart/form-data'>

					<?php do_action( 'dozent_before_add_to_cart_button' ); ?>

					<button type="submit" name="add-to-cart" value="<?php echo esc_attr( $product->get_id() ); ?>" class="single_add_to_cart_button button alt"> <i class="dozent-icon-shopping-cart"></i> <?php echo esc_html( $product->single_add_to_cart_text() ); ?>
					</button>

					<?php do_action( 'dozent_after_add_to_cart_button' ); ?>
				</form>

			</div>

			<?php
		}else{
			?>
			<p class="dozent-alert-warning">
				<?php _e('Please make sure that your product exists and valid for this course', 'dozent'); ?>
			</p>
			<?php
		}
	}else{
		?>
		<form class="<?php echo implode( ' ', $dozent_form_class ); ?>" method="post">
			<?php wp_nonce_field( dozent()->nonce_action, dozent()->nonce ); ?>
			<input type="hidden" name="dozent_course_id" value="<?php echo get_the_ID(); ?>">
			<input type="hidden" name="dozent_course_action" value="_dozent_course_enroll_now">

			<div class=" dozent-course-enroll-wrap">
				<button type="submit" class="dozent-btn-enroll dozent-btn dozent-course-purchase-btn">
					<?php _e('Enroll Now', 'dozent'); ?>
				</button>
			</div>
		</form>

	<?php } ?>
</div>

<?php do_action('dozent_course/single/add-to-cart/after'); ?>
