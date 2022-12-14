<?php
/**
 * Common footer template.
 *
 * @package Tutor\Templates
 * @subpackage Single\Common
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.0.0
 */

$course_id         = isset( $course_id ) ? (int) $course_id : 0;
$course_content_id = get_the_ID();
$course_id         = tutor_utils()->get_course_id_by_subcontent( $course_content_id );
$content_id        = tutor_utils()->get_post_id( $course_content_id );
$contents          = tutor_utils()->get_course_prev_next_contents_by_id( $content_id );
$previous_id       = $contents->previous_id;
$next_id           = $contents->next_id;

$prev_is_preview = get_post_meta( $previous_id, '_is_preview', true );
$next_is_preview = get_post_meta( $next_id, '_is_preview', true );
$is_enrolled     = tutor_utils()->is_enrolled( $course_id );
$is_public       = get_post_meta( $course_id, '_tutor_is_public_course', true );
$prev_is_locked  = ! ( $is_enrolled || $prev_is_preview || $is_public );
$next_is_locked  = ! ( $is_enrolled || $next_is_preview || $is_public );
$prev_link       = $prev_is_locked || ! $previous_id ? '#' : get_the_permalink( $previous_id );
$next_link       = $next_is_locked || ! $next_id ? '#' : get_the_permalink( $next_id );
?>
<?php if ( $next_id || $previous_id ) : ?>
<div class="tutor-course-topic-single-footer tutor-px-32 tutor-py-12 tutor-mt-auto">
	<div class="tutor-single-course-content-prev">
		<a class="tutor-btn tutor-btn-secondary tutor-btn-sm" href="<?php echo esc_url( $prev_link ); ?>"<?php echo ! $previous_id ? ' disabled="disabled"' : ''; ?>>
			<span class="tutor-icon-<?php echo is_rtl() ? 'next' : 'previous'; ?>" area-hidden="true"></span>
			<span class="tutor-ml-8"><?php esc_html_e( 'Previous', 'tutor' ); ?></span>
		</a>
	</div>

	<div class="tutor-single-course-content-next">
		<a class="tutor-btn tutor-btn-secondary tutor-btn-sm" href="<?php echo esc_url( $next_link ); ?>"<?php echo ! $next_id ? ' disabled="disabled"' : ''; ?>>
			<span class="tutor-mr-8"><?php esc_html_e( 'Next', 'tutor' ); ?></span>
			<span class="tutor-icon-<?php echo is_rtl() ? 'previous' : 'next'; ?>" area-hidden="true"></span>
		</a>
	</div>
</div>
<?php endif; ?>
