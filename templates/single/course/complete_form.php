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


do_action('lms_course/single/before/complete_form');

$is_completed_course = lms_utils()->is_completed_course();
if ( ! $is_completed_course) {
	?>
    <div class="lms-single-course-segment lms-course-compelte-form-wrap">

        <form method="post">
			<?php wp_nonce_field( lms()->nonce_action, lms()->nonce ); ?>

            <input type="hidden" value="<?php echo get_the_ID(); ?>" name="course_id"/>
            <input type="hidden" value="lms_complete_course" name="lms_action"/>

            <button type="submit" class="course-complete-button" name="complete_course_btn" value="complete_course"><?php _e( 'Complete Course', 'lms' ); ?></button>
        </form>
    </div>
	<?php
}
do_action('lms_course/single/after/complete_form'); ?>