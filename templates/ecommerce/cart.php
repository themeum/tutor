<?php
/**
 * Cart Template.
 *
 * @package Tutor\Views
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Tutor\Ecommerce\CartController;
use Tutor\Ecommerce\CheckoutController;

$cart_controller = new CartController();
$get_cart        = $cart_controller->get_cart_items();
$courses         = $get_cart['courses'];
$total_count     = $courses['total_count'];
$course_list     = $courses['results'];
$subtotal        = 0;
$tax_amount      = 0; // @TODO: Need to implement later.

?>
<div class="tutor-cart-page">
	<div class="tutor-cart-page-wrapper">
		<div class="tutor-container">
			<?php if ( is_array( $course_list ) && count( $course_list ) ) : ?>
			<div class="tutor-row tutor-g-4">
				<div class="tutor-col-lg-8">
					<h3 class="tutor-fs-3 tutor-fw-bold tutor-color-black tutor-mb-16">
						<?php
						// translators: %d: Number of courses in the cart.
						echo esc_html( sprintf( _n( '%d Course in Cart', '%d Courses in Cart', $total_count, 'tutor' ), $total_count ) );
						?>
					</h3>

					<div class="tutor-cart-course-list">
						<?php
						foreach ( $course_list as $key => $course ) :
							$course_duration  = get_tutor_course_duration_context( $course->ID, true );
							$course_price     = tutor_utils()->get_raw_course_price( $course->ID );
							$regular_price    = $course_price->regular_price;
							$sale_price       = $course_price->sale_price;
							$tutor_course_img = get_tutor_course_thumbnail_src( '', $course->ID );

							$subtotal += $sale_price ? $sale_price : $regular_price;
							?>
							<div class="tutor-cart-course-item">
								<div class="tutor-cart-course-thumb">
									<a href="<?php echo esc_url( get_the_permalink( $course ) ); ?>">
										<img src="<?php echo esc_url( $tutor_course_img ); ?>" alt="Course thumb">
									</a>
								</div>
								<div class="tutor-cart-course-title">
									<?php if ( tutor()->has_pro && 'course-bundle' === $course->post_type ) : ?>
									<div class="tutor-cart-course-bundle-badge">
										<?php
										$bundle_model      = new \TutorPro\CourseBundle\Models\BundleModel();
										$bundle_course_ids = $bundle_model::get_bundle_course_ids( $course->ID );
										// translators: %d: Number of courses in the cart.
										echo esc_html( sprintf( __( '%d Course bundle', 'tutor' ), count( $bundle_course_ids ) ) );
										?>
									</div>
									<?php endif; ?>
									<h5 class="tutor-fs-6 tutor-fw-medium tutor-color-black">
										<a href="<?php echo esc_url( get_the_permalink( $course ) ); ?>">
											<?php echo esc_html( $course->post_title ); ?>
										</a>
									</h5>
									<ul class="tutor-cart-course-info">
										<?php if ( $course_duration ) : ?>
										<li><?php echo esc_html( tutor_utils()->clean_html_content( $course_duration ) ); ?> <span></span></li>
										<?php endif; ?>
										<li><?php echo esc_html( get_tutor_course_level( $course->ID ) ); ?></li>
									</ul>
								</div>
								<div class="tutor-cart-course-price-wrapper">
									<div class="tutor-cart-course-price">
										<div class="tutor-fw-bold">
											<?php echo tutor_get_formatted_price( $sale_price ? $sale_price : $regular_price ); //phpcs:ignore?>
										</div>
										<?php if ( $regular_price && $sale_price && $sale_price !== $regular_price ) : ?>
										<div class="tutor-cart-discount-price">
											<?php echo tutor_get_formatted_price( $regular_price ); //phpcs:ignore?>
										</div>
										<?php endif; ?>
									</div>
									<button class="tutor-btn tutor-btn-link tutor-cart-remove-button" data-course-id="<?php echo esc_attr( $course->ID ); ?>">
										<?php esc_html_e( 'Remove', 'tutor' ); ?>
									</button>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
				<div class="tutor-col-lg-4">
					<h3 class="tutor-fs-3 tutor-fw-bold tutor-color-black tutor-mb-16"><div><?php esc_html_e( 'Summary:', 'tutor' ); ?></div></h3>
					<div class="tutor-cart-summery">
						<div class="tutor-cart-summery-top">
							<div class="tutor-cart-summery-item tutor-fw-medium">
								<div><?php esc_html_e( 'Subtotal:', 'tutor' ); ?></div>
								<div><?php echo tutor_get_formatted_price( $subtotal ); //phpcs:ignore?></div>
							</div>
							<!-- <div class="tutor-cart-summery-item">
								<div><?php esc_html_e( 'Tax:', 'tutor' ); ?></div>
								<div><?php echo tutor_get_formatted_price( $tax_amount ); //phpcs:ignore?></div>
							</div> -->
						</div>
						<div class="tutor-cart-summery-bottom">
							<div class="tutor-cart-summery-item tutor-fw-medium tutor-mb-40">
								<div><?php esc_html_e( 'Grand total', 'tutor' ); ?></div>
								<div><?php echo tutor_get_formatted_price( $subtotal + $tax_amount ); //phpcs:ignore?></div>
							</div>
							<a class="tutor-btn tutor-btn-primary tutor-btn-lg tutor-w-100 tutor-justify-center" href="<?php echo esc_url( CheckoutController::get_page_url() ); ?>">
								<?php esc_html_e( 'Proceed to checkout', 'tutor' ); ?>
							</a>
						</div>
					</div>
				</div>
			</div>
			<?php else : ?>
				<div class="tutor-cart-empty-state">
					<img src="<?php echo esc_url( tutor()->url ); ?>assets/images/empty-cart.svg" alt="<?php esc_html_e( 'Empty shopping cart', 'tutor' ); ?>" />
					<p><?php esc_html_e( 'No courses in the cart', 'tutor' ); ?></p>
					<a href="<?php echo esc_url( tutor_utils()->course_archive_page_url() ); ?>" class="tutor-btn tutor-btn-lg tutor-btn-primary"><?php esc_html_e( 'Continue Browsing', 'tutor' ); ?></a>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>
