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

use TUTOR\Course_List;
use TUTOR\Input;
use Tutor\Models\CourseModel;

$courses = tutor_lms()->course_list;

/**
 * Short able params
 */
$course_id     = Input::get( 'course-id', '' );
$order_filter  = Input::get( 'order', 'DESC' );
$date          = Input::get( 'date', '' );
$search_filter = Input::get( 'search', '' );
$category_slug = Input::get( 'category', '' );

$current_user_id = get_current_user_id();

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
$add_course_url = esc_url( admin_url( 'admin.php?page=create-course' ) );
$navbar_data    = array(
	'page_title'   => $courses->page_title,
	'add_button'   => true,
	'button_title' => __( 'New Course', 'tutor' ),
	'button_url'   => '#',
	'button_class' => 'tutor-create-new-course',
);

$status_options = $courses->tabs_key_value( $category_slug, $course_id, $date, $search_filter );

$categories       = get_terms(
	array(
		'taxonomy' => CourseModel::COURSE_CATEGORY,
		'orderby'  => 'term_id',
		'order'    => 'DESC',
	)
);
$category_options = array(
	array(
		'key'   => '',
		'title' => __( 'All Categories', 'tutor' ),
	),
);
if ( ! is_wp_error( $categories ) && ! empty( $categories ) ) {
	foreach ( $categories as $category ) {
		$category_options[] = array(
			'key'   => $category->slug,
			'title' => $category->name,
		);
	}
}

/**
 * Bulk action & filters
 */
$filters = array(
	'bulk_action'  => $courses->bulk_action,
	'bulk_actions' => $courses->prepare_bulk_actions(),
	'ajax_action'  => 'tutor_course_list_bulk_action',
	'filters'      => apply_filters(
		'tutor_course_list_before_filter_items',
		array(
			array(
				'label'      => __( 'Status', 'tutor' ),
				'field_type' => 'select',
				'field_name' => 'data',
				'options'    => $status_options,
				'value'      => Input::get( 'data', '' ),
			),
			array(
				'label'      => __( 'Category', 'tutor' ),
				'field_type' => 'select',
				'field_name' => 'category',
				'options'    => $category_options,
				'searchable' => true,
				'value'      => Input::get( 'category', '' ),
			),
			array(
				'label'      => __( 'Publish Date', 'tutor' ),
				'field_type' => 'date',
				'field_name' => 'date',
				'show_label' => true,
				'value'      => Input::get( 'date', '' ),
			),
		),
	),
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
	$args['author'] = $current_user_id;
}
// Search filter.
if ( '' !== $search_filter ) {
	$args['s'] = $search_filter;
}
// Category filter.
if ( '' !== $category_slug ) {
	$args['tax_query'] = array(
		array(
			'taxonomy' => CourseModel::COURSE_CATEGORY,
			'field'    => 'slug',
			'terms'    => $category_slug,
		),
	);
}

add_filter( 'posts_search', '_tutor_search_by_title_only', 500, 2 );

$the_query = Course_List::course_list_query( $args, $current_user_id, $active_tab );

remove_filter( 'posts_search', '_tutor_search_by_title_only', 500 );

$available_status = array(
	'publish' => array( __( 'Publish', 'tutor' ), 'select-success' ),
	'pending' => array( __( 'Pending', 'tutor' ), 'select-warning' ),
	'trash'   => array( __( 'Trash', 'tutor' ), 'select-danger' ),
	'draft'   => array( __( 'Draft', 'tutor' ), 'select-default' ),
	'private' => array( __( 'Private', 'tutor' ), 'select-default' ),
);

if ( ! tutor_utils()->get_option( 'instructor_can_delete_course' ) && ! current_user_can( 'administrator' ) ) {
	unset( $available_status['trash'] );
}

$future_list = array(
	'publish' => array( __( 'Publish', 'tutor' ), 'select-success' ),
	'future'  => array( __( 'Schedule', 'tutor' ), 'select-default' ),
);

$show_course_delete = false;
if ( 'trash' === $active_tab && current_user_can( 'administrator' ) ) {
	$show_course_delete = true;
}

$total_courses_count   = $the_query->found_posts;
$trashed_courses_count = 0;
$other_courses_count   = 0;
if ( 0 === $total_courses_count ) {
	// Get total courses count.
	$list_args           = array(
		'post_type'              => tutor()->course_post_type,
		'post_status'            => array( 'publish', 'pending', 'draft', 'private', 'future', 'trash' ),
		'author'                 => current_user_can( 'administrator' ) ? null : $current_user_id,
		'ignore_sticky_posts'    => true,
		'update_post_meta_cache' => false,
		'update_post_term_cache' => false,
		'order_by'               => 'none',
	);
	$total_list_query    = Course_List::course_list_query( $list_args, $current_user_id, 'any', true );
	$total_courses_count = $total_list_query->found_posts;

	if ( 0 === $total_courses_count ) {
		$navbar_data['hide_action_buttons'] = true;
	} else {
		// Get other courses count (all but trashed courses).
		$list_args['post_status'] = array( 'any' );
		$other_list_query         = Course_List::course_list_query( $list_args, $current_user_id, 'any' );
		$other_courses_count      = $other_list_query->found_posts;

		// Get trashed courses count.
		if ( 0 === $other_courses_count ) {
			$list_args['post_status'] = array( 'trash' );
			$trashed_list_query       = Course_List::course_list_query( $list_args, $current_user_id, 'trash' );
			$trashed_courses_count    = $trashed_list_query->found_posts;
		}
	}
}
?>

<div class="tutor-admin-wrap">
	<?php
	$navbar_template = tutor()->path . 'views/elements/list-navbar.php';
	tutor_load_template_from_custom_path( $navbar_template, $navbar_data );

	if ( $total_courses_count > 0 ) {
		$filters_template = tutor()->path . 'views/elements/list-filters.php';
		tutor_load_template_from_custom_path( $filters_template, $filters );
	}
	?>
	<div class="tutor-admin-container tutor-admin-container-lg">
		<div class="tutor-mt-16">
			<?php if ( $the_query->have_posts() ) : ?>
			<div class="tutor-table-responsive tutor-dashboard-list-table">
				<table class="tutor-table tutor-table-middle table-dashboard-course-list">
					<thead class="tutor-text-sm tutor-text-400">
						<tr>
							<th>
								<div class="tutor-d-flex">
									<input type="checkbox" id="tutor-bulk-checkbox-all" class="tutor-form-check-input" />
								</div>
							</th>
							<th class="tutor-table-rows-sorting" width="30%">
								<?php esc_html_e( 'Title', 'tutor' ); ?>
								<span class="a-to-z-sort-icon tutor-icon-ordering-a-z"></span>
							</th>
							<th width="13%">
								<?php esc_html_e( 'Categories', 'tutor' ); ?>
							</th>
							<th width="10%">
								<?php
								$membership_only_mode = apply_filters( 'tutor_membership_only_mode', false );
								echo esc_html( $membership_only_mode ? __( 'Plan', 'tutor' ) : __( 'Price', 'tutor' ) );
								?>
							</th>
							<th width="13%">
								<?php esc_html_e( 'Author', 'tutor' ); ?>
							</th>
							<th class="tutor-table-rows-sorting" width="15%">
								<?php esc_html_e( 'Date', 'tutor' ); ?>
								<span class="a-to-z-sort-icon tutor-icon-ordering-a-z"></span>
							</th>
							<th></th>
						</tr>
					</thead>

					<tbody>
							<?php
							$course_ids       = array_column( $the_query->posts, 'ID' );
							$course_meta_data = tutor_utils()->get_course_meta_data( $course_ids );
							$authors          = array();

							//phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
							foreach ( $the_query->posts as $key => $post ) :
								$count_lesson = isset( $course_meta_data[ $post->ID ] ) ? $course_meta_data[ $post->ID ]['lesson'] : 0;

								$count_quiz       = isset( $course_meta_data[ $post->ID ] ) ? $course_meta_data[ $post->ID ]['tutor_quiz'] : 0;
								$count_assignment = isset( $course_meta_data[ $post->ID ] ) ? $course_meta_data[ $post->ID ]['tutor_assignments'] : 0;
								$count_topic      = isset( $course_meta_data[ $post->ID ] ) ? $course_meta_data[ $post->ID ]['topics'] : 0;
								$thumbnail        = get_tutor_course_thumbnail_src( 'post-thumbnail', $post->ID );

								/**
								 * Prevent re-query for same author details inside loop
								 */
								if ( ! isset( $authors[ $post->post_author ] ) ) {
									$authors[ $post->post_author ] = tutils()->get_tutor_user( $post->post_author );
								}

								$author_details = $authors[ $post->post_author ];
								$edit_link      = apply_filters( 'tutor_course_list_course_edit_link', $add_course_url . "&course_id=$post->ID", $post );
								?>
								<tr>
									<td>
										<div class="td-checkbox tutor-d-flex ">
											<input type="checkbox" class="tutor-form-check-input tutor-bulk-checkbox" name="tutor-bulk-checkbox-all" value="<?php echo esc_attr( $post->ID ); ?>" />
										</div>
									</td>

									<td>
										<div class="tutor-d-flex tutor-align-center tutor-gap-12px">
											<a href="<?php echo esc_url( $edit_link ); ?>" class="tutor-d-block">
												<div style="width: 76px;">
													<div class="tutor-ratio tutor-ratio-3x2">
														<img class="tutor-radius-3 <?php echo esc_attr( 'course-bundle' === $post->post_type ? 'tutor-bundle-list-thumb' : '' ); ?>" src="<?php echo esc_url( $thumbnail ); ?>" alt="<?php the_title(); ?>" loading="lazy">
													</div>
												</div>
											</a>

											<div>
												<a class="tutor-table-link" href="<?php echo esc_url( $edit_link ); ?>">
													<?php echo esc_html( $post->post_title ); ?>
												</a>

												<?php ob_start(); ?>
												<div class="tutor-meta tutor-gap-1 tutor-mt-4">
													<div class="tutor-d-flex tutor-align-center tutor-gap-4px">
														<i class="tutor-icon-topic"></i>
														<?php esc_html_e( 'Topic:', 'tutor' ); ?>
														<span class="tutor-meta-value"><?php echo esc_html( $count_topic ); ?></span>
													</div>

													<div class="tutor-d-flex tutor-align-center tutor-gap-4px">
														<i class="tutor-icon-note"></i>
														<?php esc_html_e( 'Lesson:', 'tutor' ); ?>
														<span class="tutor-meta-value"><?php echo esc_html( $count_lesson ); ?></span>
													</div>

													<div class="tutor-d-flex tutor-align-center tutor-gap-4px">
														<i class="tutor-icon-quiz-2"></i>
														<?php esc_html_e( 'Quiz:', 'tutor' ); ?>
														<span class="tutor-meta-value"><?php echo esc_html( $count_quiz ); ?></span>
													</div>

													<div class="tutor-d-flex tutor-align-center tutor-gap-4px">
														<i class="tutor-icon-report-time"></i>
														<?php esc_html_e( 'Assignment:', 'tutor' ); ?>
														<span class="tutor-meta-value"><?php echo esc_html( $count_assignment ); ?></span>
													</div>
												</div>
												<?php echo wp_kses_post( apply_filters( 'tutor_course_list_meta', ob_get_clean(), $post ) ); ?>
											</div>
										</div>
									</td>

									<td>
										<?php
										$terms         = wp_get_post_terms( $post->ID, CourseModel::COURSE_CATEGORY );
										$category_text = '';
										if ( count( $terms ) ) {
											$category_text = implode( ', ', array_column( $terms, 'name' ) ) . '&nbsp;';
										} else {
											$category_text = '...';
										}
										?>
										<div title="<?php echo esc_attr( $category_text ); ?>" class="tutor-fw-medium tutor-fs-7 tutor-color-hints tutor-text-ellipsis-2-lines">
											<?php echo esc_html( $category_text ); ?>
										</div>
									</td>
									<td>
										<div class="tutor-fw-medium tutor-fs-7 tutor-color-hints">
											<?php
												$price = tutor_utils()->get_course_price( $post->ID );
											if ( is_null( $price ) ) {
												esc_html_e( 'Free', 'tutor' );
											} else {
												echo $price; //phpcs:ignore
											}
												// Alert class for course status.
												//phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
												$status = ( 'publish' === $post->post_status ? 'select-success' : ( 'pending' === $post->post_status ? 'select-warning' : ( 'trash' === $post->post_status ? 'select-danger' : ( 'private' === $post->post_status ? 'select-default' : 'select-default' ) ) ) );
											?>
										</div>
									</td>
									<td>
										<div class="tutor-d-flex tutor-align-center">
											<?php
											echo wp_kses(
												tutor_utils()->get_tutor_avatar( $author_details, 'sm' ),
												tutor_utils()->allowed_avatar_tags()
											)
											?>
											<div class="tutor-ml-8">
												<a target="_blank" class="tutor-fs-7 tutor-table-link" href="<?php echo esc_url( tutor_utils()->profile_url( $author_details, true ) ); ?>">
													<?php echo esc_html( $author_details ? $author_details->display_name : '' ); ?>
												</a>
											</div>
										</div>
									</td>
									<td>
										<div class="tutor-fw-normal">
											<div class="tutor-fs-7 tutor-mb-4 tutor-color-black tutor-text-nowrap">
												<?php echo esc_html( tutor_i18n_get_formated_date( $post->post_date, get_option( 'date_format' ) ) ); ?>
											</div>
											<div class="tutor-fs-7 tutor-color-subdued">
												<?php echo esc_html( tutor_i18n_get_formated_date( $post->post_date, get_option( 'time_format' ) ) ); ?>
											</div>
										</div>
									</td>

									<td>
										<div class="tutor-d-flex tutor-align-center tutor-justify-end tutor-gap-1">
											<div class="tutor-form-select-with-icon <?php echo esc_attr( $status ); ?>">
												<select title="<?php esc_attr_e( 'Update course status', 'tutor' ); ?>" class="tutor-table-row-status-update" data-id="<?php echo esc_attr( $post->ID ); ?>" data-status="<?php echo esc_attr( $post->post_status ); ?>" data-status_key="status" data-action="tutor_change_course_status">
													<?php
													$status_list = $available_status;
													if ( 'future' === $post->post_status ) {
														$status_list = $future_list;
													}

													foreach ( $status_list as $key => $value ) :
														?>
													<option data-status_class="<?php echo esc_attr( $value[1] ); ?>" value="<?php echo esc_attr( $key ); ?>" <?php selected( $key, $post->post_status, 'selected' ); ?>>
														<?php echo esc_html( $value[0] ); ?>
													</option>
														<?php
													endforeach;
													?>
												</select>
												<i class="icon1 tutor-icon-eye-bold"></i>
												<i class="icon2 tutor-icon-angle-down"></i>
											</div>
											<a class="tutor-btn tutor-btn-tertiary tutor-btn-sm tutor-ml-4" href="<?php echo esc_url( get_permalink( $post->ID ) ); ?>" target="_blank">
												<?php esc_html_e( 'View', 'tutor' ); ?>
											</a>
											<div class="tutor-dropdown-parent">
												<button type="button" class="tutor-iconic-btn" action-tutor-dropdown="toggle">
													<span class="tutor-icon-kebab-menu" area-hidden="true"></span>
												</button>
												<div id="table-dashboard-course-list-<?php echo esc_attr( $post->ID ); ?>" class="tutor-dropdown tutor-dropdown-dark tutor-text-left">
													<?php do_action( 'tutor_admin_befor_course_list_action', $post->ID ); ?>
													<a class="tutor-dropdown-item" href="<?php echo esc_url( $edit_link ); ?>">
														<i class="tutor-icon-edit tutor-mr-8" area-hidden="true"></i>
														<span><?php esc_html_e( 'Edit', 'tutor' ); ?></span>
													</a>
													<?php do_action( 'tutor_admin_middle_course_list_action', $post->ID ); ?>
													<?php if ( $show_course_delete ) : ?>
													<a href="javascript:void(0)" class="tutor-dropdown-item tutor-admin-course-delete" data-tutor-modal-target="tutor-common-confirmation-modal" data-id="<?php echo esc_attr( $post->ID ); ?>">
														<i class="tutor-icon-trash-can-bold tutor-mr-8" area-hidden="true"></i>
														<span><?php esc_html_e( 'Delete Permanently', 'tutor' ); ?></span>
													</a>
													<?php endif; ?>
													<?php do_action( 'tutor_admin_after_course_list_action', $post->ID ); ?>
												</div>
											</div>
										</div>
									</td>
								</tr>
							<?php endforeach; ?>
					</tbody>
				</table>

				<div class="tutor-admin-page-pagination-wrapper tutor-mt-32">
					<?php
					/**
					 * Prepare pagination data & load template
					 */
					if ( $the_query->found_posts > $limit ) {
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
			<?php else : ?>
				<?php
				$template      = '';
				$template_args = array(
					'title' => __( 'No Courses Found.', 'tutor' ),
				);

				if ( 0 === $total_courses_count ) {
					$template = 'create-course-empty-state.php';
				} elseif ( 0 === $other_courses_count && 0 !== $trashed_courses_count ) {
					$template      = 'trashed-course-empty-state.php';
					$template_args = array(
						'trashed_courses_count' => $trashed_courses_count,
						'trashed_courses_url'   => '?page=tutor&data=trash',
					);
				} else {
					$template = 'list-empty-state.php';
				}

				$full_path = tutor()->path . 'views/elements/' . $template;
				tutor_load_template_from_custom_path( $full_path, $template_args );
				?>
			<?php endif; ?>
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
