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
use TUTOR_REPORT\Analytics;
use TUTOR_REPORT\CourseAnalytics;

$courses = new Course_List();

/**
 * Short able params
 */
$course_id     = isset( $_GET['course-id'] ) ? sanitize_text_field( $_GET['course-id'] ) : '';
$order_filter  = isset( $_GET['order'] ) ? sanitize_text_field( $_GET['order'] ) : 'DESC';
$date          = isset( $_GET['date'] ) ? sanitize_text_field( $_GET['date'] ) : '';
$search_filter = isset( $_GET['search'] ) ? sanitize_text_field( $_GET['search'] ) : '';
$category_slug = isset( $_GET['category'] ) ? sanitize_text_field( $_GET['category'] ) : '';

/**
 * Determine active tab
 */
$active_tab = isset( $_GET['data'] ) && $_GET['data'] !== '' ? esc_html__( $_GET['data'] ) : 'all';

/**
 * Pagination data
 */
$paged_filter = ( isset( $_GET['paged'] ) && is_numeric( $_GET['paged'] ) && $_GET['paged'] >= 1 ) ? $_GET['paged'] : 1;
$limit        = tutor_utils()->get_option( 'pagination_per_page' );
$offset       = ( $limit * $paged_filter ) - $limit;

/**
 * Navbar data to make nav menu
 */
$add_course_url = esc_url( admin_url( 'post-new.php?post_type=courses' ) );
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
// $filters = array(
// 'bulk_action'   => $enrollments->bulk_action,
// 'bulk_actions'  => $enrollments->prpare_bulk_actions(),
// 'search_filter' => true,
// );
$filters = array(
	'bulk_action'     => $courses->bulk_action,
	'bulk_actions'    => $courses->prepare_bulk_actions(),
	'ajax_action'     => 'tutor_course_list_bulk_action',
	'filters'         => true,
	'category_filter' => true,
);


$args = array(
	'post_type' => tutor()->course_post_type,
	'orderby'   => 'ID',
	'order'     => $order_filter,
	'paged'     => $paged_filter,
	'offset'    => $offset,
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
$date_filter = sanitize_text_field( tutor_utils()->array_get( 'date', $_GET, '' ) );

$year  = date( 'Y', strtotime( $date_filter ) );
$month = date( 'm', strtotime( $date_filter ) );
$day   = date( 'd', strtotime( $date_filter ) );
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
if ( 'mine' === $active_tab ) {
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

$the_query = new WP_Query( $args );

$available_status = array(
	'publish' => __( 'Publish', 'tutor' ),
	'pending' => __( 'Pending', 'tutor' ),
	'draft'   => __( 'Draft', 'tutor' ),
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
		<div class="tutor-ui-table-wrapper">
			<table class="tutor-ui-table tutor-ui-table-responsive table-dashboard-course-list td-align-middle">
				<thead class="tutor-text-sm tutor-text-400">
					<tr>
						<th>
							<div class="d-flex">
								<input type="checkbox" id="tutor-bulk-checkbox-all" class="tutor-form-check-input" />
							</div>
						</th>
						<th>
							  <div class="text-regular-small color-text-subsued">
								<?php esc_html_e( 'Date', 'tutor' ); ?>
							</div>
						</th>
						<th>
							<div class="text-regular-small color-text-subsued">
							<?php esc_html_e( 'Title', 'tutor-pro' ); ?>
							</div>
						</th>
						<th>
							<div class="inline-flex-center color-text-subsued">
								<span class="text-regular-small">
								<?php esc_html_e( 'Author', 'tutor-pro' ); ?>
								</span>
								<span class="tutor-v2-icon-test icon-ordering-a-to-z-filled"></span>
							</div>
						</th>	
						<th>
							<div class="text-regular-small color-text-subsued">
								<?php esc_html_e( 'Course Categories', 'tutor-pro' ); ?>
							</div>
						</th>
						<th>
							<div class="text-regular-small color-text-subsued">
								<?php esc_html_e( 'Students', 'tutor-pro' ); ?>
							</div>
						</th>
						<th>
							<div class="text-regular-small color-text-subsued">
								<?php esc_html_e( 'Price', 'tutor-pro' ); ?>
							</div>
						</th>
						<th class="tutor-shrink"></th>											
					</tr>
				</thead>
				<tbody class="tutor-text-500">
					<?php if ( $the_query->have_posts() ) : ?>
						<?php
						foreach ( $the_query->posts as $key => $post ) :
							$the_query->the_post();
							$count_lesson     = tutor_utils()->get_lesson_count_by_course( $post->ID );
							$count_quiz       = Analytics::get_all_quiz_by_course( $post->ID );
							$topics           = tutor_utils()->get_topics( $post->ID );
							$count_assignment = tutor_utils()->get_assignments_by_course( $post->ID )->count;
							$count_topic      = $topics->found_posts;
							$student_details  = CourseAnalytics::course_enrollments_with_student_details( $post->ID );
							$total_student    = $student_details['total_enrollments'];
							$author_details   = get_userdata( $post->post_author );
							?>
							<tr>
								<td data-th="Checkbox">
									<div class="td-checkbox d-flex ">
										<input type="checkbox" class="tutor-form-check-input tutor-bulk-checkbox" name="tutor-bulk-checkbox-all" value="<?php echo esc_attr( $post->ID ); ?>" />
									</div>
								</td>
								<td data-th="Date">
									<div class="td-datetime text-regular-caption color-text-primary">
										<?php echo esc_html( tutor_get_formated_date( get_option( 'date_format' ), $post->post_date ) ); ?>
									</div>
								</td>

								<td data-th="Course Name" class="column-fullwidth">
									<div class="td-course color-text-primary text-medium-body">
										<a href="#">
											<?php echo esc_html( $post->post_title ); ?>
										</a>
										<div class="course-meta">
										<span class="color-text-hints text-medium-small">
											<?php esc_html_e( 'Topic:', 'tutor' ); ?> 
											<strong class="color-text-primary">
												<?php echo esc_html( $count_topic ); ?>
											</strong>
										</span>
										<span class="color-text-hints text-medium-small">
											<?php esc_html_e( 'Lesson:', 'tutor' ); ?> 
											<strong class="color-text-primary">
												<?php echo esc_html( $count_lesson ); ?>
											</strong>
										</span>
										<span class="color-text-hints text-medium-small">
											<?php esc_html_e( 'Quiz:', 'tutor' ); ?> 
											<strong class="color-text-primary">
												<?php echo esc_html( $count_quiz ); ?>
											</strong>
										</span>
										<span class="color-text-hints text-medium-small">
											<?php esc_html_e( 'Assignment:', 'tutor' ); ?> 
											<strong class="color-text-primary">
												<?php echo esc_html( $count_assignment ); ?>
											</strong>
										</span>
										</div>
									</div>
								</td>
								<td data-th="Author">
									<div class="td-avatar">
										<?php
											echo wp_kses_post( tutor_utils()->get_tutor_avatar( $post->post_author ) );
										?>
										<p class="text-medium-body color-text-primary">
											<?php echo esc_html( $author_details->display_name ); ?>
										</p>
										<a
										href="#"
										class="btn-text btn-detail-link color-design-dark"
										>
										<span class="tutor-v2-icon-test icon-detail-link-filled"></span>
										</a>
									</div>
								</td>
								<td data-th="Course Categories">
									<?php
										$terms       = wp_get_post_terms( $post->ID, 'course-category' );
										$total_terms = count( $terms ) - 1;
									foreach ( $terms as $key => $term ) {
										$separator = $key < $total_terms ? ', ' : '';
										echo esc_html( $term->name . $separator );
									}
									?>
								</td>
								<td data-th="Student">
									  <div class="text-regular-caption color-text-primary">
										<?php echo esc_html( $total_student ); ?>
									</div>
								</td>
								<td data-th="Price">
									<div class="text-regular-caption color-text-primary">
										<?php
											$price = tutor_utils()->get_course_price( $post->ID );
										if ( null === $price ) {
											esc_html_e( 'Free', 'tutor' );
										} else {
											echo wp_kses_post( wc_price( $price ) );
										}
										// Alert class for course status.
										$status = ( 'publish' === $post->post_status ? 'select-success' : ( 'pending' === $post->post_status ? 'select-warning' : 'select-default' ) );
										?>
									</div>
								</td>
								<td data-th="Actions">
									<div class="inline-flex-center td-action-btns">
										<div class="tutor-form-select-with-icon <?php echo esc_attr( $status ); ?>">
										<select title="<?php esc_attr_e( 'Update course status', 'tutor' ); ?>" class="tutor-admin-course-status-update" data-id="<?php echo esc_attr( $post->ID ); ?>" data-status="<?php echo esc_attr( $post->post_status ); ?>">
										<?php foreach ( $available_status as $key => $value ) : ?>
											<option value="publish" <?php selected( $key, $post->post_status, 'selected' ); ?>>
												<?php echo esc_html( $value ); ?>
											</option>
										<?php endforeach; ?>	
										</select>
										<i class="icon1 ttr-eye-fill-filled"></i>
										<i class="icon2 ttr-angle-down-filled"></i>
										</div>
										<a href="<?php echo esc_url( admin_url( 'post.php?post=' . $post->ID . '&action=edit' ) ); ?>" class="btn-outline tutor-btn">
											<?php esc_html_e( 'Edit', 'tutor' ); ?>
										</a>
										<div class="tutor-popup-opener">
										<button
											type="button"
											class="popup-btn"
											data-tutor-popup-target="table-dashboard-course-list-<?php echo esc_attr( $post->ID ); ?>"
										>
											<span class="toggle-icon"></span>
										</button>
										<ul id="table-dashboard-course-list-<?php echo esc_attr( $post->ID ); ?>" class="popup-menu">
										<?php do_action( 'tutor_admin_befor_course_list_action', $post->ID ); ?>
											<li>
												<a href="<?php echo esc_url( $post->guid ); ?>" target="_blank">
													<span class="icon tutor-v2-icon-test icon-msg-archive-filled color-design-white"></span>
													<span class="text-regular-body color-text-white">
														<?php esc_html_e( 'View Course', 'tutor' ); ?>
													</span>
												</a>
											</li>
											<?php do_action( 'tutor_admin_middle_course_list_action', $post->ID ); ?>
											<li>
												<a href="#" class="tutor-admin-course-delete" data-id="<?php echo esc_attr( $post->ID ); ?>">
													<span class="icon tutor-v2-icon-test icon-delete-fill-filled color-design-white"></span>
													<span class="text-regular-body color-text-white">
													<?php esc_html_e( 'Delete', 'tutor' ); ?>
													</span>
												</a>
											</li>
											<?php do_action( 'tutor_admin_after_course_list_action', $post->ID ); ?>
										</ul>
										</div>
									</div>
								</td>
							</tr>
						<?php endforeach; ?>
					<?php else : ?>
						<tr>
							<td colspan="100%">
								<?php esc_html_e( 'No course found', 'tutor' ); ?>
							</td>
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
			'per_page'    => $limit,
			'paged'       => $paged_filter,
		);
		$pagination_template = tutor()->path . 'views/elements/pagination.php';
		tutor_load_template_from_custom_path( $pagination_template, $pagination_data );
		?>
	</div>
</div>
