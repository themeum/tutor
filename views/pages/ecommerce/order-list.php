<?php
/**
 * Course List Template.
 *
 * @package Tutor\Views
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Tutor\Ecommerce\OrderController as OrderController;
use TUTOR\Input;

$courses = \TUTOR\Tutor::instance()->course_list;

/**
 * Short able params
 */
$course_id     = Input::get( 'course-id', '' );
$order_filter  = Input::get( 'order', 'DESC' );
$date          = Input::get( 'date', '' );
$search_filter = Input::get( 'search', '' );
$category_slug = Input::get( 'category', '' );

/**
 * Determine active tab
 */
$active_tab = Input::get( 'data', 'all' );

/**
 * Pagination data
 */
$paged_filter = Input::get( 'paged', 1, Input::TYPE_INT );
$limit        = 3; // tutor_utils()->get_option( 'pagination_per_page' );
$offset       = ( $limit * $paged_filter ) - $limit;

/**
 * Navbar data to make nav menu
 */
$add_course_url = esc_url( admin_url( 'admin.php?page=create-course' ) );
$navbar_data    = array(
	'page_title'   => $courses->page_title,
	'tabs'         => $courses->tabs_key_value( $category_slug, $course_id, $date, $search_filter ),
	'active'       => $active_tab,
	'add_button'   => true,
	'button_title' => __( 'Add New', 'tutor' ),
	'button_url'   => $add_course_url,
);

/**
 * Bulk action & filters
 */
$filters = array(
	'bulk_action'     => $courses->bulk_action,
	'bulk_actions'    => $courses->prepare_bulk_actions(),
	'ajax_action'     => 'tutor_course_list_bulk_action',
	'filters'         => true,
	'category_filter' => true,
);


$args = array(
	'post_type'      => tutor()->course_post_type,
	'orderby'        => 'ID',
	'order'          => $order_filter,
	'paged'          => $paged_filter,
	'offset'         => $offset,
	'posts_per_page' => tutor_utils()->get_option( 'pagination_per_page' ),
);

if ( 'all' === $active_tab || 'mine' === $active_tab ) {
	$args['post_status'] = array( 'publish', 'pending', 'draft', 'private', 'future' );
} else {
	//phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
	$status              = 'published' === $active_tab ? 'publish' : $active_tab;
	$args['post_status'] = array( $status );
}

if ( 'mine' === $active_tab ) {
	$args['author'] = get_current_user_id();
}
$date_filter = sanitize_text_field( tutor_utils()->array_get( 'date', $_GET, '' ) );

//phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
$year  = gmdate( 'Y', strtotime( $date_filter ) );
$month = gmdate( 'm', strtotime( $date_filter ) );
$day   = gmdate( 'd', strtotime( $date_filter ) );
// Add date query.
if ( '' !== $date_filter ) {
	$args['date_query'] = array(
		array(
			'year'  => $year,
			'month' => $month,
			'day'   => $day,
		),
	);
}

if ( '' !== $course_id ) {
	$args['p'] = $course_id;
}
// Add author param.
if ( 'mine' === $active_tab || ! current_user_can( 'administrator' ) ) {
	$args['author'] = get_current_user_id();
}
// Search filter.
if ( '' !== $search_filter ) {
	$args['s'] = $search_filter;
}
// Category filter.
if ( '' !== $category_slug ) {
	$args['tax_query'] = array(
		array(
			'taxonomy' => 'course-category',
			'field'    => 'slug',
			'terms'    => $category_slug,
		),
	);
}

add_filter( 'posts_search', '_tutor_search_by_title_only', 500, 2 );

remove_filter( 'posts_search', '_tutor_search_by_title_only', 500 );

$available_status = array(
	'publish' => array( __( 'Publish', 'tutor' ), 'select-success' ),
	'pending' => array( __( 'Pending', 'tutor' ), 'select-warning' ),
	'trash'   => array( __( 'Trash', 'tutor' ), 'select-danger' ),
	'draft'   => array( __( 'Draft', 'tutor' ), 'select-default' ),
	'private' => array( __( 'Private', 'tutor' ), 'select-default' ),
);

$future_list = array(
	'publish' => array( __( 'Publish', 'tutor' ), 'select-success' ),
	'future'  => array( __( 'Schedule', 'tutor' ), 'select-default' ),
);

$where = array();

$get_orders  = OrderController::get_orders( $where, $limit, $offset );
$orders      = $get_orders['results'];
$total_items = $get_orders['total_count'];
?>

<div class="tutor-admin-wrap">
	<?php
		/**
		 * Load Templates with data.
		 */
		$navbar_template  = tutor()->path . 'views/elements/navbar.php';
		$filters_template = tutor()->path . 'views/elements/filters.php';
		tutor_load_template_from_custom_path( $navbar_template, $navbar_data );
		tutor_load_template_from_custom_path( $filters_template, $filters );
	?>
	<div class="tutor-admin-body">
		<div class="tutor-mt-24">
			<div class="tutor-table-responsive">

				<table class="tutor-table tutor-table-middle table-dashboard-course-list">
					<thead class="tutor-text-sm tutor-text-400">
						<tr>
							<th>
								<div class="tutor-d-flex">
									<input type="checkbox" id="tutor-bulk-checkbox-all" class="tutor-form-check-input" />
								</div>
							</th>
							<th class="tutor-table-rows-sorting">
								<?php esc_html_e( 'Order ID', 'tutor' ); ?>
								<span class="a-to-z-sort-icon tutor-icon-ordering-a-z"></span>
							</th>
							<th>
								<?php esc_html_e( 'Name', 'tutor' ); ?>
							</th>
							<th class="tutor-table-rows-sorting">
								<?php esc_html_e( 'Date', 'tutor' ); ?>
								<span class="a-to-z-sort-icon tutor-icon-ordering-a-z"></span>
							</th>
							<th>
								<?php esc_html_e( 'Payment Status', 'tutor' ); ?>
							</th>
							<th>
								<?php esc_html_e( 'Order Status', 'tutor' ); ?>
							</th>
							<th class="tutor-table-rows-sorting">
								<?php esc_html_e( 'Total', 'tutor' ); ?>
								<span class="a-to-z-sort-icon tutor-icon-ordering-a-z"></span>
							</th>
							<th  width="10%">
							<?php esc_html_e( 'Action', 'tutor' ); ?>
							</th>
						</tr>
					</thead>

					<tbody>
						<?php if ( is_array( $orders ) && count( $orders ) ) : ?>
							<?php
							foreach ( $orders as $key => $order ) :
								$user_data = get_userdata( $order->user_id );
								?>
								<tr>
									<td>
										<div class="td-checkbox tutor-d-flex ">
											<input type="checkbox" class="tutor-form-check-input tutor-bulk-checkbox" name="tutor-bulk-checkbox-all" value="<?php echo esc_attr( $order->id ); ?>" />
										</div>
									</td>

									<td>
										<div class="tutor-fs-7">
											<?php echo esc_html( '#' . $order->id ); ?>
										</div>
									</td>
									
									<td>
										<div class="tutor-d-flex tutor-align-center">
											<?php
											echo wp_kses(
												tutor_utils()->get_tutor_avatar( $user_data, 'sm' ),
												tutor_utils()->allowed_avatar_tags()
											)
											?>
											<div class="tutor-ml-12">
												<a target="_blank" class="tutor-fs-7 tutor-table-link" href="<?php echo esc_url( tutor_utils()->profile_url( $user_data, true ) ); ?>">
													<?php echo esc_html( $user_data ? $user_data->display_name : '' ); ?>
												</a>
											</div>
										</div>
									</td>

									<td>
										<span class="tutor-fw-normal tutor-fs-7">
											<?php echo esc_attr( tutor_i18n_get_formated_date( $order->created_at_gmt ) ); ?>
										</span>
									</td>

									<td>
									<?php echo wp_kses_post( tutor_utils()->translate_dynamic_text( $order->payment_status, true ) ); ?>
									</td>

									<td>
										<?php echo wp_kses_post( tutor_utils()->translate_dynamic_text( $order->order_status, true ) ); ?>
									</td>
									<td>
										<?php echo wp_kses_post( tutor_utils()->tutor_price( $order->total_price ) ); ?>
									</td>
									<td>
										Action
									</td>
								</tr>
							<?php endforeach; ?>
						<?php else : ?>
							<tr>
								<td colspan="100%" class="column-empty-state">
									<?php tutor_utils()->tutor_empty_state( tutor_utils()->not_found_text() ); ?>
								</td>
							</tr>
						<?php endif; ?>
					</tbody>
				</table>

				<div class="tutor-admin-page-pagination-wrapper tutor-mt-32">
					<?php
					/**
					 * Prepare pagination data & load template
					 */
					if ( $total_items > $limit ) {
						$pagination_data     = array(
							'total_items' => $total_items,
							'per_page'    => $limit,
							'paged'       => $paged_filter,
						);
						$pagination_template = tutor()->path . 'views/elements/pagination.php';
						tutor_load_template_from_custom_path( $pagination_template, $pagination_data );
					}
					?>
				</div>
			</div>
			<!-- end table responsive -->
		</div>
	</div>
</div>

<?php
tutor_load_template_from_custom_path(
	tutor()->path . 'views/elements/common-confirm-popup.php',
	array(
		'message' => __( 'Deletion of the course will erase all its topics, lessons, quizzes, events, and other information. Please confirm your choice.', 'tutor' ),
	)
);
