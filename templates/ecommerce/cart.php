<?php
/**
 * Cart Template.
 *
 * @package Tutor\Views
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Tutor\Ecommerce\CartController;

$cart_controller = new CartController();
$get_cart        = $cart_controller->get_cart_items();
$total_count     = $get_cart['total_count'];
$courses         = $get_cart['results'];

?>
<div class="tutor-cart-page">
	<div class="tutor-container">
		<div class="tutor-row tutor-g-4">
			<div class="tutor-col-md-8">
				<h3 class="tutor-fs-3 tutor-fw-bold tutor-color-black tutor-mb-16"><?php echo esc_html( $total_count ); ?> <?php esc_html_e( 'Course in Cart', 'tutor' ); ?></h3>

				<div class="tutor-cart-course-list">
					<?php if ( is_array( $courses ) && count( $courses ) ) : ?>
						<?php
						foreach ( $courses as $key => $course ) :
							$course_duration  = get_tutor_course_duration_context( $course->ID, true );
							$price            = tutor_utils()->get_course_price( $course->ID );
							$tutor_course_img = get_tutor_course_thumbnail_src( '', $course->ID );
							$is_bundle        = false;
							?>
							<div class="tutor-cart-course-item">
								<div class="tutor-cart-course-thumb">
									<img src="<?php echo esc_url( $tutor_course_img ); ?>" alt="Course thumb">
								</div>
								<div class="tutor-cart-course-title">
									<!-- @TODO: Need to add bundle product support -->
									<!-- <div class="tutor-cart-course-bundle-badge">5 Course bundle</div> -->
									<h5 class="tutor-fs-6 tutor-fw-medium tutor-color-black"><?php echo esc_html( $course->post_title ); ?></h5>
									<ul class="tutor-cart-course-info">
										<li><?php echo esc_html( tutor_utils()->clean_html_content( $course_duration ) ); ?> <span></span></li>
										<li>147 lectures <span></span></li>
										<li><?php echo esc_html( get_tutor_course_level( $course->ID ) ); ?></li>
									</ul>
								</div>
								<div class="tutor-cart-course-price">
									<div class="tutor-fs-6 tutor-fw-medium tutor-color-black"><?php echo esc_html( $price ); ?></div>
									<button class="tutor-btn tutor-btn-link tutor-cart-remove-button" data-course-id="<?php echo esc_attr( $course->ID ); ?>">
										<?php esc_html_e( 'Remove', 'tutor' ); ?>
									</button>
								</div>
							</div>
						<?php endforeach; ?>
					<?php else : ?>
						<tr>
							<td colspan="100%" class="column-empty-state">
								<?php tutor_utils()->tutor_empty_state( tutor_utils()->not_found_text() ); ?>
							</td>
						</tr>
					<?php endif; ?>
				</div>
			</div>
			<div class="tutor-col-md-4">
				<h3 class="tutor-fs-3 tutor-fw-bold tutor-color-black tutor-mb-16">Summary</h3>
				<div class="tutor-cart-summery">
					<div class="tutor-cart-summery-top">
						<div class="tutor-cart-summery-item tutor-fw-medium">
							<div>Subtotal:</div>
							<div>$400.00</div>
						</div>
						<div class="tutor-cart-summery-item">
							<div>Tax:</div>
							<div>$0.00</div>
						</div>
					</div>
					<div class="tutor-cart-summery-bottom">
						<div class="tutor-cart-summery-item tutor-fw-medium tutor-mb-40">
							<div>Grand total</div>
							<div>$400.00</div>
						</div>
						<a class="tutor-btn tutor-btn-primary tutor-btn-lg tutor-w-100 tutor-justify-center" href="#">
							Proceed to checkout
						</a>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
