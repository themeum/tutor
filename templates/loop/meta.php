<?php
/**
 * Course meta template
 *
 * Meta template contains author avatar & categories
 *
 * @package Tutor\Templates
 * @subpackage CourseLoopPart
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.5.8
 */

global $post, $authordata;
$course_id         = $post->ID;
$profile_url       = tutor_utils()->profile_url( $authordata->ID, true );
$course_categories = get_tutor_course_categories( $course_id );
$course_duration   = get_tutor_course_duration_context( $course_id, true );
$course_students   = apply_filters( 'tutor_course_students', tutor_utils()->count_enrolled_users_by_course( $course_id ), $course_id )
?>

<?php if ( tutor_utils()->get_option( 'enable_course_total_enrolled' ) || ! empty( $course_duration ) ) : ?>
<div class="tutor-meta tutor-course-card-meta tutor-mt-2 tutor-mb-2">
	<?php if ( tutor_utils()->get_option( 'enable_course_total_enrolled' ) ) : ?>
		<span class="tutor-course-meta-value"><?php echo esc_html( $course_students ); ?></span>
		<span><?php esc_html_e( 'Learners', 'tutor' ); ?></span>
	<?php endif; ?>

	<?php if ( ! empty( $course_duration ) ) : ?>
		<span class="tutor-course-card-separator"></span>
		<span class="">
			<?php
				//phpcs:ignore --escaping through helper method
				echo tutor_utils()->clean_html_content( $course_duration );
			?>
		</span>
	<?php endif; ?>

	<span class="tutor-course-card-separator"></span>
	<?php esc_html_e( 'By', 'tutor' ); ?>
	<a href="<?php echo esc_url( $profile_url ); ?>"><?php echo esc_html( get_the_author() ); ?></a>

	<div class="tutor-mt-2">
	<?php if ( ! empty( $course_categories ) && is_array( $course_categories ) && count( $course_categories ) ) : ?>
		<?php esc_html_e( 'In', 'tutor' ); ?>
		<?php
			$category_links = array();
		foreach ( $course_categories as $course_category ) :
			$category_name    = $course_category->name;
			$category_link    = get_term_link( $course_category->term_id );
			$category_links[] = wp_sprintf( '<a href="%1$s">%2$s</a>', esc_url( $category_link ), esc_html( $category_name ) );
			endforeach;
			echo implode( ', ', $category_links ); //phpcs:ignore --contain safe data
		?>
	<?php endif; ?>
	</div>

</div>
<?php endif; ?>
