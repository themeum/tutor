<?php
/**
 * Withdraw List Template.
 *
 * @package Withdraw List
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use TUTOR\Withdraw_Requests_List;
$withdraw = new Withdraw_Requests_List();

/**
 * Short able params
 */
$order       = isset( $_GET['order'] ) ? $_GET['order'] : 'DESC';
$date        = isset( $_GET['date'] ) ? tutor_get_formated_date( 'Y-m-d', $_GET['date'] ) : '';
$search_term = isset( $_GET['search'] ) ? $_GET['search'] : '';

/**
 * Determine active tab
 */
$active_tab = isset( $_GET['data'] ) && $_GET['data'] !== '' ? esc_html__( $_GET['data'] ) : 'all';

/**
 * Pagination data
 */
$paged    = ( isset( $_GET['paged'] ) && is_numeric( $_GET['paged'] ) && $_GET['paged'] >= 1 ) ? $_GET['paged'] : 1;
$per_page = tutor_utils()->get_option( 'pagination_per_page' );
$start    = ( $per_page * $paged ) - $per_page;

$withdraw_list = tutor_utils()->get_withdrawals_history( null, compact( 'start', 'per_page', 'search_term' ) );
$total         = $withdraw_list->count;

/**
 * Navbar data to make nav menu
 */
$navbar_data = array(
	'page_title' => $withdraw->page_title,
	'tabs'       => $withdraw->tabs_key_value( $date, $search_term ),
	'active'     => $active_tab,
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
	'bulk_action'   => false,
	'filters'       => true,
	'course_filter' => false,
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

	<div class="tutor-admin-page-content-wrapper tutor-mt-50 tutor-mr-20">
		<div class="tutor-ui-table-wrapper">
			<table class="tutor-ui-table tutor-ui-table-responsive">
				<thead class="tutor-text-sm tutor-text-400">
					<tr>
						<th>
							<div class="text-regular-small color-text-subsued">
								<?php esc_html_e( 'Request Date', 'tutor-pro' ); ?>
							</div>
						</th>
						<th>
							<div class="text-regular-small color-text-subsued">
								<?php esc_html_e( 'Request By', 'tutor-pro' ); ?>
							</div>
						</th>
						<th>
							<div class="text-regular-small color-text-subsued">
								<?php esc_html_e( 'Withdraw Method', 'tutor-pro' ); ?>
							</div>
						</th>
						<th>
							<div class="text-regular-small color-text-subsued">
								<?php esc_html_e( 'Withdraw Details', 'tutor-pro' ); ?>
							</div>
						</th>
						<th>
							<div class="text-regular-small color-text-subsued">
								<?php esc_html_e( 'Amount', 'tutor-pro' ); ?>
							</div>
						</th>
						<th>
							<div class="text-regular-small color-text-subsued">
								<?php esc_html_e( 'Status', 'tutor-pro' ); ?>
							</div>
						</th>
						<th>
							<div class="text-regular-small color-text-subsued">
								<?php esc_html_e( 'Update', 'tutor-pro' ); ?>
							</div>
						</th>
					</tr>
				</thead>
				<tbody class="tutor-text-500">
					<?php if ( is_array( $withdraw_list->results ) && count( $withdraw_list->results ) ) : ?>
						<?php foreach ( $withdraw_list->results as $list ) : ?>
							<?php
								$user_data = get_userdata( $list->user_id );
								$details   = unserialize( $list->method_data );
							?>
							<tr>
								<td>
									<?php esc_html_e( tutor_get_formated_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $list->created_at ) ); ?>
								</td>
								<td>
									<span>
										<?php echo esc_html( $list->user_name ); ?>
									</span>
									<span>
										<?php echo esc_html( $list->user_email ); ?>
									</span>
								</td>
								<td>
									<?php echo esc_html( $details['withdraw_method_name'] ); ?>
								</td>
								<td>
									<li>
										<?php esc_html_e( 'Name:', 'tutor' ); ?>
										<span>
											<?php
												$account_name = isset( $details['account_name']['value'] ) ? $details['account_name']['value'] : '';
												echo esc_html( $account_name );
											?>
										</span>
									</li>
									<li>
										<?php esc_html_e( 'Account No:', 'tutor' ); ?>
										<span>
											<?php
												$account_no = isset( $details['account_number']['value'] ) ? $details['account_number']['value'] : '';
												echo esc_html( $account_no );
											?>
										</span>
									</li>
									<li>
										<?php esc_html_e( 'Bank Name:', 'tutor' ); ?>
										<span>
											<?php
												$bank_name = isset( $details['bank_name']['value'] ) ? $details['bank_name']['value'] : '';
												echo esc_html( $bank_name );
											?>
										</span>										
									</li>
									<li>
										<?php esc_html_e( 'IBAN:', 'tutor' ); ?>
										<span>
											<?php
												$iban = isset( $details['iban']['value'] ) ? $details['iban']['value'] : '';
												echo esc_html( $iban );
											?>
										</span>										
									</li>
									<li>
										<?php esc_html_e( 'BIC/SWIFT:', 'tutor' ); ?>
										<span>
											<?php
												$swift = isset( $details['swift']['value'] ) ? $details['swift']['value'] : '';
												echo esc_html( $swift );
											?>
										</span>	
									</li>
								</td>
								<td>
									<?php echo wp_kses_post( tutor_utils()->tutor_price( $list->amount ) ); ?>
								</td>
								<td>
									<?php echo esc_html( $list->status ); ?>
								</td>
								<td>
									<?php
										$updated_at = $list->updated_at ? tutor_get_formated_date( get_option( 'date_format' ), $list->updated_at ) : '';
										echo esc_html( $updated_at );
									?>
								</td>
							</tr>
						<?php endforeach; ?>
					<?php else : ?>
						<tr>
							<td colspan="100%">
								<?php esc_html_e( 'No record found', 'tutor-pro' ); ?>
							</td>
						</tr>
					<?php endif; ?>
				</tbody>
			</table>
		</div>
	</div>
	<div class="tutor-admin-page-pagination-wrapper tutor-mt-50 tutor-mr-20">
		<?php
			/**
			 * Prepare pagination data & load template
			 */
			$pagination_data     = array(
				'total_items' => $total,
				'per_page'    => $per_page,
				'paged'       => $paged,
			);
			$pagination_template = tutor()->path . 'views/elements/pagination.php';
			tutor_load_template_from_custom_path( $pagination_template, $pagination_data );
			?>
	</div>
</div>
