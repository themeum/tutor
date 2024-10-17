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
use Tutor\Ecommerce\OptionKeys;
use Tutor\Ecommerce\Settings;
use TUTOR\Input;

/**
 * Determine active tab
 */
$active_tab = Input::get( 'data', 'all' );

/**
 * Pagination data
 */
$paged_filter = Input::get( 'paged', 1, Input::TYPE_INT );
$limit        = (int) tutor_utils()->get_option( 'pagination_per_page', 10 );
$offset       = ( $limit * $paged_filter ) - $limit;

$coupon_controller = new CouponController();

$get_coupons = $coupon_controller->get_coupons( $limit, $offset );
$coupons     = $get_coupons['results'];
$total_items = $get_coupons['total_count'];
/**
 * Navbar data to make nav menu
 */
$page_slug       = $coupon_controller::PAGE_SLUG;
$coupon_page_url = $coupon_controller::get_coupon_page_url();

$navbar_data = array(
	'page_title'   => $coupon_controller->page_title,
	'tabs'         => $coupon_controller->tabs_key_value(),
	'active'       => $active_tab,
	'add_button'   => true,
	'button_title' => __( 'Add New', 'tutor' ),
	'button_url'   => $coupon_controller::get_coupon_page_url() . '&action=add_new',
);

/**
 * Bulk action & filters
 */
$filters = array(
	'bulk_action'  => $coupon_controller->bulk_action,
	'bulk_actions' => $coupon_controller->prepare_bulk_actions(),
	'ajax_action'  => 'tutor_coupon_bulk_action',
	'filters'      => true,
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
		$currency_symbol = Settings::get_currency_symbol_by_code( tutor_utils()->get_option( OptionKeys::CURRENCY_CODE, 'USD' ) );
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
								<?php esc_html_e( 'Name', 'tutor' ); ?>
							</th>
							<th>
								<?php esc_html_e( 'Discount', 'tutor' ); ?>
							</th>
							<th>
								<?php esc_html_e( 'Type', 'tutor' ); ?>
							</th>
							<th>
								<?php esc_html_e( 'Code', 'tutor' ); ?>
							</th>
							<th>
								<?php esc_html_e( 'Status', 'tutor' ); ?>
							</th>
							<th colspan="2">
								<?php esc_html_e( 'Uses', 'tutor' ); ?>
							</th>
						</tr>
					</thead>

					<tbody>
						<?php if ( is_array( $coupons ) && count( $coupons ) ) : ?>
							<?php
							foreach ( $coupons as $key => $coupon ) :
								?>
								<tr>
									<td>
										<div class="td-checkbox tutor-d-flex ">
											<input type="checkbox" class="tutor-form-check-input tutor-bulk-checkbox" name="tutor-bulk-checkbox-all" value="<?php echo esc_attr( $coupon->id ); ?>" />
										</div>
									</td>

									<td>
										<div class="tutor-fs-7">
											<?php echo esc_html( $coupon->coupon_title ); ?>
										</div>
									</td>

									<td>
										<div class="tutor-fs-7">
											<?php echo esc_html( 'flat' === $coupon->discount_type ? $currency_symbol . $coupon->discount_amount : $coupon->discount_amount . '%' ); ?>
										</div>
									</td>
									<td>
										<div class="tutor-fs-7">
											<?php echo esc_html( 'flat' === $coupon->discount_type ? __( 'Amount', 'tutor' ) : __( 'Percent', 'tutor' ) ); ?>
										</div>
									</td>

									<td>
										<div class="tutor-fs-7">
											<?php echo esc_html( 'automatic' === $coupon->coupon_type ? __( 'Automatic', 'tutor' ) : $coupon->coupon_code ); ?>
										</div>
									</td>

									<td>
										<?php
										echo wp_kses_post( tutor_utils()->translate_dynamic_text( $coupon->coupon_status, true ) );
										?>
									</td>

									<td>
										<?php echo esc_html( $coupon->usage_count ); ?>
									</td>
									<td>
										<div class="tutor-d-flex tutor-align-center tutor-justify-end tutor-gap-2">
											<a href="<?php echo esc_url( $coupon_page_url . '&action=edit&coupon_id=' . $coupon->id ); ?>" class="tutor-btn tutor-btn-outline-primary tutor-btn-sm">
												<?php esc_html_e( 'Edit', 'tutor' ); ?>
											</a>
											<?php if ( 'trash' === $active_tab ) : ?>
											<div class="tutor-dropdown-parent">
												<button type="button" class="tutor-iconic-btn" action-tutor-dropdown="toggle">
													<span class="tutor-icon-kebab-menu" area-hidden="true"></span>
												</button>
												<div id="table-dashboard-coupon-list-<?php echo esc_attr( $coupon->id ); ?>" class="tutor-dropdown tutor-dropdown-dark tutor-text-left">
													<a href="javascript:void(0)" class="tutor-dropdown-item tutor-delete-permanently"
														data-tutor-modal-target="tutor-common-confirmation-modal" data-action="tutor_coupon_permanent_delete" data-id="<?php echo esc_attr( $coupon->id ); ?>">
														<i class="tutor-icon-trash-can-bold tutor-mr-8" area-hidden="true"></i>
														<span>
															<?php esc_html_e( 'Delete Permanently', 'tutor' ); ?>
														</span>
													</a>
												</div>
											</div>
											<?php endif ?>
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
