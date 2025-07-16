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
use Tutor\Models\CouponModel;

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

$coupon_controller = new CouponController( false );

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

$applies_to_options = array(
	array(
		'key'   => '',
		'title' => __( 'Select', 'tutor' ),
	),
);

$applies_to = array_map(
	function ( $val, $key ) {
		return array(
			'key'   => $key,
			'title' => $val,
		);
	},
	CouponModel::get_coupon_applies_to(),
	array_keys( CouponModel::get_coupon_applies_to() )
);

$applies_to_options = array_merge( $applies_to_options, $applies_to );

/**
 * Bulk action & filters
 */
$filters = array(
	'bulk_action'  => $coupon_controller->bulk_action,
	'bulk_actions' => $coupon_controller->prepare_bulk_actions(),
	'ajax_action'  => 'tutor_coupon_bulk_action',
	'filters'      => array(
		array(
			'label'      => __( 'Status', 'tutor' ),
			'field_type' => 'select',
			'field_name' => 'data',
			'options'    => $coupon_controller->tabs_key_value(),
			'searchable' => false,
			'value'      => Input::get( 'data', '' ),
		),
		array(
			'label'      => __( 'Applies To', 'tutor' ),
			'field_type' => 'select',
			'field_name' => 'applies_to',
			'options'    => $applies_to_options,
			'show_label' => true,
			'value'      => Input::get( 'applies_to', '' ),
		),
	),
);

?>

<div class="tutor-admin-wrap">
	<?php
		/**
		 * Load Templates with data.
		 */
		$navbar_template  = tutor()->path . 'views/elements/list-navbar.php';
		$filters_template = tutor()->path . 'views/elements/list-filters.php';
		tutor_load_template_from_custom_path( $navbar_template, $navbar_data );
		tutor_load_template_from_custom_path( $filters_template, $filters );
		$currency_symbol = Settings::get_currency_symbol_by_code( tutor_utils()->get_option( OptionKeys::CURRENCY_CODE, 'USD' ) );
	?>
	<div class="tutor-admin-container tutor-admin-container-lg">
		<div class="tutor-mt-24 tutor-dashboard-list-table">
			<div class="tutor-table-responsive">
				<?php if ( is_array( $coupons ) && count( $coupons ) ) : ?>
				<table class="tutor-table tutor-table-middle">
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
								<?php esc_html_e( 'Applies to', 'tutor' ); ?>
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
								<?php esc_html_e( 'Usage', 'tutor' ); ?>
							</th>
						</tr>
					</thead>

					<tbody>
						
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
										<a href="<?php echo esc_url( $coupon_page_url . '&action=edit&coupon_id=' . $coupon->id ); ?>" class="tutor-table-link tutor-fs-7">
											<?php echo esc_html( $coupon->coupon_title ); ?>
										</a>
									</td>

									<td>
										<div class="tutor-fs-7">
											<?php echo esc_html( CouponModel::get_coupon_applies_to_label( $coupon->applies_to ) ); ?>
										</div>
									</td>

									<td>
										<div class="tutor-fs-7">
											<?php echo wp_kses_post( ( 'flat' === $coupon->discount_type ? tutor_utils()->tutor_price( $coupon->discount_amount ) : $coupon->discount_amount . '%' ) ); ?>
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
										$coupon_status = $coupon->coupon_status;
										if ( CouponModel::STATUS_ACTIVE === $coupon_status ) {
											$coupon_status = $coupon_controller->model->has_coupon_validity( $coupon ) ? $coupon->coupon_status : 'expired';
										}
										echo wp_kses_post( tutor_utils()->translate_dynamic_text( $coupon_status, true ) );
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
					</tbody>
				</table>
				<?php else : ?>
					<?php tutils()->render_list_empty_state(); ?>
				<?php endif; ?>

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
