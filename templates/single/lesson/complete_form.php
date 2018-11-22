<?php
/**
 * Display attachments
 *
 * @since v.1.0.0
 * @author themeum
 * @url https://themeum.com
 */

if ( ! defined( 'ABSPATH' ) )
	exit;


do_action('dozent_lesson/single/before/complete_form');

$is_completed_lesson = dozent_utils()->is_completed_lesson();
if ( ! $is_completed_lesson) {
	?>
    <div class="dozent-single-lesson-segment dozent-lesson-compelte-form-wrap">

        <form method="post">
			<?php wp_nonce_field( dozent()->nonce_action, dozent()->nonce ); ?>

            <input type="hidden" value="<?php echo get_the_ID(); ?>" name="lesson_id"/>
            <input type="hidden" value="dozent_complete_lesson" name="dozent_action"/>

            <button type="submit" class="course-complete-button dozent-button" name="complete_lesson_btn" value="complete_lesson"><?php _e( 'Complete Lesson', 'dozent' ); ?></button>
        </form>
    </div>
	<?php
}
do_action('dozent_lesson/single/after/complete_form'); ?>