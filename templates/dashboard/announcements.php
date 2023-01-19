<?php
/**
 * Template for displaying Announcements
 *
 * @package Tutor\Templates
 * @subpackage Dashboard
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.7.9
 */

use TUTOR\Input;
use Tutor\Models\CourseModel;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$per_page = tutor_utils()->get_option( 'pagination_per_page', 10 );
$paged    = max( 1, Input::get( 'current_page', 1, Input::TYPE_INT ) );

$order_filter  = Input::get( 'order', 'DESC' );
$search_filter = Input::get( 'search', '' );

// Announcement's parent.
$course_id   = Input::get( 'course-id', '' );
$date_filter = Input::get( 'date', '' );

$year  = date( 'Y', strtotime( $date_filter ) );
$month = date( 'm', strtotime( $date_filter ) );
$day   = date( 'd', strtotime( $date_filter ) );

$args = array(
	'post_type'      => 'tutor_announcements',
	'post_status'    => 'publish',
	's'              => sanitize_text_field( $search_filter ),
	'post_parent'    => sanitize_text_field( $course_id ),
	'posts_per_page' => sanitize_text_field( $per_page ),
	'paged'          => sanitize_text_field( $paged ),
	'orderBy'        => 'ID',
	'order'          => sanitize_text_field( $order_filter ),

);
if ( ! empty( $date_filter ) ) {
	$args['date_query'] = array(
		array(
			'year'  => $year,
			'month' => $month,
			'day'   => $day,
		),
	);
}
if ( ! current_user_can( 'administrator' ) ) {
	$args['author'] = get_current_user_id();
}
$the_query = new WP_Query( $args );

// Get courses.
$courses    = ( current_user_can( 'administrator' ) ) ? CourseModel::get_courses() : CourseModel::get_courses_by_instructor();
$image_base = tutor()->url . '/assets/images/';
?>

<div class="tutor-card tutor-p-24">
	<div class="tutor-row tutor-align-lg-center">
		<div class="tutor-col-lg-auto tutor-mb-16 tutor-mb-lg-0">
			<div class="tutor-round-box tutor-p-8">
				<i class="tutor-icon-bullhorn tutor-fs-3" area-hidden="true"></i>
			</div>
		</div>

		<div class="tutor-col tutor-mb-16 tutor-mb-lg-0">
			<div class="tutor-fs-6 tutor-color-muted tutor-mb-4">
				<?php esc_html_e( 'Create Announcement', 'tutor' ); ?>
			</div>
			<div class="tutor-fs-5 tutor-color-black">
				<?php esc_html_e( 'Notify all students of your course', 'tutor' ); ?>
			</div>
		</div>

		<div class="tutor-col-lg-auto">
			<button type="button" class="tutor-btn tutor-btn-primary" data-tutor-modal-target="tutor_announcement_new">
				<?php esc_html_e( 'Add New Announcement', 'tutor' ); ?>
			</button>
		</div>
	</div>
</div>

<div class="tutor-row tutor-mb-32 tutor-mt-44" style="width: calc(100% + 30px);">
	<div class="tutor-col-12 tutor-col-lg-6 tutor-mt-12 tutor-mt-lg-0">
		<label class="tutor-d-block tutor-mb-12 tutor-form-label">
			<?php esc_html_e( 'Courses', 'tutor' ); ?>
		</label>
		<select class="tutor-form-select tutor-announcement-course-sorting">
			<option value=""><?php esc_html_e( 'All', 'tutor' ); ?></option>
			<?php if ( $courses ) : ?>
				<?php foreach ( $courses as $course ) : ?>
					<option value="<?php echo esc_attr( $course->ID ); ?>" <?php selected( $course_id, $course->ID, 'selected' ); ?>>
						<?php echo esc_html( $course->post_title ); ?>
					</option>
				<?php endforeach; ?>
			<?php else : ?>
				<option value=""><?php esc_html_e( 'No course found', 'tutor' ); ?></option>
			<?php endif; ?>
		</select>
	</div>

	<div class="tutor-col-6 tutor-col-lg-3 tutor-mt-12 tutor-mt-lg-0">
		<label class="tutor-d-block tutor-mb-12 tutor-form-label"><?php esc_html_e( 'Sort By', 'tutor' ); ?></label>
		<select class="tutor-form-select tutor-announcement-order-sorting tutor-form-control-sm" data-search="no">
			<option <?php selected( $order_filter, 'ASC' ); ?>><?php esc_html_e( 'ASC', 'tutor' ); ?></option>
			<option <?php selected( $order_filter, 'DESC' ); ?>><?php esc_html_e( 'DESC', 'tutor' ); ?></option>
		</select>
	</div>

	<div class="tutor-col-6 tutor-col-lg-3 tutor-mt-12 tutor-mt-lg-0">
		<label class="tutor-form-label tutor-d-block tutor-mb-12"><?php esc_html_e( 'Date', 'tutor' ); ?></label>
		<div class="tutor-v2-date-picker"></div>
	</div>
</div>

<?php
$announcements = $the_query->have_posts() ? $the_query->posts : array();
tutor_load_template_from_custom_path(
	tutor()->path . '/views/fragments/announcement-list.php',
	array(
		'announcements' => is_array( $announcements ) ? $announcements : array(),
		'the_query'     => $the_query,
		'paged'         => $paged,
	)
);
?>
