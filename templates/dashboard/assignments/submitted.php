<?php

/**
 * @package TutorLMS/Templates
 * @version 1.4.3
 */

global $wpdb;

$order_filter          = isset( $_GET['order'] ) ? sanitize_text_field( $_GET['order'] ) : 'desc';
$assignment_id         = sanitize_text_field( $_GET['assignment'] );
$assignments_submitted = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->comments} WHERE comment_type = 'tutor_assignment' AND comment_post_ID = %d ORDER BY comment_ID $order_filter", $assignment_id ) );

$max_mark  = tutor_utils()->get_assignment_option( $assignment_id, 'total_mark' );
$pass_mark = tutor_utils()->get_assignment_option( $assignment_id, 'pass_mark' );
$format    = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );
$deadline  = tutor_utils()->get_assignment_deadline_date( $assignment_id, $format, __( 'No Limit', 'tutor' ) );
?>

<div class="tutor-dashboard-content-inner tutor-dashboard-assignment-submits">
	<div class="tutor-mb-22">
		<a class="prev-btn" href="<?php echo esc_url( tutor_utils()->get_tutor_dashboard_page_permalink( 'assignments' ) ); ?>">
			<span>&leftarrow;</span><?php esc_html_e( 'Back', 'tutor' ); ?>
		</a>
	</div>

	<?php if ( tutor_utils()->count( $assignments_submitted ) ) : ?>
		<div class="tutor-assignment-review-header tutor-assignment-submitted-page">
			<div class="text-regular-small color-text-subsued tutor-mb-6">
				<?php esc_html_e( 'Course', 'tutor' ); ?> : <?php echo esc_html( get_the_title( $assignments_submitted[0]->comment_parent ) ); ?>
			</div>
			<div class="color-text-primary text-medium-h6 tutor-mb-9">
				<?php echo esc_html( get_the_title( $assignment_id ) ); ?>
			</div>
			<div class="assignment-info">
				<p>
					<?php esc_html_e( 'Submission Deadline', 'tutor' ); ?>:
					<span><?php echo esc_html( $deadline ); ?></span>
				</p>
				<p>
					<?php esc_html_e( 'Total Points', 'tutor' ); ?>:
					<span><?php echo esc_html( $max_mark ); ?></span>
				</p>
				<p>
					<?php esc_html_e( 'Pass Points', 'tutor' ); ?>:
					<span><?php echo esc_html( $pass_mark ); ?></span>
				</p>
			</div>
		</div>

		<div class="tutor-dashboard-announcement-sorting-wrap submitted-assignments-sorting-wrap">
			<div class="tutor-form-group">
				<label><?php esc_html_e( 'Sort By:', 'tutor' ); ?></label>
				<select class="tutor-announcement-order-sorting tutor-form-select">
					<option value="desc" <?php selected( $order_filter, 'desc' ); ?>><?php esc_html_e( 'Latest', 'tutor' ); ?></option>
					<option value="asc" <?php selected( $order_filter, 'asc' ); ?>><?php esc_html_e( 'Oldest', 'tutor' ); ?></option>
				</select>
			</div>
		</div>

		<table class="tutor-ui-table tutor-ui-table-responsive table-assignment">
			<thead>
				<tr>
					<th>
						<span class="text-regular-small color-text-subsued">
							<?php esc_html_e( 'Date', 'tutor' ); ?>
						</span>
					</th>
					<th class="tutor-table-rows-sorting">
						<div class="inline-flex-center color-text-subsued">
							<span class="text-regular-small">
								<?php esc_html_e( 'Student', 'tutor' ); ?>
							</span>
							<span class="a-to-z-sort-icon ttr-ordering-a-to-z-filled"></span>
						</div>
					</th>
					<th>
						<div class="inline-flex-center color-text-subsued">
							<span class="text-regular-small">
								<?php esc_html_e( 'Total Points', 'tutor' ); ?>
							</span>
						</div>
					</th>
					<th>
						<div class="inline-flex-center color-text-subsued">
							<span class="text-regular-small">
								<?php esc_html_e( 'Result', 'tutor' ); ?>
							</span>
						</div>
					</th>
					<th class="tutor-shrink"></th>
				</tr>
			</thead>
			<tbody>
				<?php
				foreach ( $assignments_submitted as $assignment ) {
					$review_url                = tutor_utils()->get_tutor_dashboard_page_permalink( 'assignments/review' );
					$comment_author            = get_user_by( 'login', $assignment->comment_author ); // login=username
					$is_reviewed_by_instructor = get_comment_meta( $assignment->comment_ID, 'evaluate_time', true );
					$given_mark                = get_comment_meta( $assignment->comment_ID, 'assignment_mark', true );
					$not_evaluated             = $given_mark === '';
					$status                    = sprintf( __( '%1$s Pending %2$s', 'tutor' ), '<span class="review-required tutor-badge-label label-warning tutor-m-5">', '</span>' );
					$button_text               = __( 'Evaluate', 'tutor' );

					if ( ! empty( $given_mark ) || ! $not_evaluated ) {
						$status      = (int) $given_mark >= (int) $pass_mark ? sprintf( __( '%1$s Pass %2$s', 'tutor' ), '<span class="result-pass tutor-badge-label label-success tutor-m-5">', '</span>' ) : sprintf( __( '%1$s Fail %2$s', 'tutor' ), '<span class="result-fail tutor-badge-label label-danger tutor-m-5">', '</span>' );
						$button_text = __( 'Details', 'tutor' );
					}
					?>
					<tr>
						<td data-th="<?php echo esc_attr( 'Date', 'tutor' ); ?>">
							<span class="color-text-primary text-medium-caption">
							<?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $assignment->comment_date ) ) ); ?>,<br>
							<?php echo esc_html( date_i18n( get_option( 'time_format' ), strtotime( $assignment->comment_date ) ) ); ?>
							</span>
						</td>
						<td data-th="<?php echo esc_attr( 'Student', 'tutor' ); ?>">
							<div class="td-avatar">
								<?php echo get_avatar( $comment_author->ID ); ?>
								<div class="td-avatar-detials">
									<div class="td-avatar-name d-flex align-items-center">
										<span class="color-text-primary text-medium-body">
											<?php echo esc_html( $comment_author->display_name ); ?>												
										</span>
									</div>
									<span class="color-text-title text-regular-small">
										<?php echo esc_html( $comment_author->user_email ); ?>										
									</span>
								</div>
							</div>
						</td>
						<td data-th="<?php echo esc_attr( 'Total Points', 'tutor' ); ?>">
							<span class="color-text-primary text-medium-caption">
							<?php echo ! empty( $given_mark ) ? $given_mark . '/' . $max_mark : ''; ?>
							</span>
						</td>
						<td data-th="<?php echo esc_attr( 'Result', 'tutor' ); ?>">
						    <?php echo wp_kses_post( $status ); ?>
						</td>
						<td data-th="<?php echo esc_attr( 'Details URL', 'tutor' ); ?>">
							<div class="inline-flex-center td-action-btns">
								<a href="<?php echo esc_url( $review_url . '?view_assignment=' . $assignment->comment_ID ) . '&assignment=' . $assignment_id; ?>" class="tutor-btn btn-outline">
								<?php esc_html_e( $button_text ); ?>
								</a>
							</div>
						</td>
					</tr>
					<?php
				}
				?>
			</tbody>
		</table>
	<?php else : ?>
		<p><?php esc_html_e( 'No assignment has been submitted yet', 'tutor' ); ?></p>
	<?php endif; ?>
</div>
