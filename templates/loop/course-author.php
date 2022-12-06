<?php

/**
 * Display loop thumbnail
 *
 * @package Tutor\Templates
 * @subpackage CourseLoopPart
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.4.3
 */

global $post, $authordata;

$profile_url       = tutor_utils()->profile_url( $authordata->ID, true );
$course_categories = get_tutor_course_categories();
?>

<div class="tutor-meta-course-by-cat tutor-meta tutor-mt-32">
	<div class="tutor-meta-course-by">
		<a href="<?php echo esc_url( $profile_url ); ?>" class="tutor-d-flex">
			<?php
				echo wp_kses(
					tutor_utils()->get_tutor_avatar( $post->post_author ),
					tutor_utils()->allowed_avatar_tags()
				);
				?>
		</a>
	</div>

	<div class="tutor-meta-course-cat tutor-line-clamp-3">
		<?php esc_html_e( 'By', 'tutor' ); ?>
		<a href="<?php echo esc_url( $profile_url ); ?>"><?php echo esc_html( get_the_author() ); ?></a>

		<?php if ( ! empty( $course_categories ) && is_array( $course_categories ) && count( $course_categories ) ) : ?>
			<?php esc_html_e( 'In', 'tutor' ); ?>
			<?php
				$category_links = array();
			foreach ( $course_categories as $course_category ) :
				$category_name    = $course_category->name;
				$category_link    = get_term_link( $course_category->term_id );
				$category_links[] = wp_sprintf( '<a href="%1$s">%2$s</a>', esc_url( $category_link ), esc_html( $category_name ) );
				endforeach;
				echo implode( ', ', $category_links );//phpcs:ignore
			?>
		<?php endif; ?>
	</div>
</div>
