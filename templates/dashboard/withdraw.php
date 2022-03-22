<?php

/**
 * @package TutorLMS/Templates
 * @version 1.4.3
 */

$per_page     = tutor_utils()->get_option('statement_show_per_page', 20);
$current_page = max( 1, tutor_utils()->avalue_dot( 'current_page', tutor_sanitize_data($_GET) ) );
$offset       = ( $current_page - 1 ) * $per_page;

$earning_sum                   = tutor_utils()->get_earning_sum();
$min_withdraw                  = tutor_utils()->get_option( 'min_withdraw_amount' );
$formatted_min_withdraw_amount = tutor_utils()->tutor_price( $min_withdraw );

$saved_account        = tutor_utils()->get_user_withdraw_method();
$withdraw_method_name = tutor_utils()->avalue_dot( 'withdraw_method_name', $saved_account );

$user_id               = get_current_user_id();
$balance_formatted     = tutor_utils()->tutor_price( $earning_sum->balance );
$is_balance_sufficient = true; // $earning_sum->balance >= $min_withdraw;
$all_histories         = tutor_utils()->get_withdrawals_history( $user_id, array( 'status' => array( 'pending', 'approved', 'rejected' ) ), $offset, $per_page );
$image_base   = tutor()->url . '/assets/images/';
$method_icons = array(
	'bank_transfer_withdraw' => $image_base . 'icon-bank.svg',
	'echeck_withdraw'        => $image_base . 'icon-echeck.svg',
	'paypal_withdraw'        => $image_base . 'icon-paypal.svg',
);

$status_message = array(
	'rejected' => __( 'Please contact the site administrator for more information.', 'tutor' ),
	'pending'  => __( 'Withdrawal request is pending for approval, please hold tight.', 'tutor' ),
);

$currency_symbol = '';
if ( function_exists( 'get_woocommerce_currency_symbol' ) ) {
	$currency_symbol = get_woocommerce_currency_symbol();
} elseif ( function_exists( 'edd_currency_symbol' ) ) {
	$currency_symbol = edd_currency_symbol();
}

?>

<div class="tutor-dashboard-content-inner tutor-frontend-dashboard-withdrawal tutor-color-black">
	<div class="tutor-fs-5 tutor-fw-medium tutor-color-black tutor-mb-24"><?php echo __( 'Withdrawal', 'tutor' ); ?></div>

	<div class="tutor-component-three-col-action">
		<img src="<?php echo esc_url( $image_base ); ?>wallet.svg" />

		<div class="tutor-mt-12 tutor-mt-sm-0">
			<small><?php esc_html_e( 'Current Balance', 'tutor' ); ?></small>
			<p>
				<?php
				if ( $is_balance_sufficient ) {
					echo sprintf( __( 'You currently have %1$s %2$s %3$s ready to withdraw', 'tutor' ), "<strong class='available_balance'>", $balance_formatted, '</strong>' );
				} else {
					echo sprintf( __( 'You currently have %1$s %2$s %3$s and this is insufficient balance to withdraw', 'tutor' ), "<strong class='available_balance'>", $balance_formatted, '</strong>' );
				}
				?>
			</p>
		</div>

		<?php
		if ( $is_balance_sufficient && $withdraw_method_name ) {
			?>
				<button class="tutor-btn open-withdraw-form-btn tutor-mt-12 tutor-mt-sm-0">
				<?php esc_html_e( 'Withdrawal Request', 'tutor' ); ?>
				</button>
				<?php
		}
		?>
	</div>

	<div class="current-withdraw-account-wrap withdrawal-preference inline-image-text tutor-mt-20">
		<!-- <img src="<?php echo esc_url( $image_base ); ?>info-icon-question.svg" /> -->
		<svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
			<path fill-rule="evenodd" clip-rule="evenodd" d="M0.5 9.00005C0.5 4.31315 4.3131 0.5 9 0.5C13.6869 0.5 17.5 4.31315 17.5 9.00005C17.5 13.687 13.6869 17.5 9 17.5C4.3131 17.5 0.5 13.687 0.5 9.00005ZM2.04498 9.00036C2.04498 12.8351 5.16474 15.9549 8.99953 15.9549C12.8343 15.9549 15.9541 12.8351 15.9541 9.00036C15.9541 5.16562 12.8343 2.04576 8.99953 2.04576C5.16474 2.04576 2.04498 5.16562 2.04498 9.00036ZM8.99976 4.10617C8.43176 4.10617 7.96967 4.56857 7.96967 5.13694C7.96967 5.70479 8.43176 6.16678 8.99976 6.16678C9.56777 6.16678 10.0299 5.70479 10.0299 5.13694C10.0299 4.56857 9.56777 4.10617 8.99976 4.10617ZM8.22699 8.48481C8.22699 8.05806 8.57297 7.71208 8.99972 7.71208C9.42647 7.71208 9.77244 8.05806 9.77244 8.48481V13.1212C9.77244 13.5479 9.42647 13.8939 8.99972 13.8939C8.57297 13.8939 8.22699 13.5479 8.22699 13.1212V8.48481Z" fill="#212327"/>
		</svg>
		<span class="tutor-fs-7 tutor-color-muted">
			<?php
			$my_profile_url = tutor_utils()->get_tutor_dashboard_page_permalink( 'settings/withdraw-settings' );
			echo $withdraw_method_name ? sprintf( __( 'The preferred payment method is selected as %s. ', 'tutor' ), $withdraw_method_name ) : '';
			echo sprintf( __( 'You can change your %1$s Withdraw Preference %2$s', 'tutor' ), "<a href='{$my_profile_url}'>", '</a>' );
			?>
		</span>
	</div>

	<?php
	if ( $is_balance_sufficient && $withdraw_method_name ) {
		?>

		<div class="tutor-earning-withdraw-form-wrap">
			<div>
				<div class="tutor-withdrawal-pop-up-success">
					<div>
						<i class="tutor-icon-line-cross-line close-withdraw-form-btn tutor-color-black-40" data-reload="yes"></i>
						<br />
						<br />
						<div style="text-align:center">
							<img src="<?php echo $image_base; ?>icon-cheers.svg" />
							<div class="tutor-fs-4"><?php esc_html_e( 'Your withdrawal request has been successfully accepted', 'tutor' ); ?></div>
							<span class="tutor-fs-6 tutor-color-black-60"><?php esc_html_e( 'Please check your transaction notification on your connected withdrawal method', 'tutor' ); ?></span>
						</div>
						<br />
						<br />
						<div class="tutor-withdraw-form-response"></div>
					</div>
				</div>
				<div class="tutor-withdrawal-op-up-frorm ">
					<i class="tutor-icon-line-cross-line close-withdraw-form-btn tutor-color-black-40"></i>
					<div>
						<img src="<?php echo $image_base; ?>wallet.svg" />
						<div class="tutor-fs-5 tutor-fw-medium tutor-color-black tutor-mt-20">
							<?php esc_html_e( 'Withdrawal Request', 'tutor' ); ?>
						</div>
						<p class="tutor-mb-40"><?php esc_html_e( 'Please enter withdrawal amount and click the submit request button', 'tutor' ); ?></p>
						<table>
							<tbody>
								<tr>
									<td>
										<span><?php esc_html_e( 'Current Balance', 'tutor' ); ?></span><br />
										<b><?php echo wp_kses_post( $balance_formatted ); ?></b>
									</td>
									<td>
										<span><?php esc_html_e( 'Selected Payment Method', 'tutor' ); ?></span><br />
										<b><?php esc_html_e( $withdraw_method_name ); ?></b>
									</td>
								</tr>
							</tbody>
						</table>
					</div>
					<div>
					<?php
						/**
						 * @since 1.8.1
						 * set min value for withdraw input field as per settings
						 * field req step .01
						 */

					?>
						<form id="tutor-earning-withdraw-form" action="" method="post">
							<?php wp_nonce_field( tutor()->nonce_action, tutor()->nonce ); ?>
							<input type="hidden" value="tutor_make_an_withdraw" name="action" />
							<?php do_action( 'tutor_withdraw_form_before' ); ?>
							<div class="withdraw-form-field-row">
								<label for="tutor_withdraw_amount"><?php esc_html_e( 'Amount', 'tutor' ); ?></label>
								<div class="withdraw-form-field-amount">
									<span>
										<span><?php echo esc_attr( $currency_symbol ); ?></span>
									</span>
									<input type="number" min="<?php echo esc_attr( $min_withdraw ); ?>" name="tutor_withdraw_amount" id="tutor_withdraw_amount" step=".01" required>
								</div>
								<div class="inline-image-text">
									<img src="<?php echo $image_base; ?>info-icon-question.svg" />
									<span>
										<?php echo __( 'Minimum withdraw amount is', 'tutor' ) . ' ' . strip_tags( $formatted_min_withdraw_amount ); ?>
									</span>
								</div>
							</div>

							<div class="tutor-withdraw-button-container">
								<button class="tutor-btn tutor-btn-secondary close-withdraw-form-btn"><?php esc_html_e( 'Cancel', 'tutor' ); ?></button>
								<button class="tutor-btn" type="submit" id="tutor-earning-withdraw-btn" name="withdraw-form-submit"><?php esc_html_e( 'Submit Request', 'tutor' ); ?></button>
							</div>

							<div class="tutor-withdraw-form-response"></div>

							<?php do_action( 'tutor_withdraw_form_after' ); ?>
						</form>
					</div>
				</div>
			</div>
		</div>

		<?php
	}
	
	if ( is_array( $all_histories->results ) && count ( $all_histories->results ) ) {
		?>
		<div class="withdraw-history-table-wrap tutor-tooltip-inside tutor-mt-40">
			<div class="withdraw-history-table-title">
				<div class="tutor-fs-5 tutor-fw-medium tutor-color-black tutor-mb-24"> 
					<?php esc_html_e( 'Withdrawal History', 'tutor' ); ?>
				</div>
			</div>

			<table class="tutor-ui-table tutor-ui-table-responsive">
				<thead class="tutor-fs-7 tutor-color-black-60">
					<tr>
						<th width="40%">
							<div>
								<?php esc_html_e( 'Withdrawal Method', 'tutor' ); ?>
							</div>
						</th>
						<th width="28%">
							<div>
								<?php esc_html_e( 'Requested On', 'tutor' ); ?>
							</div>
						</th>
						<th width="13%">
							<div>
								<?php esc_html_e( 'Amount', 'tutor' ); ?>
							</div>
						</th>
						<th width="13%">
							<div>
								<?php esc_html_e( 'Status', 'tutor' ); ?>
							</div>
						</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach ( $all_histories->results as $withdraw_history ) {
						?>
						<tr>
							<td>
								<?php
								$method_data  = maybe_unserialize( $withdraw_history->method_data );
								$method_key   = $method_data['withdraw_method_key'];
								$method_title = '';

								switch ( $method_key ) {
									case 'bank_transfer_withdraw':
										$method_title = $method_data['account_number']['value'];
										$method_title = substr_replace( $method_title, '****', 2, strlen( $method_title ) - 4 );
										break;
									case 'paypal_withdraw':
										$method_title = $method_data['paypal_email']['value'];
										$email_base   = substr( $method_title, 0, strpos( $method_title, '@' ) );
										$method_title = substr_replace( $email_base, '****', 2, strlen( $email_base ) - 3 ) . substr( $method_title, strpos( $method_title, '@' ) );
										break;
								}
								?>
								<div class="tutor-withdrawals-method">
									<div class="tutor-withdrawals-method-icon">
										<img src="<?php echo esc_url( isset( $method_icons[ $method_key ] ) ? $method_icons[ $method_key ] : '' ); ?>" />
									</div>
									<div class="tutor-withdrawals-method-name">
										<div class="withdraw-method-name tutor-fs-6 tutor-fw-medium tutor-color-black">
											<?php echo esc_html( tutor_utils()->avalue_dot( 'withdraw_method_name', $method_data ) ); ?>
										</div>
										<div class="tutor-fs-7 tutor-color-muted">
											<?php echo esc_html( $method_title ); ?>
										</div>
									</div>
								</div>
							</td>
							<td>
								<span class="tutor-color-black">
									<?php
										echo esc_attr( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $withdraw_history->created_at ) ) );
									?>
								</span>
							</td>
							<td>
								<div class="tutor-fs-7 tutor-fw-medium tutor-color-black">
									<?php echo tutor_utils()->tutor_price( $withdraw_history->amount ); ?>
								</div>
							</td>
							<td>
								<span class="inline-image-text is-inline-block">
									<span class="tutor-badge-label
									<?php
									if ( $withdraw_history->status == 'approved' ) {
										echo 'label-success'; }
									?>
									<?php
									if ( $withdraw_history->status == 'pending' ) {
										echo 'label-warning'; }
									?>
									<?php
									if ( $withdraw_history->status == 'rejected' ) {
										echo 'label-danger'; }
									?>
									">
										<?php echo __( ucfirst( $withdraw_history->status ), 'tutor' ); ?>
									</span>
								</span>
							</td>
							<td>
								<?php
								if ( $withdraw_history->status !== 'approved' && isset( $status_message[ $withdraw_history->status ] ) ) {
									?>
									<span class="tool-tip-container">
										<div class="tooltip-wrap tooltip-icon tutor-mt-12">
											<span class="tooltip-txt tooltip-left">
												<?php echo esc_html( $status_message[ $withdraw_history->status ] ); ?>
											</span>
										</div>
									</span>
									<?php
								}
								?>
							</td>
						</tr>
						<?php
					}
					?>
				</tbody>
			</table>
		</div>
		<?php
	} else {
		tutor_utils()->tutor_empty_state( tutor_utils()->not_found_text() );
	}
	?>
</div>

<div class="tutor-mt-25">
	<?php 
		if($all_histories->count >= $per_page) {
			$pagination_data = array(
				'total_items' => $all_histories->count,
				'per_page'    => $per_page,
				'paged'       => $current_page,
			);

			tutor_load_template_from_custom_path(
				tutor()->path . 'templates/dashboard/elements/pagination.php',
				$pagination_data
			);
		}
	?>
</div>