<?php
/**
 * Course embed template
 *
 * @package Tutor\Templates
 * @subpackage CourseEmbed
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.1.0
 */

wp_head();
$thumbnail_url     = get_the_post_thumbnail_url();
$course_id         = get_the_ID();
$profile_url       = tutor_utils()->profile_url( $authordata->ID, true );
$course_categories = get_tutor_course_categories( $course_id );
$course_duration   = get_tutor_course_duration_context( $course_id, true );
$course_students   = tutor_utils()->count_enrolled_users_by_course( $course_id );
$tutor_course_img  = get_tutor_course_thumbnail_src();
$placeholder_img   = tutor()->url . 'assets/images/placeholder.svg';

?>

<div class="tutor-card tutor-course-card">
	<div class="tutor-course-thumbnail">
		<a href="<?php the_permalink(); ?>" class="tutor-d-block">
			<div class="tutor-ratio tutor-ratio-16x9">
				<img class="tutor-card-image-top" src="<?php echo empty( $tutor_course_img ) ? esc_url( $placeholder_img ) : esc_url( $tutor_course_img ); ?>" alt="<?php the_title(); ?>" loading="lazy">
			</div>
		</a>
	</div>
	<div class="tutor-card-body">

		<div class="tutor-mb-12 tutor-course-ratings<?php echo esc_html( $class ); ?>">
			<div class="tutor-ratings">
				<div class="tutor-ratings-stars">
					<?php
						$course_rating = tutor_utils()->get_course_rating( $course_id );
						tutor_utils()->star_rating_generator_course( $course_rating->rating_avg );
					?>
				</div>

				<?php if ( $course_rating->rating_avg > 0 ) : ?>
					<div class="tutor-ratings-average">
						<?php echo esc_html( apply_filters( 'tutor_course_rating_average', $course_rating->rating_avg ) ); ?>
					</div>
					<div class="tutor-ratings-count">
						(<?php echo esc_html( $course_rating->rating_count > 0 ? $course_rating->rating_count : 0 ); ?>)
					</div>
				<?php endif; ?>
			</div>
		</div>
		<h3 class="tutor-course-name tutor-fs-5 tutor-fw-medium" title="Woocommerce Auto Cancel">
			<a href="<?php the_permalink(); ?>" target="_parent">
				<?php the_title(); ?>
			</a>
		</h3>
		<!-- course meta  -->
		<?php if ( tutor_utils()->get_option( 'enable_course_total_enrolled' ) || ! empty( $course_duration ) ) : ?>
			<div class="tutor-meta tutor-mt-12 tutor-mb-20">
				<?php if ( tutor_utils()->get_option( 'enable_course_total_enrolled' ) ) : ?>
					<div>
						<span class="tutor-meta-icon tutor-icon-user-line" area-hidden="true"></span>
						<span class="tutor-meta-value"><?php echo esc_html( $course_students ); ?></span>
					</div>
				<?php endif; ?>

				<?php if ( ! empty( $course_duration ) ) : ?>
					<div>
						<span class="tutor-icon-clock-line tutor-meta-icon" area-hidden="true"></span>
						<span class="tutor-meta-value">
							<?php
								//phpcs:ignore --data sanitize through helper method
								echo tutor_utils()->clean_html_content( $course_duration );
							?>
						</span>
					</div>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<div class="tutor-meta tutor-mt-auto">
			<div>
				<a href="<?php echo esc_url( $profile_url ); ?>" class="tutor-d-flex" target="_parent">
					<?php
					echo wp_kses(
						tutor_utils()->get_tutor_avatar( $post->post_author ),
						tutor_utils()->allowed_avatar_tags()
					);
					?>
				</a>
			</div>

			<div>
				<?php esc_html_e( 'By', 'tutor' ); ?>
				<a href="<?php echo esc_url( $profile_url ); ?>" target="_parent"><?php echo esc_html( get_the_author() ); ?></a>

				<?php if ( ! empty( $course_categories ) && is_array( $course_categories ) && count( $course_categories ) ) : ?>
					<?php esc_html_e( 'In', 'tutor' ); ?>
					<?php
						$category_links = array();
					foreach ( $course_categories as $course_category ) :
						$category_name    = $course_category->name;
						$category_link    = get_term_link( $course_category->term_id );
						$category_links[] = wp_sprintf( '<a href="%1$s" target="_parent">%2$s</a>', esc_url( $category_link ), esc_html( $category_name ) );
						endforeach;
						echo wp_kses(
							implode( ', ', $category_links ),
							array(
								'a' => array(
									'href'   => true,
									'target' => true,
								),
							)
						);
					?>
				<?php endif; ?>
			</div>
		</div>
		<!-- course meta  -->
	</div>
	<div class="tutor-card-footer">
		<a href="<?php the_permalink(); ?>" class="tutor-btn tutor-btn-outline-primary tutor-btn-md tutor-btn-block " target="_parent">
			<?php esc_html_e( 'View Details', 'tutor-pro' ); ?>
		</a>
	</div>
</div>
