<?php
/**
 * Template for displaying course tags
 *
 * @package Tutor\Templates
 * @subpackage Single\Course
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.0.0
 */

do_action( 'tutor_course/single/before/tags' );

$course_tags = get_tutor_course_tags();
if ( is_array( $course_tags ) && count( $course_tags ) ) { ?>
	<div class="tutor-course-details-widget">
		<h3 class="tutor-course-details-widget-title tutor-fs-5 tutor-fw-bold tutor-color-black tutor-mb-16">
			<?php esc_html_e( 'Tags', 'tutor' ); ?>
		</h3>
		<div class="tutor-course-details-widget-tags">
		  <ul class="tutor-tag-list">
				<?php
				foreach ( $course_tags as $course_tag ) {
					$tag_link = get_term_link( $course_tag->term_id );
					echo "<li><a href=' " . esc_url( $tag_link ) . " '> " . esc_html( $course_tag->name ) . ' </a></li>';
				}
				?>
		  </ul>
		</div>
	</div>
	<?php
}

do_action( 'tutor_course/single/after/tags' ); ?>
