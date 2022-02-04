<?php
/**
 * Display attachments
 *
 * @since v.1.0.0
 * @author themeum
 * @url https://themeum.com
 *
 * @package TutorLMS/Templates
 * @version 1.4.3
 */

if ( ! defined( 'ABSPATH' ) )
	exit;

do_action('tutor_lesson/single/before/complete_form');

$is_completed_lesson = tutor_utils()->is_completed_lesson();
if ( ! $is_completed_lesson) {
	?>
    <div class="tutor-topbar-complete-btn tutor-ml-24">
        <form method="post">
            <?php wp_nonce_field( tutor()->nonce_action, tutor()->nonce ); ?>
            <input type="hidden" value="<?php echo get_the_ID(); ?>" name="lesson_id"/>
            <input type="hidden" value="tutor_complete_lesson" name="tutor_action"/>
            <button type="submit" class="tutor-topbar-mark-btn tutor-btn tutor-btn-icon tutor-btn-md" name="complete_lesson_btn" value="complete_lesson">
                <span class="btn-icon tutor-icon-tick-circle-outline-filled"></span>
                <span class="tutor-content-responsive tutor-btn-content"><?php _e( 'Mark as ', 'tutor' ); ?></span>
                <span class="tutor-btn-content"><?php _e( 'Complete', 'tutor' ); ?></span>
            </button>
        </form>
    </div>
	<?php
}
do_action('tutor_lesson/single/after/complete_form'); ?>