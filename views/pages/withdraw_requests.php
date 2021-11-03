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
$offset   = ( $per_page * $paged ) - $per_page;

$args          = array(
	'status'   => 'all' === $active_tab ? '' : $active_tab,
	'date'     => $date,
	'order'    => $order,
	'start'    => $offset,
	'per_page' => $per_page,
	'search'   => $search_term,
);
$withdraw_list = tutor_utils()->get_withdrawals_history( null, $args );
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
$available_status = array(
	'pending'  => __( 'pending', 'tutor' ),
	'approved' => __( 'approved', 'tutor' ),
	'rejected' => __( 'rejected', 'tutor' ),
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
								Request Date							
							</div>
						</th>
						<th>
							<div class="text-regular-small color-text-subsued">
								Request By							
							</div>
						</th>
						<th>
							<div class="text-regular-small color-text-subsued">
								Withdraw Method							
							</div>
						</th>
						<th>
							<div class="text-regular-small color-text-subsued">
								Withdraw Details							
							</div>
						</th>
						<th>
							<div class="text-regular-small color-text-subsued">
								Amount							
							</div>
						</th>
						<th>
							<div class="text-regular-small color-text-subsued">
								Status							
							</div>
						</th>
						<th>
							<div class="text-regular-small color-text-subsued">
								Update							
							</div>
						</th>
					</tr>
				</thead>
				<tbody class="tutor-text-500">
					<tr>
						<td>
							October 31, 2021 11:24 am								
						</td>
						<td>
							<span>
								tutor2									
							</span>
							<span>
								tutor2@gmail.com									
							</span>
						</td>
						<td>
							Bank Transfer								
						</td>
						<td>
							<li>
								Name:										<span>
									Shewa										</span>
							</li>
							<li>
								Account No:										<span>
									798709709809										</span>
							</li>
							<li>
								Bank Name:										<span>
									DBBL										</span>										
							</li>
							<li>
								IBAN:										<span>
									79878097										</span>										
							</li>
							<li>
								BIC/SWIFT:										<span>
									7987										</span>	
							</li>
						</td>
						<td>
							<span class="woocommerce-Price-amount amount">5.00<span class="woocommerce-Price-currencySymbol">৳&nbsp;</span></span>								
						</td>
						<td>
							approved
						</td>
						<td>
							October 31, 2021,11:25 am
						</td>
					</tr>
					<tr>
						<td>
							<div class="text-medium-caption color-text-primary"> October 31, 2021 11:23 am </div>
						</td>
						<td>
							<div class="text-medium-caption color-text-primary"> 
								tutor2 tutor2@gmail.com
							</div>
						</td>
						<td>
							<div class="text-medium-caption color-text-primary"> 
								Bank Transfer
							</div>
						</td>
						<td>
							<ul class="tutor-table-inside-table">
								<li>
									<span class="text-regular-small color-text-hints">Name:</span>
									<span class="text-medium-small color-text-primary">Ricky Ponting</span>
								</li>
								<li>
									<span class="text-regular-small color-text-hints">A/C Number:</span>
									<span class="text-medium-small color-text-primary">002465********45</span>
								</li>
								<li>        
									<span class="text-regular-small color-text-hints">Bank Name:</span>
									<span class="text-medium-small color-text-primary">One Bank Limited</span>										
								</li>
								<li>                    
									<span class="text-regular-small color-text-hints">IBAN:</span>
									<span class="text-medium-small color-text-primary">IBAN000********65</span>										
								</li>
								<li>        
									<span class="text-regular-small color-text-hints">BIC/SWIFT:</span>
									<span class="text-medium-small color-text-primary">INHA66A</span>	
								</li>
							</ul>
						</td>
						<td>
							<div class="text-medium-caption color-text-primary"> 
								$60.00
							</div>
						</td>
						<td>
							<div class="d-flex flex-column">
								<span class="tutor-badge-label label-success tutor-m-5">Success</span>
								<span class="tutor-badge-label label-primary-wp tutor-m-5">WordPress</span>
								<span class="tutor-badge-label label-success tutor-m-5">Success</span>
								<span class="tutor-badge-label label-warning tutor-m-5">Warning</span>
								<span class="tutor-badge-label label-danger tutor-m-5">Danger</span>
								<span class="tutor-badge-label label-processing tutor-m-5">Processing</span>
								<span class="tutor-badge-label label-onhold tutor-m-5">On hold</span>
								<span class="tutor-badge-label label-refund tutor-m-5">Refunded</span>
								<span class="tutor-badge-label tutor-m-5">Default</span>
							</div>
						</td>
						<td>
							<div class="d-flex justify-content-center align-items-center">
								<button class="tutor-btn tutor-btn-wordpress-outline tutor-btn-sm tutor-mr-20">
									Approve
								</button>
								<button class="tutor-btn tutor-btn-disable-outline tutor-no-hover tutor-btn-sm">
									Reject
								</button>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							October 31, 2021 10:30 am								
						</td>
						<td>
							<span>
								tutor2									
							</span>
							<span>
								tutor2@gmail.com									
							</span>
						</td>
						<td>
							Bank Transfer								
						</td>
						<td>
							<li>
								Name:										<span>
									Shewa										</span>
							</li>
							<li>
								Account No:										<span>
									798709709809										</span>
							</li>
							<li>
								Bank Name:										<span>
									DBBL										</span>										
							</li>
							<li>
								IBAN:										<span>
									79878097										</span>										
							</li>
							<li>
								BIC/SWIFT:										<span>
									7987										</span>	
							</li>
						</td>
						<td>
							<span class="woocommerce-Price-amount amount">2.00<span class="woocommerce-Price-currencySymbol">৳&nbsp;</span></span>								</td>
						<td>
							pending								
						</td>
						<td>
							
							<button class="tutor-btn tutor-btn-wordpress-outline tutor-no-hover tutor-btn-sm tutor-admin-open-withdraw-approve-modal" data-tutor-modal-target="tutor-admin-withdraw-approve" data-amount="<span class=&quot;woocommerce-Price-amount amount&quot;><bdi>2.00<span class=&quot;woocommerce-Price-currencySymbol&quot;>৳&nbsp;</span></bdi></span>" data-name="Shewa" data-id="2">
									Approve										
							</button>
							<button data-tutor-modal-target="tutor-admin-withdraw-reject" class="tutor-btn tutor-btn-wordpress-outline tutor-no-hover tutor-btn-sm tutor-admin-open-withdraw-reject-modal" data-amount="<span class=&quot;woocommerce-Price-amount amount&quot;><bdi>2.00<span class=&quot;woocommerce-Price-currencySymbol&quot;>৳&nbsp;</span></bdi></span>" data-name="Shewa" data-id="2">
									Reject										
							</button>
						</td>
					</tr>
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

	<!-- withdraw approve modal-->
	<div id="tutor-admin-withdraw-approve" class="tutor-modal">
		<span class="tutor-modal-overlay"></span>
		<button data-tutor-modal-close class="tutor-modal-close">
			<span class="las la-times"></span>
		</button>
		<div class="tutor-modal-root">
			<div class="tutor-modal-inner">
			<div class="tutor-modal-body tutor-text-center">
				<form action="" id="tutor-admin-withdraw-approve-form">
					<input type="hidden" name="action" value="<?php echo esc_html( 'tutor_admin_withdraw_action' ); ?>">
					<input type="hidden" name="action-type" value="<?php echo esc_html( 'approved' ); ?>">
					<div class="tutor-modal-icon">
					<img src="https://i.imgur.com/Nx6U2u7.png" alt="" />
					</div>
					<div class="tutor-modal-text-wrap">
					<h3 class="tutor-modal-title">
						<?php esc_html_e( 'Approve Withdrawal?', 'tutor' ); ?>
					</h3>
					<p id="tutor-admin-withdraw-approve-content">

					</p>
					</div>
					<div class="tutor-modal-btns tutor-btn-group">
					<button
						data-tutor-modal-close
						class="tutor-btn tutor-is-outline tutor-is-default"
					>
						<?php esc_html_e( 'Cancel', 'tutor' ); ?>
					</button>
					<button type="submit" class="tutor-btn">
						<?php esc_html_e( 'Yes, Approve Withdrawal', 'tutor' ); ?>
					</button>
					</div>
				</form>
			</div>
			</div>
		</div>
	</div>
	<!-- withdraw approve modal end-->

	<!-- withdraw reject modal-->
	<div id="tutor-admin-withdraw-reject" class="tutor-modal">
		<span class="tutor-modal-overlay"></span>
		<button data-tutor-modal-close class="tutor-modal-close">
			<span class="las la-times"></span>
		</button>
		<div class="tutor-modal-root">
			<div class="tutor-modal-inner">
			<div class="tutor-modal-body tutor-text-center">
				<form action="" id="tutor-admin-withdraw-reject-form">
					<input type="hidden" name="action" value="<?php echo esc_html( 'tutor_admin_withdraw_action' ); ?>">
					<input type="hidden" name="action-type" value="<?php echo esc_html( 'rejected' ); ?>">
					<div class="tutor-modal-icon">
					<img src="https://i.imgur.com/Nx6U2u7.png" alt="" />
					</div>
					<div class="tutor-modal-text-wrap">
						<h3 class="tutor-modal-title">
							<?php esc_html_e( 'Reject Withdrawal?', 'tutor' ); ?>
						</h3>
						<p id="tutor-admin-withdraw-reject-content">

						</p>
						<div class="tutor-mb-15">
							<select class="tutor-form-select" name="reject-type" id="tutor-admin-withdraw-reject-type">
								<option value="<?php esc_attr_e( 'Invalid Payment Details', 'tutor' ); ?>">
									<?php esc_html_e( 'Invalid Payment Details', 'tutor' ); ?>
								</option>
								<option value="<?php esc_attr_e( 'Invalid Request', 'tutor' ); ?>">
									<?php esc_html_e( 'Invalid Request', 'tutor' ); ?>
								</option>
								<option value="<?php esc_attr_e( 'Other', 'tutor' ); ?>">
									<?php esc_html_e( 'Other', 'tutor' ); ?>
								</option>
							</select>
						</div>
						<div class="tutor-md-15" id="tutor-withdraw-reject-other" style="width: 96%;">

						</div>
					</div>
					<div class="tutor-modal-btns tutor-btn-group">
					<button
						data-tutor-modal-close
						class="tutor-btn tutor-is-outline tutor-is-default"
					>
						<?php esc_html_e( 'Cancel', 'tutor' ); ?>
					</button>
					<button type="submit" class="tutor-btn">
						<?php esc_html_e( 'Yes, Reject Withdrawal', 'tutor' ); ?>
					</button>
					</div>
				</form>
			</div>
			</div>
		</div>
	</div>
	<!-- withdraw approve modal end-->
</div>
