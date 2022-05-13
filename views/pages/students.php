<?php
/**
 * Student List Template.
 *
 * @package Student List
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use TUTOR\Students_List;
use TUTOR\Input;

$students = new Students_List();

/**
 * Short able params
 */
$user_id	= Input::get( 'user_id' , '' );
$course_id	= Input::get( 'course-id' , '' );
$order		= Input::get( 'order', 'DESC' );
$date		= Input::has( 'date' ) ? tutor_get_formated_date( 'Y-m-d' , Input::get( 'date' ) ) : '';
$search		= Input::get( 'search', '' );

/**
 * Pagination data
 */
$paged    = Input::get( 'paged', 1, Input::TYPE_INT );
$per_page = tutor_utils()->get_option( 'pagination_per_page' );
$offset   = ( $per_page * $paged ) - $per_page;

$students_list 	= tutor_utils()->get_students( $offset, $per_page, $search, $course_id, $date, $order );
$total     		= tutor_utils()->get_total_students( $search, $course_id, $date );

/**
 * Navbar data to make nav menu
 */
$navbar_data = array(
	'page_title' => $students->page_title,
);

/**
 * Bulk action & filters
 */
$filters = array(
	'bulk_action'   => tutor_utils()->has_user_role('administrator') ? $students->bulk_action : false,
	'bulk_actions'  => $students->prpare_bulk_actions(),
	'ajax_action'   => 'tutor_student_bulk_action',
	'filters'       => true,
	'course_filter' => true,
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
		<div class="tutor-table-responsive tutor-mt-24">
			<table class="tutor-table tutor-table-responsive tutor-table-with-checkbox">
				<thead>
				<tr>
					<th width="3%">
						<div class="tutor-d-flex">
							<input type="checkbox" id="tutor-bulk-checkbox-all" class="tutor-form-check-input" />
						</div>
					</th>
					<th class="tutor-table-rows-sorting">
						<div class="tutor-color-secondary">
							<span class="tutor-fs-7"> <?php esc_html_e( 'Students', 'tutor' ); ?></span>
							<span class="tutor-icon-ordering-a-z a-to-z-sort-icon"></span>
						</div>
					</th>
					<th class="tutor-table-rows-sorting">
						<div class="tutor-color-secondary">
							<span class="tutor-fs-7"><?php esc_html_e( 'Email', 'tutor' ); ?></span>
							<span class="tutor-icon-order-down up-down-icon"></span>
						</div>
					</th>
					<th class="tutor-table-rows-sorting">
						<div class="tutor-color-secondary">
							<span class="tutor-fs-7"><?php esc_html_e( 'Registration Date', 'tutor' ); ?></span>
							<span class="tutor-icon-order-down up-down-icon"></span>
						</div>
					</th>
					<th class="tutor-table-rows-sorting">
						<div class="tutor-color-secondary">
							<span class="tutor-fs-7"><?php esc_html_e( 'Course Taken', 'tutor' ); ?></span>
							<span class="tutor-icon-order-down up-down-icon"></span>
						</div>
					</th>
					<th class="tutor-shrink"></th>
				</tr>
				</thead>
				<tbody>
					<?php if ( is_array( $students_list ) && count( $students_list ) ) : ?>
						<?php foreach ( $students_list as $list ) : ?>
						<tr>
							<td data-th="<?php esc_html_e( 'Checkbox', 'tutor' ); ?>">
								<div class="td-checkbox tutor-d-flex ">
									<input id="tutor-admin-list-<?php esc_attr_e( $list->ID ); ?>" type="checkbox" class="tutor-form-check-input tutor-bulk-checkbox" name="tutor-bulk-checkbox-all" value="<?php echo esc_attr( $list->ID ); ?>" />
								</div>
							</td>
							<td data-th="<?php esc_html_e( 'Avatar', 'tutor' ); ?>" class="column-fullwidth">
								<div class="td-avatar">
									<?php echo tutor_utils()->get_tutor_avatar( $list->ID ); ?>
									<span class="tutor-color-black tutor-fs-6 tutor-fw-medium tutor-m-0">
										<?php esc_html_e( $list->display_name ); ?>
									</span>
									<a href="<?php echo esc_url( tutor_utils()->profile_url( $list->ID, false ) ); ?>" class="tutor-iconic-btn" target="_blank">
										<span class="tutor-icon-external-link"></span>
									</a>
								</div>
							</td>
							<td data-th="<?php esc_html_e( 'Email', 'tutor' ); ?>">
								<span class="tutor-color-black tutor-fs-7">
								<?php echo esc_html( $list->user_email ); ?>
								</span>
							</td>
							<td data-th="<?php esc_html_e( 'Registration Date', 'tutor' ); ?>">
								<span class="tutor-color-black tutor-fs-7">
								<?php echo tutor_utils()->get_local_time_from_unix( $list->user_registered ); ?>
								</span>
							</td>
							<td data-th="<?php esc_html_e( 'Course Taken', 'tutor' ); ?>">
							<?php $course_taken = tutor_utils()->get_enrolled_courses_ids_by_user( $list->ID ); ?>
								<span class="tutor-color-black tutor-fs-7 tutor-fw-medium"><?php echo esc_html( is_array( $course_taken ) ? count( $course_taken ) : 0 ); ?></span>
							</td>
							<td data-th="<?php esc_html_e( 'URL', 'tutor' ); ?>">
								<?php if( tutor()->has_pro ) : ?>
								<div class="tutor-d-inline-flex tutor-align-center td-action-btns">
									<a href="<?php echo esc_url( admin_url( 'admin.php?page=tutor_report&sub_page=students&student_id=' . $list->ID ) ); ?>"
									class="tutor-btn tutor-btn-outline-primary tutor-btn-sm">
									<?php esc_html_e( 'Details', 'tutor' ); ?>
									</a>
								</div>
								<?php endif; ?>
							</td>
						</tr>
						<?php endforeach; ?>
					<?php else : ?>
						<tr>
							<td colspan="100%">
								<?php tutor_utils()->tutor_empty_state( tutor_utils()->not_found_text() ); ?>
							</td>
						</tr>
					<?php endif; ?>	
				</tbody>
			</table>
		</div>

		<div class="tutor-admin-page-pagination-wrapper tutor-mt-32">
			<?php
				/**
				 * Prepare pagination data & load template
				 */
				if($total > $per_page) {
					$pagination_data     = array(
						'total_items' => $total,
						'per_page'    => $per_page,
						'paged'       => $paged,
					);
					$pagination_template = tutor()->path . 'views/elements/pagination.php';
					tutor_load_template_from_custom_path( $pagination_template, $pagination_data );
				}
			?>
		</div>
	</div>
</div>