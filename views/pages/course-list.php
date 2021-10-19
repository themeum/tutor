<?php
/**
 * Course List Template.
 *
 * @package Course List
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use TUTOR\Course_List;
$courses = new Course_List();

/**
 * Short able params
 */
$course_id = isset( $_GET['course-id'] ) ? $_GET['course-id'] : '';
$order     = isset( $_GET['order'] ) ? $_GET['order'] : 'DESC';
$date      = isset( $_GET['date'] ) ? $_GET['date'] : '';
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

/**
 * Navbar data to make nav menu
 */
$navbar_data = array(
	'page_title' => $courses->page_title,
	'tabs'       => $courses->tabs_key_value( $course_id, $date, $search ),
	'active'     => $active_tab,
);

/**
 * Bulk action & filters
 */
// $filters = array(
// 'bulk_action'   => $enrollments->bulk_action,
// 'bulk_actions'  => $enrollments->prpare_bulk_actions(),
// 'search_filter' => true,
// );
$filters = array(
	'bulk_action'  => $courses->bulk_action,
	'bulk_actions' => $courses->prepare_bulk_actions(),
	'ajax_action'  => 'tutor_course_list_bulk_action',
	'filters'      => true,
);


$args = array(
	'post_type' => tutor()->course_post_type,
	'orderby'   => 'ID',
	'order'     => $order,
);

if ( 'all' === $active_tab || 'mine' === $active_tab ) {
	$args['post_status'] = array( 'publish', 'pending', 'draft' );
} else {
	$status              = $active_tab === 'published' ? 'publish' : $active_tab;
	$args['post_status'] = array( $status );
}

if ( 'mine' === $active_tab ) {
	$args['author'] = get_current_user_id();
}
$the_query = new WP_Query( $args );

?>
<div class="tutor-admin-page-wrapper">
	<?php
		/**
		 * Load Templates with data.
		 */
		$navbar_template  = esc_url( tutor()->path . 'views/elements/navbar.php' );
		$filters_template = esc_url( tutor()->path . 'views/elements/filters.php' );
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
									<?php esc_html_e( 'Date', 'tutor-pro' ); ?>
								</label>
							</div>
						</th>
						<th>
							<?php esc_html_e( 'Course', 'tutor-pro' ); ?>
						</th>
						<th>
							<?php esc_html_e( 'Name', 'tutor-pro' ); ?>
						</th>
						<th>
							<?php esc_html_e( 'Status', 'tutor-pro' ); ?>
						</th>
					</tr>
				</thead>
				<tbody class="tutor-text-500">
					<?php if ( $the_query->have_posts() ) : ?>
						<?php
						while ( $the_query->have_post() ) :
							$the_query->the_post();
							?>
							<tr>
								<td>
									<div class="tutor-form-check tutor-mb-15">
										<input
											id="tutor-admin-list-<?php echo esc_attr( the_ID() ); ?>"
											type="checkbox"
											class="tutor-form-check-input tutor-form-check-square tutor-bulk-checkbox"
											name="tutor-bulk-checkbox-all"
											value="<?php echo esc_attr( the_ID() ); ?>"
										/>
										<label for="tutor-admin-list-<?php esc_attr_e( $list->enrol_id ); ?>">
										<?php echo esc_html( the_time( get_option( 'date_format' ) ) ); ?>
										</label>
									</div>
								</td>
								<td>
								    <?php echo esc_html( the_title() ); ?>
								</td>
								<td>
									<p>
									<?php echo esc_html( the_author() ); ?>
									</p>
									<p>
										fsdfd
									</p>
								</td>
								<td>
									<span>
									fsdf
									</span>
								</td>
							</tr>
						<?php endwhile; ?>
					<?php else : ?>
						<tr>
							<?php esc_html_e( 'No course found', 'tutor' ); ?>
						</tr>
					<?php endif; ?>    
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
				'total_items' => $the_query->found_posts,
				'per_page'    => $per_page,
				'paged'       => $paged,
			);
			$pagination_template = esc_url( tutor()->path . 'views/elements/pagination.php', $pagination_data );
			tutor_load_template_from_custom_path( $pagination_template, $pagination_data );
			?>
	</div>
</div>
