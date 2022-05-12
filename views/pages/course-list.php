<?php
/**
 * Course List Template.
 *
 * @package Course List
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use TUTOR\Input;
use TUTOR\Course_List;

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
	$args['post_status'] = array( 'publish', 'pending', 'draft', 'private' );
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

$the_query = new WP_Query( $args );

remove_filter( 'posts_search', '_tutor_search_by_title_only', 500 );

$available_status = array(
	'publish' => array(__( 'Publish', 'tutor' ), 'select-success'),
	'pending' => array(__( 'Pending', 'tutor' ), 'select-warning'),
	'trash'   => array(__( 'Trash', 'tutor' ), 'select-danger'),
	'draft'   => array(__( 'Draft', 'tutor' ), 'select-default'),
	'private' => array(__( 'Private', 'tutor' ), 'select-default'),
);

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
			<div class="tutor-table-wrapper">
				<table class="tutor-table tutor-table-responsive table-dashboard-course-list td-align-middle">
					<thead class="tutor-text-sm tutor-text-400">
						<tr>
							<th>
								<div class="tutor-d-flex">
									<input type="checkbox" id="tutor-bulk-checkbox-all" class="tutor-form-check-input" />
								</div>
							</th>
							<th class="tutor-table-rows-sorting">
								<div class="tutor-fs-7 tutor-color-secondary">
									<span class="tutor-fs-7">
										<?php esc_html_e( 'Date', 'tutor' ); ?>
									</span>
									<span class="a-to-z-sort-icon tutor-icon-ordering-a-z  "></span>
								</div>
							</th>
							<th class="tutor-table-rows-sorting">
								<div class="tutor-fs-7 tutor-color-secondary">
									<span class="tutor-fs-7">
										<?php esc_html_e( 'Title', 'tutor' ); ?>
									</span>
									<span class="a-to-z-sort-icon tutor-icon-ordering-a-z  "></span>
								</div>
							</th>
							<th class="tutor-table-rows-sorting">
								<div class="tutor-color-secondary">
									<span class="tutor-fs-7">
									<?php esc_html_e( 'Author', 'tutor' ); ?>
									</span>
									<span class="a-to-z-sort-icon tutor-icon-ordering-a-z  "></span>
								</div>
							</th>
							<th>
								<div class="tutor-fs-7 tutor-color-secondary">
									<?php esc_html_e( 'Course Categories', 'tutor' ); ?>
								</div>
							</th>
							<th>
								<div class="tutor-fs-7 tutor-color-secondary">
									<?php esc_html_e( 'Students', 'tutor' ); ?>
								</div>
							</th>
							<th>
								<div class="tutor-fs-7 tutor-color-secondary">
									<?php esc_html_e( 'Price', 'tutor' ); ?>
								</div>
							</th>
							<th class="tutor-shrink"></th>
						</tr>
					</thead>
					<tbody class="tutor-text-500">
						<?php if ( $the_query->have_posts() ) : ?>
							<?php
							$course_ids = array_column($the_query->posts, 'ID');
							$course_meta_data = tutor_utils()->get_course_meta_data($course_ids);
							$authors = array();
							
							foreach ( $the_query->posts as $key => $post ) :
								$count_lesson     = isset($course_meta_data[$post->ID]) ? $course_meta_data[$post->ID]['lesson'] : 0;;
								$count_quiz       = isset($course_meta_data[$post->ID]) ? $course_meta_data[$post->ID]['tutor_quiz'] : 0;
								$count_assignment = isset($course_meta_data[$post->ID]) ? $course_meta_data[$post->ID]['tutor_assignments'] : 0;
								$count_topic      = isset($course_meta_data[$post->ID]) ? $course_meta_data[$post->ID]['topics'] : 0;
								$total_student    = isset($course_meta_data[$post->ID]) ? $course_meta_data[$post->ID]['tutor_enrolled'] : 0;
								
								!isset($authors[$post->post_author]) ? $authors[$post->post_author]=get_userdata( $post->post_author ) : 0;
								$author_details = $authors[$post->post_author];
								?>
								<tr>
									<td data-th="<?php esc_html_e( 'Checkbox', 'tutor' ); ?>">
										<div class="td-checkbox tutor-d-flex ">
											<input type="checkbox" class="tutor-form-check-input tutor-bulk-checkbox" name="tutor-bulk-checkbox-all" value="<?php echo esc_attr( $post->ID ); ?>" />
										</div>
									</td>
									<td data-th="<?php esc_html_e( 'Date', 'tutor' ); ?>">
										<div class="td-datetime">
											<div class="tutor-fs-7 tutor-color-black tutor-fw-medium tutor-d-block tutor-mb-8">
												<?php echo esc_html( tutor_get_formated_date( get_option( 'date_format' ), $post->post_date ) ); ?>
											</div>
											<div class="tutor-fs-8 tutor-color-muted tutor-fw-medium tutor-d-block">
												<?php echo esc_html( tutor_get_formated_date( get_option( 'time_format' ), $post->post_date ) ); ?>
											</div>
										</div>
									</td>

									<td data-th="<?php esc_html_e( 'Course Name', 'tutor' ); ?>" class="column-fullwidth">
										<div class="td-course tutor-color-black tutor-fs-6 tutor-fw-medium tutor-line-clamp-2">
											<a href="<?php echo esc_url( admin_url( 'post.php?post=' . $post->ID . '&action=edit' ) ); ?>">
												<?php echo esc_html( $post->post_title ); ?>
											</a>
											<div class="course-meta">
											<span class="tutor-color-muted tutor-fs-7 tutor-fw-medium">
												<?php esc_html_e( 'Topic:', 'tutor' ); ?>
												<strong class="tutor-color-black">
													<?php echo esc_html( $count_topic ); ?>
												</strong>
											</span>
											<span class="tutor-color-muted tutor-fs-7 tutor-fw-medium">
												<?php esc_html_e( 'Lesson:', 'tutor' ); ?>
												<strong class="tutor-color-black">
													<?php echo esc_html( $count_lesson ); ?>
												</strong>
											</span>
											<span class="tutor-color-muted tutor-fs-7 tutor-fw-medium">
												<?php esc_html_e( 'Quiz:', 'tutor' ); ?>
												<strong class="tutor-color-black">
													<?php echo esc_html( $count_quiz ); ?>
												</strong>
											</span>
											<span class="tutor-color-muted tutor-fs-7 tutor-fw-medium">
												<?php esc_html_e( 'Assignment:', 'tutor' ); ?>
												<strong class="tutor-color-black">
													<?php echo esc_html( $count_assignment ); ?>
												</strong>
											</span>
											</div>
										</div>
									</td>
									<td data-th="<?php esc_html_e( 'Author', 'tutor' ); ?>">
										<div class="td-avatar">
											<?php echo tutor_utils()->get_tutor_avatar( $author_details->ID ); ?>
											
											<div class="tutor-fs-6 tutor-fw-medium tutor-color-black">
												<?php echo esc_html( $author_details ? $author_details->display_name : '' ); ?>
											</div>
											<a
												href="<?php echo esc_url( tutor_utils()->profile_url( $post->post_author, true ) ); ?>"
												class="tutor-iconic-btn" target="_blank"
											>
												<span class="tutor-icon-external-link"></span>
											</a>
										</div>
									</td>
									<td data-th="<?php esc_html_e( 'Course Category', 'tutor' ); ?>">
										<?php
											$terms = wp_get_post_terms( $post->ID, 'course-category' );
											echo implode(', ', array_column($terms, 'name')) . '&nbsp;';
										?>
									</td>
									<td data-th="<?php esc_html_e( 'Student', 'tutor' ); ?>">
											<div class="tutor-fs-7 tutor-color-black">
											<?php echo esc_html( $total_student ); ?>
										</div>
									</td>
									<td data-th="<?php esc_html_e( 'Price', 'tutor' ); ?>">
										<div class="tutor-fs-7 tutor-color-black">
											<?php
												$price = tutor_utils()->get_course_price( $post->ID );
												if ( null == $price ) {
													esc_html_e( 'Free', 'tutor' );
												} else {
													echo $price;
												}
												// Alert class for course status.
												$status = ( 'publish' === $post->post_status ? 'select-success' : ( 'pending' === $post->post_status ? 'select-warning' : ( 'trash' === $post->post_status ? 'select-danger' : ( 'private' === $post->post_status ? 'select-default' : 'select-default' ) ) ) );
											?>
										</div>
									</td>
									<td data-th="<?php esc_html_e( 'Action', 'tutor' ); ?>">
										<div class="tutor-d-inline-flex tutor-align-center td-action-btns">
											<div class="tutor-form-select-with-icon <?php echo esc_attr( $status ); ?>">
												<select title="<?php esc_attr_e( 'Update course status', 'tutor' ); ?>" class="tutor-table-row-status-update" data-id="<?php echo esc_attr( $post->ID ); ?>" data-status="<?php echo esc_attr( $post->post_status ); ?>" data-status_key="status" data-action="tutor_change_course_status">
													<?php foreach ( $available_status as $key => $value ) : ?>
														<option data-status_class="<?php echo esc_attr( $value[1] ); ?>" value="<?php echo $key; ?>" <?php selected( $key, $post->post_status, 'selected' ); ?>>
															<?php echo esc_html( $value[0] ); ?>
														</option>
													<?php endforeach; ?>
												</select>
												<i class="icon1 tutor-icon-eye-bold"></i>
												<i class="icon2 tutor-icon-angle-down"></i>
											</div>
											<a href="<?php echo esc_url( admin_url( 'post.php?post=' . $post->ID . '&action=edit' ) ); ?>" class="tutor-btn tutor-btn-outline-primary tutor-btn-sm">
												<?php esc_html_e( 'Edit', 'tutor' ); ?>
											</a>
											<div class="tutor-dropdown-parent">
												<button type="button" class="tutor-iconic-btn" action-tutor-dropdown="toggle">
													<span class="tutor-icon-kebab-menu" area-hidden="true"></span>
												</button>
												<div id="table-dashboard-course-list-<?php echo esc_attr( $post->ID ); ?>" class="tutor-dropdown tutor-dropdown-dark tutor-text-left">
													<?php do_action( 'tutor_admin_befor_course_list_action', $post->ID ); ?>
													<a class="tutor-dropdown-item" href="<?php echo esc_url( get_permalink( $post->ID ) ); ?>" target="_blank">
														<i class="tutor-icon-eye-bold tutor-mr-8" area-hidden="true"></i>
														<span><?php esc_html_e( 'View Course', 'tutor' ); ?></span>
													</a>
													<?php do_action( 'tutor_admin_middle_course_list_action', $post->ID ); ?>
													<a href="javascript:void(0)" class="tutor-dropdown-item tutor-admin-course-delete" data-tutor-modal-target="tutor-common-confirmation-modal" data-id="<?php echo esc_attr( $post->ID ); ?>">
														<i class="tutor-icon-trash-can-bold tutor-mr-8" area-hidden="true"></i>
														<span><?php esc_html_e( 'Delete Permanently', 'tutor' ); ?></span>
													</a>
													<?php do_action( 'tutor_admin_after_course_list_action', $post->ID ); ?>
												</div>
											</div>
										</div>
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
			</div>
		</div>
		<div class="tutor-admin-page-pagination-wrapper tutor-mt-32">
			<?php
			/**
			 * Prepare pagination data & load template
			 */
			if($the_query->found_posts > $limit) {
				$pagination_data     = array(
					'total_items' => $the_query->found_posts,
					'per_page'    => $limit,
					'paged'       => $paged_filter,
				);
				$pagination_template = tutor()->path . 'views/elements/pagination.php';
				tutor_load_template_from_custom_path( $pagination_template, $pagination_data );
			}
			?>
		</div>
	</div>
</div>

<?php tutor_load_template_from_custom_path( tutor()->path . 'views/elements/common-confirm-popup.php' );