<?php
/**
 * @package TutorLMS/Templates
 * @version 1.4.3
 */

?>

<h3><?php esc_html_e('Wishlist', 'tutor'); ?></h3>
<div class="tutor-dashboard-content-inner my-wishlist">
    <div class="tutor-bs-row">
		<div class="tutor-course-listing-grid tutor-course-listing-grid-3">
			<?php
				$wishlists = tutor_utils()->get_wishlist();
				if (is_array($wishlists) && count($wishlists)):
					foreach ($wishlists as $wish):
						setup_postdata($wish);
						/**
						 * @hook tutor_course/archive/before_loop_course
						 * @type action
						 * Usage Idea, you may keep a loop within a wrap, such as bootstrap col
						 */
						do_action('tutor_course/archive/before_loop_course');

						tutor_load_template('loop.course');

						/**
						 * @hook tutor_course/archive/after_loop_course
						 * @type action
						 * Usage Idea, If you start any div before course loop, you can end it here, such as </div>
						 */
						do_action('tutor_course/archive/after_loop_course');
					endforeach;
					wp_reset_postdata();
				else:
					$msg = __('You do not have any course on the wishlist yet.', 'tutor');
					echo wp_kses_post("<div class=\"tutor-bs-col\">{$msg}</div>");
				endif;
			?>
		</div>
    </div>
</div>
