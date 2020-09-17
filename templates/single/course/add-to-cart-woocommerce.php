<?php
/**
 * @package TutorLMS/Templates
 * @version 1.4.3
 */


$product_id = tutor_utils()->get_course_product_id();
$product = wc_get_product( $product_id );
if ($product) {
	?>

    <div class="tutor-course-purchase-box">

        <form class="cart"
              action="<?php echo esc_url( apply_filters( 'tutor_course_add_to_cart_form_action', get_permalink( get_the_ID() ) ) ); ?>"
              method="post" enctype='multipart/form-data'>

			<?php do_action( 'tutor_before_add_to_cart_button' ); ?>

            <button type="submit" name="add-to-cart" value="<?php echo esc_attr( $product->get_id() ); ?>" class="single_add_to_cart_button tutor-button alt"> <i class="tutor-icon-shopping-cart"></i> <?php echo esc_html( $product->single_add_to_cart_text() ); ?>
            </button>

			<?php do_action( 'tutor_after_add_to_cart_button' ); ?>
        </form>

    </div>

	<?php
}else{
	?>
    <p class="tutor-alert-warning">
		<?php _e('Please make sure that your product exists and valid for this course', 'tutor'); ?>
    </p>
	<?php
}