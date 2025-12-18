<?php
/**
 * Frontend Wishlist Page
 *
 * @package Tutor\Templates
 * @subpackage Dashboard
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @version 1.4.3
 */

use TUTOR\Input;

global $post;
$per_page     = tutor_utils()->get_option( 'pagination_per_page', 20 );
$current_page = max( 1, Input::get( 'current_page', 1, Input::TYPE_INT ) );
$offset       = ( $current_page - 1 ) * $per_page;

$wishlists             = tutor_utils()->get_wishlist( null, $offset, $per_page );
$total_wishlists_count = count( tutor_utils()->get_wishlist( null ) );


// Default values - all data must be passed from parent.
$image_url       = isset( $image_url ) ? $image_url : '';
$course_title    = isset( $title ) ? $title : '';
$rating_avg      = isset( $rating_avg ) ? $rating_avg : 0;
$rating_count    = isset( $rating_count ) ? $rating_count : 0;
$learners        = isset( $learners ) ? $learners : 0;
$instructor      = isset( $instructor ) ? $instructor : '';
$instructor_url  = isset( $instructor_url ) ? $instructor_url : '#';
$provider        = isset( $provider ) ? $provider : '';
$show_bestseller = isset( $show_bestseller ) ? $show_bestseller : false;
$price           = isset( $price ) ? $price : '';
$original_price  = isset( $original_price ) ? $original_price : '';
$permalink       = isset( $permalink ) ? $permalink : '#';

?>

<?php
// tutor_load_template( 'demo-components.dashboard.pages.wishlist' );
?>

<div class="tutor-dashboard-page-card-body">
	<?php if ( is_array( $wishlists ) && count( $wishlists ) ) : ?>
		<div class="tutor-wishlist-grid">
			<?php
			foreach ( $wishlists as $post ) :
				setup_postdata( $post );
				$tutor_course_img = get_tutor_course_thumbnail_src();
				?>
				<div>
					<div class="tutor-card tutor-card--rounded-2xl tutor-card--padding-small tutor-course-card">
						<a href="<?php echo esc_url( $permalink ); ?>" class="tutor-course-card-thumbnail">
							<div class="tutor-ratio tutor-ratio-16x9" style="max-width: 100px;">
								<img src="<?php echo esc_url( $tutor_course_img ); ?>"
									alt="<?php the_title(); ?>" loading="lazy" />
							</div>
							<?php if ( $show_bestseller ) : ?>
								<span class="tutor-badge tutor-badge-primary tutor-course-card-badge">
									<?php esc_html_e( 'Bestseller', 'tutor' ); ?>
								</span>
							<?php endif; ?>
						</a>

						<div class="tutor-card-body">
							<div class="tutor-course-card-ratings-stars">
								<?php
									// $course_rating = tutor_utils()->get_course_rating();
									// tutor_utils()->star_rating_generator_course( $course_rating->rating_avg );
								?>
							</div>
							<!-- star rating  -->
							<?php
							// if ( $rating_avg > 0 ) :
							?>
							<?php if ( true ) : ?>
								<div class="tutor-course-card-rating">
									<div class="tutor-ratings">
										<?php
										$course_rating = tutor_utils()->get_course_rating();
										tutor_load_template(
											'dashboard.wishlist.star-rating',
											array(
												'rating' => $course_rating,
												'wrapper_class' => 'tutor-course-card-ratings-stars',
												'icon_class' => '',
												'show_rating_average' => true,
											)
										);
										?>
										<?php if ( $course_rating->rating_count > 0 ) : ?>
											<div class="tutor-ratings-count">
												(<?php echo esc_html( number_format_i18n( $course_rating->rating_count ) ); ?>)
											</div>
										<?php endif; ?>
									</div>
								</div>
							<?php endif; ?>

							<h3 class="tutor-course-card-title">
								<a href="<?php the_permalink(); ?>">
									<?php the_title(); ?>
								</a>
							</h3>

							<div class="tutor-course-card-meta">
								<?php if ( $learners > 0 ) : ?>
									<span>
										<?php
										/* translators: %d: number of learners */
										echo esc_html( sprintf( _n( '%d Learner', '%d Learners', $learners, 'tutor' ), $learners ) );
										?>
									</span>
								<?php endif; ?>
								<?php if ( ! empty( $instructor ) ) : ?>
									<?php if ( $learners > 0 ) : ?>
										<span class="tutor-course-card-separator">•</span>
									<?php endif; ?>
									<span class="tutor-course-card-instructor">
										<?php echo esc_html( $instructor ); ?>
									</span>
								<?php endif; ?>
								<?php if ( ! empty( $provider ) ) : ?>
									<?php if ( $learners > 0 || ! empty( $instructor ) ) : ?>
										<span class="tutor-course-card-separator">•</span>
									<?php endif; ?>
									<span>
										<?php
										/* translators: %s: provider name */
										echo esc_html( sprintf( __( 'by %s', 'tutor' ), $provider ) );
										?>
									</span>
								<?php endif; ?>
							</div>
						</div>

						<?php if ( ! empty( $price ) ) : ?>
							<div class="tutor-course-card-footer">
								<span class="tutor-course-card-price">
									<?php echo esc_html( $price ); ?>
								</span>
								<?php if ( ! empty( $original_price ) ) : ?>
									<del class="tutor-course-card-price-original">
										<?php echo esc_html( $original_price ); ?>
									</del>
								<?php endif; ?>
							</div>
						<?php endif; ?>
					</div>
				</div>
				<?php
			endforeach;
			wp_reset_postdata();
			?>
		</div>
	<?php else : ?>
		<div class="tutor-text-center tutor-py-16 tutor-text-muted">
			<?php esc_html_e( 'You have not added any courses to your wishlist yet.', 'tutor' ); ?>
		</div>
	<?php endif; ?>
</div>


<div class="tutor-fs-5 tutor-fw-medium tutor-color-black tutor-mb-24"><?php esc_html_e( 'Wishlist', 'tutor' ); ?></div>
<div class="tutor-dashboard-content-inner my-wishlist" style="display: block;">
	<?php if ( is_array( $wishlists ) && count( $wishlists ) ) : ?>
		<div class="tutor-grid tutor-grid-3">
			<?php
			foreach ( $wishlists as $post ) :
				setup_postdata( $post );
				$tutor_course_img = get_tutor_course_thumbnail_src();
				?>
				<div class="tutor-card tutor-course-card">
					<?php tutor_load_template( 'loop.header' ); ?>

					<div class="tutor-card-body">
						<?php tutor_load_template( 'loop.rating' ); ?>

						<div class="tutor-course-name tutor-fs-6 tutor-fw-bold">
							<a href="<?php echo esc_url( get_the_permalink() ); ?>">
								<?php the_title(); ?>
							</a>
						</div>

						<div class="tutor-mt-auto">
							<?php tutor_load_template( 'loop.course-author' ); ?>
						</div>
					</div>

					<div class="tutor-card-footer">
						<?php tutor_course_loop_price(); ?>
					</div>
				</div>
				<?php
			endforeach;
			wp_reset_postdata();
			?>
		</div>
	<?php else : ?>
		<?php tutor_utils()->tutor_empty_state( tutor_utils()->not_found_text() ); ?>
	<?php endif; ?>
</div>
<div class="tutor-mt-24">
	<?php
	if ( $total_wishlists_count >= $per_page ) {
		$pagination_data = array(
			'total_items' => $total_wishlists_count,
			'per_page'    => $per_page,
			'paged'       => $current_page,
		);

		tutor_load_template_from_custom_path(
			tutor()->path . 'templates/dashboard/elements/pagination.php',
			$pagination_data
		);
	}
	?>
</div>