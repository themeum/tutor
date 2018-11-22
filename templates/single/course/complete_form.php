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


do_action('dozent_course/single/before/complete_form');

$is_completed_course = dozent_utils()->is_completed_course();
if ( ! $is_completed_course) {
	?>
    <div class="dozent-course-compelte-form-wrap">

        <form method="post">
			<?php wp_nonce_field( dozent()->nonce_action, dozent()->nonce ); ?>

            <input type="hidden" value="<?php echo get_the_ID(); ?>" name="course_id"/>
            <input type="hidden" value="dozent_complete_course" name="dozent_action"/>

            <button type="submit" class="course-complete-button" name="complete_course_btn" value="complete_course"><?php _e( 'Complete Course', 'dozent' ); ?></button>
        </form>
    </div>
	<?php
}
do_action('dozent_course/single/after/complete_form'); ?>