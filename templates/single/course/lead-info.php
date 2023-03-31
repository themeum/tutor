<?php
/**
 * Template for displaying lead info
 *
 * @package Tutor\Templates
 * @subpackage Single\Course
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.0.0
 */

use TUTOR\Input;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $post, $authordata, $course_rating;

$course_id         = Input::post( 'course_id', get_the_ID(), Input::TYPE_INT );
$profile_url       = tutor_utils()->profile_url( $authordata->ID, true );
$show_author       = tutor_utils()->get_option( 'enable_course_author' );
$course_categories = get_tutor_course_categories();
$disable_reviews   = ! get_tutor_option( 'enable_course_review' );
$is_wish_listed    = tutor_utils()->is_wishlisted( $post->ID, get_current_user_id() );

/**
 * Global $course_rating get null for third party
 * who only include this file without single-course.php file.
 *
 * @since 2.1.9
 */
if ( is_null( $course_rating ) ) {
	$course_rating = tutor_utils()->get_course_rating( $course_id );
}
?>

<header class="tutor-course-details-header tutor-mb-44">
	<?php if ( ! $disable_reviews ) : ?>
		<div class="tutor-course-details-ratings">
			<?php
				tutor_utils()->star_rating_generator_v2( $course_rating->rating_avg, $course_rating->rating_count, true );
			?>
		</div>
	<?php endif; ?>

	<h1 class="tutor-course-details-title tutor-fs-4 tutor-fw-bold tutor-color-black tutor-mt-12 tutor-mb-0">
		<?php do_action( 'tutor_course/single/title/before' ); ?>
		<span><?php the_title(); ?></span>
	</h1>
	
	<div class="tutor-course-details-top tutor-mt-16">
		<div class="tutor-row">
			<div class="tutor-col">
				<div class="tutor-meta tutor-course-details-info"> 
					<?php if ( $show_author ) : ?>
					<div>
						<a href="<?php echo esc_url( $profile_url ); ?>" class="tutor-d-flex">
							<?php
							echo wp_kses(
								tutor_utils()->get_tutor_avatar( get_the_author_meta( 'ID' ) ),
								tutor_utils()->allowed_avatar_tags()
							);
							?>
						</a>
					</div>
					<?php endif; ?>

					<div>
						<?php if ( $show_author ) : ?>
							<span class="tutor-mr-16">
								<?php esc_html_e( 'By', 'tutor' ); ?>
								<a href="<?php echo esc_url( $profile_url ); ?>"><?php echo esc_html( get_the_author_meta( 'display_name' ) ); ?></a>
							</span>
						<?php endif; ?>

						<?php if ( ! empty( $course_categories ) && is_array( $course_categories ) && count( $course_categories ) ) : ?>
							<?php esc_html_e( 'Categories:', 'tutor' ); ?>
							<?php
								$category_links = array();
							foreach ( $course_categories as $course_category ) :
								$category_name    = $course_category->name;
								$category_link    = get_term_link( $course_category->term_id );
								$category_links[] = wp_sprintf( '<a href="%1$s">%2$s</a>', esc_url( $category_link ), esc_html( $category_name ) );
								endforeach;
								echo wp_kses(
									implode( ', ', $category_links ),
									array( 'a' => array( 'href' => true ) )
								);
							?>
						<?php else : ?>
							<?php esc_html_e( 'Uncategorized', 'tutor' ); ?>
						<?php endif; ?>
					</div>
				</div>
			</div>

			<div class="tutor-col-auto">
				<div class="tutor-course-details-actions tutor-mt-12 tutor-mt-sm-0">
					<a href="#" class="tutor-btn tutor-btn-ghost tutor-course-wishlist-btn tutor-mr-16" data-course-id="<?php echo get_the_ID(); ?>">
						<i class="<?php echo $is_wish_listed ? 'tutor-icon-bookmark-bold' : 'tutor-icon-bookmark-line'; ?> tutor-mr-8"></i> <?php esc_html_e( 'Wishlist', 'tutor' ); ?>
					</a>

					<?php
					if ( tutor_utils()->get_option( 'enable_course_share', false, true, true ) ) {
						tutor_load_template_from_custom_path( tutor()->path . '/views/course-share.php', array(), false );
					}
					?>
				</div>
			</div>
		</div>
	</div>
</header>
