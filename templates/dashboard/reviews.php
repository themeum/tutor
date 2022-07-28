<?php
/**
 * Reviews received
 *
 * @since v.1.2.13
 *
 * @author Themeum
 * @url https://themeum.com
 * @package Tutor
 *
 * @package TutorLMS/Templates
 * @version 1.4.3
 */

if ( ! tutor_utils()->is_instructor(0, true) ) {
	include __DIR__ . '/reviews/given-reviews.php';
	return;
}

// Pagination Variable
$per_page     = tutor_utils()->get_option( 'pagination_per_page', 20 );
$current_page = max( 1, tutor_utils()->avalue_dot( 'current_page', $_GET ) );
$offset       = ( $current_page - 1 ) * $per_page;

$reviews     = tutor_utils()->get_reviews_by_instructor( get_current_user_id(), $offset, $per_page );
$given_count = tutor_utils()->get_reviews_by_user( 0, 0, 0, true )->count;
?>
<div class="tutor-dashboard-content-inner">
	<div class="tutor-fs-5 tutor-fw-medium tutor-color-black tutor-mb-16"><?php _e( 'Reviews', 'tutor' ); ?></div>
	<?php if ( current_user_can( tutor()->instructor_role ) ) : ?>
		<div class="tutor-mb-32">
			<ul class="tutor-nav">
				<li class="tutor-nav-item">
					<a class="tutor-nav-link is-active" href="<?php echo tutor_utils()->get_tutor_dashboard_page_permalink( 'reviews' ); ?>"> 
						<?php _e( 'Received', 'tutor' ); ?> (<?php echo $reviews->count; ?>)
					</a> 
				</li>
				<?php if ( $given_count ) : ?>
					<li class="tutor-nav-item"> 
						<a class="tutor-nav-link" href="<?php echo tutor_utils()->get_tutor_dashboard_page_permalink( 'reviews/given-reviews' ); ?>"> 
							<?php _e( 'Given', 'tutor' ); ?> (<?php echo $given_count; ?>)
						</a> 
					</li>
				<?php endif; ?>
			</ul>
		</div>
	<?php endif; ?>

	<?php if ( $reviews->count ) : ?>
		<div class="tutor-table-responsive">
			<table class="tutor-table table-reviews">
				<thead>
					<tr>
						<th width="20%">
							<?php esc_html_e( 'Student', 'tutor' ); ?>
						</th>
						<th width="25%">
							<?php esc_html_e( 'Date', 'tutor' ); ?>
						</th>
						<th>
							<?php esc_html_e( 'Feedback', 'tutor' ); ?>
						</th>
					</tr>
				</thead>

				<tbody>
				<?php foreach ( $reviews->results as $review ) : ?>
					<?php
						$user_data    = get_userdata( $review->user_id );
						$student_name = $user_data->display_name;
					?>
					<tr>
						<td class="tutor-td-top">
							<div class="tutor-d-flex tutor-align-center">
								<?php echo tutor_utils()->get_tutor_avatar( $review->user_id ); ?>
								<span class="tutor-ml-16">
									<?php esc_html_e( $student_name ); ?>
								</span>
							</div>
						</td>

						<td class="tutor-td-top">
							<?php echo tutor_i18n_get_formated_date( $review->comment_date ); ?>
						</td>

						<td class="tutor-td-top">
							<?php tutor_utils()->star_rating_generator_v2( $review->rating, null, true ); ?>
							<div class="tutor-mt-8">
								<?php echo htmlspecialchars( stripslashes( $review->comment_content ) ); ?>
							</div>

							<div class="tutor-fs-7 tutor-mt-8">
								<span class="tutor-color-secondary">
									<?php esc_html_e( 'Course:', 'tutor' ); ?>
								</span>
								<span class="tutor-fw-normal tutor-color-muted">
									<?php esc_html_e( get_the_title( $review->comment_post_ID ) ); ?>
								</span>
							</div>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	<?php else : ?>
		<?php tutor_utils()->tutor_empty_state( tutor_utils()->not_found_text() ); ?>
	<?php endif; ?>

	<?php
		if ( $reviews->count > $per_page ) {
			$pagination_data = array(
				'total_items' => $reviews->count,
				'per_page'    => $per_page,
				'paged'       => $current_page,
			);
			
			$pagination_template_frontend = tutor()->path . 'templates/dashboard/elements/pagination.php';
			tutor_load_template_from_custom_path( $pagination_template_frontend, $pagination_data );
		}
	?>
</div>