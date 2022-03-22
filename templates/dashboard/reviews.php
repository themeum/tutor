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
			<div class="tutor-dashboard-inline-links">
				<ul>
					<li class="active">
						<a href="<?php echo tutor_utils()->get_tutor_dashboard_page_permalink( 'reviews' ); ?>"> 
							<?php _e( 'Received', 'tutor' ); ?> (<?php echo $reviews->count; ?>)
						</a> 
					</li>
					<?php if ( $given_count ) : ?>
						<li> 
							<a href="<?php echo tutor_utils()->get_tutor_dashboard_page_permalink( 'reviews/given-reviews' ); ?>"> 
								<?php _e( 'Given', 'tutor' ); ?> (<?php echo $given_count; ?>)
							</a> 
						</li>
					<?php endif; ?>
				</ul>
			</div>
		<?php endif; ?>

		<?php if ( $reviews->count ) : ?>
			<table class="tutor-ui-table tutor-ui-table-responsive table-reviews">
				<thead>
					<tr>
						<th>
							<div class="tutor-fs-7 tutor-color-black-60">
								<?php esc_html_e( 'Student', 'tutor' ); ?>
							</div>
						</th>
						<th>
							<div class="tutor-fs-7 tutor-color-black-60">
								<?php esc_html_e( 'Date', 'tutor' ); ?>
							</div>
						</th>
						<th>
							<div class="tutor-fs-7 tutor-color-black-60">
								<?php esc_html_e( 'Feedback', 'tutor' ); ?>
							</div>
						</th>
					</tr>
				</thead>
				<tbody>
				<?php
				foreach ( $reviews->results as $review ) {
					$user_data    = get_userdata( $review->user_id );
					// $profile_url  = tutor_utils()->profile_url( $review->user_id );
					$avatar_url   = get_avatar_url( $review->user_id );
					$student_name = $user_data->display_name;
					?>
						<tr>
							<td data-th="<?php esc_html_e( 'Student', 'tutor' ); ?>" class="column-fullwidth">
								<div class="td-avatar">
									<img src="<?php echo esc_url( $avatar_url ); ?>" alt="student avatar"/>
									<span class="tutor-fs-6 tutor-fw-medium tutor-color-black">
									<?php esc_html_e( $student_name ); ?>
									</span>
								</div>
							</td>
							<td data-th="<?php esc_html_e( 'Date', 'tutor' ); ?>">
								<div class="tutor-fs-7 tutor-fw-medium tutor-color-black">
								<?php
								$date = explode( ',', tutor_get_formated_date( null, $review->comment_date ) );
								echo '<span>'.$date[0].'</span>' . '<br />' . '<span class="tutor-fw-normal">'.$date[1].'</span>';
								?>
								</div>
							</td>
							<td data-th="<?php esc_html_e( 'Feedback', 'tutor' ); ?>">
								<div class="td-feedback">
									<div class="td-tutor-rating tutor-fs-6 tutor-color-black-60">
										<?php tutor_utils()->star_rating_generator_v2( $review->rating, null, true ); ?>
									</div>
									<div class="tutor-fs-6 tutor-color-black-60 tutor-mt-12">
										<?php echo htmlspecialchars( stripslashes( $review->comment_content ) ); ?>
									</div>
									<div class="course-name tutor-fs-7 tutor-color-black-70 tutor-mb-0">
										<span class="tutor-fs-8 tutor-fw-medium"><?php esc_html_e( 'Course', 'tutor' ); ?>:</span>&nbsp;
										<span data-href="<?php echo esc_url( get_the_permalink( $review->comment_post_ID ) ); ?>">
											<?php esc_html_e( get_the_title( $review->comment_post_ID ) ); ?>
										</span>
									</div>
								</div>
							</td>
						</tr>
						<?php
				}
				?>
				</tbody>
			</table>
		<?php else : ?>
			<div class="tutor-dashboard-content-inner">
				<?php tutor_utils()->tutor_empty_state( tutor_utils()->not_found_text() ); ?>
			</div>
		<?php endif; ?>
	</div>
<?php



$pagination_data = array(
	'total_items' => $reviews->count,
	'per_page'    => $per_page,
	'paged'       => $current_page,
);
$pagination_template_frontend = tutor()->path . 'templates/dashboard/elements/pagination.php';
tutor_load_template_from_custom_path( $pagination_template_frontend, $pagination_data );

?>