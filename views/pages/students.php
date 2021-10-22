<?php
/**
 * Enrollment List Template.
 *
 * @package Enrollment List
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use TUTOR\Students_List;
$students = new Students_List();


/**
 * Short able params
 */
$course_id = isset( $_GET['course-id'] ) ? $_GET['course-id'] : '';
$order     = isset( $_GET['order'] ) ? $_GET['order'] : 'DESC';
$date      = isset( $_GET['date'] ) ? tutor_get_formated_date( 'Y-m-d', $_GET['date'] ) : '';
$search    = isset( $_GET['search'] ) ? $_GET['search'] : '';

/**
 * Determine active tab
 */
$active_tab = isset( $_GET['data'] ) && $_GET['data'] !== '' ? esc_html__( $_GET['data'] ) : 'all';

/**
 * Pagination data
 */
$paged    = ( isset( $_GET['paged'] ) && is_numeric( $_GET['paged'] ) && $_GET['paged'] >= 1 ) ? $_GET['paged'] : 1;
$per_page = tutor_utils()->get_option( 'pagination_per_page' );
$offset   = ( $per_page * $paged ) - $per_page;

$students_list = tutor_utils()->get_enrolments( $active_tab, $offset, $per_page, $search, $course_id, $date, $order );
$total            = tutor_utils()->get_total_enrolments( $active_tab, $search, $course_id, $date );

/**
 * Navbar data to make nav menu
 */
$navbar_data = array(
	'page_title' => $students->page_title,
	'tabs'       => $students->tabs_key_value( $course_id, $date, $search ),
	'active'     => $active_tab,
);

//var_dump($navbar_data);

/**
 * Bulk action & filters
 */
// $filters = array(
// 'bulk_action'   => $enrollments->bulk_action,
// 'bulk_actions'  => $enrollments->prpare_bulk_actions(),
// 'search_filter' => true,
// );
$filters = array(
	'bulk_action'   => $students->bulk_action,
	'bulk_actions'  => $students->prpare_bulk_actions(),
	'ajax_action'   => 'tutor_enrollment_bulk_action',
	'filters'       => true,
	'course_filter' => true,
);

?>
<div class="tutor-admin-page-wrapper">
	<?php
		/**
		 * Load Templates with data.
		 */
		$navbar_template  = tutor()->path . 'views/elements/navbar.php';
		$filters_template = tutor()->path . 'views/elements/filters.php';
		tutor_load_template_from_custom_path( $navbar_template, $navbar_data );
		tutor_load_template_from_custom_path( $filters_template, $filters );
		
	?>

	<div class="tutor-admin-page-content-wrapper">
		<div class="tutor-table-responsive">
			<table class="tutor-table">
				<thead class="tutor-text-sm tutor-text-400">
					<tr>
						<th>
							<div class="tutor-form-check tutor-mb-15">
								<input
									id="tutor-bulk-checkbox-all"
									type="checkbox"
									class="tutor-form-check-input tutor-form-check-square"
									name="tutor-bulk-checkbox-all"
								/>
								<label for="tutor-bulk-checkbox-all">
									<?php esc_html_e( 'Name', 'tutor-pro' ); ?>
								</label>
							</div>
						</th>
						<th>
							<?php esc_html_e( 'Email', 'tutor-pro' ); ?>
						</th>
						<th>
							<?php esc_html_e( 'Registration Date', 'tutor-pro' ); ?>
						</th>
						<th>
							<?php esc_html_e( 'Course Taken', 'tutor-pro' ); ?>
						</th>
					</tr>
				</thead>
				<tbody class="tutor-text-500">
					<?php foreach ( $students_list as $list ) : ?>
						<tr>
							<td>
								<div class="tutor-form-check tutor-mb-15">
									<input
										id="tutor-admin-list-<?php esc_attr_e( $list->enrol_id ); ?>"
										type="checkbox"
										class="tutor-form-check-input tutor-form-check-square tutor-bulk-checkbox"
										name="tutor-bulk-checkbox-all"
										value="<?php esc_attr_e( $list->enrol_id ); ?>"
									/>
									<label for="tutor-admin-list-<?php esc_attr_e( $list->enrol_id ); ?>">
										<?php echo esc_html( $list->user_nicename ); ?>
									</label>
								</div>
							</td>
							<td>
								<?php echo esc_html( $list->course_title ); ?>
							</td>
							<td>
								<p>
								<?php esc_html_e( tutor_get_formated_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $list->enrol_date ) ); ?>
								</p>
								<p>
									
								</p>
							</td>
							<td>
								<span>
								<?php echo esc_html( $list->status ); ?>
								</span>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	</div>
	<div class="tutor-admin-page-pagination-wrapper">
		<?php
			/**
			 * Prepare pagination data & load template
			 */
			$pagination_data     = array(
				'total_items' => $total,
				'per_page'    => $per_page,
				'paged'       => $paged,
			);
			$pagination_template = esc_url( tutor()->path . 'views/elements/pagination.php', $pagination_data );
			tutor_load_template_from_custom_path( $pagination_template, $pagination_data );
			?>
	</div>
</div>
