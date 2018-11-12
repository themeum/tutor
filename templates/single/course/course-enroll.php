<?php
/**
 * Template for displaying course content
 *
 * @since v.1.0.0
 *
 * @author Themeum
 * @url https://themeum.com
 */

if ( ! defined( 'ABSPATH' ) )
	exit;

$tutor_form_class = apply_filters( 'tutor_enroll_form_classes', array(
	'tutor-enroll-form',
) );

$is_purchasable = tutor_utils()->is_course_purchasable();

?>


<div class="tutor-price-preview-box">
    <div class="tutor-price-box-thumbnail">
        <?php
        if(tutor_utils()->has_video_in_single()){
            tutor_course_video();
        } else{
            get_tutor_course_thumbnail();
        }

        ?>
    </div>

    <!--@TODO: Need Tutor price box description module-->

    <?php

        $material_includes = tutor_course_material_includes();
        if(!empty($material_includes)){
        ?>
            <div class="tutor-price-box-description">
                <h6><?php esc_html_e('Material Includes', 'tutor') ?></h6>
                <ul>
                    <?php if (is_array($material_includes) && count($material_includes)){
                        foreach ($material_includes as $material){
                            echo "<li>{$material}</li>";
                        }
                    } ?>
                </ul>
            </div>
        <?php
            }
        ?>

    <div class="tutor-single-enroll-box">
        <?php
            if ($is_purchasable){
                $product_id = tutor_utils()->get_course_product_id();
                $product = wc_get_product( $product_id );

                if ($product) {

                    ?>

                    <div class="tutor-course-purchase-box">
                        <p class="price">
                            <?php echo $product->get_price_html(); ?>
                        </p>

                        <form class="cart"
                              action="<?php echo esc_url( apply_filters( 'tutor_course_add_to_cart_form_action', get_permalink( get_the_ID() ) ) ); ?>"
                              method="post" enctype='multipart/form-data'>

                            <?php do_action( 'tutor_before_add_to_cart_button' ); ?>

                            <button type="submit" name="add-to-cart" value="<?php echo esc_attr( $product->get_id() ); ?>"
                                    class="single_add_to_cart_button button alt"><?php echo esc_html( $product->single_add_to_cart_text() ); ?>
                            </button>

                            <?php


                            if ( WC()->cart->cart_contents_total ) {
                                ?>
                                <a href="<?php echo wc_get_cart_url(); ?>"><?php _e( 'View Cart' ); ?></a>
                            <?php } ?>

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
            }else{
                ?>
                <form class="<?php echo implode( ' ', $tutor_form_class ); ?>" method="post">
                    <?php wp_nonce_field( tutor()->nonce_action, tutor()->nonce ); ?>
                    <input type="hidden" name="tutor_course_id" value="<?php echo get_the_ID(); ?>">
                    <input type="hidden" name="tutor_course_action" value="_tutor_course_enroll_now">

                    <div class="tutor-single-course-segment  tutor-course-enroll-wrap">
                        <h2><?php _e('Free', 'tutor'); ?></h2>

                        <button type="submit" class="tutor-btn-enroll tutor-btn tutor-course-purchase-btn">
                            <?php _e('Enroll Now', 'tutor'); ?>
                        </button>
                    </div>
                </form>

            <?php } ?>
    </div>
    <!--@TODO: Need Money Back Text Module-->
    <p class="tutor-money-back-text">30-Day Money-Back Guarantee</p>
</div> <!-- tutor-price-preview-box -->
