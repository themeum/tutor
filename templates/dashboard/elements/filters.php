<?php
/**
 * Filter Template for the front end
 * contain basic fields for filter/sorting table data
 *
 * @package Filter
 *
 * @since v2.0.0
 */

$courses       = ( current_user_can( 'administrator' ) ) ? tutor_utils()->get_courses() : tutor_utils()->get_courses_by_instructor();
// Filter params.
$course_id    = isset( $_GET['course-id'] ) ? sanitize_text_field( $_GET['course-id'] ) : '';
$order_filter = isset( $_GET['order'] ) ? $_GET['order'] : 'DESC';
$date_filter  = isset( $_GET['date'] ) ? $_GET['date'] : '';
?>
<div class="tutor-bs-row">
	<div class="tutor-bs-col-12 tutor-bs-col-lg-6">
		<label class="tutor-bs-d-block">
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
	<div class="tutor-bs-col-6 tutor-bs-col-lg-3">
		<label class="tutor-bs-d-block"><?php esc_html_e( 'Sort By', 'tutor' ); ?></label>
		<select class="tutor-form-select tutor-announcement-order-sorting" data-search="no">
			<option <?php selected( $order_filter, 'ASC' ); ?>><?php esc_html_e( 'ASC', 'tutor' ); ?></option>
			<option <?php selected( $order_filter, 'DESC' ); ?>><?php esc_html_e( 'DESC', 'tutor' ); ?></option>
		</select>
	</div>
	<div class="tutor-bs-col-6 tutor-bs-col-lg-3">
		<label class="tutor-bs-d-block"><?php esc_html_e( 'Create Date', 'tutor' ); ?></label>
		<div class="tutor-v2-date-picker"></div>
	</div>
</div>
