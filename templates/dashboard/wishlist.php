<?php
/**
 * @package TutorLMS/Templates
 * @version 1.4.3
 */

global $post;
$per_page     = tutor_utils()->get_option('statement_show_per_page', 20);
$current_page = max( 1, tutor_utils()->avalue_dot( 'current_page', tutor_sanitize_data($_GET) ) );
$offset       = ( $current_page - 1 ) * $per_page;
$wishlists    = tutor_utils()->get_wishlist(null, $offset, $per_page); 
$total_wishlists_count = count(tutor_utils()->get_wishlist(null)); 
?>

<div class="tutor-fs-5 tutor-fw-medium tutor-color-black tutor-mb-24"><?php _e('Wishlist', 'tutor'); ?></div>
<div class="tutor-dashboard-content-inner my-wishlist">
	<?php if (is_array($wishlists) && count($wishlists)): ?>
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
	<?php else: ?>
		<?php tutor_utils()->tutor_empty_state( tutor_utils()->not_found_text() ); ?>
	<?php endif; ?>
</div>
<div class="tutor-mt-25">
	<?php 
		if($total_wishlists_count >= $per_page) {
			$pagination_data = array(
				'total_items' => $total_wishlists_count,
				'per_page'    => $per_page,
				'paged'       => $current_page,
			);

			tutor_load_template_from_custom_path(
				tutor()->path . 'templates/dashboard/elements/pagination.php',
				$pagination_data
			);
		}
	?>
</div>
