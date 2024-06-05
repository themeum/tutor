<?php
/**
 * Student List Template.
 *
 * @package Tutor\Views
 * @subpackage Tutor\Students
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.0
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
$user_id   = Input::get( 'user_id', '' );
$course_id = Input::get( 'course-id', '' );
$order     = Input::get( 'order', 'DESC' );
$date      = Input::has( 'date' ) ? tutor_get_formated_date( 'Y-m-d', Input::get( 'date' ) ) : '';
$search    = Input::get( 'search', '' );

/**
 * Pagination data
 */
$paged    = Input::get( 'paged', 1, Input::TYPE_INT );
$per_page = tutor_utils()->get_option( 'pagination_per_page' );
$offset   = ( $per_page * $paged ) - $per_page;

$students_list = tutor_utils()->get_students( $offset, $per_page, $search, $course_id, $date, $order );
$total         = tutor_utils()->get_total_students( $search, $course_id, $date );

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
	'bulk_action'   => tutor_utils()->has_user_role( 'administrator' ) ? $students->bulk_action : false,
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
		<?php if ( is_array( $students_list ) && count( $students_list ) ) : ?>
			<div class="tutor-table-responsive tutor-mt-24">
				<table class="tutor-table tutor-table-middle tutor-table-with-checkbox">
					<thead>
						<tr>
							<th width="3%">
								<div class="tutor-d-flex">
									<input type="checkbox" id="tutor-bulk-checkbox-all" class="tutor-form-check-input" />
								</div>
							</th>
							<th class="tutor-table-rows-sorting">
								<?php esc_html_e( 'Students', 'tutor' ); ?>
								<span class="tutor-icon-ordering-a-z a-to-z-sort-icon"></span>
							</th>
							<th class="tutor-table-rows-sorting">
								<?php esc_html_e( 'Email', 'tutor' ); ?>
								<span class="tutor-icon-order-down up-down-icon"></span>
							</th>
							<th class="tutor-table-rows-sorting">
								<?php esc_html_e( 'Registration Date', 'tutor' ); ?>
								<span class="tutor-icon-order-down up-down-icon"></span>
							</th>
							<th class="tutor-table-rows-sorting">
								<?php esc_html_e( 'Course Taken', 'tutor' ); ?>
								<span class="tutor-icon-order-down up-down-icon"></span>
							</th>
							<th></th>
						</tr>
					</thead>

					<tbody>
						<?php foreach ( $students_list as $list ) :
							$reg_date = $list->user_registered;
						?>
							<tr>
								<td>
									<div class="tutor-d-flex">
										<input id="tutor-admin-list-<?php echo esc_attr( $list->ID ); ?>" type="checkbox" class="tutor-form-check-input tutor-bulk-checkbox" name="tutor-bulk-checkbox-all" value="<?php echo esc_attr( $list->ID ); ?>" />
									</div>
								</td>
								<td>
									<div class="tutor-d-flex tutor-align-center tutor-gap-1">
										<?php
										echo wp_kses(
											tutor_utils()->get_tutor_avatar( $list->ID ),
											tutor_utils()->allowed_avatar_tags()
										);
										?>
										<span>
											<?php echo esc_html( $list->display_name ); ?>
										</span>
										<a href="<?php echo esc_url( tutor_utils()->profile_url( $list->ID, false ) ); ?>" class="tutor-iconic-btn" target="_blank">
											<span class="tutor-icon-external-link" area-hidden="True"></span>
										</a>
									</div>
								</td>
								<td>
									<div class="tutor-d-flex tutor-align-center" style="gap: 5px;">
									<span class="tutor-fs-7">
										<?php echo esc_html( $list->user_email ); ?>
									</span>
									<?php do_action( 'tutor_show_email_verified_badge', $list->ID ); ?>
									</div>
								</td>
								<td>
									<span class="tutor-fs-7">
										<?php echo esc_html( $reg_date ? tutor_i18n_get_formated_date( tutor_utils()->get_local_time_from_unix( $list->user_registered ) ) : '' ); ?>
									</span>
								</td>
								<td>
									<?php $course_taken = tutor_utils()->get_enrolled_courses_ids_by_user( $list->ID ); ?>
									<span class="tutor-fs-7"><?php echo esc_html( is_array( $course_taken ) ? count( $course_taken ) : 0 ); ?></span>
								</td>
								<td>
									<?php if ( tutor()->has_pro ) : ?>
										<div class="tutor-d-flex tutor-align-center tutor-gap-1">
											<?php do_action( 'tutor_before_student_details_btn', $list->ID ); ?>
											<a href="<?php echo esc_url( admin_url( 'admin.php?page=tutor_report&sub_page=students&student_id=' . $list->ID ) ); ?>"
											class="tutor-btn tutor-btn-outline-primary tutor-btn-sm">
												<?php esc_html_e( 'Details', 'tutor' ); ?>
											</a>
										</div>
									<?php endif; ?>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		<?php else : ?>
			<?php tutor_utils()->tutor_empty_state( tutor_utils()->not_found_text() ); ?>
		<?php endif; ?>

		<div class="tutor-admin-page-pagination-wrapper tutor-mt-32">
			<?php
				/**
				 * Prepare pagination data & load template
				 */
			if ( $total > $per_page ) {
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
