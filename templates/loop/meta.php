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
$course_students   = tutor_utils()->count_enrolled_users_by_course( $course_id );
?>

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
                    //phpcs:ignore --escaping through helper method
					echo tutor_utils()->clean_html_content( $course_duration );
				?>
			</span>
		</div>
	<?php endif; ?>
</div>
<?php endif; ?>

<div class="tutor-meta tutor-mt-auto">
	<div>
		<a href="<?php echo esc_url( $profile_url ); ?>" class="tutor-d-flex">
			<?php echo wp_kses( tutor_utils()->get_tutor_avatar( $post->post_author ), tutor_utils()->allowed_avatar_tags() ); ?>
		</a>
	</div>

	<div>
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
				echo implode( ', ', $category_links ); //phpcs:ignore --contain safe data
			?>
		<?php endif; ?>
	</div>
</div>
