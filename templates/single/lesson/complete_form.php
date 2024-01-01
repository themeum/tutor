<?php
/**
 * Display attachments
 *
 * @package Tutor\Templates
 * @subpackage Single\Lesson
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'tutor_lesson/single/before/complete_form' );

$is_completed_lesson = tutor_utils()->is_completed_lesson();
if ( ! $is_completed_lesson ) {
	?>
<div class="tutor-topbar-complete-btn tutor-mr-20">
	<form method="post">
		<?php wp_nonce_field( tutor()->nonce_action, tutor()->nonce, false ); ?>
		<input type="hidden" value="<?php echo esc_attr( get_the_ID() ); ?>" name="lesson_id" />
		<input type="hidden" value="tutor_complete_lesson" name="tutor_action" />
		<button type="submit" class="tutor-topbar-mark-btn tutor-btn tutor-btn-primary tutor-ws-nowrap"
			name="complete_lesson_btn" value="complete_lesson">
			<span class="tutor-icon-circle-mark-line tutor-mr-8" area-hidden="true"></span>
			<span><?php esc_html_e( 'Mark as Complete', 'tutor' ); ?></span>
		</button>
	</form>
</div>
	<?php
}
do_action( 'tutor_lesson/single/after/complete_form' ); ?>
