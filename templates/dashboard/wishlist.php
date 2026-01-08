<?php
/**
 * Wishlist Page
 *
 * @package Tutor\Templates
 * @subpackage Dashboard
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @version 1.4.3
 */

use TUTOR\Input;
use Tutor\Components\Pagination;
use Tutor\Components\EmptyState;

global $post;
$wishlist_per_page = (int) tutor_utils()->get_option( 'pagination_per_page', 10 );
$current_page      = max( 1, Input::get( 'current_page', 1, Input::TYPE_INT ) );
$offset            = ( $current_page - 1 ) * $wishlist_per_page;

$wishlists             = tutor_utils()->get_wishlist( $offset, $wishlist_per_page );
$total_wishlists_count = count( tutor_utils()->get_wishlist() );

$course_id       = $post->ID;
$profile_url     = tutor_utils()->profile_url( $post->post_author, true );
$course_duration = get_tutor_course_duration_context( $course_id, true );
$course_students = apply_filters( 'tutor_course_students', tutor_utils()->count_enrolled_users_by_course( $course_id ), $course_id );
?>

<div class="tutor-dashboard-page-card-body tutor-dashboard-wishlist-wrapper">

	<?php if ( is_array( $wishlists ) && count( $wishlists ) ) : ?>
		<div class="tutor-wishlist-grid">
			<?php
			foreach ( $wishlists as $post ) : //phpcs:ignore
				setup_postdata( $post );
				$tutor_course_img = get_tutor_course_thumbnail_src();
				?>
				<div>
					<div class="tutor-card tutor-card--rounded-2xl tutor-card--padding-small tutor-course-card">
						<a href="<?php the_permalink(); ?>" class="tutor-course-card-thumbnail">
							<div class="tutor-ratio tutor-ratio-16x9">
								<img style="max-width: 100%;" src="<?php echo esc_url( $tutor_course_img ); ?>" alt="<?php the_title(); ?>" loading="lazy" />
							</div>
						</a>

						<div class="tutor-card-body">
							<!-- star rating  -->
							<div class="tutor-course-card-rating">
								<div class="tutor-ratings">
									<?php
									$course_rating = tutor_utils()->get_course_rating();
									tutor_load_template(
										'dashboard.wishlist.star-rating',
										array(
											'course_rating' => $course_rating,
											'wrapper_class' => 'tutor-course-card-ratings-stars tutor-mt-2',
											'icon_class' => '',
											'show_rating_average' => true,
										)
									);
									?>
								</div>
							</div>

							<div class="tutor-course-card-title tutor-mt-2">
								<a href="<?php the_permalink(); ?>">
									<?php the_title(); ?>
								</a>
							</div>

							<?php if ( tutor_utils()->get_option( 'enable_course_total_enrolled' ) || ! empty( $course_duration ) ) : ?>
							<div class="tutor-meta tutor-course-card-meta tutor-mt-2 tutor-mb-2">
								<?php if ( tutor_utils()->get_option( 'enable_course_total_enrolled' ) ) : ?>
									<span class="tutor-course-meta-value"><?php echo esc_html( $course_students ); ?></span>
									<span><?php esc_html_e( 'Learners', 'tutor' ); ?></span>
								<?php endif; ?>

								<?php if ( ! empty( $course_duration ) ) : ?>
									<span class="tutor-course-card-separator"></span>
									<span> <?php echo tutor_utils()->clean_html_content( $course_duration ); //phpcs:ignore ?> </span>
								<?php endif; ?>

								<span class="tutor-course-card-separator"></span>
								<?php esc_html_e( 'By', 'tutor' ); ?>
								<a href="<?php echo esc_url( $profile_url ); ?>"><?php echo esc_html( get_the_author() ); ?></a>
							</div>
							<?php endif; ?>
						</div>

						<div class="tutor-course-card-footer">
							<?php
							if ( null === tutor_utils()->get_course_price() ) {
								esc_html_e( 'Free', 'tutor' );
							} else {
								echo wp_kses_post( tutor_utils()->get_course_price() );
							}
							?>
						</div>
					</div>
				</div>
				<?php
			endforeach;
			wp_reset_postdata();
			?>
		</div>
	<?php else : ?>
		<?php EmptyState::make()->title( __( 'No Courses Found', 'tutor' ) )->render(); ?>
	<?php endif; ?>

	<!-- Wishlist pagination  -->
	<?php if ( $total_wishlists_count > $wishlist_per_page ) : ?>
	<div class="tutor-p-6 tutor-border-t">
		<?php
			Pagination::make()
			->current( $current_page )
			->total( $total_wishlists_count )
			->limit( $wishlist_per_page )
			->render();
		?>
	</div>
	<?php endif; ?>

</div>