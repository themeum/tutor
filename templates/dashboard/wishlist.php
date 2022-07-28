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
		<div class="tutor-grid tutor-grid-3">
			<?php
			foreach ($wishlists as $post) :
				setup_postdata($post);
			?>
			<div class="tutor-card tutor-course-card">
				<?php tutor_load_template( 'loop.header' ); ?>

				<div class="tutor-card-body">
					<?php tutor_load_template( 'loop.rating' ); ?>

					<div class="tutor-course-name tutor-fs-6 tutor-fw-bold">
						<a href="<?php echo get_the_permalink(); ?>">
							<?php the_title(); ?>
						</a>
					</div>

					<div class="tutor-mt-auto">
						<?php tutor_load_template( 'loop.course-author' ); ?>
					</div>
				</div>

				<div class="tutor-card-footer">
					<?php tutor_course_loop_price(); ?>
				</div>
			</div>
			<?php
			endforeach;
			wp_reset_postdata();
			?>
		</div>
	<?php else: ?>
		<?php tutor_utils()->tutor_empty_state( tutor_utils()->not_found_text() ); ?>
	<?php endif; ?>
</div>
<div class="tutor-mt-24">
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
