<?php
/**
 * Tutor learning area footer.
 *
 * @package Tutor\Templates
 * @subpackage LearningArea
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use TUTOR\Icon;

global $tutor_course_id,
$tutor_current_post,
$tutor_is_enrolled,
$tutor_is_public_course;

$contents    = tutor_utils()->get_course_prev_next_contents_by_id( $tutor_current_post->ID );
$previous_id = $contents->previous_id;
$next_id     = $contents->next_id;

$prev_is_preview = get_post_meta( $previous_id, '_is_preview', true );
$next_is_preview = get_post_meta( $next_id, '_is_preview', true );

$prev_is_locked = ! ( $tutor_is_enrolled || $prev_is_preview || $tutor_is_public_course );
$next_is_locked = ! ( $tutor_is_enrolled || $next_is_preview || $tutor_is_public_course );

$prev_link = $prev_is_locked || ! $previous_id ? '#' : get_the_permalink( $previous_id );
$next_link = $next_is_locked || ! $next_id ? '#' : get_the_permalink( $next_id );

$is_completed_lesson = tutor_utils()->is_completed_lesson();

?>
<div class="tutor-learning-footer">
	<a href="<?php echo esc_url( $prev_link ); ?>" type="button" class="tutor-btn tutor-btn-ghost tutor-btn-small">
		<?php tutor_utils()->render_svg_icon( Icon::CHEVRON_LEFT_2 ); ?>
		<?php esc_html_e( 'Previous', 'tutor' ); ?>
	</a>
	<form method="post" class="tutor-mb-none">
		<?php wp_nonce_field( tutor()->nonce_action, tutor()->nonce, false ); ?>
		<input type="hidden" value="<?php echo esc_attr( get_the_ID() ); ?>" name="lesson_id" />
		<input type="hidden" value="tutor_complete_lesson" name="tutor_action" />
		<button 
			type="submit" 
			name="complete_lesson_btn"
			class="tutor-btn tutor-btn-secondary tutor-btn-large tutor-rounded-full tutor-gap-5" 
			<?php echo esc_attr( $is_completed_lesson ? 'disabled' : '' ); ?>
		>
			<?php esc_html_e( 'Mark as complete', 'tutor' ); ?>
			<?php
			tutor_utils()->render_svg_icon(
				Icon::CHECK_2,
				20,
				20,
				array(
					'class' => $is_completed_lesson ? 'tutor-icon-success-primary' : 'tutor-icon-secondary',
				)
			);
			?>
		</button>
	</form>
	<a href="<?php echo esc_url( $next_link ); ?>" type="button" class="tutor-btn tutor-btn-ghost tutor-btn-small">
		<?php esc_html_e( 'Next', 'tutor' ); ?>
		<?php tutor_utils()->render_svg_icon( Icon::CHEVRON_RIGHT_2 ); ?>
	</a>
</div>
