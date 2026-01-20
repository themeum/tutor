<?php
/**
 * Order List Template.
 *
 * @package Tutor\Views
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Tutor\Ecommerce\Ecommerce;
use Tutor\Ecommerce\OrderController;
use Tutor\Helpers\DateTimeHelper;
use TUTOR\Input;
use Tutor\Models\OrderModel;

/**
 * Determine active tab
 */
$active_tab = Input::get( 'data', 'all' );

$paged_filter = Input::get( 'paged', 1, Input::TYPE_INT );
$limit        = (int) tutor_utils()->get_option( 'pagination_per_page', 10 );
$offset       = ( $limit * $paged_filter ) - $limit;

$order_controller = new OrderController( false );

$get_orders  = $order_controller->get_orders( $limit, $offset );
$orders      = $get_orders['results'];
$total_items = $get_orders['total_count'];

$add_course_url = esc_url( admin_url( 'admin.php?page=create-course' ) );
$navbar_data    = array(
	'page_title'   => $order_controller->page_title,
	'tabs'         => $order_controller->tabs_key_value(),
	'active'       => $active_tab,
	'add_button'   => false,
	'button_title' => __( 'Add New', 'tutor' ),
	'button_url'   => $add_course_url,
);


$payment_status_options = array(
	array(
		'key'   => '',
		'title' => __( 'Select', 'tutor' ),
	),
);

$payment_status = array_map(
	function ( $val, $key ) {
		return array(
			'key'   => $key,
			'title' => $val,
		);
	},
	OrderModel::get_payment_status(),
	array_keys( OrderModel::get_payment_status() )
);

$payment_status_options = array_merge( $payment_status_options, $payment_status );

/**
 * Bulk action & filters
 */
$filters = array(
	'bulk_action'  => true,
	'bulk_actions' => $order_controller->prepare_bulk_actions(),
	'ajax_action'  => 'tutor_order_bulk_action',
	'filters'      => array(
		array(
			'label'      => __( 'Status', 'tutor' ),
			'field_type' => 'select',
			'field_name' => 'data',
			'options'    => $order_controller->tabs_key_value(),
			'searchable' => false,
			'value'      => Input::get( 'data', '' ),
		),
		array(
			'label'      => __( 'Payment Status', 'tutor' ),
			'field_type' => 'select',
			'field_name' => 'payment-status',
			'options'    => $payment_status_options,
			'show_label' => true,
			'value'      => Input::get( 'payment-status', '' ),
		),
		array(
			'label'      => __( 'Date', 'tutor' ),
			'field_type' => 'date',
			'field_name' => 'date',
			'show_label' => true,
			'value'      => Input::get( 'date', '' ),
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
	?>
	<div class="tutor-admin-container tutor-admin-container-lg">
		<div class="tutor-mt-24 tutor-dashboard-list-table">
			<div class="tutor-table-responsive">
				<?php if ( is_array( $orders ) && count( $orders ) ) : ?>
				<table class="tutor-table tutor-table-middle">
					<thead class="tutor-text-sm tutor-text-400">
						<tr>
							<th>
								<div class="tutor-d-flex">
									<input type="checkbox" id="tutor-bulk-checkbox-all" class="tutor-form-check-input" />
								</div>
							</th>
							<th class="tutor-table-rows-sorting">
								<?php esc_html_e( 'ID', 'tutor' ); ?>
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
								<?php esc_html_e( 'Method', 'tutor' ); ?>
							</th>
							<th>
								<?php esc_html_e( 'Payment Status', 'tutor' ); ?>
							</th>
							<th>
								<?php esc_html_e( 'Status', 'tutor' ); ?>
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
						
							<?php
							foreach ( $orders as $key => $order ) : //phpcs:ignore
								$user_data = get_userdata( $order->user_id );
								?>
								<tr>
									<td>
										<div class="td-checkbox tutor-d-flex ">
											<input type="checkbox" class="tutor-form-check-input tutor-bulk-checkbox" name="tutor-bulk-checkbox-all" value="<?php echo esc_attr( $order->id ); ?>" />
										</div>
									</td>

									<td>
										<a href="<?php echo esc_url( $order_controller->get_order_page_url() . '&action=edit&id=' . $order->id ); ?>" class="tutor-table-link tutor-fs-7">
											<?php echo esc_html( '#' . $order->id ); ?>
										</a>
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
											<?php echo esc_attr( DateTimeHelper::get_gmt_to_user_timezone_date( $order->created_at_gmt ) ); ?>
										</span>
									</td>

									<td>
										<div class="tutor-fs-7">
											<?php echo esc_html( Ecommerce::get_payment_method_label( $order->payment_method ?? '' ) ); ?>
											<?php if ( ! empty( $order->transaction_id ) ) : ?>
												<br>
												<span class="tutor-fw-normal tutor-fs-8 tutor-color-muted">
													<?php
													/* translators: %s is the transaction ID */
													echo esc_html( sprintf( __( 'Trx ID: %s', 'tutor' ), $order->transaction_id ) );
													?>
												</span>
											<?php endif; ?>
										</div>
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
										<div class="tutor-d-flex tutor-align-center tutor-gap-1">
											<a href="<?php echo esc_url( $order_controller->get_order_page_url() . '&action=edit&id=' . $order->id ); ?>" class="tutor-btn tutor-btn-outline-primary tutor-btn-sm">
												<?php esc_html_e( 'Edit', 'tutor' ); ?>
											</a>
											<?php do_action( 'tutor_after_order_edit_link', $order ); ?>
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
