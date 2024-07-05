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

use Tutor\Ecommerce\CouponController;
use TUTOR\Input;

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

$coupon_controller = new CouponController();

$get_coupons = $coupon_controller->get_coupons( $limit, $offset );
$coupons     = $get_coupons['results'];
$total_items = $get_coupons['total_count'];
/**
 * Navbar data to make nav menu
 */
$add_course_url = esc_url( admin_url( 'admin.php?page=create-course' ) );
$navbar_data    = array(
	'page_title'   => $coupon_controller->page_title,
	'tabs'         => $coupon_controller->tabs_key_value(),
	'active'       => $active_tab,
	'add_button'   => true,
	'button_title' => __( 'Add New', 'tutor' ),
	'button_url'   => $add_course_url,
);

/**
 * Bulk action & filters
 */
$filters = array(
	'bulk_action'     => $coupon_controller->bulk_action,
	'bulk_actions'    => $coupon_controller->prepare_bulk_actions(),
	'ajax_action'     => 'tutor_course_list_bulk_action',
	'filters'         => true,
	'category_filter' => true,
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
						<?php if ( is_array( $coupons ) && count( $coupons ) ) : ?>
							<?php
							foreach ( $coupons as $key => $coupon ) :
								$user_data = get_userdata( $coupon->user_id );
								?>
								<tr>
									<td>
										<div class="td-checkbox tutor-d-flex ">
											<input type="checkbox" class="tutor-form-check-input tutor-bulk-checkbox" name="tutor-bulk-checkbox-all" value="<?php echo esc_attr( $coupon->id ); ?>" />
										</div>
									</td>

									<td>
										<div class="tutor-fs-7">
											<?php echo esc_html( '#' . $coupon->id ); ?>
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
											<?php echo esc_attr( tutor_i18n_get_formated_date( $coupon->created_at_gmt ) ); ?>
										</span>
									</td>

									<td>
									<?php echo wp_kses_post( tutor_utils()->translate_dynamic_text( $coupon->payment_status, true ) ); ?>
									</td>

									<td>
										<?php echo wp_kses_post( tutor_utils()->translate_dynamic_text( $coupon->order_status, true ) ); ?>
									</td>
									<td>
										<?php echo wp_kses_post( tutor_utils()->tutor_price( $coupon->total_price ) ); ?>
									</td>
									<td>
										<a href="<?php echo esc_url( admin_url( 'admin.php?page=tutor-coupons&id=' . $coupon->id ) ); ?>">
											<?php esc_html_e( 'Edit', 'tutor' );?>
										</a>
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
