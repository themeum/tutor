<?php
/**
 * Template for displaying course content
 *
 * @package Tutor\Templates
 * @subpackage Single\Course
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.0.0
 */

global $post;

do_action( 'tutor_course/single/before/content' );

if ( tutor_utils()->get_option( 'enable_course_about', true, true ) ) {
	$string             = apply_filters( 'tutor_course_about_content', get_the_content() );
	$content_summary    = (bool) get_tutor_option( 'course_content_summary', true );
	$post_size_in_words = sizeof( explode( ' ', $string ) );
		$word_limit     = 100;
		$has_show_more  = false;

	if ( $content_summary && ( $post_size_in_words > $word_limit ) ) {
		$has_show_more = true;
	}
	?>
	<?php if ( ! empty( $string ) ) : ?>
	<div class="tutor-course-details-content<?php echo $has_show_more ? ' tutor-toggle-more-content tutor-toggle-more-collapsed' : ''; ?>"<?php echo $has_show_more ? ' data-tutor-toggle-more-content data-toggle-height="200" style="height: 200px;"' : ''; ?>>
		<h2 class="tutor-fs-5 tutor-fw-bold tutor-color-black tutor-mb-12">
			<?php echo esc_html( apply_filters( 'tutor_course_about_title', __( 'About Course', 'tutor' ) ) ); ?>
		</h2>
		
		<div class="tutor-fs-6 tutor-color-secondary">
			<?php echo apply_filters( 'the_content', $string ); //phpcs:ignore ?>
		</div>
	</div>

		<?php if ( $has_show_more ) : ?>
		<a href="#" class="tutor-btn-show-more tutor-btn tutor-btn-ghost tutor-mt-32" data-tutor-toggle-more=".tutor-toggle-more-content">
			<span class="tutor-toggle-btn-icon tutor-icon tutor-icon-plus tutor-mr-8" area-hidden="true"></span>
			<span class="tutor-toggle-btn-text"><?php esc_html_e( 'Show More', 'tutor' ); ?></span>
		</a>
	<?php endif; ?>
<?php endif; ?>
	<?php
}

do_action( 'tutor_course/single/after/content' ); ?>
