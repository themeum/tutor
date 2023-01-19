<?php
/**
 * Courses taken template
 *
 * @package Tutor\Templates
 * @subpackage Profile
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.4.3
 */

use Tutor\Models\CourseModel;

$user_name = sanitize_text_field( get_query_var( 'tutor_profile_username' ) );
$get_user  = tutor_utils()->get_user_by_login( $user_name );
$user_id   = $get_user->ID;

$pageposts = CourseModel::get_courses_by_instructor( $user_id );
?>
<div class="tutor-grid tutor-grid-3">
	<?php
	if ( $pageposts ) {
		global $post;

		//phpcs:ignore
		foreach ( $pageposts as $post ) {
			setup_postdata( $post );

			/**
			 * Usage Idea, you may keep a loop within a wrap, such as bootstrap col
			 *
			 * @hook tutor_course/archive/before_loop_course
			 * @type action
			 */
			do_action( 'tutor_course/archive/before_loop_course' );

			tutor_load_template( 'loop.course' );

			/**
			 * Usage Idea, If you start any div before course loop, you can end it here, such as </div>
			 *
			 * @hook tutor_course/archive/after_loop_course
			 * @type action
			 */
			do_action( 'tutor_course/archive/after_loop_course' );
		}
	} else {
		?>
			<p><?php esc_html_e( 'No course yet.', 'tutor' ); ?></p>
		<?php
	}
	?>
</div>
