<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Since 1.7.9
 * configure query with get params
 *
 * @package Announcement List
 */

use TUTOR\Announcements;
$announcement_obj = new Announcements();

$per_page = tutor_utils()->get_option( 'pagination_per_page' );
$paged    = ( isset( $_GET['paged'] ) && is_numeric( $_GET['paged'] ) && $_GET['paged'] >= 1 ) ? $_GET['paged'] : 1;

$order_filter  = ( isset( $_GET['order'] ) && strtolower( $_GET['order'] ) == 'asc' ) ? 'ASC' : 'DESC';
$search_filter = sanitize_text_field( tutor_utils()->array_get( 'search', $_GET, '' ) );
// announcement's parent
$course_id   = sanitize_text_field( tutor_utils()->array_get( 'course-id', $_GET, '' ) );
$date_filter = sanitize_text_field( tutor_utils()->array_get( 'date', $_GET, '' ) );

$year  = date( 'Y', strtotime( $date_filter ) );
$month = date( 'm', strtotime( $date_filter ) );
$day   = date( 'd', strtotime( $date_filter ) );

$args = array(
	'post_type'      => 'tutor_announcements',
	'post_status'    => 'publish',
	's'              => $search_filter,
	'post_parent'    => $course_id,
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

/**
 * Navbar data to make nav menu
 */
$navbar_data = array(
	'page_title' => $announcement_obj->page_title,
);

/**
 * Filters for sorting searching
 */
$filters = array(
	'bulk_action'  => $announcement_obj->bulk_action,
	'bulk_actions' => $announcement_obj->prepare_bulk_actions(),
	'ajax_action'  => 'tutor_announcement_bulk_action',
	'filters'      => true,
);
?>
<div class="tutor-admin-page-wrapper">
	<?php
		/**
		 * Load Templates with data.
		 */
		$filters_template = esc_url( tutor()->path . 'views/elements/filters.php' );
		$navbar_template  = esc_url( tutor()->path . 'views/elements/navbar.php' );
		tutor_load_template_from_custom_path( $navbar_template, $navbar_data );
		tutor_load_template_from_custom_path( $filters_template, $filters );
	?>

	<div class="tutor-dashboard-content-inner">
		<div class="tutor-component-three-col-action new-announcement-wrap">
			<div class="tutor-announcement-big-icon">
				<i class="tutor-icon-speaker"></i>
			</div>
			<div>
				<small><?php _e( 'Create Announcement', 'tutor' ); ?></small>
				<p>
					<strong>
						<?php _e( 'Notify all students of your course', 'tutor' ); ?>
					</strong>
				</p>
			</div>
			<div class="new-announcement-button">
				<button type="button" class="tutor-btn" data-tutor-modal-target="tutor_announcement_new">
					<?php _e( 'Add New Announcement', 'tutor' ); ?>
				</button>
			</div>
		</div>
	</div>
	
	<div class="tutor-admin-page-content-wrapper">
	<?php
		$announcements = $the_query->have_posts() ? $the_query->posts : array();
		$announcement_template = esc_url( tutor()->path . '/views/fragments/announcement-list.php' );
		tutor_load_template_from_custom_path(
			$announcement_template,
			array(
				'announcements' => is_array( $announcements ) ? $announcements : array(),
                'the_query' => $the_query,
                'paged' => $paged
			)
		);
    ?>
	</div>
</div>
