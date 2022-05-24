<?php

/**
 * Template for displaying Assignments
 *
 * @since v.1.3.4
 *
 * @author Themeum
 * @url https://themeum.com
 *
 * @package TutorLMS/Templates
 * @version 1.4.3
 */

if ( ! defined( 'TUTOR_PRO_VERSION' ) ) {
	return;
}
use TUTOR_ASSIGNMENTS\Assignments_List;

$per_page     = tutor_utils()->get_option( 'pagination_per_page', 10 );
$current_page = max( 1, tutor_utils()->avalue_dot( 'current_page', tutor_sanitize_data($_GET) ) );
$offset       = ( $current_page - 1 ) * $per_page;

$course_id    = isset( $_GET['course-id'] ) ? sanitize_text_field( $_GET['course-id'] ) : '';
$order_filter = isset( $_GET['order'] ) ? $_GET['order'] : 'DESC';
$date_filter  = isset( $_GET['date'] ) ? $_GET['date'] : '';

$current_user = get_current_user_id();
$assignments  = tutor_utils()->get_assignments_by_instructor( null, compact( 'course_id', 'order_filter', 'date_filter', 'per_page', 'offset' ) );
$courses      = ( current_user_can( 'administrator' ) ) ? tutor_utils()->get_courses() : tutor_utils()->get_courses_by_instructor();

?>

<div class="tutor-fs-5 tutor-fw-medium tutor-color-black tutor-mb-24"><?php esc_html_e( 'Assignment', 'tutor' ); ?></div>

<div class="tutor-dashboard-content-inner tutor-dashboard-assignments">
	<div class="tutor-row tutor-mb-24">
		<div class="tutor-col-lg-6 tutor-mb-16 tutor-mb-lg-0">
			<label class="tutor-form-label">
				<?php esc_html_e( 'Courses', 'tutor' ); ?>
			</label>
			<select class="tutor-form-select tutor-announcement-course-sorting">
				<option value=""><?php esc_html_e( 'All', 'tutor' ); ?></option>
				<?php if ( $courses ) : ?>
					<?php foreach ( $courses as $course ) : ?>
						<option value="<?php echo esc_attr( $course->ID ); ?>" <?php selected( $course_id, $course->ID, 'selected' ); ?>>
							<?php echo $course->post_title; ?>
						</option>
					<?php endforeach; ?>
				<?php else : ?>
					<option value=""><?php esc_html_e( 'No course found', 'tutor' ); ?></option>
				<?php endif; ?>
			</select>
		</div>

		<div class="tutor-col-6 tutor-col-lg-3">
			<label class="tutor-form-label"><?php esc_html_e( 'Sort By', 'tutor' ); ?></label>
			<select class="tutor-form-select tutor-announcement-order-sorting" data-search="no">
				<option <?php selected( $order_filter, 'ASC' ); ?>><?php esc_html_e( 'ASC', 'tutor' ); ?></option>
				<option <?php selected( $order_filter, 'DESC' ); ?>><?php esc_html_e( 'DESC', 'tutor' ); ?></option>
			</select>
		</div>

		<div class="tutor-col-6 tutor-col-lg-3">
			<label class="tutor-form-label"><?php esc_html_e( 'Create Date', 'tutor' ); ?></label>
			<div class="tutor-v2-date-picker"></div>
		</div>
	</div>

	<?php if ( is_array( $assignments->results ) && count( $assignments->results ) ) : ?>
		<?php $submitted_url = tutor_utils()->get_tutor_dashboard_page_permalink( 'assignments/submitted' ); ?>
		<div class="tutor-table-responsive">
			<table class="tutor-table table-assignment">
				<thead>
					<tr>
						<th>
							<?php esc_html_e( 'Assignment Name', 'tutor' ); ?>
						</th>
						<th>
							<span class="tutor-fs-7"><?php esc_html_e( 'Total Marks', 'tutor' ); ?></span>
						</th>
						<th>
							<span class="tutor-fs-7"><?php esc_html_e( 'Total Submit', 'tutor' ); ?></span>
						</th>
						<th></th>
					</tr>
				</thead>

				<tbody>
					<?php
						foreach ( $assignments->results as $item ) :
						$max_mark      = tutor_utils()->get_assignment_option( $item->ID, 'total_mark' );
						$course_id     = tutor_utils()->get_course_id_by( 'assignment', $item->ID );
						$comment_count = Assignments_List::assignment_comment_count( $item->ID );
						// @TODO: assign post_meta is empty if user don't click on update button (http://prntscr.com/oax4t8) but post status is publish.
					?>
						<tr>
							<td>
								<?php esc_html_e( $item->post_title ); ?>
								<div class="tutor-fs-7 tutor-mt-8">
									<span class="tutor-fw-medium"><?php esc_html_e( 'Course', 'tutor' ); ?>: </span>
									<a target="_blank" href='<?php echo esc_url( get_the_permalink( $course_id ) ); ?>'><?php echo esc_html_e( get_the_title( $course_id ) ); ?> </a>
								</div>
							</td>

							<td>
								<?php echo esc_html_e( $max_mark ); ?>
							</td>
							
							<td>
								<?php echo esc_html_e( $comment_count ); ?>
							</td>

							<td>
								<a href="<?php echo esc_url( $submitted_url . '?assignment=' . $item->ID ); ?>" class="tutor-btn tutor-btn-outline-primary tutor-btn-sm">
									<?php esc_html_e( 'Details', 'tutor' ); ?>
								</a>
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
		if( $assignments->count > $per_page ) {
			$pagination_data = array(
				'total_items' => $assignments->count,
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