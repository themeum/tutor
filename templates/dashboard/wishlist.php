<?php
/**
 * @package TutorLMS/Templates
 * @version 1.4.3
 */

global $post;
$wishlists = tutor_utils()->get_wishlist(); 
?>

<h3><?php _e('Wishlist', 'tutor'); ?></h3>
<div class="tutor-dashboard-content-inner my-wishlist">
	<?php if (is_array($wishlists) && count($wishlists)): ?>
		<div class="tutor-bs-row">
			<div class="tutor-course-listing-grid tutor-course-listing-grid-3">
				<?php
					foreach ($wishlists as $post) {
						setup_postdata($post);

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
					}
					wp_reset_postdata();
				?>
			</div>
    	</div>
	<?php else: ?>
		<?php tutor_utils()->tutor_empty_state( __('You do not have any course on the wishlist yet.', 'tutor')); ?>
	<?php endif; ?>
</div>