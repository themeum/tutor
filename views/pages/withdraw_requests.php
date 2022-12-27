<?php
/**
 * Withdraw List Template.
 *
 * @package Tutor\Views
 * @subpackage Tutor\Withdraw
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use TUTOR\Input;
use Tutor\Models\WithdrawModel;
use TUTOR\Withdraw_Requests_List;
$withdraw = new Withdraw_Requests_List();

/**
 * Short able params
 */
$order       = Input::get( 'order', 'DESC' );
$date        = Input::has( 'date' ) ? tutor_get_formated_date( 'Y-m-d', Input::get( 'date' ) ) : '';
$search_term = Input::get( 'search', '' );

/**
 * Determine active tab
 */
$active_tab = Input::get( 'data', 'all' );

/**
 * Pagination data
 */
$paged    = Input::get( 'paged', 1, Input::TYPE_INT );
$per_page = tutor_utils()->get_option( 'pagination_per_page' );
$offset   = ( $per_page * $paged ) - $per_page;

$args = array(
	'status' => 'all' === $active_tab ? '' : $active_tab,
	'date'   => $date,
	'order'  => $order,
	'search' => $search_term,
);

$withdraw_list = WithdrawModel::get_withdrawals_history( null, $args, $offset, $per_page );
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
$filters = array(
	'bulk_action'   => false,
	'filters'       => true,
	'course_filter' => false,
);

?>

<div class="tutor-admin-wrap">
	<?php
		$navbar_template  = tutor()->path . 'views/elements/navbar.php';
		$filters_template = tutor()->path . 'views/elements/filters.php';
		tutor_load_template_from_custom_path( $navbar_template, $navbar_data );
		tutor_load_template_from_custom_path( $filters_template, $filters );
	?>

	<div class="tutor-admin-body">
		<div class="tutor-admin-page-content-wrapper tutor-withdraw-wrapper tutor-mt-24">
			<div class="tutor-table-responsive">
				<table class="tutor-table tutor-table-middle tutor-withdraw-request">
					<thead>
						<tr>
							<th>
								<?php esc_html_e( 'Request Date', 'tutor' ); ?>
							</th>
							<th class="tutor-table-rows-sorting">
								<?php esc_html_e( 'Request By', 'tutor' ); ?>
								<span class="tutor-icon-ordering-a-z  a-to-z-sort-icon tutor-color-secondary"></span>
							</th>
							<th>
								<?php esc_html_e( 'Withdraw Method', 'tutor' ); ?>
							</th>
							<th>
								<?php esc_html_e( 'Withdraw Details', 'tutor' ); ?>
							</th>
							<th>
								<?php esc_html_e( 'Amount', 'tutor' ); ?>
							</th>
							<th>
								<?php esc_html_e( 'Status', 'tutor' ); ?>
							</th>
							<th>
								<?php esc_html_e( 'Update', 'tutor' ); ?>
							</th>
						</tr>
					</thead>

					<tbody>
						<?php if ( is_array( $withdraw_list->results ) && count( $withdraw_list->results ) ) : ?>
							<?php foreach ( $withdraw_list->results as $list ) : ?>
								<?php
									$user_data = get_userdata( $list->user_id );
									$details   = unserialize( $list->method_data );
									$alert     = ( 'pending' == $list->status ? 'warning' : ( 'rejected' === $list->status ? 'danger' : ( 'approved' === $list->status ? 'success' : 'default' ) ) );
									$data_name = isset( $details['account_name']['value'] ) ? $details['account_name']['value'] : $user_data->display_name;
								if ( ! $details ) {
									continue;
								}
								?>
							<tr>
								<td>
									<div class="tutor-fs-7"><?php echo esc_html( tutor_get_formated_date( get_option( 'date_format' ), $list->created_at ) ); ?>,</div>
									<div class="tutor-fs-7 tutor-color-muted tutor-mt-4"><?php echo esc_html( tutor_get_formated_date( get_option( 'time_format' ), $list->created_at ) ); ?></div>
								</td>

								<td>
									<div class="tutor-d-flex tutor-gap-2">
										<?php
										echo wp_kses(
											tutor_utils()->get_tutor_avatar( $user_data->ID ),
											tutor_utils()->allowed_avatar_tags()
										);
										?>

										<div>
											<div class="tutor-d-flex tutor-align-center tutor-gap-1">
												<span class="tutor-color-black tutor-fs-6 tutor-fw-medium">
													<?php echo esc_html( $user_data->display_name ); ?>
												</span>
												<a href="<?php echo esc_url( tutor_utils()->profile_url( $user_data->ID, true ) ); ?>" class="tutor-iconic-btn" target="_blank">
													<span class="tutor-icon-external-link"></span>
												</a>
											</div>

											<span class="tutor-fs-7 tutor-color-muted">
												<?php echo esc_html( $user_data->user_email ); ?>
											</span>
										</div>
									</div>
								</td>

								<td>
									<div class="tutor-fs-7">
										<?php echo esc_html( $details['withdraw_method_name'] ); ?>
									</div>
								</td>

								<td>
									<?php if ( 'bank_transfer_withdraw' === $details['withdraw_method_key'] ) : ?>
										<ul class="tutor-table-inside-table">
											<li>
												<span class="tutor-fs-7 tutor-color-muted">
													<?php esc_html_e( 'Name:', 'tutor' ); ?>
												</span>
												<span class="tutor-fs-7 tutor-fw-medium tutor-color-black">
													<?php echo esc_html( $details['account_name']['value'] ); ?>
												</span>
											</li>
											<li>
												<span class="tutor-fs-7 tutor-color-muted">
													<?php esc_html_e( 'A/C Number:', 'tutor' ); ?>
												</span>
												<div class="tooltip-wrap">
													<span class="tutor-fs-7 tutor-fw-medium tutor-color-black">
														<?php echo esc_html( tutor_utils()->asterisks_center_text( $details['account_number']['value'] ) ); ?>
													</span>
													<div class="tutor-tooltip-wrap-area tutor-fs-7 tooltip-txt tooltip-top tutor-d-flex">
														<div class="withdraw-tutor-tooltip-content tutor-fs-7 tutor-d-flex tutor-align-center tutor-mr-12">
															<?php echo esc_html( $details['account_number']['value'] ); ?>
														</div>
														<div data-text-copy="<?php echo esc_attr( $details['account_number']['value'] ); ?>" class="withdraw-tutor-copy-to-clipboard tutor-fs-7 tutor-d-flex tutor-align-center">
															<span class="icon tutor-icon-copy"></span>
															<?php esc_html_e( 'Copy', 'tutor' ); ?>
														</div>
													</div>
												</div>
											</li>
											<li>
												<span class="tutor-fs-7 tutor-color-muted">
													<?php esc_html_e( 'Bank Name:', 'tutor' ); ?>
												</span>
												<span class="tutor-fs-7 tutor-fw-medium tutor-color-black">
													<?php echo esc_html( $details['bank_name']['value'] ); ?>
												</span>
											</li>
											<li>
												<span class="tutor-fs-7 tutor-color-muted">
													<?php esc_html_e( 'IBAN:', 'tutor' ); ?>
												</span>
												<div class="tooltip-wrap">
													<span class="tutor-fs-7 tutor-fw-medium tutor-color-black dotedtext">
														<?php echo esc_html( tutor_utils()->asterisks_center_text( $details['iban']['value'] ) ); ?>
													</span>
													<div class="tutor-tooltip-wrap-area tutor-fs-7 tooltip-txt tooltip-top  tutor-d-flex">
														<div class="withdraw-tutor-tooltip-content tutor-fs-7 tutor-d-flex tutor-align-center tutor-mr-12">
															<?php echo esc_html( $details['iban']['value'] ); ?>
														</div>
														<div data-text-copy="<?php echo esc_attr( $details['iban']['value'] ); ?>" class="withdraw-tutor-copy-to-clipboard tutor-fs-7 tutor-d-flex tutor-align-center">
															<span class="icon tutor-icon-copy"></span>
															<?php esc_html_e( 'Copy', 'tutor' ); ?>
														</div>
													</div>
												</div>
											</li>
											<li>
												<span class="tutor-fs-7 tutor-color-muted">
													<?php esc_html_e( 'BIC/SWIFT:', 'tutor' ); ?>
												</span>
												<span class="tutor-fs-7 tutor-fw-medium tutor-color-black">
													<?php echo esc_html( $details['swift']['value'] ); ?>
												</span>
											</li>
										</ul>
									<?php elseif ( 'echeck_withdraw' === $details['withdraw_method_key'] ) : ?>
										<span>
											<?php echo esc_html( $details['physical_address']['value'] ); ?>
										</span>
									<?php elseif ( 'paypal_withdraw' === $details['withdraw_method_key'] ) : ?>
										<div class="tooltip-wrap">
											<span class="dotedtext">
												<?php echo esc_html( '' !== $details['paypal_email']['value'] ? tutor_utils()->asterisks_email( $details['paypal_email']['value'] ) : '' ); ?>
											</span>
											<div class="tutor-tooltip-wrap-area tutor-fs-7 tooltip-txt tooltip-top  tutor-d-flex me-auto">
												<div class="withdraw-tutor-tooltip-content tutor-fs-7 tutor-d-flex tutor-align-center tutor-mr-12">
													<?php echo esc_html( $details['paypal_email']['value'] ); ?>
												</div>
												<div data-text-copy="<?php echo esc_attr( $details['paypal_email']['value'] ); ?>" class="withdraw-tutor-copy-to-clipboard tutor-fs-7 tutor-d-flex tutor-align-center">
													<span class="icon tutor-icon-copy"></span>
													Copy
												</div>
											</div>
										</div>
									<?php endif; ?>
								</td>
								<td data-th="<?php esc_html_e( 'Amount', 'tutor' ); ?>">
									<div class="tutor-fs-7 tutor-fw-medium tutor-color-black">
										<?php echo tutor_utils()->tutor_price( $list->amount ); //phpcs:ignore ?>
									</div>
								</td>
								<td data-th="<?php esc_html_e( 'Status', 'tutor' ); ?>">
									<div>
										<span class="tutor-badge-label label-<?php echo esc_attr( $alert ); ?> tutor-m-4 tutor-justify-center">
											<?php echo esc_html( tutor_utils()->translate_dynamic_text( $list->status ) ); ?>
										</span>
									</div>
								</td>
								<td data-th="<?php esc_html_e( 'Update', 'tutor' ); ?>" class="tutor-withdraw-btns">
									<?php if ( 'pending' === $list->status ) : ?>
									<div class="tutor-d-flex tutor-gap-1">
										<button data-tutor-modal-target="tutor-admin-withdraw-approve" data-id="<?php echo esc_attr( $list->withdraw_id ); ?>" data-name="<?php echo esc_attr( $data_name ); ?>" data-amount="<?php echo esc_attr( $list->amount ); ?>" class="tutor-btn tutor-btn-outline-primary tutor-btn-sm tutor-admin-open-withdraw-approve-modal">
											<?php esc_html_e( 'Approve', 'tutor' ); ?>
										</button>
										<button data-tutor-modal-target="tutor-admin-withdraw-reject"  data-id="<?php echo esc_attr( $list->withdraw_id ); ?>" data-name="<?php echo esc_attr( $data_name ); ?>" data-amount="<?php echo esc_attr( $list->amount ); ?>"  class="tutor-btn tutor-btn-outline-primary tutor-btn-sm tutor-admin-open-withdraw-reject-modal">
											<?php esc_html_e( 'Reject', 'tutor' ); ?>
										</button>
									</div>
									<?php elseif ( 'approved' === $list->status ) : ?>
										<div class="tutor-fs-7 tutor-fw-medium tutor-color-muted">
											<?php echo esc_html( $list->updated_at ? tutor_get_formated_date( get_option( 'date_format' ), $list->updated_at ) : '' ); ?>,<br>
											<?php echo esc_html( $list->updated_at ? tutor_get_formated_date( get_option( 'time_format' ), $list->updated_at ) : '' ); ?>
										</div>
									<?php elseif ( 'rejected' === $list->status ) : ?>
										<div class="tutor-d-flex ">
											<div class="tutor-fs-7 tutor-fw-medium tutor-color-muted">
												<?php echo esc_html( $list->updated_at ? tutor_get_formated_date( get_option( 'date_format' ), $list->updated_at ) : '' ); ?>,<br>
												<?php echo esc_html( $list->updated_at ? tutor_get_formated_date( get_option( 'time_format' ), $list->updated_at ) : '' ); ?>
											</div>
											<div class="tooltip-wrap tutor-ml-lg-20 tutor-ml-0">
												<span class="tutor-fs-7 tutor-fw-medium tutor-color-black">
													<i class="tutor-icon-circle-info tutor-color-muted"></i>
												</span>
												<div class="tutor-tooltip-wrap-area tutor-fs-7 tooltip-txt tooltip-left">
													<div class="withdraw-tutor-tooltip-content tutor-fs-7 tutor-d-flex tutor-align-center">
														<span>
														<?php
														if ( is_array( $details ) && isset( $details['rejects'] ) ) {
															echo esc_html( '' !== $details['rejects']['reject_comment'] ? $details['rejects']['reject_comment'] : $details['rejects']['reject_type'] );
														}
														?>
														</span>
													</div>
												</div>
											</div>
										</div>
									<?php else : ?>
										<div class="tutor-fs-7 tutor-fw-medium tutor-color-muted">
											<?php echo esc_html( $list->updated_at ? tutor_get_formated_date( get_option( 'date_format' ), $list->updated_at ) : '' ); ?>
											<br>
											<?php echo esc_html( $list->updated_at ? tutor_get_formated_date( get_option( 'time_format' ), $list->updated_at ) : '' ); ?>
										</div>
									<?php endif; ?>
								</td>
							</tr>
							<?php endforeach; ?>
						<?php else : ?>
							<tr>
								<td colspan="100%" class="column-empty-state">
									<?php tutor_utils()->tutor_empty_state( __( 'No request found', 'tutor' ) ); ?>
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

			<div id="tutor-admin-withdraw-approve" class="tutor-modal">
				<div class="tutor-modal-overlay"></div>
				<div class="tutor-modal-window">
					<form id="tutor-admin-withdraw-approve-form" class="tutor-modal-content tutor-modal-content-white">
						<button class="tutor-iconic-btn tutor-modal-close-o" data-tutor-modal-close>
							<span class="tutor-icon-times" area-hidden="true"></span>
						</button>

						<div class="tutor-modal-body tutor-text-center">
							<div class="tutor-mt-48">
								<span class="tutor-d-inline-block">
									<svg width="100" height="93" viewBox="0 0 100 93" fill="none" xmlns="http://www.w3.org/2000/svg">
										<path d="M79.0555 32.8059L72.9167 29.5281H27.0833L20.9444 32.8059C19.6111 33.5281 18.75 34.9447 18.75 36.4725V70.0836C18.75 72.3892 20.6111 74.2503 22.9167 74.2503H77.0833C79.3889 74.2503 81.25 72.3892 81.25 70.0836V36.4725C81.25 34.9447 80.3889 33.5281 79.0555 32.8059Z" fill="#F3F3F1"/>
										<path d="M25 70.0836V36.4725C25 34.9447 25.8611 33.5281 27.1944 32.8059L33.3333 29.5281H27.0833L20.9444 32.8059C19.6111 33.5281 18.75 34.9447 18.75 36.4725V70.0836C18.75 72.3892 20.6111 74.2503 22.9167 74.2503H29.1667C26.8611 74.2503 25 72.3892 25 70.0836Z" fill="#D5DBE1"/>
										<path d="M34.0742 18.7509L65.9075 18.7713V42.0905L49.9909 50.3967L34.0742 42.0905V18.7509Z" stroke="white" stroke-width="14"/>
										<path d="M83.2461 8.03271L87.1745 4.10435L89.1386 6.06853L85.2103 9.9969L83.2461 8.03271Z" fill="#A4AFC1"/>
										<path d="M71.957 5.57666L73.9212 3.61247L77.8496 7.54085L75.8854 9.50503L71.957 5.57666Z" fill="#A4AFC1"/>
										<path d="M83.2461 16.8682L85.2103 14.904L89.1386 18.8323L87.1745 20.7965L83.2461 16.8682Z" fill="#A4AFC1"/>
										<path d="M13.5417 16.6112C11.2444 16.6112 9.375 14.7417 9.375 12.4445C9.375 10.1473 11.2444 8.27783 13.5417 8.27783C15.8389 8.27783 17.7083 10.1473 17.7083 12.4445C17.7083 14.7417 15.8389 16.6112 13.5417 16.6112ZM13.5417 11.0556C12.7778 11.0556 12.1528 11.6806 12.1528 12.4445C12.1528 13.2084 12.7778 13.8334 13.5417 13.8334C14.3056 13.8334 14.9306 13.2084 14.9306 12.4445C14.9306 11.6806 14.3056 11.0556 13.5417 11.0556Z" fill="#A4AFC1"/>
										<path d="M77.0846 76.3332H22.918C19.4707 76.3332 16.668 73.5276 16.668 70.0832V36.4721C16.668 34.1776 17.9263 32.0693 19.9541 30.9748L26.1041 27.6887L28.068 31.3665L21.9291 34.6443C21.268 34.9998 20.8346 35.7221 20.8346 36.4721V70.0832C20.8346 71.2304 21.7707 72.1665 22.918 72.1665H77.0846C78.2319 72.1665 79.168 71.2304 79.168 70.0832V36.4721C79.168 35.7193 78.7374 34.9998 78.068 34.6387L71.9374 31.3665L73.9013 27.6887L80.0402 30.9665C82.0763 32.0693 83.3346 34.1748 83.3346 36.4721V70.0832C83.3346 73.5276 80.5291 76.3332 77.0846 76.3332Z" fill="#212327"/>
										<path d="M50.0018 59.6551C48.7852 59.6551 47.5685 59.3662 46.4602 58.7912L17.7852 43.819L19.7157 40.1245L48.3824 55.0967C49.3963 55.6245 50.6157 55.6245 51.624 55.0939L80.2824 40.1245L82.2129 43.819L53.549 58.7912C52.4379 59.3662 51.2185 59.6551 50.0018 59.6551Z" fill="#212327"/>
										<path d="M75 46.3332H70.8333V17.3276C70.8333 15.4193 69.2806 13.8609 67.375 13.8554L32.6389 13.8332C30.725 13.8332 29.1667 15.3915 29.1667 17.3054V46.3332H25V17.3054C25 13.0915 28.425 9.6665 32.6389 9.6665L67.3833 9.68873C71.5861 9.70261 75 13.1304 75 17.3276V46.3332Z" fill="#212327"/>
										<path d="M69.303 66.5794H66.1206V64.3382H69.303C69.814 64.3382 70.2294 63.9229 70.2294 63.4119C70.2294 62.886 69.814 62.4706 69.303 62.4706H68.1675C66.4209 62.4706 65 61.0497 65 59.3031C65 57.5415 66.4209 56.1206 68.1675 56.1206H71.35V58.3618H68.1675C67.6565 58.3618 67.2412 58.7771 67.2412 59.2881C67.2412 59.8141 67.6565 60.2294 68.1675 60.2294H69.303C71.0497 60.2294 72.4706 61.6503 72.4706 63.3969C72.4706 65.1585 71.0497 66.5794 69.303 66.5794Z" fill="#212327"/>
										<path d="M67.6133 55H69.8544V57.2412H67.6133V55Z" fill="#212327"/>
										<path d="M67.6133 65.4587H69.8544V67.6999H67.6133V65.4587Z" fill="#212327"/>
										<circle cx="51" cy="31" r="12" fill="#1973AA"/>
										<path d="M51 45C43.2806 45 37 38.7194 37 31C37 23.2806 43.2806 17 51 17C58.7194 17 65 23.2806 65 31C65 38.7194 58.7194 45 51 45ZM51 20.2308C45.0618 20.2308 40.2308 25.0618 40.2308 31C40.2308 36.9382 45.0618 41.7692 51 41.7692C56.9382 41.7692 61.7692 36.9382 61.7692 31C61.7692 25.0618 56.9382 20.2308 51 20.2308Z" fill="#212327"/>
										<path d="M48.8438 36.9234C48.4151 36.9234 48.0038 36.7533 47.7022 36.4496L43.3945 32.1419L45.6798 29.8567L48.7662 32.9431L55.1675 25.6287L57.5992 27.7545L50.0607 36.3699C49.7656 36.7059 49.3456 36.9062 48.8976 36.9213C48.8804 36.9234 48.861 36.9234 48.8438 36.9234Z" fill="white"/>
									</svg>
								</span>
								<input type="hidden" name="action" value="<?php echo esc_html( 'tutor_admin_withdraw_action' ); ?>">
								<input type="hidden" name="action-type" value="<?php echo esc_html( 'approved' ); ?>">
							</div>

							<div class="tutor-fs-3 tutor-fw-medium tutor-color-black tutor-mb-12"><?php esc_html_e( 'Approve Withdrawal?', 'tutor' ); ?></div>
							<div id="tutor-admin-withdraw-approve-content" class="tutor-fs-6 tutor-color-muted"></div>
							
							<div class="tutor-d-flex tutor-justify-center tutor-my-48">
								<button data-tutor-modal-close class="tutor-btn tutor-btn-outline-primary">
									<?php esc_html_e( 'Cancel', 'tutor' ); ?>
								</button>
								<button type="submit" class="tutor-btn tutor-btn-primary tutor-ml-20" data-tutor-modal-submit>
									<?php esc_html_e( 'Yes, Approve Withdrawal', 'tutor' ); ?>
								</button>
							</div>
						</div>
					</form>
				</div>
			</div>

			<!-- withdraw reject modal-->
			<div id="tutor-admin-withdraw-reject" class="tutor-modal">
				<div class="tutor-modal-overlay"></div>
				<div class="tutor-modal-window">
					<form id="tutor-admin-withdraw-reject-form" class="tutor-modal-content tutor-modal-content-white">
						<button class="tutor-iconic-btn tutor-modal-close-o" data-tutor-modal-close>
							<span class="tutor-icon-times" area-hidden="true"></span>
						</button>

						<div class="tutor-modal-body tutor-text-center">
							<div class="tutor-mt-48">
								<span class="tutor-d-inline-block">
									<svg width="100" height="100" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
										<path d="M63.6328 89.2495L68.0532 85.8843L69.7341 88.0922L65.3137 91.4575L63.6328 89.2495Z" fill="#A4AFC1"/>
										<path d="M76.3477 79.5718L80.768 76.2066L82.4489 78.4145L78.0286 81.7797L76.3477 79.5718Z" fill="#A4AFC1"/>
										<path d="M75.0312 89.293L77.2414 87.6104L80.6067 92.0307L78.3965 93.7133L75.0312 89.293Z" fill="#A4AFC1"/>
										<path d="M21.526 22.9642C19.2288 22.9642 17.3594 21.0947 17.3594 18.7975C17.3594 16.5003 19.2288 14.6309 21.526 14.6309C23.8233 14.6309 25.6927 16.5003 25.6927 18.7975C25.6927 21.0947 23.8233 22.9642 21.526 22.9642ZM21.526 17.4086C20.7594 17.4086 20.1372 18.0336 20.1372 18.7975C20.1372 19.5614 20.7594 20.1864 21.526 20.1864C22.2927 20.1864 22.9149 19.5614 22.9149 18.7975C22.9149 18.0336 22.2927 17.4086 21.526 17.4086Z" fill="#A4AFC1"/>
										<path d="M80.75 50C80.75 66.9828 66.9828 80.75 50 80.75C33.0172 80.75 19.25 66.9828 19.25 50C19.25 33.0172 33.0172 19.25 50 19.25C66.9828 19.25 80.75 33.0172 80.75 50Z" stroke="#1973AA"/>
										<path d="M50.0013 83.3332C31.6207 83.3332 16.668 68.3804 16.668 49.9998C16.668 31.6193 31.6207 16.6665 50.0013 16.6665C55.2263 16.6665 60.2319 17.8443 64.8763 20.1637L63.0124 23.8915C58.9541 21.8609 54.5735 20.8332 50.0013 20.8332C33.918 20.8332 20.8346 33.9165 20.8346 49.9998C20.8346 66.0832 33.918 79.1665 50.0013 79.1665C66.0846 79.1665 79.168 66.0832 79.168 49.9998C79.168 41.3554 75.3652 33.2054 68.7319 27.6387L71.4096 24.4498C78.9902 30.8082 83.3346 40.1193 83.3346 49.9998C83.3346 68.3804 68.3819 83.3332 50.0013 83.3332Z" fill="#212327"/>
										<path d="M64.989 60.2113C65.7403 60.9626 65.7403 62.1956 64.989 62.9469L62.9469 64.989C62.1956 65.7403 60.9626 65.7403 60.2113 64.989L50.0008 54.7785L39.7904 64.989C39.0391 65.7403 37.8061 65.7403 37.0548 64.989L35.0127 62.9469C34.2614 62.1956 34.2614 60.9626 35.0127 60.2113L45.2231 50.0008L35.0127 39.7904C34.2614 39.0391 34.2614 37.8061 35.0127 37.0548L37.0548 35.0127C37.8061 34.2614 39.0391 34.2614 39.7904 35.0127L50.0008 45.2231L60.2113 35.0127C60.9626 34.2614 62.1956 34.2614 62.9469 35.0127L64.989 37.0548C65.7403 37.8061 65.7403 39.0391 64.989 39.7904L54.7785 50.0008L64.989 60.2113Z" fill="#1973AA"/>
										<path d="M39.8297 62.9464C39.0783 62.1951 39.0783 60.964 39.8297 60.2108L47.8632 52.1773C49.0653 50.9751 49.0653 49.0255 47.8632 47.8234L39.8297 39.7899C39.0783 39.0386 39.0764 37.8076 39.8297 37.0543L40.8315 36.0525L39.7912 35.0122C39.0398 34.259 37.8088 34.259 37.0555 35.0122L35.0134 37.0543C34.2621 37.8056 34.2621 39.0367 35.0134 39.7899L45.2239 50.0003L35.0134 60.2108C34.2621 60.9621 34.2602 62.1931 35.0134 62.9464L37.0555 64.9885C37.8069 65.7398 39.0379 65.7398 39.7912 64.9885L40.8315 63.9482L39.8297 62.9464Z" fill="#1973AA"/>
										<path d="M61.5771 66.9969C60.7141 66.9969 59.8491 66.6675 59.1902 66.0105L49.9989 56.8211L40.8095 66.0105C39.488 67.3282 37.3476 67.3282 36.0318 66.0105L33.9878 63.9665C32.672 62.6507 32.6701 60.5065 33.9859 59.1907L43.1772 49.9994L33.9878 40.81C32.6701 39.4904 32.672 37.3462 33.9878 36.0323L36.0318 33.9883C37.3495 32.6725 39.4918 32.6706 40.8076 33.9864L49.9989 43.1777L59.1883 33.9883C60.5099 32.6706 62.6502 32.6706 63.966 33.9883L66.01 36.0323C67.3258 37.3481 67.3277 39.4923 66.0119 40.8081L56.8206 49.9994L66.01 59.1888C66.01 59.1888 66.0119 59.1888 66.0119 59.1907C67.3277 60.5084 67.3258 62.6526 66.01 63.9665L63.966 66.0105C63.3071 66.6675 62.4421 66.9969 61.5771 66.9969ZM49.9989 53.3322C50.3688 53.3322 50.7387 53.4729 51.0199 53.7561L61.2304 63.9665C61.4192 64.1553 61.7351 64.1553 61.922 63.9665L63.966 61.9225C64.1548 61.7337 64.1529 61.4178 63.9641 61.229L53.7556 51.0204C53.1911 50.456 53.1911 49.5409 53.7556 48.9764L63.966 38.766C64.1529 38.5772 64.1548 38.2613 63.966 38.0744L61.922 36.0304C61.7332 35.8416 61.4192 35.8435 61.2284 36.0323L51.0199 46.2427C50.4555 46.8072 49.5404 46.8072 48.9759 46.2427L38.7655 36.0323C38.5767 35.8435 38.2608 35.8435 38.0739 36.0323L36.0299 38.0763C35.8411 38.2651 35.843 38.5811 36.0318 38.7699L46.2403 48.9784C46.8048 49.5428 46.8048 50.4579 46.2403 51.0224L36.0299 61.2328C35.843 61.4216 35.8411 61.7376 36.0299 61.9244L38.0739 63.9684C38.2627 64.1572 38.5767 64.1553 38.7674 63.9665L48.9759 53.758C49.2591 53.4748 49.629 53.3322 49.9989 53.3322Z" fill="black"/>
									</svg>
								</span>
								<input type="hidden" name="action" value="<?php echo esc_html( 'tutor_admin_withdraw_action' ); ?>">
								<input type="hidden" name="action-type" value="<?php echo esc_html( 'rejected' ); ?>">
							</div>

							<div class="tutor-fs-3 tutor-fw-medium tutor-color-black tutor-mb-12"><?php esc_html_e( 'Reject Withdrawal?', 'tutor' ); ?></div>
							<div id="tutor-admin-withdraw-reject-content" class="tutor-fs-6 tutor-color-muted tutor-mb-16"></div>

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

							<div id="tutor-withdraw-reject-other" class="tutor-mt-16"></div>
							
							<div class="tutor-d-flex tutor-justify-center tutor-my-48">
								<button data-tutor-modal-close class="tutor-btn tutor-btn-outline-primary">
									<?php esc_html_e( 'Cancel', 'tutor' ); ?>
								</button>
								<button type="submit" class="tutor-btn tutor-btn-primary tutor-ml-20" data-tutor-modal-submit>
									<?php esc_html_e( 'Yes, Reject Withdrawal', 'tutor' ); ?>
								</button>
							</div>
						</div>
					</form>
				</div>
			</div>
			<!-- withdraw approve modal end-->
		</div>
	</div>
</div>

<script>
	let urlRaw = window.location;
	var url = new URL(urlRaw);
	var action = url.searchParams.get("action");
	var withdraw_id = url.searchParams.get("withdraw_id");

	document.addEventListener('readystatechange', (event) => {
		if (event.target.readyState === 'complete') {
			const approveTarget = document.querySelectorAll(`[data-tutor-modal-target="tutor-admin-withdraw-${action}"]`);
			approveTarget.forEach((item)=>{
			if(item.dataset.id == withdraw_id){
				item.click();
			}
		})
		}
	});
</script>
